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
class cmsShowView {
	
	/**
	 * Параметры, которые можно устанавливать для колонок.
	 * Используется для проверки наличия параметра и типа передаваемых данных. И для установки значений по умолчанию.
	 * @var array
	 */
	private $column_skel = array(
		'order' => '',
		'editable' => false
	);
	
	/**
	 * Информация о колонках таблицы
	 * @var array
	 */
	private $columns = array();

	/**
	 * Порядок вывода колонок
	 * @var array
	 */
	private $columns_priority = array();
	
	/**
	 * Страница, на которой сейчас находимся
	 * @var int
	 */
	private $current_page = 0;
	
	/**
	 * Названия колонок, которые редактируются. Этот параметр используется для того, чтоб для
	 * колонок enum определять ключ _field_checked
	 * @var array
	 */
	private $editable = array();
	
	/**
	 * Порядок вывода кнопок событий над таблицей
	 * @var array
	 */
	private $events_order = array('add', 'edit', 'delete', 'copy', 'filter', 'xls');
	
	/**
	 * Массив в котором функция buildPath сохраняет путь к данной таблице
	 * @var array
	 */
	private $path = array();
	
	/**
	 * id страницы, на которую вернется скрипт
	 * @var int
	 */
	private $return_id = 0;
	
	/**
	 * Страница на которую вернется скрипт после успешного выполнения
	 * @var string
	 */
	private $return_path = '';
	
	/**
	 * Индесы для JavaScript
	 * @var int
	 */
	private $row_index = 0;

	
	/**#@+
	 * Поля для сортировки значений
	 * @var string
	 */
	private $order_field = '';
	private $order_direction = 'DESC';
	/**#@-*/
	
	/**
	 * Данные обработаны и выведены на экран
	 *
	 * @var bool
	 */
	private $display = false;
	
	/**
	 * Параметры вывода таблицы
	 * @access private
	 * @var array
	 */
	private $param = array(
		'add' => true, // отображать ли кнопку Добавить
		'edit' => true, // отображать ли кнопки редактирования
		'delete' => true, // отображать ли кнопки Удалить
		'copy' => false, // отображать ли кнопку копировать
		'jslink' => '', // JavaScript, который будет выполнятся при щелчке по ряду, удаляет свойство link
		'title' => '', // Название таблицы
		'subtitle' => '', // Название колонки, которая содержит подзаголовки
		'row_filter' => '', // Название функции построчной обработки данных
		'data_filter' => '', // Название функции полной обработки данных
		'show_parent_link' => false, // Отображать ли ряд для перехода на уровень вверх
		'parent_link' => '', // Ссылка, которую содержит ряд, который ведёт на уровень вверх
		'show_path' => true, // Показывать ли путь
		'path' => array(), // Путь, которым надо заменить текущий, (массив параметров url,name)
		'path_current' => '', // Название текущего раздела, которое выводится в path
		'class_field' => '', // Название колонки, которая будет содержать класс
		'show_title' => true, // Отображать заголовок таблицы
		'show_rows_limit' => true, // Отображать форму, для указания количества строк вывода
		'show_filter' => true, // Отображать большую форму фильтрации данных
		'excel' => true, // Отображать ссылку для скачивания таблицы в формате Excel
		'priority' => false, // Отображать колонку сортировки данных
	);
	
	/**
	 * Объединение колонок в шапке таблицы
	 * @var array
	 */
	private $merge_title = array();
	private $merge_columns = array();
	
	
	/**
	 * Сервер БД на котором находится таблица, с которой будет вестись работа
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * Данные из таблицы
	 * @var array
	 */
	private $data = array();
	
	/**
	 * Кнопки с событиями
	 * @var array
	 */
	private $events = array();
	
	/**
	 * Информация о колонках
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * Номер строки, котороя была добавлена
	 * @var int
	 */
	private $inserted_id = -1;
	
	/**
	 * SQL запрос, разобранный по частям, в качестве ключа указаны рператоры SQL
	 * @var array
	 */
	private $parsed_sql = array();
	
	/**
	 * Количество рядов, которые необходимо вывести на одной странице
	 * @var int
	 */
	private $rows_per_page = CMS_VIEW;
	
	/**
	 * Необходимо ли имскать страницу, на которой находится запись с id=$this->insert_id
	 * @var bool
	 */
	private $search_page = false;
	
	/**
	 * Обработчик SQL запросов
	 * @var object
	 */
	private $SQLParser;
	
	/**
	 * Информация о таблице
	 * @var array
	 */
	private $table = array();
	
	/**
	 * На каком языке выводить значения колонок. Если значение пустое, то обработчик не запускается
	 * @var string
	 */
	private $table_language = '';
	
	/**
	* Шаблон вывода информации
	* @var object
	*/
	private $Template;
	
	/**
	 * Общее количество рядов в таблице, без условия LIMIT
	 * @var int
	 */
	public $total_rows = 0;
	
	/**
	 * Строка с которой начинается вывод
	 * @var int $view_start
	 */
	private $view_start = 0;
	
	/**
	 * Номер текущего экземпляра класса cmsShow
	 * @var int
	 */
	static private $instance_number = 0;
	
	/**
	 * Не выводить в фильтре таблицы
	 *
	 * @var array
	 */
	private $filter_skip_tables = array();
	
	/**
	 * Не выводить в фильтре поля
	 *
	 * @var array
	 */
	private $filter_skip_fields = array();
	
	/**
	 * Конструктор
	 *
	 * @param DB $DBServer
	 * @param string $data_query
	 * @param int $rows_per_page
	 * @param string $table_name
	 */
	public function __construct(DB $DBServer, $data_query, $rows_per_page = CMS_VIEW, $table_name = '') {
		global $DB;
		
		// Номер экземпляра класса на текущей странице
		self::$instance_number++;
		
		// База данных, в которую должен быть отправлен запрос
		$this->DBServer = $DBServer;
		
		// Шаблон таблицы, в которой выводятся данные
		$this->Template = new Template(SITE_ROOT.'templates/cms/admin/cms_view');
		
		// Обработчик SQL запроса
		$this->SQLParser = new SQLParserMySQLi($DBServer, $data_query);
		
		// Определяем имя обрабатываемой таблицы
		$table_name = (empty($table_name)) ? $this->SQLParser->getTableName() : $table_name;
		
		// Определяем информацию о таблице
		$this->table = cmsTable::getInfoByAlias($this->DBServer->db_alias, $table_name);
		if (empty($this->table)) {
			trigger_error(cms_message('CMS', 'Информация о таблице %s.%s не введена в таблицу CMS_TABLES', $this->DBServer->db_alias, $table_name), E_USER_ERROR);
		}
		
		// Язык, на котором выводятся значения колонок
		$this->table_language = globalVar($_GET['_tb_language_'.$this->table['id']], $this->table['default_language']);
		
		// Определяем информацию о колонках в табюлице
		$this->fields = cmsTable::getFields($this->table['id']);
		
		$this->param['title'] = $this->table['title'];
		
		// Количество рядов на странице
		$this->rows_per_page = (isset($_COOKIE['rows_per_page_'.CMS_STRUCTURE_ID.'_'.$this->table['id']])) ?
			intval($_COOKIE['rows_per_page_'.CMS_STRUCTURE_ID.'_'.$this->table['id']]):
			$rows_per_page;
			
		// Номер страницы, на которой сейчас находится пользователь
		$this->current_page = abs(globalVar($_GET['_tb_start_'.$this->table['id']], 0));

		// Строка, с которой начинается вывод
		$this->view_start = $this->current_page * $this->rows_per_page;
		
		
		// id последнего измененного раздела в данной таблице
		$event_table_id = globalVar($_GET['_event_table_id'], -1); 
		if ($this->table['id'] == $event_table_id) {
			$this->inserted_id = globalVar($_GET['_event_insert_id'], -1);
		}
		
		// Определяем необходимость поиска страницы, на которой находится добавленная запись
		$event_type = globalEnum($_GET['_event_type'], array('insert', 'update'));
		$this->search_page = ($event_type == 'insert');

		
		// Поле, по которому производится сортировка
		$this->order_field = globalVar($_GET['_tb_order_field'][$this->table['id']], '');
		$this->order_direction = globalEnum($_GET['_tb_order_direction'][$this->table['id']], array('ASC', 'DESC'));
		
		// ссылка на родительский раздел
		$this->return_id = globalVar($_GET[$this->table['parent_field_name']], 0);
		
		// URL без параметров
		$this->return_path = substr(CURRENT_URL_FORM, 0, strpos(CURRENT_URL_FORM, '?'));
				
		/**
		 * Определяем id родительского раздела и ссылку на родительский раздел
		 * если в этом есть необходимость
		 */
		if ($this->table['cms_type'] != 'list' && !empty($this->return_id)) {
			$this->param['parent_link'] = $this->getParentData();
			$this->param['show_parent_link'] = (empty($this->param['parent_link'])) ? false : true;
		}
	}
	
	/**
	 * Исключает из фильтра таблицу
	 *
	 * @param string $table_name
	 */
	public function filterSkipTable($table_name) {
		$this->filter_skip_tables[$table_name] = 1;
	}
	
	/**
	 * Исключает из фильтра поле
	 *
	 * @param string $table_name
	 * @param string $field_name
	 */
	public function filterSkipField($table_name, $field_name) {
		$this->filter_skip_fields[$table_name][$field_name] = 1;
	}

	
	/**
	 * Изменение свойства таблицы
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setParam($name, $value) {
		$name = ($name == 'prefilter') ? 'row_filter' : $name;
		if (!isset($this->param[$name])) {
			trigger_error(cms_message('CMS', 'Направильно задано свойство таблицы: %s. Возможные значения: %s.', $name, implode(',', array_keys($this->param))), E_USER_WARNING); 
		} elseif (gettype($this->param[$name]) != gettype($value)) {
			trigger_error(cms_message('CMS', 'Неправильно указан параметр %s', $name), E_USER_WARNING); 
		} elseif (in_array($name, array('row_filter', 'data_filter')) && !function_exists($value)) {
			trigger_error(cms_message('CMS', 'Указанная Вами функция "%s" - не существует.', $value), E_USER_ERROR);
		} else {
			$this->param[$name] = $value;
		}
	}
	

	/**
	 * Объеденяет рядом стоящие колонки в заголовке
	 * @param string $title
	 * @param array $columns
	 */
	public function mergeTitle($title, $columns) {
		$this->merge_title[$title] = $columns;
		reset($columns); 
		while (list(,$column) = each($columns)) { 
			 if (isset($this->merge_columns[$column])) {
			 	trigger_error("Для колонки $column уже определён объеденяющий заголовок", E_USER_ERROR);
			 }
			 $this->merge_columns[$column] = array('count' => count($columns), 'title' => $title, 'show' => true);
		}
	}
	

	/**
	 * Добавляем колонку которую надо выводить
	 * @param string $name имя колонки без указания языка!!!
	 * @param string $width
	 * @param string $align
	 * @param string $title
	 * @param string $text используется для динамически создаваемых колонок, прямо в этом классе
	 * @param bool $add_before
	 * @return void
	 */
	public function addColumn($name, $width, $align = '', $title = '', $text = '', $add_before = false) {
		
		$this->columns[$name] = $this->column_skel;
		$this->columns[$name]['name'] = $name; // параметр name используется в шаблоне для фильтров
		$this->columns[$name]['title'] = (empty($title)) ? $this->getColumnTitle($name) : $title;
		$this->columns[$name]['width'] = $width;
		$this->columns[$name]['align'] = (empty($align)) ? $this->getColumnAlign($name) : $align;
		$this->columns[$name]['text'] = (empty($text)) ? '{'.$name.'}' : $text;
		
		// Куда добавлять колонку - в начало или конец
		($add_before) ? array_unshift($this->columns_priority, $name) : array_push($this->columns_priority, $name);
	}	
	
	/**
	 * Передача дополнительных параметров в события, которые выполняются кнопками.
	 * Впервые применено на хостинге. Используется когда надо добавлять значения в таблицу
	 * с заведомо известными данными. Например при добавлении дополнительных услуг для 
	 * пользователя хостинга, нам уже известен его тарифный план и логин, поэтому мы передаём 
	 * эти параметры в таблицу для того, что б администратору не пришлось их ставить вручную.
	 * @access public
	 * @param array $params
	 * @return void
	 */
	public function addEventParams($params) {
		reset($params);
		while(list($key, $val) = each($params)) {
			$_GET[$key] = $val; // необходимо для того, что б эти переменные были добавлены в кнопку [+] (добавления новой записи)
			$this->Template->iterate('/hidden_field/', null, array('name' => $key, 'value' => urlencode($val)));
		}
	}
	

	/**
	 * Изменяет параметры для колонки
	 * @return void
	 */
	public function setColumnParam($column_name, $param, $value) {
		if (!isset($this->column_skel[$param])) {
			// Направильно задано свойство колонки: %s. Возможные значения: %s.
			trigger_error(cms_message('CMS', 'Направильно задано свойство колонки: %s. Возможные значения: %s.', $param, implode(',', array_keys($this->column_skel))), E_USER_ERROR);
		}
		
		if (!isset($this->columns[$column_name])) {
			// Перед установкой параметра колонки необходимо добавить колонку методом $cmsView->addColumn()
			trigger_error(cms_message('CMS', 'Перед установкой параметра колонки необходимо добавить колонку методом $cmsView->addColumn()'), E_USER_ERROR);
		}
		
		$this->columns[$column_name][$param] = $value;
	}
	
	/**
	 * Информация о таблице
	 * 
	 * @return array
	 */
	public function getTableInfo() {
		return $this->table;
	}
	
	
	/**
	 * Возвращает результат SQL запроса или false, если запрос еще не выполнялся.
	 * Данный метод работает только после вызова метода display
	 *
	 * @return mixed
	 */
	public function getData() {
		if (!$this->display) return false;
		else return $this->data;
	}
	
	
	/**
	* Метод, который выполняется после того, как указаны колонки, которые необходимо вывести
	* @return string
	*/
	public function display() {
		global $TmplDesign;
		
		$this->display = true;
		
		// onload вызывается из функции init и все объекты, которые в ней определены доступны только внутри неё
//		$TmplDesign->iterate('/onload_var/', null, array('function' => 'var cmsView'.self::$instance_number.' = new cmsView();'));
//		$TmplDesign->iterate('/onload/', null, array('function' => 'cmsView'.self::$instance_number.'.init('.$this->table['id'].', '.self::$instance_number.');'));
		
		$this->Template->setGlobal('rows_per_page', $this->rows_per_page);
		$this->Template->setGlobal('table', $this->table);
		
		// Язык, на котором выводятся значения колонок
		$this->Template->set('table_language', $this->table_language);
		
		// Номер страницы, на которой сейчас находится пользователь
		$this->Template->setGlobal('current_page', $this->current_page);
		
		$this->Template->iterate('/hidden_field/', null, array('name' => $this->table['parent_field_name'], 'value' => globalVar($_GET[$this->table['parent_field_name']], 0)));
		$this->Template->iterate('/hidden_field/', null, array('name' => '_table_id', 'value' => $this->table['id']));
		$this->Template->iterate('/hidden_field/', null, array('name' => '_start_row', 'value' => $this->view_start));
		
		// Форма фильтрации данных в таблице
		if ($this->param['show_filter']) {
			$Filter = new cmsFilter($this->Template, $this->SQLParser, $this->DBServer, self::$instance_number);
			if($Filter->show($this->filter_skip_tables, $this->filter_skip_fields, $this->table['id'])) {
				$this->addEvent('filter', 'javascript:cmsView.showFilter('.self::$instance_number.');', true, true, true, '/design/cms/img/event/table/filter.gif', '/design/cms/img/event/table/filter_over.gif', 'Фильтр', null, false);
			}
		} else { 
			$this->Template->set('show_filter', 'none');
		}
		
		// Добавляем параметры, которые передаются методом get
		$get = $_GET;
		unset($get['_start['.$this->table['id'].']']);
		unset($get['_REWRITE_URL']);
		unset($get['_event_insert_id']);
		unset($get['_event_table_id']);
		unset($get['_event_type']);
		$get = http_build_query($get);
		$this->Template->setGlobal('get_vars', $get);
		unset($get);

		// ссылка на родительский раздел
		$this->return_id = globalVar($_GET[$this->table['parent_field_name']], 0);
		$this->Template->setGlobal('return_id', $this->return_id);
		$this->Template->setGlobal('parent_table_id', $this->table['parent_table_id']);
		
		
		// Обрабатываем параметры таблицы
		$this->parseTableParams();
		
		// Обрабатываем параметры колонок
		$this->parseColumnParams();
		
		// Выводим события
		$this->showEvents();
		
		// Вывод списка языков в таблице
		$this->displayLanguages();
		
		// Определяем - есть связанные заголовки или нет
		$this->Template->set('merged_columns', count($this->merge_columns));
		if (IS_DEVELOPER) {
			$this->Template->set('table_title', $this->param['title'].'</h2> Вывод таблицы: <a href="/Admin/CMS/DB/Tables/Fields/?table_id='.$this->table['id'].'">'.$this->table['name'].'</a><br>');
		} else {
			$this->Template->set('table_title', $this->param['title']);
		}
		$this->Template->set('show_path', $this->param['show_path']);
		$this->Template->set('show_title', $this->param['show_title']);
		$this->Template->set('show_rows_limit', $this->param['show_rows_limit']);
		$this->Template->setGlobal('instance_number', self::$instance_number);
		$this->Template->set('show_parent_link', $this->param['show_parent_link']);
		$this->Template->set('parent_link', $this->param['parent_link']);
		
		/**
		 * Добавляем условие сортировки в таблицу
		 */
		if (!empty($this->order_field)) {
			$order_by = array();
			if (!empty($this->param['subtitle'])) {
				// Если пользователь применяет сортировку в таблице с подзаголовком, то подзаголовки должны
				// сразу же убираться
				// $order_by[ $this->param['subtitle'] ] = 'ASC';
				$this->param['subtitle'] = '';
			}
			$order_by[$this->order_field] = $this->order_direction;
			$this->SQLParser->changeOrder($order_by);
			
			unset($order_by);
		}
		
		/**
		 * Изменяем язык вывода значений колонок, делать это надо после того,
		 * как будут добавлены колонки сортировки и фильтрации, чтоб изменение
		 * языка затронуло весь SQL запрос.
		 */
		if (!empty($this->table_language) && $this->table_language != LANGUAGE_CURRENT) {
			$this->SQLParser->changeTableLanguage(LANGUAGE_CURRENT, $this->table_language);
		}
		
		/**
		 * Определяем страницу на которой находятся добавленные данные.
		 */
		if ($this->inserted_id > 0 && $this->search_page == true) {
			$this->getQueryPage();
		}
		
		/**
		 * Выполняем SQL запрос
		 */
		if (isset($_GET['output'][self::$instance_number]) && $_GET['output'][self::$instance_number] == 'xls') {
			$this->data = $this->SQLParser->execQuery();
		} else {
			$this->data = $this->SQLParser->execQuery($this->view_start, $this->rows_per_page);
		}
		
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
		$this->Template->setGlobal('current_page', $this->current_page);

		/**
		 * Вызывает функции предварительной обработки информации
		 */
		if (isset($this->param['row_filter']) && !empty($this->param['row_filter'])) {
			reset($this->data);
			while(list($index, $row) = each($this->data)) {
				// Если функция фильтрации возвращает пустое значение, то это значит что надо удалить этот ряд с вывода
				$row = call_user_func_array($this->param['row_filter'], array($row));
				if (!empty($row)) {
					$this->data[$index] = $row;
				} else {
					unset($this->data[$index]);
				}
			}
		}
		if (isset($this->param['data_filter']) && !empty($this->param['data_filter'])) {
			$this->data = call_user_func_array($this->param['data_filter'], array($this->data));
		}
                
                //Добавляем класс запрета приоритета
                if ( !isset($this->fields['priority']) ) $this->Template->setGlobal('no_priority', 1);                
		
		// Проверка наличия нужной информации в результате запроса
		$this->checkData();
		
		// Вывод дополнительных колонок в таблице
		$this->createExtraColumns();
		
		// Количество колонок, определять этот параметр необходимо перед обработкой /th/ секции шаблона
		$this->Template->setGlobal('total_columns', count($this->columns));
		
		// Проверка правильности объединённых колонок в шапке таблицы
		$this->checkMerged();
		
		// Определяем формат вывода
		if (isset($_GET['output'][self::$instance_number]) && $_GET['output'][self::$instance_number] == 'xls') {
			$this->outputXls();
		}
		
		/**
		 * Выводим шапку таблицы
		 */
		reset($this->columns_priority);
		while(list(, $field_name) = each($this->columns_priority)) {
			$field = $this->columns[$field_name];
			if (!empty($field['order'])) {
				$url = set_query_param(CURRENT_URL_FORM, '_tb_order_field['.$this->table['id'].']', $field['order']);
				$url = set_query_param($url, '_tb_order_direction['.$this->table['id'].']', ($this->order_direction == 'ASC') ? 'DESC' : 'ASC');
				
				$url_remove = set_query_param(CURRENT_URL_FORM, '_tb_order_field['.$this->table['id'].']');
				$url_remove = set_query_param($url_remove, '_tb_order_direction['.$this->table['id'].']');
				
				if ($field['order'] == $this->order_field) {
					$image_direction = ' <img align="absmiddle" src="/design/cms/img/icons/order_'.strtolower($this->order_direction).'.gif" border="0">';
					$image_remove = '<a href="'.$url_remove.'"><img align="middle" src="/design/cms/img/icons/order_remove.gif" border="0"></a> ';
				} else {
					$image_direction = '';
					$image_remove = '';
				}
				$field['title'] = $image_remove.'<a href="'.$url.'">'.$field['title'].$image_direction.'</a>';
			}
			// Определяем rowspan
			if (!empty($this->merge_columns) && !isset($this->merge_columns[$field_name])) {
				// Если есть объединённые заголовки но для этой колонки нет объединения
				$field['colspan'] = 1;
				$field['rowspan'] = 2;
				$this->Template->iterate('/th1/', null, $field);
				
			} elseif (!empty($this->merge_columns) && isset($this->merge_columns[$field_name])) {
				// Если есть объединённые заголовки и для этой колонки есть объединения
				$field['colspan'] = $this->merge_columns[$field_name]['count'];
				$field['rowspan'] = 1;
				$this->Template->iterate('/th2/', null, $field);
				if (isset($this->merge_title[ $this->merge_columns[$field_name]['title'] ])) {
					$field['title'] = $this->merge_columns[$field_name]['title'];
					$this->Template->iterate('/th1/', null, $field);
					unset($this->merge_title[ $this->merge_columns[$field_name]['title'] ]);
				}
				
			} else {
				$field['colspan'] = 1;
				$field['rowspan'] = 2;
				$this->Template->iterate('/th1/', null, $field);
			}
			// Необходимо для отображения фильтров
			$this->Template->iterate('/th/', null, $field);
			$this->Template->iterate('/parent_cell/', null, array('align' => $field['align']));
		}
		
		/**
		 * Выводим данные
		 */
		$rows = array();
		$prev_title = '';
		if ($this->total_rows > 0) {
			$row_template = $this->makeRowTemplate();
			reset($this->data);
			while(list(,$row) = each($this->data)) {
				
				/**
				* Проставляем заголовки, которые находятся посреди таблицы
				* делаем это только для таблиц, в которых нет поля priority или
				* не вывоодится поле сортировка. И для которых указано название поля,
				* которое будет использовано в качестве subtitle
				*/
				if (
					(!isset($row['priority']) || $this->param['edit'] == false) 
					&& !empty($this->param['subtitle'])
					&& $prev_title != $row[ $this->param['subtitle'] ]
				) {
					$rows[] = '<tr><th colspan="'.count($this->columns).'">'.$row[ $this->param['subtitle'] ].'</tr>';
					$prev_title = $row[ $this->param['subtitle'] ];
				}
				$rows[] = $this->rowParser($row_template, $row);
			}
		}
		
		$this->Template->set('grid', implode("\n", $rows));
		
		/**
		* Формируем путь к текущей таблице
		*/
		$data = array('table_id' => $this->table['parent_table_id'], 'parent_id' => $this->return_id);
		do {
			$data = $this->buildPath($data['table_id'], $data['parent_id']);
		} while (!empty($data));
		
		/**
		 * Проставляем URL для path
		 */
		$url = (strpos(CURRENT_URL_FORM, '?')) ? substr(CURRENT_URL_FORM, 0, strpos(CURRENT_URL_FORM, '?')) : CURRENT_URL_FORM;
		$structure = preg_split('/\//', substr($url, strlen('/Admin')), -1, PREG_SPLIT_NO_EMPTY);
		
		// Для таблиц, которые ссылаются на другие таблицы значения элемента parent_field смещаем на уровень вниз
		if (!empty($this->path) && empty($this->path[count($this->path)-1]['table_id'])) {
			$structure[] = '';
			for($i = count($this->path) - 1; $i >= 0; $i--) {
				if (isset($this->path[$i - 1])) {
					$this->path[$i]['parent_field'] = $this->path[$i-1]['parent_field'];
				}
			}
		}
		
		$prev_table_id = $this->table['id'];
		reset($this->path);
		while(list($index, $row) = each($this->path)) {
			if ($prev_table_id != $row['table_id']) {
				array_pop($structure);
			}
			$this->path[$index]['url'] = '/Admin/'.implode('/', $structure).'/?'.$row['parent_field'].'=' . $row['id'];
			$prev_table_id = $row['table_id'];
		}
		
		// Если начальная таблица имеет ссылку на другую таблицу, значит она всегда ссылается сама на себя
		if (!empty($this->path) && !empty($this->path[count($this->path)-1]['table_id'])) {
			$this->path[] = array('id' => 0, 'name' => cms_message('CMS', 'Главная'), 'table_id' => 0, 'parent_field' => '', 'url'=>'./');
		} elseif (!empty($this->path)) {
			array_pop($structure);
			$this->path[] = array('id' => 0, 'name' => cms_message('CMS', 'Главная'), 'table_id' => 0, 'parent_field' => '', 'url'=>'/Admin/'.implode('/', $structure).'/');
		}
		unset($prev_table);
		unset($structure);
		
		/**
		 * Выводим путь к текущей странице
		 */
		if (!empty($this->param['path_current'])) {
			array_shift($this->path); // Удаляем последний элемент из пути
			$this->Template->set('path_current', $this->param['path_current']);
		} else {
			$path_current = array_shift($this->path);
			$this->Template->set('path_current', $path_current['name']);
		}
		
		if (empty($this->param['path'])) {
			$this->Template->iterateArray('/path/', null, array_reverse($this->path));
		} else {
			$this->Template->iterateArray('/path/', null, $this->param['path']);
		}
		
		// Вывод списка страниц
		$this->displayPagesList();
		
		// Парсим и выводим шаблон
		return $this->Template->display();
	}
	

	
	/**
	 * PRIVATE
	 */
	
	
	/**
	 * Вывод списка языков в таблице
	 */
	private function displayLanguages() {
		if (count($this->table['languages']) < 2) return;
		reset($this->table['languages']);
		while (list(,$row) = each($this->table['languages'])) {
			$this->Template->iterate('/table_language/', null, array(
				'language' => $row,
				'url' => set_query_param(CURRENT_URL_FORM, '_tb_language_'.$this->table['id'], $row),
				'class' => ($row == $this->table_language) ? '' : 'class="disabled"'
			));
		}
	}
	
	/**
	 * Удаляет объединённые колонки
	 * @param  string $title
	 */
	private function deleteMerged($title) {
		$columns = $this->merge_title[$title];
		reset($columns); 
		while (list(,$column) = each($columns)) { 
			 unset($this->merge_columns[$column]);
		}
		unset($this->merge_title[$title]);
	}
	
	/**
	 * Проверяет правильность обеъединения колонок в шапке таблицы.
	 * Объединённые колонки должны идти одна за другой
	 */
	private function checkMerged() {
		/**
		 * Делаем проверку правильности объединения колонок в шапке таблицы
		 */
		$table_columns = array_flip(array_keys($this->columns));
		reset($this->merge_title); 
		while (list($title,$columns) = each($this->merge_title)) { 
			$check = array();
			reset($columns); 
			while (list(,$column) = each($columns)) { 
				if (isset($table_columns[$column])) {
			 		$check[] = $table_columns[$column];
				}
			}
		 	// Через арифметическую прогрессию вычисляем стоят ли колонки одна за другой или нет.
		 	// Сумма Sn первых n членов арифметической прогрессии выражается формулой:
		 	// Sn=(a1+an/2)*n=(2*a1+d(n-1)/2)*n, где d - разность арифметической прогрессии
		 	$table_columns_sum = ((1+max($check)-(min($check)-1))/2)*count($check);
		 	$merge_columns_sum = ((count($columns)+1)/2)*count($columns);
		 	if ($table_columns_sum != $merge_columns_sum) {
		 		// Колонки не идут одна за другой
		 		$this->deleteMerged($title);
		 	}
		}
	}
	
	/**
	 * Определяет выравнивание колонки
	 * 
	 * @param string $name
	 */
	private function getColumnAlign($name) {
		if (!isset($this->fields[$name])) {
			return 'left';
		} elseif (empty($this->fields[$name]['fk_table_id']) && in_array($this->fields[$name]['data_type'], array('bigint', 'int', 'mediumint', 'smallint', 'tinyint', 'float', 'decimal'))) {
			return 'right';
		} elseif (in_array($this->fields[$name]['data_type'], array('enum', 'date', 'time', 'datetime', 'timestamp'))) {
			return 'center';
		} else {
			return 'left';
		}
	}
	
	/**
	 * Определяет название колонки
	 * 
	 * @param string $name
	 */
	private function getColumnTitle($name) {
		if (isset($this->fields[$name]['title'])) {
			return $this->fields[$name]['title'];
		} elseif (isset($this->fields[$name.'_'.$this->table_language]['title'])) {
			return $this->fields[$name.'_'.$this->table_language]['title'];
		} else {
			return 'No title';
		}
	}
	
	/**
	 * Проверяет правильность указания параметров для колонки перед их выводом
	 * 
	 * @return void
	 */
	private function parseColumnParams() {
		reset($this->columns);
		while(list($index, $row) = each($this->columns)) {
			if ($row['editable'] == true) {
				// Выводить кнопку "Сохранить изменения"
				$this->Template->set('show_update_button', 1);
			}
		}
	}
	
	/**
	 * Вывод колонок специального назначения
	 *
	 */
	private function createExtraColumns() {

		// Колонка сортировки
		if (isset($this->fields['priority']) && $this->param['priority']) {
			$this->addColumn('priority', '5%', 'center', cms_message('CMS', 'Порядок'), '<img src="/design/cms/img/icons/table_sort.gif" border="0" class="move"><input type="hidden" name="id[]" value="{id}">');			
		}
		
		// Колонка с ссылкой на редактирование
		if ($this->param['edit']) {
			$this->addColumn('edit', '5%', 'center', cms_message('CMS', 'Ред.'), '<a href="javascript:void(0);" onclick="EditWindow(\'{id}\', '.$this->table['id'].', \''.CMS_STRUCTURE_URL.'\', \''.CURRENT_URL_LINK.'\', \''.LANGUAGE_CURRENT.'\', \'\');return false;" title="'.cms_message('CMS', 'Редактировать').'"><img src="/design/cms/img/icons/change.gif" width="16" height="16" border="0" alt="'.cms_message('CMS', 'Редактировать').'"></a>');
		}
		
		// Колонка с сылкой на удаление
		if ($this->param['delete']) {
			$this->addColumn('del', '5%', 'center', cms_message('CMS', 'Удл.'), '<a href="/action/admin/cms/table_delete/?_return_path='.CURRENT_URL_LINK.'&_language='.LANGUAGE_CURRENT.'&_table_id='.$this->table['id'].'&'.$this->table['id'].'[id][]={id}&_language_'.LANGUAGE_CURRENT.'" title="'.cms_message('CMS', 'Удалить').'" onclick="return confirm(\''.cms_message('CMS', 'Удалить').'?\')"><img src="/design/cms/img/icons/del.gif" width="16" height="16" border="0" alt="'.cms_message('CMS', 'Удалить').'"></a>');
		}
		
	}
	
	/**
	 * Проверка наличия нужных полей в результате SQL запроса
	 *
	 */
	private function checkData() {
		/**
		 * Сортировка, выводим только тогда, когда есть колонка priority в таблице 
		 * и значения в таблице можно редактировать (есть кнопка edit).
		 */
		if (!empty($this->data) && isset($this->fields['priority']) && $this->param['priority']) {
			// Проверяем, Есть ли в результате запроса колонка priority
			$row = reset($this->data);
			if (!isset($row['priority'])) {
				trigger_error(cms_message('CMS', 'Результат запроса должен содержать колонку priority или в таблице должна быть отключена сортировка'), E_USER_WARNING);
			}
		}
		// Проверяем есть ли поле id в редактируемой таблице
		if (!empty($this->data) && ($this->param['edit'] || $this->param['delete'])) {
			$row = reset($this->data);
			if (!isset($row['id'])) {
				trigger_error(cms_message('CMS', 'Результат запроса должен содержать колонку id'), E_USER_WARNING);
			}
		}
	}

	/**
	 * Вывод списка страниц
	 */
	private function displayPagesList() {
		$rows_to = $this->rows_per_page + $this->view_start;
		if ($rows_to > $this->total_rows) {
			$rows_to = $this->total_rows;
		}


		$total_pages = intval(($this->total_rows - 1)/ $this->rows_per_page);
		$this->Template->set('total_pages', $total_pages);
		$this->Template->set('page_link', array(
			'first' => set_query_param(CURRENT_URL_FORM, '_tb_start_'.$this->table['id'], 0),
			'previous' => set_query_param(CURRENT_URL_FORM, '_tb_start_'.$this->table['id'], $this->current_page - 1),
			'next' => set_query_param(CURRENT_URL_FORM, '_tb_start_'.$this->table['id'], $this->current_page + 1),
			'last' => set_query_param(CURRENT_URL_FORM, '_tb_start_'.$this->table['id'], $total_pages)
		));
		
		$options = array();
		$list_start_page = ($this->current_page - 50 < 0) ? 0 : $this->current_page - 50;
		$list_end_page = ($this->current_page + 50 > $total_pages) ? $total_pages  : $this->current_page + 50;
		for ($i = $list_start_page; $i <= $list_end_page; $i++) {
			$options[$i] = $i + 1;
		}
		$this->Template->set('pages_list', $options);
		$this->Template->set('from', number_format($this->view_start + 1, 0, ',', ' '));
		$this->Template->set('to', number_format($rows_to, 0, ',', ' '));
		$this->Template->set('out_of', number_format($this->total_rows, 0, ',', ' '));
		$this->Template->set('param', $this->param);
	}
	

	/**
	 * Определение, на какой странице находится нужная информация
	 * @return void
	 */
	private function getQueryPage() {
		
		$query = $this->SQLParser->getQueryArray();
		
		// Определяем alias для таблицы
		$alias = $this->table['name'];
		if (preg_match("/^FROM[\s\n\r\t]+([^\s\n\r\t]+)[\s\n\r\t]+(?:AS[\s\n\r\t]+)?([^\s\n\r\t]+)/i", trim($query['FROM']), $matches)) {
			$alias = str_replace('`', '', $matches[2]);
		}
		
		if (!isset($query['GROUP BY']) && isset($query['ORDER BY'])) {
			// Добавляем это условие в ORDER BY, так как если добавить просто $query['GROUP BY']
			// то оно будет идти после ORDER BY поля, что приведёт к ошибке
			$query['ORDER BY'] = "GROUP BY `$alias`.`id`\n".$query['ORDER BY'];
		} elseif (!isset($query['GROUP BY'])) {
			$query['GROUP BY'] = "GROUP BY `$alias`.`id`\n";
		}
		
		$query['SELECT'] = "SELECT `$alias`.`id` ";
		$query = implode("\n", $query);
		
		$start = 0;
		do {
			$tmp_query = $query." LIMIT ".$start.", 500";
			$data = $this->DBServer->fetch_column($tmp_query, null, 'id');
			$data = array_flip($data);
			if (isset($data[$this->inserted_id])) {
				$this->view_start = floor(($start + $data[$this->inserted_id]) / $this->rows_per_page) * $this->rows_per_page;
				$this->current_page = $this->view_start / $this->rows_per_page;
				break;
			}
			$start += 500;
		} while ($this->DBServer->rows > 0);
	}
	
	/**
	* Определяет ссылку на родительский раздел для $this->param[parent_link]
	* @param void
	* @return string
	*/
	private function getParentData() {
		global $DB;
		
		$query = "
			SELECT CONCAT('/Admin/', url, '/')
			FROM cms_structure
			WHERE id=(SELECT structure_id FROM cms_structure WHERE id='".CMS_STRUCTURE_ID."')
		";
		$return_path_parent = $DB->result($query);
		if ($this->table['cms_type'] == 'tree') {
			$query = "
				SELECT ".$this->table['parent_field_name']." AS parent_tree
				FROM ".$this->table['name']."
				WHERE id='".$this->return_id."'
			";
			$parent_id = $this->DBServer->result($query, false);
			$parent_field = $this->table['parent_field_name'];
			$parent_link = $this->return_path;
		} elseif ($this->table['cms_type'] == 'cascade') {
			$fk_table = cmsTable::getInfoById($this->fields[$this->table['parent_field_name']]['fk_table_id']);
			$query = "
				SELECT $fk_table[parent_field_name] as parent_cascade
				FROM `$fk_table[name]`
				WHERE id='".$this->return_id."'
			";
			$parent_id = $this->DBServer->result($query, false);
			$parent_field = $fk_table['parent_field_name'];
			$parent_link = $return_path_parent;
		} else {
			// Таблица, находящаяся уровнем выше не имеет parent раздела
			$parent_id = 0;
			$parent_field = 'parent';
			$parent_link = $return_path_parent;
		}
		return $parent_link.'?'.$parent_field.'='.$parent_id;
	}
	
	
	/**
	* Создает шаблон колонки (когда вся информация о колонках введена)
	* @param void
	* @return string
	*/
	private function makeRowTemplate() {
		global $DB;
		
		$result = '<tr class="{_class}">';
		reset($this->columns_priority);
		while(list(, $field_name) = each($this->columns_priority)) {
			$val = $this->columns[$field_name];
			
			/**
			 * Обработка редактируемых колонок
			 */
			if ($val['editable']) {
				$pilot_type = (isset($this->fields[$field_name.'_'.$this->table_language])) ? $this->fields[$field_name.'_'.$this->table_language]['pilot_type'] : $this->fields[$field_name]['pilot_type'];
				if ($pilot_type == 'boolean') {
					// Checkbox
					$this->editable[ $val['name'] ] = 'checkbox';
					$val['align'] = 'center';
					$val['text'] = '
						<input type="hidden" name="'.$this->table['id'].'[{id}]['.$val['name'].']" value="0">
						<input type="checkbox" name="'.$this->table['id'].'[{id}]['.$val['name'].']" value="1" {_'.$val['name'].'_checked}>
					';
				} elseif ($pilot_type == 'variant' || $pilot_type == 'enum') {
					// Загружаем справочник enum полей
					$query = "SELECT name, if(title_".LANGUAGE_CURRENT."='', name, title_".LANGUAGE_CURRENT.") FROM cms_field_enum WHERE field_id='".$this->fields[$field_name]['id']."' ORDER BY priority";
					$enum = $DB->fetch_column($query);
					// Select
					$this->editable[ $val['name'] ] = 'select';
					$val['text'] = '<select name="'.$this->table['id'].'[{id}]['.$val['name'].']">';
					reset($enum); 
					while (list($enum_key, $enum_val) = each($enum)) { 
						$val['text'] .= '<option  {_'.$enum_key.'_checked} value="'.$enum_key.'">'.$enum_val.'</option>';
					}
					$val['text'] .= '</select>';
					
				} else {
					// text
					$this->editable[ $val['name'] ] = 'text';
					$val['text'] = '<input type="text" name="'.$this->table['id'].'[{id}]['.$val['name'].']" class="alpha" value="'.htmlspecialchars($val['text'], ENT_COMPAT, LANGUAGE_CHARSET).'">';
				}
			}
			$result .= "\n\t<td align=\"$val[align]\">$val[text]</td>";
		}
		$result .= "\n</tr>\n";
		return $result;
	}
		
	/**
	* Заменяет конструкцию [[id]] на значение переменной $row['id']
	* 
	* В каждой колонке можно употреблять параметры [[row_index]] и [[return_path]] они ставятся автоматически
	* @param string $content шаблон
	* @param array $row значения из БД
	* @return string
	*/
	private function rowParser($content, $row) {
		
		// Стили для ряда
		if (isset($row['id']) && $this->inserted_id == $row['id']) {
			$row['_class'] = 'last_inserted';
		} elseif (isset($row['_class']) && !empty($row['_class'])) {
			// используем то, что указано в колонке _class
		} elseif (!empty($this->param['class_field']) && isset($row[$this->param['class_field']]) && !empty($row[$this->param['class_field']])) {
			$row['_class'] = $row[ $this->param['class_field'] ];
		} elseif ($this->row_index % 2) {
			$row['_class'] = 'odd';
		} else {
			$row['_class'] = 'even';
		}
		reset($this->editable);
		while(list($field, $type) = each($this->editable)) {
			if ($type == 'checkbox' && isset($row[$field])) {
				$row['_'.$field.'_checked'] = ($row[$field] == 'true' || $row[$field] == 1) ? 'checked' : '';
			} elseif ($type == 'select' && isset($row[$field])) {
				$row['_'.$row[$field].'_checked'] = 'selected';
			}
		}
		
		$row['row_index'] = $this->row_index;
		$row['return_path'] = $this->return_path;
		$this->row_index++;
		
		return @preg_replace("/{([a-z0-9_]+)}/ie", '$row[\'\\1\']', $content);
	}
	
	/**
	 * Построение пути к данной таблице, если она связана с другими таблицами
	 * @param int $table_id
	 * @param int $id
	 * @return array
	 */
	private function buildPath($table_id, $id) {
		if (empty($table_id)) return array();
		
		// Определяем родительскую таблицу для данной
		$cms_table = cmsTable::getInfoById($table_id);
		if ($cms_table['cms_type'] == 'list') {
			$cms_table['parent_field'] = 0;
		}
		
		// Если не указано поле для отображение - выходим
		if (empty($cms_table['fk_show_name'])) return array();
		
		// Определяем название родительского раздела
		$query = "
			SELECT 
				`$cms_table[fk_show_name]` AS name,
				$cms_table[parent_field_name] AS parent_id
			FROM `$cms_table[name]`
			WHERE id='$id'
		";
		$path = $this->DBServer->query_row($query);
		
		// Если нет записи по данному разделу, то выходим
		if (empty($path)) return array();	
		
		// Добавляем еще один элемент в путь
		$this->path[] = array(
			'id' => $id,
			'name' => $path['name'],
			'table_id' => $cms_table['parent_table_id'],
			'parent_field' => $cms_table['parent_field_name']
		);
		
		// Если нет родительского раздела - выходим	
		if ($cms_table['cms_type'] == 'list') return array();
		
		return array('table_id' => $cms_table['parent_table_id'], 'parent_id' => $path['parent_id']);
	}
	

	/**
	 * Метод, который добавляет обработчики событий, которые находятся вверху таблицы
	 */
	public function addEvent($name, $event, $select_none, $select_one, $select_few, $image, $image_over, $alt, $alert) {
		
		$event = (stripos($event, 'javascript') === false) ? 
			"cmsView.changeAction('$event', '".self::$instance_number."')":
			substr($event, strlen('javascript:'));
		
		$this->events[$name] = array(
			'name' => $name,
			'event' => $event,
			'select_none' => intval($select_none),
			'select_one' => intval($select_one),
			'select_few' => intval($select_few),
			'image' => $image,
			'image_over' => $image_over,
			'alt' => $alt,
			'alert' => htmlspecialchars($alert, ENT_QUOTES, LANGUAGE_CHARSET),
		);
	}
	
	/**
	 * Удаление события
	 */
	public function delEvent($name) {
		reset($this->events); 
		while (list($index,) = each($this->events)) {
			if ($this->events[$index]['name'] == $name) {
				unset($this->events[$index]);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Указание порядка сортировки событий
	 */
	public function orderEvents() {
		$this->events_order = func_get_args();
	}
	
	/**
	 * Выводит события в шаблоне
	 *
	 */
	private function showEvents() {
		$events = array_flip(array_keys($this->events));
		reset($this->events_order);
		while (list(,$name) = each($this->events_order)) {
			if (!isset($this->events[$name])) continue;
			$this->Template->iterate('/event_button/', null, $this->events[$name]);
			unset($events[$name]);
		}
		
		// Выводим события, порядок которых не указан
		reset($events);
		while (list($name) = each($events)) {
			$this->Template->iterate('/event_button/', null, $this->events[$name]);
		}
		unset($events);
		
		// Если событий > 0 то необходимо показать стрелочку перед кнопками
		$this->Template->set('event_counter', count($this->events));
	}
	
	
	
	/**
	 * Обработчик параметров таблицы
	 */
	private function parseTableParams() {	
		// Checkbox column
		if ($this->param['delete'] || $this->param['edit']) {
			// Исправление от 18.12.2008 назване колонки поменяли с id на _id, так как не было возможности выводить колонку id
			$this->addColumn('_id', '2%', 'center', '<input type="checkbox" class="check_all">', '<input type="checkbox" name="'.$this->table['id'].'[id][]" value="{id}" class="id">', true);
		}
		
		// Добавляем события
		if ($this->param['excel']) $this->addEvent('xls', CURRENT_URL_FORM.'&output['.self::$instance_number.']=xls', true, true, true, '/design/cms/img/event/table/xls.gif', '/design/cms/img/event/table/xls_over.gif', 'Скачать в формате Excel', null, true);
		if ($this->param['delete']) $this->addEvent('delete', '/action/admin/cms/table_delete/', false, true, true, '/design/cms/img/event/table/delete.gif', '/design/cms/img/event/table/delete_over.gif', 'Удалить', 'Вы уверены что хотите удалить выделенные записи?', true);
		if ($this->param['edit']) $this->addEvent('edit', 'javascript:cmsView.editWindow(this, 0)', false, true, true, '/design/cms/img/event/table/edit.gif', '/design/cms/img/event/table/edit_over.gif', 'Редактировать', null, true);
		if ($this->param['copy']) $this->addEvent('copy', 'javascript:cmsView.editWindow(this, 1)', false, true, false, '/design/cms/img/event/table/copy.gif', '/design/cms/img/event/table/copy_over.gif', 'Копировать', null, true);
		if ($this->param['add']) $this->addEvent('add', 'javascript:cmsView.addWindow(this, \''.http_build_query($_GET).'\');', true, false, false, '/design/cms/img/event/table/new.gif', '/design/cms/img/event/table/new_over.gif', 'Добавить', null, true);
	}
	
	private function outputXls() {
		require_once 'Spreadsheet/Excel/Writer.php';
		ob_end_clean();
		
		/**
		 * Имя листа обязательно должно быть указано и содержать не более 30 символов
		 */
		$title = (empty($this->param['title']) ? cms_message('cms', 'Таблица') : substr($this->param['title'], 0, 30));
		$title = preg_replace('~:~', '-', $title);
		
		/**
		 * Поскольку ширина в таблицах cmsShowView указывается в процентах - необходимо
		 * ее привести к численному значению относительно чего-то. Это значение устанавливает
		 * 100% ширины таблицы
		 */
		$page_width = globalVar($_GET['output_xls_width'], 120);
		
		$workbook = new Spreadsheet_Excel_Writer(); 
		$workbook->setTempDir(TMP_ROOT);
		$worksheet =& $workbook->addWorksheet($title); 
		$worksheet->setMargins(0.3937);
		$worksheet->setSelection(0, 40, 0, 40);
		
		/**
		 * Создаем форматы вывода
		 */
		$default_font = array('FontFamily' => 'Verdana', 'Size' => 9, 'Border'=>1);
		$workbook->setCustomColor(20, 203, 224, 255); // bgColor шапки
		$workbook->setCustomColor(21,  63, 145, 255); // Color заголовка таблицы
		$workbook->setCustomColor(22, 224, 237, 255); // Color для subtitle
		
		$formats['title'] =& $workbook->addFormat(array('Size'=>15, 'FontFamily'=>'Georgia', 'Color'=>21, 'Border'=>0)+$default_font);
		$formats['head'] =& $workbook->addFormat(array('Bold'=>1, 'Pattern'=>1, 'FgColor'=>20, 'Align'=>'center')+$default_font);
		$formats['head']->setVAlign('vcenter');
		$formats['head']->setTextWrap();
		$formats['subtitle'] =& $workbook->addFormat(array('Bold'=>1, 'Pattern'=>1, 'FgColor'=>22, 'Align'=>'center')+$default_font);
		$formats['path'] =& $workbook->addFormat(array('Border'=>0)+$default_font);
		
		$formats['cell_left'] =& $workbook->addFormat($default_font);
		$formats['cell_left']->setTextWrap();
		$formats['cell_left']->setAlign('left');
		$formats['cell_left']->setVAlign('top');
		$formats['cell_right'] =& $workbook->addFormat($default_font);
		$formats['cell_right']->setTextWrap();
		$formats['cell_right']->setAlign('right');
		$formats['cell_right']->setVAlign('top');
		$formats['cell_center'] =& $workbook->addFormat($default_font);
		$formats['cell_center']->setTextWrap();
		$formats['cell_center']->setAlign('center');
		$formats['cell_center']->setVAlign('top');
		
		$columns = $this->columns;
		unset($columns['priority'], $columns['priority_text'], $columns['edit'], $columns['del'], $columns['up'], $columns['down']);
		
		/**
		 * Определяем ширину столбцов
		 */
		$column_width = array();
		$counter = 0;
		reset($columns); 
		while (list($index,$row) = each($columns)) { 
			$column_width[$counter++] = (int) str_replace('%', '', $row['width']);  
		}
		
		/**
		 * Приводим сумму всех столбцов к 100%
		 */
		$width_sum = array_sum($column_width);
		reset($column_width); 
		while (list($index,$row) = each($column_width)) { 
			$column_width[$index] = round(($row*100)/$width_sum, 0);
		}
		
		/**
		 * Задаем ширину столбцов в таблице Excel
		 */
		reset($column_width); 
		while (list($index,$width) = each($column_width)) { 
			$worksheet->setColumn($index, $index, $page_width * ($width/100));
		}
		
		/**
		* Формируем путь к текущей таблице
		*/
		$data = array('table_id' => $this->table['parent_table_id'], 'parent_id' => $this->return_id);
		do {
			$data = $this->buildPath($data['table_id'], $data['parent_id']);
		} while (!empty($data));
		
		/**
		 * Выводим путь к текущей странице
		 */
		$path_current = '';
		$path = '';
		if (!empty($this->param['path_current'])) {
			array_shift($this->path); // Удаляем последний элемент из пути
			$path_current = $this->param['path_current'];
		} else {
			$path_current_array = array_shift($this->path);
			$path_current = $path_current_array['name'];
		}
		
		
		if (empty($this->param['path'])) {
			$path_array = array_reverse($this->path);
		} else {
			$path_array = $this->param['path'];
		}
		reset($path_array); 
		while (list(,$row) = each($path_array)) { 
			$path .= "$row[name] :: "; 
		}
		$path .= $path_current;
		$path = trim($path);
		
		
		/**
		 * Заголовок и шапка таблицы 
		 */
		$worksheet->write(0, 0, $this->param['title'], $formats['title']);
		$worksheet->write(1, 0, $path, $formats['path']);
		$worksheet->mergeCells(1, 0, 1, count($columns)-1);
		
		$c1 = 2; $c2 = 3;
		
		if (count($this->merge_columns)==0) {
			$counter = 0;
			$data_start_row = $c1+1;
			reset($columns); 
			while (list($index,$row) = each($columns)) { 
				$row['title'] = html_entity_decode(strip_tags($row['title']));
				$worksheet->write($c1, $counter, $row['title'], $formats['head']);
				$columns[$index]['index'] = $counter;
				$counter++;
			}
		} else {
			$counter = 0;
			$data_start_row = $c2+1;
			reset($columns); 
			while (list($index,$row) = each($columns)) {
				$row['title'] = html_entity_decode(strip_tags($row['title']));
				if (!isset($this->merge_columns[$row['name']])) {
					/**
					 * Столбец не участвует в объединении
					 * Выводим в первую строку значение, во вторую - пустую ячейку и обьединяем
					 */
					$worksheet->write($c1, $counter, $row['title'], $formats['head']);
					$worksheet->write($c2, $counter, "", $formats['head']);
					$worksheet->mergeCells($c1, $counter, $c2, $counter);
				} else {
					/**
					 * Столбец участвует в объединении
					 */
					$merge_title = $this->merge_columns[$row['name']]['title'];
					if (!isset($this->merge_title[$merge_title]['printed'])) {
						$worksheet->write($c1, $counter, $this->merge_columns[$row['name']]['title'], $formats['head']);
						for ($i=1; $i<$this->merge_columns[$row['name']]['count']; $i++) {
							$worksheet->write($c1, $counter+$i, "", $formats['head']);
						}
						$worksheet->mergeCells($c1, $counter, $c1, $counter + $this->merge_columns[$row['name']]['count']-1);
						$this->merge_title[$merge_title]['printed'] = true;
					}
					$worksheet->write($c2, $counter, $row['title'], $formats['head']);
				}
				
				$columns[$index]['index'] = $counter;
				$counter++;
			}
		}
		
		/**
		 * Данные таблицы
		 */
		$prev_title = '';
		$row_counter = 0;
		reset($this->data); 
		while (list(,$row) = each($this->data)) { 
			
			reset($columns); 
			while (list(,$column) = each($columns)) { 
				
				if (isset($row[$column['name']])) {
					
					/**
					 * Subtitle
					 */
					if (
						(!isset($row['priority']) || $this->param['edit'] == false) 
						&& !empty($this->param['subtitle'])
						&& $prev_title != $row[ $this->param['subtitle'] ]
					) {
						$worksheet->write($data_start_row+$row_counter, 0, $row[ $this->param['subtitle'] ], $formats['subtitle']);

						for ($i=1; $i<count($columns); $i++) {
							$worksheet->write($data_start_row+$row_counter, $i, "", $formats['subtitle']);
						}
						$worksheet->mergeCells($data_start_row+$row_counter, 0, $data_start_row+$row_counter, count($columns)-1);
						$row_counter++;
						
						$prev_title = $row[ $this->param['subtitle'] ];
					}
					
					/**
					 * Подготовка значения для вывода в Excel
					 */
					$value = html_entity_decode($row[$column['name']]);
					
					// editable поля типа checkbox
					if ($value === 'true') {
						$value = cms_message('cms', 'Да');
					} elseif ($value === 'false') {
						$value = cms_message('cms', 'Нет');
					}
					
					// disabled checkbox используется как Да/Нет
					if (preg_match('~^[\s\t\n\r]*<input[^>]+type=[\'"]?checkbox[\'"]?[^>]+disabled[^>]*>[\s\t\n\r]*$~iUms', $value)) {
						if (preg_match('~checked~', $value)) {
							$value = cms_message('cms', 'Да');
						} else {
							$value = cms_message('cms', 'Нет');
						}
					}
										
					// Excel не понимает html тегов
					$value = preg_replace('~[\r\n\t]+~', ' ', $value);
					$value = preg_replace('~<span[^>]+class=[\'"]comment[\'"][^>]*>(.+)</span>~iUms', ' ($1)', $value);
					$value = preg_replace('~([^\s\t])<~', '$1 <', $value);
					$value = trim(strip_tags($value));
					
					/**
					 * Выравнивание в столбце
					 */
					if ($column['align'] == 'right') {
						$format = 'cell_right';
					} elseif ($column['align'] == 'center') {
						$format = 'cell_center';
					} else {
						$format = 'cell_left';
					}
					
					$worksheet->write($data_start_row + $row_counter, $column['index'], $value, $formats[$format]);
				} else {
					$worksheet->write($data_start_row + $row_counter, $column['index'], "", $formats['cell_left']);
				}
				 
			}
			$row_counter++;
			 
		}
		
		$workbook->send($this->table['table_name'].'.xls');
		$workbook->close();
		exit;
	}
}

?>