<?php
/**
 * Класс для создания таблиц с информацией в административном интерфейсе
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2006, Delta-X ltd.
 */

/**
 * Создание таблиц с информацией в административном интерфейсе
 * @package CMS
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */

/**
 * Создание HTML форм для редактирования данных в административном интерфейсе
 * @package CMS
 * @subpackage CMS
 */
class cmsShowEdit {
	
	/**
	* Шаблон
	* @var object
	*/
	private $Template;
	
	/**
	* Параметры текущего ряда
	* @var array
	*/
	private $current_row_param = array();
	
	/**
	* Данные для заполнения полей
	* @var array
	*/
	private $data = array();
	
	/**
	 * Поля, значение которых равно null
	 * @var array
	 */
	private $null_values = array();
	
	/**
	* Путь к разделу в который переходим после Submit формы и возникновения ошибки
	* @var string
	*/
	private $return_error;
	
	/**
	* Информация о таблице
	* @var array
	*/
	private $table = array();
	
	/**
	* Информация о колонках
	* @var array
	*/
	private $fields = array();
	
	/**
	 * Соединенеие с БД
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * Значения, которые будут отображены во внешнем ключе
	 * @var array
	 */
	private $fkey_data = array();
	
	/**
	 * Флаг, говорящий о том, что создается копия сушествующей в таблице записи
	 * @var bool
	 */
	private $copy = false;
	
	/**
	 * Список модулей, установленных в системе.
	 *
	 * @var array
	 */
	private $modules = array();
	
	/**
	 * Страница, на которой сейчас находимся
	 * @var int
	 */
	protected $current_page = 0;
	
	/**
	 * Кнопки с событиями
	 * @var array
	 */
	protected $events = array();
	
	
	/**
	 * Номер строки, котороя была добавлена
	 * @var int
	 */
	protected $inserted_id = -1;
	
	/**
	 * SQL запрос, разобранный по частям, в качестве ключа указаны рператоры SQL
	 * @var array
	 */
	protected $parsed_sql = array();
	
	/**
	 * Количество рядов, которые необходимо вывести на одной странице
	 * @var int
	 */
	protected $rows_per_page = CMS_VIEW;
	
	/**
	 * Необходимо ли имскать страницу, на которой находится запись с id=$this->insert_id
	 * @var bool
	 */
	protected $search_page = false;
	

	/**
	 * На каком языке выводить значения колонок. Если значение пустое, то обработчик не запускается
	 * @var string
	 */
	protected $table_language = '';
		
	/**
	 * Общее количество рядов в таблице, без условия LIMIT
	 * @var int
	 */
	public $total_rows = 0;
	
	/**
	 * Строка с которой начинается вывод
	 * @var int $view_start
	 */
	protected $view_start = 0;
	
	/**
	 * Подсчет экземпляров таблиц cmsShow
	 * @var int
	 */
	static protected $instance_counter = 0;
	
	/**
	 * Номер текущего экземпляра класса cmsShow
	 * @var int
	 */
	protected $instance_number = 0;
	
	/**
	 * Директория в которой будут храниться закачанные файлы до того, как 
	 * не будет присвоен ряду id
	 *
	 * @var string
	 */
	private $tmp_dir = '';
	
	/**
	 * Флаг обновления нескольких записей
	 *
	 * @var bool
	 */
	private $is_group_update = false;
	
	/**
	 * Выделение id из свойства $this->data[id] необходимо в случаях копируется ряд в таблице
	 * тогда мы определяем $this->data[id]=0, а функции ext_multiple по прежнему грузят данные из БД
	 * так как используют внешние подгружаемые поля и нам надо для них передвать правильное значение id.
	 *
	 * @var mixed
	 */
	private $id = 0;
	
	/**
	 * Уникальный номер разделителя
	 *
	 * @var int
	 */
	private $tmpl_devider = 0;
	
	/**
	 * Конструктор
	 * @param int $table_id
	 * @param string $id_list
	 * @return object
	 */
	public function __construct($table_id, $id_list, $copy = false) {
		global $DB, $TmplDesign;
		
		$this->Template = $TmplDesign;
		$this->copy = $copy;
		$this->id = (empty($id_list)) ? 0 : $id_list;
		
		$id_list = (empty($id_list)) ? array() : preg_split("/[^\d]+/", $id_list, -1, PREG_SPLIT_NO_EMPTY);
		$this->is_group_update = (count($id_list) > 1) ? true : false;
		
		/**
		 * Определяем структуру БД
		 */
		$this->table = cmsTable::getInfoById($table_id); 
		$this->fields = cmsTable::getFields($table_id);
		$this->DBServer = DB::factory($this->table['db_alias']);
		
		// Директория для хранения файлов
		$this->tmp_dir = (isset($_SESSION['ActionError']['tmp_dir'])) ?
			$_SESSION['ActionError']['tmp_dir']:
			Auth::getUserId().'/'.$this->table['name'].'/'.uniqid().'/';
			
		// Загружаем информацию с сессии или БД
		$this->data = $this->load($id_list);
		
		// Создаем шаблон
		$this->Template->set('title', $this->table['title']);
		$this->Template->setGlobal('return_path', globalVar($_GET['_return_path'], ''));
		$this->Template->setGlobal('return_type', globalVar($_GET['_return_type'], 'popup'));
		$this->Template->setGlobal('return_anchor', globalVar($_GET['_return_anchor'], ''));
		$this->Template->setGlobal('tmp_dir', $this->tmp_dir);
		$this->Template->setGlobal('id', ($this->is_group_update) ? 0 : implode(",", $id_list));
		$this->Template->setGlobal('table_name', $this->table['name']);
		$this->Template->setGlobal('table_id', $this->table['id']);
		$this->Template->setGlobal('no_refresh', globalVar($_COOKIE['no_refresh'], 0));
		if ($this->is_group_update) {
			$this->Template->iterate('/hidden/', null, array('name' => $this->table['id'].'[id]', 'value' => implode(",", $id_list)));
		}
	}
	
	/**
	 * Возвращает информацию
	 * @param void
	 * @return string
	 */
	public function show() {
		return $this->Template->display();
	}
	
	
	/**
	 * Если возникла ошибка, все данные добавляеются в сессию, а здесь мы их выводим,
	 * предварительно очистив от слешей, которые добавляются при отправке данных
	 *
	 * @return array
	 */
	private function loadFromError() {
		
		$data = $_SESSION['ActionError'][$this->table['id']];

		/**
		 * Разбираем dummie_fields, если по умолчанию есть галочка, но мы ее снимаем,
		 * то после ошибки она будет опять включена по умолчанию.
		 */
		if (isset($data['_dummie_fields_']) && is_array($data['_dummie_fields_'])) {
			reset($data['_dummie_fields_']);
			while (list($key, $val) = each($data['_dummie_fields_'])) {
				if (!isset($data[$key])) {
					$data[$key] = $val;
				}
			}
		}
		
		/**
		 * Разбираем NULL поля
		 */
		if (isset($data['_null_']) && is_array($data['_null_'])) {
			reset($data['_null_']); 
			while (list($field,) = each($data['_null_'])) {
				$this->null_values[$field] = 'true';
			}
		}
		unset($data['_null_']);
		unset($data['_dummie_fields_']);
		unset($_SESSION['ActionError'][$this->table['id']]);
		
		return $data;
	}
	
	/**
	 * Создается новая запись в таблице
	 * 1. Берём данные из последней записи в таблице для полей с stick = true
	 * 2. Берём переданные переменные
	 *
	 * @return array
	 */
	private function loadFromGET() {
		global $DB;
		
		$data = $this->loadStick();
		$get = $_GET;
		
		// Обрабатываем переданные GET методом данные
		reset($get); 
		while (list($key,$val) = each($get)) {
			 if (!is_array($val) && isset($this->fields[$key])) {
			 	$data[$key] = urldecode($val);
			 }
		}
		
		reset($data);
		while (list($key,$val) = each($data)) {
			if (!is_null($val) && !is_array($val)) { 
				$data[$key] = htmlspecialchars($val);
			}
		}
		
		// определяем значения NULL
		$query = "select name from cms_field_static where table_id='{$this->table['id']}' and column_default is null and is_real=1 and is_nullable=1";
		$null = $DB->fetch_column($query);
		reset($null);
		while (list(,$field_name) = each($null)) {
			if (!isset($data[$field_name])) {
				$data[$field_name] = null;
			}
		}
		

		return $data;
	}
	
	/**
	 * Загружает значения, которые были вставлены в последнюю запись
	 *
	 * @return array
	 */
	private function loadStick() {
		global $DB;
		
		if (!isset($this->fields['id'])) return array();
		
		
		$query = "select id from `".$this->table['name']."` order by id desc limit 1";
		$id = $this->DBServer->result($query);
		if (empty($id)) $id = 0;
		
		$data = $this->loadFromTable($id);
		reset($data);
		while (list($field_name,) = each($data)) {
			if (!$this->fields[$field_name]['stick']) {
				unset($data[$field_name]);
			}
		}
		return $data;
	}
	
	/**
	 * Загрузка данных с существующей таблицы
	 * 
	 * @param int $id
	 * @return array
	 */
	private function loadFromTable($id) {
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			if (!$field['is_real']) continue;
			if ($field['data_type'] == 'date' && $field['field_type'] != 'hidden') {
				$select[] = "DATE_FORMAT(`$field_name`, '".LANGUAGE_DATE_SQL."') AS `$field_name`";
			} elseif ($field['data_type'] == 'datetime' && $field['field_type'] != 'hidden') {
				$select[] = "DATE_FORMAT(`$field_name`, '".LANGUAGE_DATETIME_SQL.":%s') AS `$field_name`";
			} else {
				$select[] = "`$field_name`";
			}
		}
		
		$query = "SELECT ".implode(", ", $select)." FROM `".$this->table['name']."` WHERE id='$id'";
		$data = $this->DBServer->query_row($query);
		
		// Копия
		if ($this->copy == true) $data['id'] = 0;
		
		// Экранирование спецсимволов, делать перед тем, как загрузятся 
		reset($data);
		while (list($key,$val) = each($data)) {
			// если через htmlspecialchars пропустить NULL то он вернёт пустую строку и мы дальше не сможем опредилить что NULL, а что нет
			if (!is_null($val)) { 
				$data[$key] = htmlspecialchars($val);
			}
		}
		
		// Загружаем значения внешних ключей
		$data = array_merge($data, $this->loadFKey($id));
		return $data;
	}
	
	/**
	 * Загружает информацию
	 * @param int $id
	 * 
	 * @return array
	 */
	private function load($id) {
		global $DB;
		$data = array();
		// Обрабатываем значения, которые необходимо выводить
		if (isset($_SESSION['ActionError'][$this->table['id']]) && !empty($_SESSION['ActionError'][$this->table['id']])) {
			$data = $this->loadFromError();
		} elseif (empty($id)) {
			$data = $this->loadFromGET();
		} elseif (count($id) == 1) {
			$data = $this->loadFromTable(implode(",", $id));
		}
		

		/**
		 * Определяем, какие значения равны null. Определять значение null или не Null прямо из массива $this->data
		 * невозможно из-за того что такие поля как даты всё равно должны иметь значение, на случай если пользователь уберёт галочку NULL,
		 * (там должна быть отображена текущая дата). Даты устанавливаются ниже.
		 * 
		 * Для полей, которые вернулись с сессией после того, как произошла ошибка. обработка идеёт немного выше и не
		 * конфликтует с данным обрабочиком NULL значений
		 */
		reset($data); 
		while (list($field,$value) = each($data)) { 
			 if (is_null($value)) {
			 	$this->null_values[$field] = 1;
			 }
		}
		
		// Обработка данных
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			if (!$field['is_real'] || isset($data[$field_name])) {
				continue;
			}
			
			if ($field['data_type'] == 'date' && $field['field_type'] == 'hidden') {
				// значение, которое пойдет прямо в SQL запрос
				$data[$field_name] = date('Y-m-d');
			} elseif ($field['data_type'] == 'date') {
				$data[$field_name] = date('d.m.Y');
			} elseif ($field['data_type'] == 'datetime' && $field['field_type'] == 'hidden') {
				// значение, которое пойдет прямо в SQL запрос
				$data[$field_name] = date('Y-m-d H:i:s');
			} elseif ($field['data_type'] == 'datetime') {
				$data[$field_name] = date('d.m.Y H:i:s');
			} elseif ($field['data_type'] == 'time') {
				$data[$field_name] = date('H:i:s');
			} else {
				$data[$field_name] = $field['column_default'];
			}
		}
		
		return $data;
	}
	
	/**
	 * Переопределение параметров поля
	 *
	 * @param string $field
	 * @param string $param
	 * @param string $value
	 */
	public function overrideFieldParam($field, $param, $value) {
		$this->fields[$field][$param] = $value;
	}
	
	/**
	 * Устанавливает значение, которое будет отображено во внешнем ключе
	 *
	 * @param string $field_name
	 * @param array $data (id, parent, real_id, name)
	 */
	public function setFKeyData($field_name, $data) {
		$this->fkey_data[$field_name] = $data;
	}
	
	
	/**
	 * Загружает значения для связей n:n (через внешнюю таблицу)
	 * @param void
	 * @return void
	 */
	private function loadFKey($id) {
		global $DB;
		
		$return = array();
		
		reset($this->fields);
		while (list($field_name, $field) = each($this->fields)) {
			if (empty($field['fk_link_table_id'])) {
				continue;
			}
			$info = cmsTable::getFkeyNNInfo($field['table_id'], $field['fk_table_id'], $field['fk_link_table_id']);
			$query = "SELECT `$info[select_field]` FROM `$info[from_table]` WHERE `$info[where_field]`='$id'";
			$return[$field_name] = $DB->fetch_column($query);
		}
		return $return;
	}
	
	/**
	* Обрабатывает поля
	* @param void
	* @return void
	*/
	public function parseFields() {
		global $DB;
		
		/**
		 * Так как поле типа Деньги состоит из двух колонок в таблице, и выводятся они в одной строке,
		 * то необходимо заблокировать вывод второго поля, после того, как оно будет выведено
		 */
		$skip_money_fields = array();
		
		// Ярлыки, в которых содержатся поля с ошибками
		$error_folders = array();
		$current = 'default';
		reset($this->fields); 
		while (list(,$field) = each($this->fields)) {
			if ($field['field_type'] == 'devider') {
				$current = $field['name'];
			}
			if (isset($_SESSION['cmsEditError'][ $field['id'] ])) {
				$error_folders[$current] = 1;
			}
		}
			
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			// Пропускаем поля которые не меняются при групповом редактировании
			if ($this->is_group_update && !$field['group_edit']) {
				continue;
			}
			
			// Игнорируем priority поле, оно не должно обновлятся через редактирование
			if ($field_name == 'priority' || $field['data_type'] == 'timestamp') {
				continue;
			}
			
			/**
			 * Обрабатываем спрятанные поля, делать это надо до начала обработки любого из полей
			 * и до итерирования ряда шаблона.
			 * @todo hidden поля не могут содержать в себе значения NULL? - проверить это.
			 */
			if ($field['cms_type'] == 'hidden') {
				$this->showHidden($field_name);
				continue;
			}
			
			/**
			 * Итерируем новый ряд в шаблоне
			 */
			$this->current_row_param = array(
				'input_id' => $this->table['name'].'_'.$field['name'],
				'input_name' => $this->table['id'].'['.$field['name'].']',
				'field' => $field_name,
				'id' => $field['id'], 
				'title' => (empty($field['title'])) ? $field['name'] : $field['title'], 
				'comment' => $field['comment'],
				'fk_table_id' => $field['fk_table_id'],
				'fk_table_type' => (empty($field['fk_table_id'])) ? '': $DB->result("SELECT UPPER(_table_type) FROM cms_table WHERE id='$field[fk_table_id]'"),
				'is_nullable' => $field['is_nullable'],
				'class' => (isset($_SESSION['cmsEditError'][ $field['id'] ])) ? 'error' : '',
				'error' => (isset($_SESSION['cmsEditError'][ $field['id'] ])) ? $_SESSION['cmsEditError'][ $field['id'] ] : ''
			);
			
			if ($field['field_type'] == 'devider') {
				$field['class'] = (isset($error_folders[$field['name']])) ? "error" : "";
				$this->tmpl_devider = $this->Template->iterate('/devider/', null, $field);
				continue;
			} elseif (empty($this->tmpl_devider)) {
				$class = (isset($error_folders['default'])) ? "error" : "";
				$this->tmpl_devider = $this->Template->iterate('/devider/', null, array('name' => 'default', 'title' => 'Главная', 'class' => $class));
			}
			
			// $this->current_row_param['title'] .= "<br><span class=comment>$field[cms_type]</span>";

			// Маркируем обязательные для заполнения поля
			if ($field['is_obligatory']) {
				$this->current_row_param['title'] = '<font color="red">*</font>' . $this->current_row_param['title'];
			}

			// Ставим флаг с языком
			if ($field['is_multilanguage'] && !is_file(SITE_ROOT.'design/cms/img/language/'.$field['language'].'.gif')) {
				$this->current_row_param['title'] .= ' ['.$field['language'].']';
			} elseif ($field['is_multilanguage']) {
				$this->current_row_param['title'] .= ' <img src="/design/cms/img/language/'.$field['language'].'.gif" width="16" height="12" border="0" alt="'.$field['language'].'" align="absmiddle">';
			}
			
			// Отмечаем поля, значение которых = null
			if ($field['is_real'] && is_null($this->data[$field_name]) && $field['is_nullable'] && is_null($field['column_default'])) {
				// Проверка $field[is_real] добавлена из-за того, что $x = null; isset($x) == false;
				// Поля, которые по умолчанию должны быть null
				$this->current_row_param['null_checked'] = 'checked';
				$this->Template->iterate('/onload/', null, array('function'=>"set_null('".$this->table['name']."_$field_name', true);"));
			
			} elseif (!isset($this->null_values[$field_name])) {
				// Поля, которые не установлены как NULL отображаются со снятой NULL галочкой
				$this->current_row_param['null_checked'] = '';
				
			} elseif (isset($this->null_values[$field_name]) && in_array($field['pilot_type'], array('date', 'time'))) {
				// Для дат, даже если установлено NULL значение программа автоматически формирует дату, которую увидит пользователь
				// если снимет галочку с NULL поля
				$this->current_row_param['null_checked'] = 'checked';
				$this->Template->iterate('/onload/', null, array('function'=>"set_null('".$this->table['name']."_$field_name', true);"));
				
			} elseif (isset($this->null_values[$field_name]) && (isset($this->data[$field_name]) && !empty($this->data[$field_name]))) {
				// Все поля, кроме дат, которые содержат значение, даже если у них установлено NULL будут отображены
				$this->current_row_param['null_checked'] = '';
				
			} else {
				// Остальные поля - это NULL
				$this->current_row_param['null_checked'] = 'checked';
				$this->Template->iterate('/onload/', null, array('function'=>"set_null('".$this->table['name']."_$field_name', true);"));
			}
			
			
			if ($field['cms_type'] == 'fk_nn_tree') {
				
				$data = cmsTable::loadInfoTree($this->fields[$field_name]['fk_table_id']);
				$this->showFKeyNN($field_name, $data);
				unset($data);
					
			} elseif ($field['cms_type'] == 'fk_nn_cascade') {
				
				$data = cmsTable::loadInfoCascade($this->fields[$field_name]['fk_table_id']);
				$this->showFKeyNN($field_name, $data);
				unset($data);
					
			} elseif ($field['cms_type'] == 'fk_nn_list') {
				
				$data = cmsTable::loadInfoList($this->fields[$field_name]['fk_table_id']);
				$this->showFKeyNNList($field_name, $data);
				unset($data);
					
			}  elseif ($field['cms_type'] == 'ext_multiple') {
				
				$this->showExtMultiple($field_name);
					
			} elseif ($field['cms_type'] == 'swf_upload') {
				
				$this->showSWFUpload($field_name);

			} elseif ($field['cms_type'] == 'money') {
				
				if (isset($skip_money_fields[$field_name])) {
					// пропускаем поля, которые уже выведены в административном интерфейсе
					continue;
				} else {
					// фиксируем то, что данное поле прошло вывод
					$skip_money_fields[$field['currency_field_name']] = 1;
				}
				$this->showMoney($field_name);
					
			} elseif ($field['cms_type'] == 'text') {
				
				$this->showText($field_name);
				
			} elseif ($field['cms_type'] == 'textarea') {
				
				$this->showTextarea($field_name);
				
			} elseif ($field['cms_type'] == 'checkbox_set') {
				
				$this->showCheckboxSet($field_name);
				
			} elseif ($field['cms_type'] == 'checkbox') {
				
				$this->showCheckbox($field_name);
				
			} elseif ($field['cms_type'] == 'radio') {
				
				$this->showRadio($field_name);
				
			} elseif ($field['cms_type'] == 'datetime') {
				
				$this->showDateTime($field_name);
				
			} elseif ($field['cms_type'] == 'date') {
				
				$this->showDate($field_name);
				
			} elseif ($field['cms_type'] == 'time') {
				
				$this->showTime($field_name);
				
			} elseif ($field['cms_type'] == 'fk_list') {
				$data = (isset($this->fkey_data[$field_name])) ? $this->fkey_data[$field_name] : cmsTable::loadInfoList($this->fields[$field_name]['fk_table_id']);
				$this->showFKeyList($field_name, $data);
				unset($data);
				
			} elseif ($field['cms_type'] == 'fk_cascade') {
				
				$data = (isset($this->fkey_data[$field_name])) ? $this->fkey_data[$field_name] : cmsTable::loadInfoCascade($this->fields[$field_name]['fk_table_id']);
				$this->showFKey($field_name, $data);
				unset($data);
				
			} elseif ($field['cms_type'] == 'fk_tree') {
				
				$data = (isset($this->fkey_data[$field_name])) ? $this->fkey_data[$field_name] : cmsTable::loadInfoTree($this->fields[$field_name]['fk_table_id']);
				$this->showFKey($field_name, $data);
				unset($data);
				
			} elseif ($field['cms_type'] == 'decimal') {
				
				$this->showText($field_name);

			} elseif ($field['cms_type'] == 'file') {
				
				$this->showFile($field_name);
				
			} elseif ($field['cms_type'] == 'password') {
				
				$this->showPassword($field_name);
				
			} elseif ($field['cms_type'] == 'fk_ext_list') {
				
				$this->showExtList($field_name);
				
			} elseif ($field['cms_type'] == 'fk_ext_cascade') {
				
				$this->showExtSelect($field_name);
				
			} elseif ($field['cms_type'] == 'fk_ext_tree') {
				
				$this->showExtSelect($field_name);
				
			} elseif ($field['cms_type'] == 'ajax_select') {
				
				$this->showAjaxSelect($field_name);
				
			} elseif ($field['cms_type'] == 'fixed_hidden') {
				
				// Не отображаем это поле, оно не будет передано в обработчик и не будет изменено 
				// при update
				
			} elseif ($field['cms_type'] == 'fixed_open') {
				
				$this->showHidden($field_name);
				$this->showFixedOpen($field_name);
				
			} elseif ($field['cms_type'] == 'html') {
				
				$this->showHTML($field_name);
				
			} else {
				
				$this->showErrorField($field_name);
//				x($field);
//				trigger_error(cms_message('CMS', "Невозможно определить формат вывода поля %s. Более подробную информацию смотрите в описании таблицы `%s`.", $field_name, $this->table['name']), E_USER_ERROR);
			}
		}
	}
	
	/**
	* Ставит поля, которые могут быть пустыми и не передаваться браузером в скрипт-обработчик
	* @param string $field_name
	* @param string value
	* @return void
	*/
	private function dummieField($field_name, $value) {
		$this->Template->iterate('/hidden/', null, array('name' => $this->table['id'].'[_dummie_fields_]['.$field_name.']', 'value' => $value));
	}

	/**
	* Поле checkbox
	* @param string $field_name
	* @return void
	*/
	private function showCheckbox ($field_name) {
		$tmpl_row = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array('row' => $this->current_row_param));
		if ($this->fields[$field_name]['column_type'] == 'tinyint(1)') {
			$this->dummieField($field_name, 0);
			$checked = ($this->data[$field_name] == 1) ? 'checked' : '';
			$value = 1;
		} else {
			$this->dummieField($field_name, 'false');
			$checked = ($this->data[$field_name] == 'true') ? 'checked' : '';
			$value = 'true';
		}
		$this->Template->iterate('/devider/row/checkbox/', $tmpl_row, array(
			'type' => 'checkbox', 
			'value' => $value, 
			'row' => $this->current_row_param, 
			'checked' => $checked
			)
		);
	}
	
	/**
	* Поле EXT SELECT
	* @param string $field_name
	* @return void
	*/
	private function showExtSelect ($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'ext_select',
			'row' => $this->current_row_param,
			'text_value' => htmlspecialchars(cmsTable::showFK($this->fields[$field_name]['fk_table_id'], $this->data[$field_name])),
			'field_fk_table_id' => $this->fields[$field_name]['fk_table_id'],
			'value' => $this->data[$field_name])
		);
	}
	
	/**
	 * Поле BIG SELECT
	 * @param string $field_name
	 * @return void
	 */
	private function showExtList($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'ext_list',
			'row' => $this->current_row_param,
			'text_value' => htmlspecialchars(cmsTable::showFK($this->fields[$field_name]['fk_table_id'], $this->data[$field_name])),
			'field_fk_table_id' => $this->fields[$field_name]['fk_table_id'],
			'value' => $this->data[$field_name])
		);
	}
	
	/**
	* Поле EXT SELECT MULTIPLE
	* @param string $field_name
	* @return void
	*/
	private function showExtMultiple($field_name) {
		global $DB, $TmplDesign;
		
		$info = cmsTable::getFkeyNNInfo($this->fields[$field_name]['table_id'], $this->fields[$field_name]['fk_table_id'], $this->fields[$field_name]['fk_link_table_id']);
		
		// Иногда поле отображается в виде перечня подгружаемых checkbox'ов
		$this->dummieField($field_name, '');		
		
		// Определяем название таблицы первого уровня, для редактируемой колонки
		$parent_tables = cmsTable::getParentTables($this->fields[$field_name]['fk_table_id']);
		$row = $this->current_row_param;
		
		$TmplDesign->iterate('/onload/', null, array('function' => 'extMultipleOpen_'.$field_name.'();'));
		$tmpl_row = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'field' => $field_name, // необходимо для создания уникального имени функции extMultipleOpen_$field_name
			'type' => 'ext_multiple', 
			'row' => $row,
			'field_fk_table_id' => $this->fields[$field_name]['fk_table_id'],
			'id' => $this->id)
		);
		
		$table = cmsTable::getInfoById($parent_tables[0]);
		
		$global_param = array(
			'fk_table_id' => $this->fields[$field_name]['fk_table_id'],
			'master_table_id' => $this->table['id'],
			'code' => uniqid(),
			'field' => $field_name,
			'relation_table_name' => $info['from_table'],
			'relation_select_field' => $info['select_field'],
			'relation_parent_field' => $info['where_field'],
			'recursive' => ($table['id'] != $table['parent_table_id']) ? 'false' : 'true'
		);
		
		// определяем перечень элементов, которые необходимо разворачивать
		if ($table['cms_type'] == 'list') {
			
			// не рекурсивная таблица
			Misc::extMultipleOpen($this->DBServer, $this->id, $parent_tables, $info['from_table'], $info['select_field'], $info['where_field']);
			$query = "
				SELECT
					tb_table.id,
					tb_table.`$table[fk_show_name]` AS name,
					IF(tb_open.id IS NOT NULL, 'true', 'false') AS open
				FROM `$table[table_name]` AS tb_table
				LEFT JOIN tmp_open AS tb_open ON tb_open.id=tb_table.id
				ORDER BY tb_table.`$table[fk_order_name]` ASC
			";
			$data = $this->DBServer->query($query, 'id');
			
			$query = "select id from tmp_open";
			$open = $this->DBServer->fetch_column($query, 'id', 'id');
			
		} elseif (empty($table['relation_table_name'])) {
			
			trigger_error(cms_message('CMS', 'Для таблиц, внешний ключ которых указывает на себя необходимо определить таблицу, в которой будут храниться связи'), E_USER_ERROR);
		
		} else {
			
			$query = "select name from cms_table where id='".$this->fields[$field_name]['fk_link_table_id']."'";
			$fk_link_table_name = $DB->result($query);
			
			if (empty($fk_link_table_name)) {
				trigger_error(cms_message('CMS', 'Проверьте правильность указания внешнего ключа для колонки %s.%s', $this->table['name'], $field_name), E_USER_ERROR);
			}
			
			// Выводим текущие разделы
			$query = "
				SELECT id, `$table[fk_show_name]` AS name
				FROM `$table[table_name]`
				WHERE `$table[parent_field_name]`=0
				ORDER BY `$table[fk_order_name]` ASC
			";
			$data = $this->DBServer->query($query, 'id');
			
			// Определяем разделы, которые необходимо открыть, так как дочерние разделы содержат выделенные checkbox'ы
			$query = "
				SELECT distinct tb_optimized.parent as id
				FROM `$table[relation_table_name]` AS  tb_optimized
				INNER JOIN `$fk_link_table_name` AS tb_relation ON tb_optimized.id=tb_relation.`$info[select_field]`
				WHERE 
					tb_relation.`$info[where_field]`='".intval($this->id)."'
					AND tb_optimized.id<>tb_optimized.parent
					and tb_optimized.parent in (0".implode(",", array_keys($data)).")
			";
			$open = $this->DBServer->fetch_column($query, 'id', 'id');
			
			// Определяем, для каких разделов установлены галочки
			$query = "
				select distinct `$info[select_field]` as id
				from `$fk_link_table_name`
				where
					`$info[where_field]`='".$this->id."'
					and `$info[select_field]` in (0".implode(',', array_keys($data)).")
			";
			$checked = $this->DBServer->fetch_column($query, 'id', 'id');
		}
		reset($data);
		while(list($id, $row) = each($data)) {
			$row = array_merge($global_param, $row);
			$row['open'] = (isset($open[$id])) ? 'true' : 'false';
			$row['checked'] = (isset($checked[$id])) ? 'checked' : '';
			$this->Template->iterate('/devider/row/ext_multiple/', $tmpl_row, $row);
			if (isset($open[$id])) {
				$this->Template->iterate('/devider/row/open_ext_multiple/', $tmpl_row, $row);
			}
		}
	}

	/**
	* Поле Option, используется для вывода многозначных enum полей
	* @param string $field_name
	* @return void
	*/
	private function showRadio ($field_name) {
		global $DB;
	
		$tmpl_row = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array('row' => $this->current_row_param));
		$this->dummieField($field_name, '');
		$checked = (empty($this->data[$field_name])) ? $this->fields[$field_name]['column_default'] : $this->data[$field_name];
		
		$query = "select name, title_".LANGUAGE_CURRENT." as title from cms_field_enum where field_id='".$this->fields[$field_name]['id']."' order by priority asc";
		$values = $DB->fetch_column($query);
		reset($values);
		while(list($name, $title) = each($values)) {
			$this->Template->iterate('/devider/row/radio/', $tmpl_row, array(
					'row' => $this->current_row_param, 
					'value' => $name, 
					'checked' => ($name == $checked) ? 'checked' : '', 
					'description' => (empty($title)) ? $name : $title
				)
			);
		}
	}

	/**
	 * Поле option
	 * @param string $field_name
	 * @return string
	 */
	private function showCheckboxSet($field_name) {
		global $DB;
		
		$tmpl_row = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array('row' => $this->current_row_param));
		$this->dummieField($field_name, '');
		
		$checked = (is_array($this->data[$field_name])) ? $this->data[$field_name] : preg_split('/,/', $this->data[$field_name], -1, PREG_SPLIT_NO_EMPTY);
		
		$query = "select name, title_".LANGUAGE_CURRENT." from cms_field_enum where field_id='".$this->fields[$field_name]['id']."' order by priority asc";
		$values = $DB->fetch_column($query);
		
		reset($values);
		while(list($name, $title) = each($values)) {
			$this->Template->iterate('/devider/row/checkboxset/', $tmpl_row, array(
					'row' => $this->current_row_param, 
					'value' => $name, 
					'description' => (empty($title)) ? $name : $title, 
					'checked' => (in_array($name, $checked)) ? 'checked' : ''
				)
			);
		}
	}

	/**
	* Создает элемент формы text
	* @param string $field_name
	* @return void
	*/
	private function showText ($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'text', 
				'row' => $this->current_row_param, 
				'value' => $this->data[$field_name], 
				'max_length' => $this->fields[$field_name]['max_length'], 
				'size' => ($this->fields[$field_name]['max_length'] < 50) ? intval($this->fields[$field_name]['max_length'] * 10).'px': '325px'
			)
		);
	}

	/**
	* Создает элемент формы password_md5
	* @param string $field_name
	* @return void
	*/
	private function showPassword ($field_name) {
		/**
		 * Обрабатываем поля passwd_md5
		 * Если для новой записи отправить данные, а они вернутся с ошибкой, то для
		 * спрятанного поля passwd_old будет установлено значение с вернувшимся, незахешированным паролем
		 * Повторная отпрака данных добавит незахешированный пароль в систему
		 */
		if (!isset($this->data[$field_name.'_old_password'])) {
			$old_passwd = $this->data[$field_name];
		} else {
			$old_passwd = $this->data[$field_name.'_old_password'];
		}
		
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type'=>'password',
			'row' => $this->current_row_param,
			'value'=>$this->data[$field_name],
			'max_length' => $this->fields[$field_name]['max_length'],
			'size' => ($this->fields[$field_name]['max_length'] < 50) ? intval($this->fields[$field_name]['max_length'] * 10).'px': '325px',
			'old_password' => $old_passwd)
		);
	}

	/**
	* Форма textarea
	* @param string $field_name
	* @return void
	*/
	private function showTextarea ($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'textarea',
			'row' => $this->current_row_param,
			'value' => $this->data[$field_name],
			'max_length' => $this->fields[$field_name]['max_length'])
		);
	}
	
	/**
	 * Выводит возможные значения для внешнего ключа n:n
	 * 
	 * @param string $field_name
	 * @param array $data
	 */
	private function showFKeyNN ($field_name, $data) {
		$this->dummieField($field_name, '');
		$selected = (isset($this->data[$field_name]) && !empty($this->data[$field_name])) ? $this->data[$field_name] : array();
		$Tree = new Tree($data, $selected);
		if (count($data) < 7) {
			$count_rows = 7;
		} elseif (count($data) > 20) {
			$count_rows = 20;
		} else {
			$count_rows = count($data);
		}
		
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'fk_nn',
			'row' => $this->current_row_param,
			'rows' => $count_rows,
			'tree' => $Tree->build())
		);
	}
	
	/**
	 * Выводит возможные значения для внешнего ключа n:n
	 * 
	 * @param string $field_name
	 * @param array $data
	 */
	private function showFKeyNNList ($field_name, $data) {
		$this->dummieField($field_name, '');
		
		if (count($data) < 7) {
			$count_rows = 7;
		} elseif (count($data) > 20) {
			$count_rows = 20;
		} else {
			$count_rows = count($data);
		}
		
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'fk_nn',
			'row' => $this->current_row_param,
			'rows' => $count_rows,
			'options' => $data,
			'selected' => isset($this->data[$field_name]) ? $this->data[$field_name] : array()
		));
	}
	
	/**
	 * Создает иерархию подразделов в виде <select>
	 * 
	 * @param string $field
	 */
	private function showFKey($field_name, $data) {
		$selected = (isset($this->data[$field_name]) && !empty($this->data[$field_name])) ? array($this->data[$field_name]) : array();
		$Tree = new Tree($data, $selected);
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'fk',
			'row' => $this->current_row_param,
			'tree' => $Tree->build(),
			'null_text' => ($this->is_group_update) ? cms_message('CMS', 'Без изменений') : cms_message('CMS', 'Сделайте выбор...')
		));
		
	}
	
	/**
	 * Создает иерархию подразделов в виде <select>
	 * 
	 * @param string $field_name
	 * @param array $data
	 */
	private function showFKeyList($field_name, $data) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'type' => 'fk',
			'row' => $this->current_row_param,
			'options' => $data,
			'selected' => $this->data[$field_name],
			'value' => isset($data[$this->data[$field_name]]) ? $data[$this->data[$field_name]] : '',
			'null_text' => ($this->is_group_update) ? cms_message('CMS', 'Без изменений') : cms_message('CMS', 'Сделайте выбор...')
		));
	}

	/**
	 * Создает форму для закачки файлов
	 * 
	 * @param string $field_name
	 */
	private function showFile ($field_name) {
		if (is_array($this->data[$field_name]) && isset($this->data[$field_name]['extension'])) {
			// Обработка данных, которые возвращены после возникновения ошибки
			$this->data[$field_name] = $this->data[$field_name]['extension'];
		}
		
		// Если закачан файл, то выводим информацию о файле
		$file_exists = false;
		$file_type = '';
		$width = $height = 0;
		$file = Uploads::getFile($this->table['name'], $field_name, $this->id, $this->data[$field_name]);
//		$thumb = substr($file, 0, -(strlen($this->data[$field_name]) + 1)).'_thumb.jpg';
//		if (is_file($thumb)) {
//			$file = $thumb;
//		}
		
		if (!empty($this->data[$field_name]) && is_file($file)) {
			$file_exists = true;
			$size = getimagesize($file);
			if (!empty($size)) {
				// Закачана картинка
				$height = ($size[1] > 600) ? 600 : $size[1] + 15;
				$width = ($size[0] > 600) ? 600 : $size[0] + 17;
				$file_type = 'image';
			} else {
				// Закачано что-то другое
				$file_type = 'upload';
			}
		}
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type' => 'file',
				'row' => $this->current_row_param,
				'value' => $this->data[$field_name],
				'width' => $width,
				'height' => $height,
				'file_exists' => $file_exists,
				'file_type' => $file_type
			)
		);
	}
	
	/**
	 * Создает форму для закачки большого количества файлов
	 * 
	 * @param string $field_name
	 */
	private function showSWFUpload($field_name) {
		$this->Template->iterate('/swf_upload_var/', null, array('field' => $field_name));
		$this->Template->iterate('/swf_upload_constructor/', null, array('field' => $field_name));
		$tmpl = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array('type' => 'swf_upload', 'row' => $this->current_row_param));
		
		$uploads_root = (empty($this->id)) ?
			TMP_ROOT.$this->tmp_dir.$field_name.'/':
			UPLOADS_ROOT.Uploads::getStorage($this->table['name'], $field_name, $this->id);
		$files = Filesystem::getDirContent($uploads_root, true, false, true);
		$available = Filesystem::getDirContent(SITE_ROOT.'img/shared/ico/', false, false, true);
		$value = '';
		reset($files); 
		while (list(,$file) = each($files)) { 
			$extension = strtolower(Uploads::getFileExtension($file));
			$icon = (in_array($extension.'.gif', $available)) ? $extension : 'file';
			$file = iconv('UTF-8', CMS_CHARSET.'//IGNORE', $file);
			
			$this->Template->iterate('/devider/row/uploads/', $tmpl, array(
				'field' => $field_name,
				'filename' => basename($file),
				'icon' => $icon,
				'file_url' => substr($file, strlen(SITE_ROOT) - 1)
			));
		}
	}

	/**
	 * Форма с датой и временем
	 * 
	 * @param string $field_name
	 */
	private function showDateTime($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type' => 'datetime', 
				'row' => $this->current_row_param, 
				'value' => $this->data[$field_name]
			)
		);
	}
	
	/**
	 * Форма с датой
	 * 
	 * @param string $field_name
	 */
	private function showDate($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type' => 'date', 
				'row' => $this->current_row_param, 
				'value' => $this->data[$field_name]
			)
		);
	}


	/**
	* Форма со временем
	* @param string $field_name
	* @return string
	*/
	private function showTime ($field_name) {
		$tmpl_time = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'time',
				'row' => $this->current_row_param,
				'value' => $this->data[$field_name]
			)
		);
	}

	/*
	* Создает Hidden поле
	* @param string $name
	* @param string $this->data[$field_name]
	* @return string
	*/
	private function showHidden($field_name) {
		$this->Template->iterate('/hidden/', null, 
			array(
				'id' => $this->table['name'].'_'.$field_name, 
				'name'=> $this->table['id'].'['.$field_name.']',
				'value' => $this->data[$field_name]
			)
		);
	}
	
	/**
	 * Выводит поле типа Money
	 *
	 * @param string $field_name
	 */
	private function showMoney($field_name) {
		// Определяем поле currency и text
		if (!empty($this->fields[$field_name]['fk_table_id'])) {
			$currency_field = $field_name;
			$text_field = $this->fields[$field_name]['currency_field_name'];
		} else {
			$currency_field = $this->fields[$field_name]['currency_field_name'];
			$text_field = $field_name;
		}
		$this->current_row_param['text_field'] = $text_field;
		$this->current_row_param['currency_field'] = $currency_field;
		
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'money', 
				'row' => $this->current_row_param, 
				'text_value' => $this->data[$text_field], 
				'max_length' => $this->fields[$text_field]['max_length'], 
				'size' => ($this->fields[$field_name]['max_length'] < 50) ? intval($this->fields[$field_name]['max_length'] * 10).'px': '325px',
				'currency_data' => cmsTable::loadInfoList($this->fields[$currency_field]['fk_table_id']),
				'currency_id' => $this->data[$currency_field],
				'null_text' => ($this->is_group_update) ? cms_message('CMS', 'Без изменений') : cms_message('CMS', 'Сделайте выбор...')
			)
		);
	}
	
	/**
	 * Выводит поле типа "Подгружаемый список"
	 *
	 * @param string $field_name
	 */
	private function showAjaxSelect($field_name) {
		$this->Template->iterate('/onload/', null, array('function' => "AjaxSelect.init('{$this->table['id']}', '{$this->current_row_param['input_id']}', '$field_name');"));
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'ajax_select', 
				'row' => $this->current_row_param, 
				'ajax_value' => htmlspecialchars(cmsTable::showFK($this->fields[$field_name]['fk_table_id'], $this->data[$field_name])), 
				'value' => $this->data[$field_name], 
				'value_fixed' => (empty($this->data[$field_name]) ? '' : 'checked'),
				'max_length' => $this->fields[$field_name]['max_length'], 
				'uniqid' => uniqid(), 
				'size' => '325px'
			)
		);
	}
	
	
	/**
	 * Отображает неизменяемое поле
	 * @param string $field
	 */
	private function showFixedOpen($field_name) {
		// Для этого типа поля нельзя устанавливать значение NULL даже если для него допустимо NULL значение,
		// это связано с тем, что данное поле нередактируемое
		$this->current_row_param['is_nullable'] = false;
		
		// Для полей, которые ссылаются на другие таблицы выводим значение, а не просто id
		$value = (!empty($this->data[$field_name]) && is_numeric($this->data[$field_name]) && !empty($this->fields[$field_name]['fk_table_id'])) ?
			cmsTable::showFK($this->fields[$field_name]['fk_table_id'], $this->data[$field_name]):
			$this->data[$field_name];
		
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'fixed_open', 
				'row' => $this->current_row_param,
				'value' => htmlspecialchars($value)
			)
		);
	}
	
	/**
	 * Отображает поле, редактируемое HTML редактором
	 * @param string $field_name
	 */
	private function showHTML($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'html', 
				'row' => $this->current_row_param,
				'value' => $this->data[$field_name]
			)
		);
	}
	
	/**
	 * Выводит сообщение об ошибке для полей тип которых невозможно определить
	 * @param string $field_name
	 */
	private function showErrorField($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'fixed_open', 
				'row' => $this->current_row_param,
				'value' => '<font color=red>Невозможно определить тип поля '.$field_name.'</font>'
			)
		);
	}
}
?>