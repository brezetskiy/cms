<?php
/**
 * Класс для создания таблиц с информацией в административном интерфейсе
 * @package Pilot
 * @subpackage Gallery
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2004, Delta-X ltd.
 */

/**
 * Создание таблиц с информацией в административном интерфейсе
 * @package CMS
 * @subpackage Gallery
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class CoolGallery  {
	
	/**
	 * Параметры вывода таблицы
	 * @access private
	 * @var array
	 */
	protected $param = array(
		'title' => '',
		'title_maxlen' => 20,
		'image_field' => ''
	);

	
	/**
	* Конструктор класса
	* @param string $db_name
	* @param string $data_query
	* @param int $rows_per_page
	* @param string $table_name
	* @return object
	*/
	public function __construct($DBServer, $data_query, $table_name = '') {
		global $DB;
		
		// этот класс не поддерживает разбиение на страницы
		$this->rows_per_page = $rows_per_page = 1000;
		
		// Шаблон таблицы, в которой выводятся данные
		$this->Template = new Template(SITE_ROOT.'templates/cms/admin/cms_cool_gallery');
		
		$this->construct($DBServer, $data_query, $rows_per_page, $table_name);
		
		/**
		 * Изменяем язык вывода значений колонок, делать это надо после того,
		 * как будут добавлены колонки сортировки и фильтрации, чтоб изменение
		 * языка затронуло весь SQL запрос.
		 */
		if (!empty($this->table_language) && $this->table_language != LANGUAGE_CURRENT) {
			$this->SQLParser->changeTableLanguage(LANGUAGE_CURRENT, $this->table_language);
		}
		
		/**
		 * Выполняем SQL запрос
		 */
		$this->data = $this->SQLParser->execQuery($this->view_start, $this->rows_per_page);
		$this->total_rows = $this->SQLParser->total_rows;
		
		/**
		 * Если удалить в таблице на второй странице все ряды и при этом при удалении на странице небыло
		 * значка _event_insert_id, то нашему взору откроется пустая таблица.
		 * А такого быть не должно. 
		 */
		if ($this->view_start >= $this->total_rows) {
			$this->view_start = 0;
			$this->current_page = 0;
			$this->data = $this->SQLParser->execQuery($this->view_start, $this->rows_per_page);
			$this->total_rows = $this->SQLParser->total_rows;
		}
		$this->Template->set('total_rows', $this->total_rows);
		$this->Template->setGlobal('parent_id', globalVar($_GET[$this->table['parent_field_name']], 0));
	}
	
	/**
	 * Конструктор
	 *
	 * @param DB $DBServer
	 * @param string $data_query
	 * @param int $rows_per_page
	 * @param string $table_name
	 */
	protected function construct(DB $DBServer, $data_query, $rows_per_page = CMS_VIEW, $table_name = '') {
		global $DB;
		
		$rows_per_page = 1000;
		
		// База данных, в которую должен быть отправлен запрос
		$this->DBServer = $DBServer;
		
		$this->SQLParser = new SQLParserMySQLi($DBServer, $data_query);
		
		/**
		 * Определяем имя таблицы
		 */
		if (empty($table_name)) {
			$table_name = $this->SQLParser->getTableName();
		}
		
		// Определяем id таблицы
		$query = "
			SELECT tb_table.id 
			FROM cms_table AS tb_table
			INNER JOIN cms_db AS tb_db ON tb_db.id=tb_table.db_id
			WHERE 
				tb_table.name='".$table_name."'  
				AND tb_db.alias='".$this->DBServer->db_alias."'
		";
		
		$table_id = $DB->result($query);
		if ($DB->rows == 0) {
			// Информация о таблице %s не введена в таблицу CMS_TABLES
			trigger_error(cms_message('CMS', 'Информация о таблице %s не введена в таблицу CMS_TABLES', $table_name), E_USER_ERROR);
		}
		
		// Определяем информацию о таблице
		$this->table = cmsTable::getInfoById($table_id);
		$this->Template->setGlobal('table', $this->table);
		$this->param['title'] = $this->table['title'];
		
		// Определяем информацию о колонках в таблице
		$this->fields = cmsTable::getFields($table_id);
		
		// Язык, на котором выводятся значения колонок
		// $this->table_language = globalVar($_GET['_tb_language'][$this->table['id']], $this->table['default_language']);
		$this->table_language = globalVar($_GET['_tb_language_'.$this->table['id']], $this->table['default_language']);
		$this->Template->set('table_language', $this->table_language);

		// Строка, с которой начинается вывод
		$this->view_start = 0;
		
		$this->Template->iterate('/hidden_field/', null, array('name' => $this->table['parent_field_name'], 'value' => globalVar($_GET[$this->table['parent_field_name']], 0)));
		$this->Template->iterate('/hidden_field/', null, array('name' => '_table_id', 'value' => $this->table['id']));
		
		// Добавляем параметры, которые передаются методом get
		$get = $_GET;
		unset($get['_start['.$this->table['id'].']']);
		unset($get['_REWRITE_URL']);
		unset($get['_event_insert_id']);
		unset($get['_event_table_id']);
		unset($get['_event_type']);
		$get = http_build_query($get);
		$this->Template->setGlobal('get_vars', $get);
	}
	
	/**
	 * Изменение свойства таблицы
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setParam($name, $value) {
		if (!isset($this->param[$name])) {
			trigger_error(cms_message('CMS', 'Направильно задано свойство таблицы: %s. Возможные значения: %s.', $name, implode(',', array_keys($this->param))), E_USER_WARNING); 
		} elseif (gettype($this->param[$name]) != gettype($value)) {
			trigger_error(cms_message('CMS', 'Неправильно указан параметр %s', $name), E_USER_WARNING); 
		} elseif ($name == 'prefilter' && !function_exists($value)) {
			trigger_error(cms_message('CMS', 'Указанная Вами функция "%s" - не существует.', $value), E_USER_ERROR);
		} else {
			$this->param[$name] = $value;
		}
	}
	
	/**
	* Метод, который выполняется после того, как указаны колонки, которые необходимо вывести
	* @param void
	* @return string
	*/
	public function display() {
		global $DB;
		$query = "select width, height from cms_image_size where uniq_name='cms_gallery'";
		$size = $DB->query_row($query);
		
		$this->Template->setGlobal('max_height', $size['height']+40);
		$this->Template->setGlobal('height', $size['height']);
		$this->Template->setGlobal('width', $size['width']);
		$this->Template->setGlobal('cell_height', $size['height']+40);
		$this->Template->setGlobal('cell_width', max(160, $size['width']+20));
		$this->Template->setGlobal('table', $this->table);
		$this->Template->setGlobal('param', $this->param);
		$this->Template->set('table_title', $this->param['title']);
		$this->Template->set('image_field', $this->param['image_field']);
		
		/**
		 * Тело таблицы
		 */
		$max_height = 0;
		$rows = array();
		if ($this->total_rows > 0) {
			reset($this->data);
			while(list($index, $row) = each($this->data)) {

				$file = Uploads::getFile($this->table['name'], $this->param['image_field'], $row['id'], $row['photo']);
				if (in_array($row['photo'], array('flv', 'mp3', 'mp4')) && file_exists($file)) {
					$thumb_file = SITE_ROOT.'design/cms/img/'.$row['photo'].'.png';
				}

				if ($row['photo'] == 'flv') {
					$row['thumb_url'] = '/img/gallery/video.png';
				} else {
					$row['thumb_url'] = '/i/cms_gallery/'.substr($file, strlen(UPLOADS_ROOT));
				}
				$row['description_show'] = $this->formatDescription($row['description']);
				$row['description'] = htmlspecialchars($row['description'], ENT_QUOTES);
				$row['extension'] = $row['photo'];
				$row['counter'] = $index;
				$row['url'] = Uploads::getURL($file);
				$this->Template->iterate('/image/', null, $row);
			}
			
		}
		
		/**
		 * Парсим и выводим шаблон
		 */
		return $this->Template->display();
	}
	
	/**
	 * Формирует описание картинки для вывода в галерее
	 *
	 * @param string $description
	 * @return string
	 */
	public static function formatDescription($description) {
		$maxlen = 13;
		$return = $description;
		if (strlen($return)>$maxlen) {
			$return = substr($return, 0, $maxlen-3).'...';
		}
		$return = htmlspecialchars($return, ENT_QUOTES);
		if (empty($return)) {
			$return = '<i>нет описания</i>';
		}
		return $return;
	}
	
}
?>