<?php
/**
* Класс обработки событий по удалению рядов из БД
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* Класс обработки событий по удалению рядов из БД, также удаляет связанные с таблицей файлы
* @package CMS
* @subpackage CMS
*/
Class cmsEditDel {
	
	/**
	 * Столбцы и их значения, которые надо удалять, в формате SQL
	 * @var array 
	 */
	private $delete_data = array();
	
	/**
	 * Столбцы и их значения, которые необходимо удалять в виде массива
	 * @var array
	 */
	private $delete_array = array();
	
	/**
	 * Старые значения ряда
	 * @var array
	 */
	private $OLD = array();
	
	
	/**
	 * Соединение с БД
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * Информация о колонках
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * Информация о ключах в таблице
	 * @var array
	 */
	private $keys = array();
	private $field_key = array();
	
	/**
	 * Структура колонок в таблице
	 * @var aray
	 */
	private $columns_schema = array();
	
	/**
	 * Информация о таблице
	 * @var array
	 */
	private $table = array();
	
	/**
	 * Массив через который передаются данные между add_pre и add_post
	 * или же для del_pre и del_post триггерами. Значения берутся и выставляются
	 * через $this->triggerGet() и $this->triggerSet()
	 * @var array
	 */
	private $trigger_data = array();
	
	/**
	 * Путь к файлам-триггерам
	 * @var string
	 */
	private $triggers_root = '';
	
	/**
	 * Поля типа UPLOAD
	 * @var array
	 */
	private $upload_fields = array();
	
	/**
	* Конструктор класса
	*
	* @param int $table_id уникальный номер таблицы
	* @param array $data - информация о том, какие строки удалять
	* @return object
	*/
	public function __construct($table_id, $data) {
		
		$this->table = cmsTable::getInfoById($table_id);
		$this->triggers_root = TRIGGERS_ROOT . $this->table['triggers_dir'];
		$this->fields = cmsTable::getFields($table_id); 
		$this->DBServer = db::factory($this->table['db_alias']);
		
		/**
		 * Определяем имена UPLOAD полей
		 */
		reset($this->fields);
		while(list($field, $val) = each($this->fields)) {
			if ($val['field_type'] == 'file')
				$this->upload_fields[] = $field;
		}
		
		reset($data);
		while(list($field, $value) = each($data)) {
			$this->delete_data[] = (is_array($value)) ?
				"`".$field."` IN ('".implode("', '", $value)."')":
				"`".$field."`='".$value."'";
				
			$this->delete_array[$field] = (is_array($value)) ? $value : array($value);
		}
	}
	
	/**
	* Удаляет связанные с таблицей файлы
	* 
	* @param array $id_array
	* @return void
	*/
	private function deleteUploadedFiles($id_array) {
		$fields = Filesystem::getDirContent(SITE_ROOT.'uploads/'.$this->table['name'].'/', true, true, false);
		reset($id_array);
		while(list($id,) = each($id_array)) {
			reset($fields); 
			while (list(,$field) = each($fields)) { 
				$path = Uploads::getStorage($this->table['name'], $field, $id);
				
				// Ошибка в определении пути
				if ($path === false) continue;
				
				// Удаляем картинки
				if (is_dir(UPLOADS_ROOT.$path.'/'))  {
					Filesystem::delete(UPLOADS_ROOT.$path.'/');
					Action::setLog(cms_message('CMS', 'Удалена директория с картинками %s.', $path.'/'));
				}
			}				
		}
	} 

	/**
	 * Удаляет ряд из БД и возвращает его id
	 * @param void
	 * @return array
	 */
	public function dbChange () {
		global $DB;
		
		if ($this->table['table_type'] == 'VIEW') {
			return 0;
		}
		
		if (!is_array($this->delete_data) || empty($this->delete_data)) {
			Action::setError(cms_message('CMS', 'Не указаны поля, на базе которых будет происходить удаление рядов.'));
			Action::onError();
		}
		
		/**
		* Игнорируем таблицы, у которых нет поля id
		* к примеру это могут быить  InnoDB таблицы связанные по внешнему ключу с подержкой целостности
		* и они обрабатываются на уровне сервера БД. Такой таблицей является очередь сообщений в рассылке
		* почты. Мгнорируем их потому, что этот метод пытается определить id рядов, которые идут на удаление
		* и только после того, как эти ряды будут определены - удаляет их.
		*/
		if (!isset($this->fields['id'])) {
			return array();
		}
		
		/**
		 * Проверка на наличие поля id сделана для того, что б в удалении могли участвовать таблицы, у которых
		 * нет поля id. Это необходимо для таблиц которые выступают как связь N:N
		 */
		if (isset($this->fields['id'])) {
			$query = "
				SELECT * 
				FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE ".implode(' AND ', $this->delete_data);
			$delete_id = $this->DBServer->query($query, 'id');
		
			/**
			 * Событие перед удалением данных из таблицы pre trigger
			 */
			if (is_file($this->triggers_root . 'delete_before.act.php')) {
				reset($delete_id);
				while (list($current_id, $this->OLD) = each($delete_id)) {
					include($this->triggers_root . 'delete_before.act.php');
				}
			}
		
			/**
			 * После выполнения триггеров before.
			 */
			if ($this->table['id'] == $this->table['parent_table_id']) {
				if (empty($this->table['relation_table_name'])) {
					trigger_error("Please define in `cms_table` name of relation table for recursive table `".$this->table['name']."`", E_USER_ERROR);
				}
				reset($delete_id);
				while(list($id,) = each($delete_id)) {
					$query = "CALL clean_relation('".$this->table['relation_table_name']."', '$id')";
					$DB->query($query);
				}
			}
		
			/**
			 * Удаляем связанные с таблицей файлы
			 */
			$this->deleteUploadedFiles($delete_id);
		}
				
		/**
		 * Сохраняем в логи список записей, которые будут удалены
		 */
		$query = "
			SELECT *
			FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
			WHERE ".implode(' AND ', $this->delete_data);
		$log = $this->DBServer->query($query);
		
		/**
		 * Удаляем строки из БД
		 */
		if (isset($this->fields['id'])) {
			$query = "
				DELETE FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE id IN (0".implode(", ", array_keys($delete_id)).")
			";
			Action::saveLog($query);
			$this->DBServer->delete($query);
		} else {
			$query = "
				DELETE FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE ".implode(' AND ', $this->delete_data);
			Action::saveLog($query);
			$this->DBServer->delete($query);
		}
		
		
		/**
		 * Записываем информацию о столбцах, которые были удалены
		 */
		Action::saveLog('Rows was deleted: '.serialize($log));
		unset($log);
		

		/**
		 * Фиксируем изменения в CVS
		 */
		if ($this->table['use_cvs'] == 'true' && isset($this->fields['id'])) {
			$insert = array();
			reset($delete_id); 
			while (list($current_id,) = each($delete_id)) {
				 $insert[] = "('".$_SESSION['auth']['id']."', '".$this->table['id']."', 'delete', '$current_id')";
			}
			if (!empty($insert)) {
				$query = "
					insert into cvs_db_transaction (admin_id,table_id,event_type,row_id) 
					values ".implode(",", $insert)."
				";
				$DB->insert($query);
			}
		}
		
		/**
		* Формируем ответ системы
		*/
		if ($this->DBServer->affected_rows == -1) {
			Action::setError(cms_message('CMS', 'Запрос не выполнен. SQL сервер вернул ошибку - %s.', $this->DBServer->error()));
			Action::onError();
		} else {
			Action::setLog(cms_message('CMS', '%s, удалено %d ряда(ов).', $this->table['title'], $this->DBServer->affected_rows));
		}
		
		/**
		* Событие после удаления данных из таблицы
		*/
		if (is_file($this->triggers_root . 'delete_after.act.php')) {
			reset($delete_id);
			while (list($current_id, $this->OLD) = each($delete_id)) {
				include($this->triggers_root . 'delete_after.act.php');
			}
		}
		
		// Обновляем поисковый индекс
		if (is_module('Search') && count($delete_id) > 0) {
			Search::delete($this->table['name'], array_keys($delete_id));
		}
		
		return array_keys($delete_id);
	}
}
?>