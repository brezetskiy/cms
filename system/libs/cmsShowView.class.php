<?php
/**
 * ����� ��� �������� ������ � ����������� � ���������������� ����������
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2006, Delta-X ltd.
 */

/**
 * �������� ������ � ����������� � ���������������� ����������
 * @package CMS
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class cmsShowView {
	
	/**
	 * ���������, ������� ����� ������������� ��� �������.
	 * ������������ ��� �������� ������� ��������� � ���� ������������ ������. � ��� ��������� �������� �� ���������.
	 * @var array
	 */
	private $column_skel = array(
		'order' => '',
		'editable' => false
	);
	
	/**
	 * ���������� � �������� �������
	 * @var array
	 */
	private $columns = array();

	/**
	 * ������� ������ �������
	 * @var array
	 */
	private $columns_priority = array();
	
	/**
	 * ��������, �� ������� ������ ���������
	 * @var int
	 */
	private $current_page = 0;
	
	/**
	 * �������� �������, ������� �������������. ���� �������� ������������ ��� ����, ���� ���
	 * ������� enum ���������� ���� _field_checked
	 * @var array
	 */
	private $editable = array();
	
	/**
	 * ������� ������ ������ ������� ��� ��������
	 * @var array
	 */
	private $events_order = array('add', 'edit', 'delete', 'copy', 'filter', 'xls');
	
	/**
	 * ������ � ������� ������� buildPath ��������� ���� � ������ �������
	 * @var array
	 */
	private $path = array();
	
	/**
	 * id ��������, �� ������� �������� ������
	 * @var int
	 */
	private $return_id = 0;
	
	/**
	 * �������� �� ������� �������� ������ ����� ��������� ����������
	 * @var string
	 */
	private $return_path = '';
	
	/**
	 * ������ ��� JavaScript
	 * @var int
	 */
	private $row_index = 0;

	
	/**#@+
	 * ���� ��� ���������� ��������
	 * @var string
	 */
	private $order_field = '';
	private $order_direction = 'DESC';
	/**#@-*/
	
	/**
	 * ������ ���������� � �������� �� �����
	 *
	 * @var bool
	 */
	private $display = false;
	
	/**
	 * ��������� ������ �������
	 * @access private
	 * @var array
	 */
	private $param = array(
		'add' => true, // ���������� �� ������ ��������
		'edit' => true, // ���������� �� ������ ��������������
		'delete' => true, // ���������� �� ������ �������
		'copy' => false, // ���������� �� ������ ����������
		'jslink' => '', // JavaScript, ������� ����� ���������� ��� ������ �� ����, ������� �������� link
		'title' => '', // �������� �������
		'subtitle' => '', // �������� �������, ������� �������� ������������
		'row_filter' => '', // �������� ������� ���������� ��������� ������
		'data_filter' => '', // �������� ������� ������ ��������� ������
		'show_parent_link' => false, // ���������� �� ��� ��� �������� �� ������� �����
		'parent_link' => '', // ������, ������� �������� ���, ������� ���� �� ������� �����
		'show_path' => true, // ���������� �� ����
		'path' => array(), // ����, ������� ���� �������� �������, (������ ���������� url,name)
		'path_current' => '', // �������� �������� �������, ������� ��������� � path
		'class_field' => '', // �������� �������, ������� ����� ��������� �����
		'show_title' => true, // ���������� ��������� �������
		'show_rows_limit' => true, // ���������� �����, ��� �������� ���������� ����� ������
		'show_filter' => true, // ���������� ������� ����� ���������� ������
		'excel' => true, // ���������� ������ ��� ���������� ������� � ������� Excel
		'priority' => true, // ���������� ������� ���������� ������
	);
	
	/**
	 * ����������� ������� � ����� �������
	 * @var array
	 */
	private $merge_title = array();
	private $merge_columns = array();
	
	
	/**
	 * ������ �� �� ������� ��������� �������, � ������� ����� ������� ������
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * ������ �� �������
	 * @var array
	 */
	private $data = array();
	
	/**
	 * ������ � ���������
	 * @var array
	 */
	private $events = array();
	
	/**
	 * ���������� � ��������
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * ����� ������, ������� ���� ���������
	 * @var int
	 */
	private $inserted_id = -1;
	
	/**
	 * SQL ������, ����������� �� ������, � �������� ����� ������� ��������� SQL
	 * @var array
	 */
	private $parsed_sql = array();
	
	/**
	 * ���������� �����, ������� ���������� ������� �� ����� ��������
	 * @var int
	 */
	private $rows_per_page = CMS_VIEW;
	
	/**
	 * ���������� �� ������� ��������, �� ������� ��������� ������ � id=$this->insert_id
	 * @var bool
	 */
	private $search_page = false;
	
	/**
	 * ���������� SQL ��������
	 * @var object
	 */
	private $SQLParser;
	
	/**
	 * ���������� � �������
	 * @var array
	 */
	private $table = array();
	
	/**
	 * �� ����� ����� �������� �������� �������. ���� �������� ������, �� ���������� �� �����������
	 * @var string
	 */
	private $table_language = '';
	
	/**
	* ������ ������ ����������
	* @var object
	*/
	private $Template;
	
	/**
	 * ����� ���������� ����� � �������, ��� ������� LIMIT
	 * @var int
	 */
	public $total_rows = 0;
	
	/**
	 * ������ � ������� ���������� �����
	 * @var int $view_start
	 */
	private $view_start = 0;
	
	/**
	 * ����� �������� ���������� ������ cmsShow
	 * @var int
	 */
	static private $instance_number = 0;
	
	/**
	 * �� �������� � ������� �������
	 *
	 * @var array
	 */
	private $filter_skip_tables = array();
	
	/**
	 * �� �������� � ������� ����
	 *
	 * @var array
	 */
	private $filter_skip_fields = array();
	
	/**
	 * �����������
	 *
	 * @param DB $DBServer
	 * @param string $data_query
	 * @param int $rows_per_page
	 * @param string $table_name
	 */
	public function __construct(DB $DBServer, $data_query, $rows_per_page = CMS_VIEW, $table_name = '') {
		global $DB;
		
		// ����� ���������� ������ �� ������� ��������
		self::$instance_number++;
		
		// ���� ������, � ������� ������ ���� ��������� ������
		$this->DBServer = $DBServer;
		
		// ������ �������, � ������� ��������� ������
		$this->Template = new Template(SITE_ROOT.'templates/cms/admin/cms_view');
		
		// ���������� SQL �������
		$this->SQLParser = new SQLParserMySQLi($DBServer, $data_query);
		
		// ���������� ��� �������������� �������
		$table_name = (empty($table_name)) ? $this->SQLParser->getTableName() : $table_name;
		
		// ���������� ���������� � �������
		$this->table = cmsTable::getInfoByAlias($this->DBServer->db_alias, $table_name);
		if (empty($this->table)) {
			trigger_error(cms_message('CMS', '���������� � ������� %s.%s �� ������� � ������� CMS_TABLES', $this->DBServer->db_alias, $table_name), E_USER_ERROR);
		}
		
		// ����, �� ������� ��������� �������� �������
		$this->table_language = globalVar($_GET['_tb_language_'.$this->table['id']], $this->table['default_language']);
		
		// ���������� ���������� � �������� � ��������
		$this->fields = cmsTable::getFields($this->table['id']);
		
		$this->param['title'] = $this->table['title'];
		
		// ���������� ����� �� ��������
		$this->rows_per_page = (isset($_COOKIE['rows_per_page_'.CMS_STRUCTURE_ID.'_'.$this->table['id']])) ?
			intval($_COOKIE['rows_per_page_'.CMS_STRUCTURE_ID.'_'.$this->table['id']]):
			$rows_per_page;
			
		// ����� ��������, �� ������� ������ ��������� ������������
		$this->current_page = abs(globalVar($_GET['_tb_start_'.$this->table['id']], 0));

		// ������, � ������� ���������� �����
		$this->view_start = $this->current_page * $this->rows_per_page;
		
		
		// id ���������� ����������� ������� � ������ �������
		$event_table_id = globalVar($_GET['_event_table_id'], -1); 
		if ($this->table['id'] == $event_table_id) {
			$this->inserted_id = globalVar($_GET['_event_insert_id'], -1);
		}
		
		// ���������� ������������� ������ ��������, �� ������� ��������� ����������� ������
		$event_type = globalEnum($_GET['_event_type'], array('insert', 'update'));
		$this->search_page = ($event_type == 'insert');

		
		// ����, �� �������� ������������ ����������
		$this->order_field = globalVar($_GET['_tb_order_field'][$this->table['id']], '');
		$this->order_direction = globalEnum($_GET['_tb_order_direction'][$this->table['id']], array('ASC', 'DESC'));
		
		// ������ �� ������������ ������
		$this->return_id = globalVar($_GET[$this->table['parent_field_name']], 0);
		
		// URL ��� ����������
		$this->return_path = substr(CURRENT_URL_FORM, 0, strpos(CURRENT_URL_FORM, '?'));
				
		/**
		 * ���������� id ������������� ������� � ������ �� ������������ ������
		 * ���� � ���� ���� �������������
		 */
		if ($this->table['cms_type'] != 'list' && !empty($this->return_id)) {
			$this->param['parent_link'] = $this->getParentData();
			$this->param['show_parent_link'] = (empty($this->param['parent_link'])) ? false : true;
		}
	}
	
	/**
	 * ��������� �� ������� �������
	 *
	 * @param string $table_name
	 */
	public function filterSkipTable($table_name) {
		$this->filter_skip_tables[$table_name] = 1;
	}
	
	/**
	 * ��������� �� ������� ����
	 *
	 * @param string $table_name
	 * @param string $field_name
	 */
	public function filterSkipField($table_name, $field_name) {
		$this->filter_skip_fields[$table_name][$field_name] = 1;
	}

	
	/**
	 * ��������� �������� �������
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setParam($name, $value) {
		$name = ($name == 'prefilter') ? 'row_filter' : $name;
		if (!isset($this->param[$name])) {
			trigger_error(cms_message('CMS', '����������� ������ �������� �������: %s. ��������� ��������: %s.', $name, implode(',', array_keys($this->param))), E_USER_WARNING); 
		} elseif (gettype($this->param[$name]) != gettype($value)) {
			trigger_error(cms_message('CMS', '����������� ������ �������� %s', $name), E_USER_WARNING); 
		} elseif (in_array($name, array('row_filter', 'data_filter')) && !function_exists($value)) {
			trigger_error(cms_message('CMS', '��������� ���� ������� "%s" - �� ����������.', $value), E_USER_ERROR);
		} else {
			$this->param[$name] = $value;
		}
	}
	

	/**
	 * ���������� ����� ������� ������� � ���������
	 * @param string $title
	 * @param array $columns
	 */
	public function mergeTitle($title, $columns) {
		$this->merge_title[$title] = $columns;
		reset($columns); 
		while (list(,$column) = each($columns)) { 
			 if (isset($this->merge_columns[$column])) {
			 	trigger_error("��� ������� $column ��� �������� ������������ ���������", E_USER_ERROR);
			 }
			 $this->merge_columns[$column] = array('count' => count($columns), 'title' => $title, 'show' => true);
		}
	}
	

	/**
	 * ��������� ������� ������� ���� ��������
	 * @param string $name ��� ������� ��� �������� �����!!!
	 * @param string $width
	 * @param string $align
	 * @param string $title
	 * @param string $text ������������ ��� ����������� ����������� �������, ����� � ���� ������
	 * @param bool $add_before
	 * @return void
	 */
	public function addColumn($name, $width, $align = '', $title = '', $text = '', $add_before = false) {
		
		$this->columns[$name] = $this->column_skel;
		$this->columns[$name]['name'] = $name; // �������� name ������������ � ������� ��� ��������
		$this->columns[$name]['title'] = (empty($title)) ? $this->getColumnTitle($name) : $title;
		$this->columns[$name]['width'] = $width;
		$this->columns[$name]['align'] = (empty($align)) ? $this->getColumnAlign($name) : $align;
		$this->columns[$name]['text'] = (empty($text)) ? '{'.$name.'}' : $text;
		
		// ���� ��������� ������� - � ������ ��� �����
		($add_before) ? array_unshift($this->columns_priority, $name) : array_push($this->columns_priority, $name);
	}	
	
	/**
	 * �������� �������������� ���������� � �������, ������� ����������� ��������.
	 * ������� ��������� �� ��������. ������������ ����� ���� ��������� �������� � �������
	 * � �������� ���������� �������. �������� ��� ���������� �������������� ����� ��� 
	 * ������������ ��������, ��� ��� �������� ��� �������� ���� � �����, ������� �� ������� 
	 * ��� ��������� � ������� ��� ����, ��� � �������������� �� �������� �� ������� �������.
	 * @access public
	 * @param array $params
	 * @return void
	 */
	public function addEventParams($params) {
		reset($params);
		while(list($key, $val) = each($params)) {
			$_GET[$key] = $val; // ���������� ��� ����, ��� � ��� ���������� ���� ��������� � ������ [+] (���������� ����� ������)
			$this->Template->iterate('/hidden_field/', null, array('name' => $key, 'value' => urlencode($val)));
		}
	}
	

	/**
	 * �������� ��������� ��� �������
	 * @return void
	 */
	public function setColumnParam($column_name, $param, $value) {
		if (!isset($this->column_skel[$param])) {
			// ����������� ������ �������� �������: %s. ��������� ��������: %s.
			trigger_error(cms_message('CMS', '����������� ������ �������� �������: %s. ��������� ��������: %s.', $param, implode(',', array_keys($this->column_skel))), E_USER_ERROR);
		}
		
		if (!isset($this->columns[$column_name])) {
			// ����� ���������� ��������� ������� ���������� �������� ������� ������� $cmsView->addColumn()
			trigger_error(cms_message('CMS', '����� ���������� ��������� ������� ���������� �������� ������� ������� $cmsView->addColumn()'), E_USER_ERROR);
		}
		
		$this->columns[$column_name][$param] = $value;
	}
	
	/**
	 * ���������� � �������
	 * 
	 * @return array
	 */
	public function getTableInfo() {
		return $this->table;
	}
	
	
	/**
	 * ���������� ��������� SQL ������� ��� false, ���� ������ ��� �� ����������.
	 * ������ ����� �������� ������ ����� ������ ������ display
	 *
	 * @return mixed
	 */
	public function getData() {
		if (!$this->display) return false;
		else return $this->data;
	}
	
	
	/**
	* �����, ������� ����������� ����� ����, ��� ������� �������, ������� ���������� �������
	* @return string
	*/
	public function display() {
		global $TmplDesign;
		
		$this->display = true;
		
		// onload ���������� �� ������� init � ��� �������, ������� � ��� ���������� �������� ������ ������ ��
//		$TmplDesign->iterate('/onload_var/', null, array('function' => 'var cmsView'.self::$instance_number.' = new cmsView();'));
//		$TmplDesign->iterate('/onload/', null, array('function' => 'cmsView'.self::$instance_number.'.init('.$this->table['id'].', '.self::$instance_number.');'));
		
		$this->Template->setGlobal('rows_per_page', $this->rows_per_page);
		$this->Template->setGlobal('table', $this->table);
		
		// ����, �� ������� ��������� �������� �������
		$this->Template->set('table_language', $this->table_language);
		
		// ����� ��������, �� ������� ������ ��������� ������������
		$this->Template->setGlobal('current_page', $this->current_page);
		
		$this->Template->iterate('/hidden_field/', null, array('name' => $this->table['parent_field_name'], 'value' => globalVar($_GET[$this->table['parent_field_name']], 0)));
		$this->Template->iterate('/hidden_field/', null, array('name' => '_table_id', 'value' => $this->table['id']));
		$this->Template->iterate('/hidden_field/', null, array('name' => '_start_row', 'value' => $this->view_start));
		
		// ����� ���������� ������ � �������
		if ($this->param['show_filter']) {
			$Filter = new cmsFilter($this->Template, $this->SQLParser, $this->DBServer, self::$instance_number);
			if($Filter->show($this->filter_skip_tables, $this->filter_skip_fields, $this->table['id'])) {
				$this->addEvent('filter', 'javascript:cmsView.showFilter('.self::$instance_number.');', true, true, true, '/design/cms/img/event/table/filter.gif', '/design/cms/img/event/table/filter_over.gif', '������', null, false);
			}
		} else { 
			$this->Template->set('show_filter', 'none');
		}
		
		// ��������� ���������, ������� ���������� ������� get
		$get = $_GET;
		unset($get['_start['.$this->table['id'].']']);
		unset($get['_REWRITE_URL']);
		unset($get['_event_insert_id']);
		unset($get['_event_table_id']);
		unset($get['_event_type']);
		$get = http_build_query($get);
		$this->Template->setGlobal('get_vars', $get);
		unset($get);

		// ������ �� ������������ ������
		$this->return_id = globalVar($_GET[$this->table['parent_field_name']], 0);
		$this->Template->setGlobal('return_id', $this->return_id);
		$this->Template->setGlobal('parent_table_id', $this->table['parent_table_id']);
		
		
		// ������������ ��������� �������
		$this->parseTableParams();
		
		// ������������ ��������� �������
		$this->parseColumnParams();
		
		// ������� �������
		$this->showEvents();
		
		// ����� ������ ������ � �������
		$this->displayLanguages();
		
		// ���������� - ���� ��������� ��������� ��� ���
		$this->Template->set('merged_columns', count($this->merge_columns));
		if (IS_DEVELOPER) {
			$this->Template->set('table_title', $this->param['title'].'</h2> ����� �������: <a href="/Admin/CMS/DB/Tables/Fields/?table_id='.$this->table['id'].'">'.$this->table['name'].'</a><br>');
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
		 * ��������� ������� ���������� � �������
		 */
		if (!empty($this->order_field)) {
			$order_by = array();
			if (!empty($this->param['subtitle'])) {
				// ���� ������������ ��������� ���������� � ������� � �������������, �� ������������ ������
				// ����� �� ���������
				// $order_by[ $this->param['subtitle'] ] = 'ASC';
				$this->param['subtitle'] = '';
			}
			$order_by[$this->order_field] = $this->order_direction;
			$this->SQLParser->changeOrder($order_by);
			
			unset($order_by);
		}
		
		/**
		 * �������� ���� ������ �������� �������, ������ ��� ���� ����� ����,
		 * ��� ����� ��������� ������� ���������� � ����������, ���� ���������
		 * ����� ��������� ���� SQL ������.
		 */
		if (!empty($this->table_language) && $this->table_language != LANGUAGE_CURRENT) {
			$this->SQLParser->changeTableLanguage(LANGUAGE_CURRENT, $this->table_language);
		}
		
		/**
		 * ���������� �������� �� ������� ��������� ����������� ������.
		 */
		if ($this->inserted_id > 0 && $this->search_page == true) {
			$this->getQueryPage();
		}
		
		/**
		 * ��������� SQL ������
		 */
		if (isset($_GET['output'][self::$instance_number]) && $_GET['output'][self::$instance_number] == 'xls') {
			$this->data = $this->SQLParser->execQuery();
		} else {
			$this->data = $this->SQLParser->execQuery($this->view_start, $this->rows_per_page);
		}
		
		$this->total_rows = $this->SQLParser->total_rows;
		
		/**
		 * ���� ������� � ������� �� ������ �������� ��� ���� � ��� ���� ��� �������� �� �������� ������
		 * ������ _event_insert_id, �� ������ ����� ��������� ������ �������.
		 * � ������ ���� �� ������. 
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
		 * �������� ������� ��������������� ��������� ����������
		 */
		if (isset($this->param['row_filter']) && !empty($this->param['row_filter'])) {
			reset($this->data);
			while(list($index, $row) = each($this->data)) {
				// ���� ������� ���������� ���������� ������ ��������, �� ��� ������ ��� ���� ������� ���� ��� � ������
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
		
		// �������� ������� ������ ���������� � ���������� �������
		$this->checkData();
		
		// ����� �������������� ������� � �������
		$this->createExtraColumns();
		
		// ���������� �������, ���������� ���� �������� ���������� ����� ���������� /th/ ������ �������
		$this->Template->setGlobal('total_columns', count($this->columns));
		
		// �������� ������������ ����������� ������� � ����� �������
		$this->checkMerged();
		
		// ���������� ������ ������
		if (isset($_GET['output'][self::$instance_number]) && $_GET['output'][self::$instance_number] == 'xls') {
			$this->outputXls();
		}
		
		/**
		 * ������� ����� �������
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
			// ���������� rowspan
			if (!empty($this->merge_columns) && !isset($this->merge_columns[$field_name])) {
				// ���� ���� ����������� ��������� �� ��� ���� ������� ��� �����������
				$field['colspan'] = 1;
				$field['rowspan'] = 2;
				$this->Template->iterate('/th1/', null, $field);
				
			} elseif (!empty($this->merge_columns) && isset($this->merge_columns[$field_name])) {
				// ���� ���� ����������� ��������� � ��� ���� ������� ���� �����������
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
			// ���������� ��� ����������� ��������
			$this->Template->iterate('/th/', null, $field);
			$this->Template->iterate('/parent_cell/', null, array('align' => $field['align']));
		}
		
		/**
		 * ������� ������
		 */
		$rows = array();
		$prev_title = '';
		if ($this->total_rows > 0) {
			$row_template = $this->makeRowTemplate();
			reset($this->data);
			while(list(,$row) = each($this->data)) {
				
				/**
				* ����������� ���������, ������� ��������� ������� �������
				* ������ ��� ������ ��� ������, � ������� ��� ���� priority ���
				* �� ���������� ���� ����������. � ��� ������� ������� �������� ����,
				* ������� ����� ������������ � �������� subtitle
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
		* ��������� ���� � ������� �������
		*/
		$data = array('table_id' => $this->table['parent_table_id'], 'parent_id' => $this->return_id);
		do {
			$data = $this->buildPath($data['table_id'], $data['parent_id']);
		} while (!empty($data));
		
		/**
		 * ����������� URL ��� path
		 */
		$url = (strpos(CURRENT_URL_FORM, '?')) ? substr(CURRENT_URL_FORM, 0, strpos(CURRENT_URL_FORM, '?')) : CURRENT_URL_FORM;
		$structure = preg_split('/\//', substr($url, strlen('/Admin')), -1, PREG_SPLIT_NO_EMPTY);
		
		// ��� ������, ������� ��������� �� ������ ������� �������� �������� parent_field ������� �� ������� ����
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
		
		// ���� ��������� ������� ����� ������ �� ������ �������, ������ ��� ������ ��������� ���� �� ����
		if (!empty($this->path) && !empty($this->path[count($this->path)-1]['table_id'])) {
			$this->path[] = array('id' => 0, 'name' => cms_message('CMS', '�������'), 'table_id' => 0, 'parent_field' => '', 'url'=>'./');
		} elseif (!empty($this->path)) {
			array_pop($structure);
			$this->path[] = array('id' => 0, 'name' => cms_message('CMS', '�������'), 'table_id' => 0, 'parent_field' => '', 'url'=>'/Admin/'.implode('/', $structure).'/');
		}
		unset($prev_table);
		unset($structure);
		
		/**
		 * ������� ���� � ������� ��������
		 */
		if (!empty($this->param['path_current'])) {
			array_shift($this->path); // ������� ��������� ������� �� ����
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
		
		// ����� ������ �������
		$this->displayPagesList();
		
		// ������ � ������� ������
		return $this->Template->display();
	}
	

	
	/**
	 * PRIVATE
	 */
	
	
	/**
	 * ����� ������ ������ � �������
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
	 * ������� ����������� �������
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
	 * ��������� ������������ ������������ ������� � ����� �������.
	 * ����������� ������� ������ ���� ���� �� ������
	 */
	private function checkMerged() {
		/**
		 * ������ �������� ������������ ����������� ������� � ����� �������
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
		 	// ����� �������������� ���������� ��������� ����� �� ������� ���� �� ������ ��� ���.
		 	// ����� Sn ������ n ������ �������������� ���������� ���������� ��������:
		 	// Sn=(a1+an/2)*n=(2*a1+d(n-1)/2)*n, ��� d - �������� �������������� ����������
		 	$table_columns_sum = ((1+max($check)-(min($check)-1))/2)*count($check);
		 	$merge_columns_sum = ((count($columns)+1)/2)*count($columns);
		 	if ($table_columns_sum != $merge_columns_sum) {
		 		// ������� �� ���� ���� �� ������
		 		$this->deleteMerged($title);
		 	}
		}
	}
	
	/**
	 * ���������� ������������ �������
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
	 * ���������� �������� �������
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
	 * ��������� ������������ �������� ���������� ��� ������� ����� �� �������
	 * 
	 * @return void
	 */
	private function parseColumnParams() {
		reset($this->columns);
		while(list($index, $row) = each($this->columns)) {
			if ($row['editable'] == true) {
				// �������� ������ "��������� ���������"
				$this->Template->set('show_update_button', 1);
			}
		}
	}
	
	/**
	 * ����� ������� ������������ ����������
	 *
	 */
	private function createExtraColumns() {

		// ������� ����������
		if (isset($this->fields['priority']) && $this->param['priority']) {
			$this->addColumn('priority', '5%', 'center', cms_message('CMS', '�������'), '<img src="/design/cms/img/icons/table_sort.gif" border="0" class="move"><input type="hidden" name="id[]" value="{id}">');			
		}
		
		// ������� � ������� �� ��������������
		if ($this->param['edit']) {
			$this->addColumn('edit', '5%', 'center', cms_message('CMS', '���.'), '<a href="javascript:void(0);" onclick="EditWindow(\'{id}\', '.$this->table['id'].', \''.CMS_STRUCTURE_URL.'\', \''.CURRENT_URL_LINK.'\', \''.LANGUAGE_CURRENT.'\', \'\');return false;" title="'.cms_message('CMS', '�������������').'"><img src="/design/cms/img/icons/change.gif" width="16" height="16" border="0" alt="'.cms_message('CMS', '�������������').'"></a>');
		}
		
		// ������� � ������ �� ��������
		if ($this->param['delete']) {
			$this->addColumn('del', '5%', 'center', cms_message('CMS', '���.'), '<a href="/action/admin/cms/table_delete/?_return_path='.CURRENT_URL_LINK.'&_language='.LANGUAGE_CURRENT.'&_table_id='.$this->table['id'].'&'.$this->table['id'].'[id][]={id}&_language_'.LANGUAGE_CURRENT.'" title="'.cms_message('CMS', '�������').'" onclick="return confirm(\''.cms_message('CMS', '�������').'?\')"><img src="/design/cms/img/icons/del.gif" width="16" height="16" border="0" alt="'.cms_message('CMS', '�������').'"></a>');
		}
		
	}
	
	/**
	 * �������� ������� ������ ����� � ���������� SQL �������
	 *
	 */
	private function checkData() {
		/**
		 * ����������, ������� ������ �����, ����� ���� ������� priority � ������� 
		 * � �������� � ������� ����� ������������� (���� ������ edit).
		 */
		if (!empty($this->data) && isset($this->fields['priority']) && $this->param['priority']) {
			// ���������, ���� �� � ���������� ������� ������� priority
			$row = reset($this->data);
			if (!isset($row['priority'])) {
				trigger_error(cms_message('CMS', '��������� ������� ������ ��������� ������� priority ��� � ������� ������ ���� ��������� ����������'), E_USER_WARNING);
			}
		}
		// ��������� ���� �� ���� id � ������������� �������
		if (!empty($this->data) && ($this->param['edit'] || $this->param['delete'])) {
			$row = reset($this->data);
			if (!isset($row['id'])) {
				trigger_error(cms_message('CMS', '��������� ������� ������ ��������� ������� id'), E_USER_WARNING);
			}
		}
	}

	/**
	 * ����� ������ �������
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
	 * �����������, �� ����� �������� ��������� ������ ����������
	 * @return void
	 */
	private function getQueryPage() {
		
		$query = $this->SQLParser->getQueryArray();
		
		// ���������� alias ��� �������
		$alias = $this->table['name'];
		if (preg_match("/^FROM[\s\n\r\t]+([^\s\n\r\t]+)[\s\n\r\t]+(?:AS[\s\n\r\t]+)?([^\s\n\r\t]+)/i", trim($query['FROM']), $matches)) {
			$alias = str_replace('`', '', $matches[2]);
		}
		
		if (!isset($query['GROUP BY']) && isset($query['ORDER BY'])) {
			// ��������� ��� ������� � ORDER BY, ��� ��� ���� �������� ������ $query['GROUP BY']
			// �� ��� ����� ���� ����� ORDER BY ����, ��� ������� � ������
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
	* ���������� ������ �� ������������ ������ ��� $this->param[parent_link]
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
			// �������, ����������� ������� ���� �� ����� parent �������
			$parent_id = 0;
			$parent_field = 'parent';
			$parent_link = $return_path_parent;
		}
		return $parent_link.'?'.$parent_field.'='.$parent_id;
	}
	
	
	/**
	* ������� ������ ������� (����� ��� ���������� � �������� �������)
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
			 * ��������� ������������� �������
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
					// ��������� ���������� enum �����
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
	* �������� ����������� [[id]] �� �������� ���������� $row['id']
	* 
	* � ������ ������� ����� ����������� ��������� [[row_index]] � [[return_path]] ��� �������� �������������
	* @param string $content ������
	* @param array $row �������� �� ��
	* @return string
	*/
	private function rowParser($content, $row) {
		
		// ����� ��� ����
		if (isset($row['id']) && $this->inserted_id == $row['id']) {
			$row['_class'] = 'last_inserted';
		} elseif (isset($row['_class']) && !empty($row['_class'])) {
			// ���������� ��, ��� ������� � ������� _class
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
	 * ���������� ���� � ������ �������, ���� ��� ������� � ������� ���������
	 * @param int $table_id
	 * @param int $id
	 * @return array
	 */
	private function buildPath($table_id, $id) {
		if (empty($table_id)) return array();
		
		// ���������� ������������ ������� ��� ������
		$cms_table = cmsTable::getInfoById($table_id);
		if ($cms_table['cms_type'] == 'list') {
			$cms_table['parent_field'] = 0;
		}
		
		// ���� �� ������� ���� ��� ����������� - �������
		if (empty($cms_table['fk_show_name'])) return array();
		
		// ���������� �������� ������������� �������
		$query = "
			SELECT 
				`$cms_table[fk_show_name]` AS name,
				$cms_table[parent_field_name] AS parent_id
			FROM `$cms_table[name]`
			WHERE id='$id'
		";
		$path = $this->DBServer->query_row($query);
		
		// ���� ��� ������ �� ������� �������, �� �������
		if (empty($path)) return array();	
		
		// ��������� ��� ���� ������� � ����
		$this->path[] = array(
			'id' => $id,
			'name' => $path['name'],
			'table_id' => $cms_table['parent_table_id'],
			'parent_field' => $cms_table['parent_field_name']
		);
		
		// ���� ��� ������������� ������� - �������	
		if ($cms_table['cms_type'] == 'list') return array();
		
		return array('table_id' => $cms_table['parent_table_id'], 'parent_id' => $path['parent_id']);
	}
	

	/**
	 * �����, ������� ��������� ����������� �������, ������� ��������� ������ �������
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
	 * �������� �������
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
	 * �������� ������� ���������� �������
	 */
	public function orderEvents() {
		$this->events_order = func_get_args();
	}
	
	/**
	 * ������� ������� � �������
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
		
		// ������� �������, ������� ������� �� ������
		reset($events);
		while (list($name) = each($events)) {
			$this->Template->iterate('/event_button/', null, $this->events[$name]);
		}
		unset($events);
		
		// ���� ������� > 0 �� ���������� �������� ��������� ����� ��������
		$this->Template->set('event_counter', count($this->events));
	}
	
	
	
	/**
	 * ���������� ���������� �������
	 */
	private function parseTableParams() {	
		// Checkbox column
		if ($this->param['delete'] || $this->param['edit']) {
			// ����������� �� 18.12.2008 ������� ������� �������� � id �� _id, ��� ��� �� ���� ����������� �������� ������� id
			$this->addColumn('_id', '2%', 'center', '<input type="checkbox" class="check_all">', '<input type="checkbox" name="'.$this->table['id'].'[id][]" value="{id}" class="id">', true);
		}
		
		// ��������� �������
		if ($this->param['excel']) $this->addEvent('xls', CURRENT_URL_FORM.'&output['.self::$instance_number.']=xls', true, true, true, '/design/cms/img/event/table/xls.gif', '/design/cms/img/event/table/xls_over.gif', '������� � ������� Excel', null, true);
		if ($this->param['delete']) $this->addEvent('delete', '/action/admin/cms/table_delete/', false, true, true, '/design/cms/img/event/table/delete.gif', '/design/cms/img/event/table/delete_over.gif', '�������', '�� ������� ��� ������ ������� ���������� ������?', true);
		if ($this->param['edit']) $this->addEvent('edit', 'javascript:cmsView.editWindow(this, 0)', false, true, true, '/design/cms/img/event/table/edit.gif', '/design/cms/img/event/table/edit_over.gif', '�������������', null, true);
		if ($this->param['copy']) $this->addEvent('copy', 'javascript:cmsView.editWindow(this, 1)', false, true, false, '/design/cms/img/event/table/copy.gif', '/design/cms/img/event/table/copy_over.gif', '����������', null, true);
		if ($this->param['add']) $this->addEvent('add', 'javascript:cmsView.addWindow(this, \''.http_build_query($_GET).'\');', true, false, false, '/design/cms/img/event/table/new.gif', '/design/cms/img/event/table/new_over.gif', '��������', null, true);
	}
	
	private function outputXls() {
		require_once 'Spreadsheet/Excel/Writer.php';
		ob_end_clean();
		
		/**
		 * ��� ����� ����������� ������ ���� ������� � ��������� �� ����� 30 ��������
		 */
		$title = (empty($this->param['title']) ? cms_message('cms', '�������') : substr($this->param['title'], 0, 30));
		$title = preg_replace('~:~', '-', $title);
		
		/**
		 * ��������� ������ � �������� cmsShowView ����������� � ��������� - ����������
		 * �� �������� � ���������� �������� ������������ ����-��. ��� �������� �������������
		 * 100% ������ �������
		 */
		$page_width = globalVar($_GET['output_xls_width'], 120);
		
		$workbook = new Spreadsheet_Excel_Writer(); 
		$workbook->setTempDir(TMP_ROOT);
		$worksheet =& $workbook->addWorksheet($title); 
		$worksheet->setMargins(0.3937);
		$worksheet->setSelection(0, 40, 0, 40);
		
		/**
		 * ������� ������� ������
		 */
		$default_font = array('FontFamily' => 'Verdana', 'Size' => 9, 'Border'=>1);
		$workbook->setCustomColor(20, 203, 224, 255); // bgColor �����
		$workbook->setCustomColor(21,  63, 145, 255); // Color ��������� �������
		$workbook->setCustomColor(22, 224, 237, 255); // Color ��� subtitle
		
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
		 * ���������� ������ ��������
		 */
		$column_width = array();
		$counter = 0;
		reset($columns); 
		while (list($index,$row) = each($columns)) { 
			$column_width[$counter++] = (int) str_replace('%', '', $row['width']);  
		}
		
		/**
		 * �������� ����� ���� �������� � 100%
		 */
		$width_sum = array_sum($column_width);
		reset($column_width); 
		while (list($index,$row) = each($column_width)) { 
			$column_width[$index] = round(($row*100)/$width_sum, 0);
		}
		
		/**
		 * ������ ������ �������� � ������� Excel
		 */
		reset($column_width); 
		while (list($index,$width) = each($column_width)) { 
			$worksheet->setColumn($index, $index, $page_width * ($width/100));
		}
		
		/**
		* ��������� ���� � ������� �������
		*/
		$data = array('table_id' => $this->table['parent_table_id'], 'parent_id' => $this->return_id);
		do {
			$data = $this->buildPath($data['table_id'], $data['parent_id']);
		} while (!empty($data));
		
		/**
		 * ������� ���� � ������� ��������
		 */
		$path_current = '';
		$path = '';
		if (!empty($this->param['path_current'])) {
			array_shift($this->path); // ������� ��������� ������� �� ����
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
		 * ��������� � ����� ������� 
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
					 * ������� �� ��������� � �����������
					 * ������� � ������ ������ ��������, �� ������ - ������ ������ � ����������
					 */
					$worksheet->write($c1, $counter, $row['title'], $formats['head']);
					$worksheet->write($c2, $counter, "", $formats['head']);
					$worksheet->mergeCells($c1, $counter, $c2, $counter);
				} else {
					/**
					 * ������� ��������� � �����������
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
		 * ������ �������
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
					 * ���������� �������� ��� ������ � Excel
					 */
					$value = html_entity_decode($row[$column['name']]);
					
					// editable ���� ���� checkbox
					if ($value === 'true') {
						$value = cms_message('cms', '��');
					} elseif ($value === 'false') {
						$value = cms_message('cms', '���');
					}
					
					// disabled checkbox ������������ ��� ��/���
					if (preg_match('~^[\s\t\n\r]*<input[^>]+type=[\'"]?checkbox[\'"]?[^>]+disabled[^>]*>[\s\t\n\r]*$~iUms', $value)) {
						if (preg_match('~checked~', $value)) {
							$value = cms_message('cms', '��');
						} else {
							$value = cms_message('cms', '���');
						}
					}
										
					// Excel �� �������� html �����
					$value = preg_replace('~[\r\n\t]+~', ' ', $value);
					$value = preg_replace('~<span[^>]+class=[\'"]comment[\'"][^>]*>(.+)</span>~iUms', ' ($1)', $value);
					$value = preg_replace('~([^\s\t])<~', '$1 <', $value);
					$value = trim(strip_tags($value));
					
					/**
					 * ������������ � �������
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