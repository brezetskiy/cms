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

/**
 * �������� HTML ���� ��� �������������� ������ � ���������������� ����������
 * @package CMS
 * @subpackage CMS
 */
class cmsShowEdit {
	
	/**
	* ������
	* @var object
	*/
	private $Template;
	
	/**
	* ��������� �������� ����
	* @var array
	*/
	private $current_row_param = array();
	
	/**
	* ������ ��� ���������� �����
	* @var array
	*/
	private $data = array();
	
	/**
	 * ����, �������� ������� ����� null
	 * @var array
	 */
	private $null_values = array();
	
	/**
	* ���� � ������� � ������� ��������� ����� Submit ����� � ������������� ������
	* @var string
	*/
	private $return_error;
	
	/**
	* ���������� � �������
	* @var array
	*/
	private $table = array();
	
	/**
	* ���������� � ��������
	* @var array
	*/
	private $fields = array();
	
	/**
	 * ����������� � ��
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * ��������, ������� ����� ���������� �� ������� �����
	 * @var array
	 */
	private $fkey_data = array();
	
	/**
	 * ����, ��������� � ���, ��� ��������� ����� ������������ � ������� ������
	 * @var bool
	 */
	private $copy = false;
	
	/**
	 * ������ �������, ������������� � �������.
	 *
	 * @var array
	 */
	private $modules = array();
	
	/**
	 * ��������, �� ������� ������ ���������
	 * @var int
	 */
	protected $current_page = 0;
	
	/**
	 * ������ � ���������
	 * @var array
	 */
	protected $events = array();
	
	
	/**
	 * ����� ������, ������� ���� ���������
	 * @var int
	 */
	protected $inserted_id = -1;
	
	/**
	 * SQL ������, ����������� �� ������, � �������� ����� ������� ��������� SQL
	 * @var array
	 */
	protected $parsed_sql = array();
	
	/**
	 * ���������� �����, ������� ���������� ������� �� ����� ��������
	 * @var int
	 */
	protected $rows_per_page = CMS_VIEW;
	
	/**
	 * ���������� �� ������� ��������, �� ������� ��������� ������ � id=$this->insert_id
	 * @var bool
	 */
	protected $search_page = false;
	

	/**
	 * �� ����� ����� �������� �������� �������. ���� �������� ������, �� ���������� �� �����������
	 * @var string
	 */
	protected $table_language = '';
		
	/**
	 * ����� ���������� ����� � �������, ��� ������� LIMIT
	 * @var int
	 */
	public $total_rows = 0;
	
	/**
	 * ������ � ������� ���������� �����
	 * @var int $view_start
	 */
	protected $view_start = 0;
	
	/**
	 * ������� ����������� ������ cmsShow
	 * @var int
	 */
	static protected $instance_counter = 0;
	
	/**
	 * ����� �������� ���������� ������ cmsShow
	 * @var int
	 */
	protected $instance_number = 0;
	
	/**
	 * ���������� � ������� ����� ��������� ���������� ����� �� ����, ��� 
	 * �� ����� �������� ���� id
	 *
	 * @var string
	 */
	private $tmp_dir = '';
	
	/**
	 * ���� ���������� ���������� �������
	 *
	 * @var bool
	 */
	private $is_group_update = false;
	
	/**
	 * ��������� id �� �������� $this->data[id] ���������� � ������� ���������� ��� � �������
	 * ����� �� ���������� $this->data[id]=0, � ������� ext_multiple �� �������� ������ ������ �� ��
	 * ��� ��� ���������� ������� ������������ ���� � ��� ���� ��� ��� ��������� ���������� �������� id.
	 *
	 * @var mixed
	 */
	private $id = 0;
	
	/**
	 * ���������� ����� �����������
	 *
	 * @var int
	 */
	private $tmpl_devider = 0;
	
	/**
	 * �����������
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
		 * ���������� ��������� ��
		 */
		$this->table = cmsTable::getInfoById($table_id); 
		$this->fields = cmsTable::getFields($table_id);
		$this->DBServer = DB::factory($this->table['db_alias']);
		
		// ���������� ��� �������� ������
		$this->tmp_dir = (isset($_SESSION['ActionError']['tmp_dir'])) ?
			$_SESSION['ActionError']['tmp_dir']:
			Auth::getUserId().'/'.$this->table['name'].'/'.uniqid().'/';
			
		// ��������� ���������� � ������ ��� ��
		$this->data = $this->load($id_list);
		
		// ������� ������
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
	 * ���������� ����������
	 * @param void
	 * @return string
	 */
	public function show() {
		return $this->Template->display();
	}
	
	
	/**
	 * ���� �������� ������, ��� ������ ������������ � ������, � ����� �� �� �������,
	 * �������������� ������� �� ������, ������� ����������� ��� �������� ������
	 *
	 * @return array
	 */
	private function loadFromError() {
		
		$data = $_SESSION['ActionError'][$this->table['id']];

		/**
		 * ��������� dummie_fields, ���� �� ��������� ���� �������, �� �� �� �������,
		 * �� ����� ������ ��� ����� ����� �������� �� ���������.
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
		 * ��������� NULL ����
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
	 * ��������� ����� ������ � �������
	 * 1. ���� ������ �� ��������� ������ � ������� ��� ����� � stick = true
	 * 2. ���� ���������� ����������
	 *
	 * @return array
	 */
	private function loadFromGET() {
		global $DB;
		
		$data = $this->loadStick();
		$get = $_GET;
		
		// ������������ ���������� GET ������� ������
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
		
		// ���������� �������� NULL
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
	 * ��������� ��������, ������� ���� ��������� � ��������� ������
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
	 * �������� ������ � ������������ �������
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
		
		// �����
		if ($this->copy == true) $data['id'] = 0;
		
		// ������������� ������������, ������ ����� ���, ��� ���������� 
		reset($data);
		while (list($key,$val) = each($data)) {
			// ���� ����� htmlspecialchars ���������� NULL �� �� ����� ������ ������ � �� ������ �� ������ ���������� ��� NULL, � ��� ���
			if (!is_null($val)) { 
				$data[$key] = htmlspecialchars($val);
			}
		}
		
		// ��������� �������� ������� ������
		$data = array_merge($data, $this->loadFKey($id));
		return $data;
	}
	
	/**
	 * ��������� ����������
	 * @param int $id
	 * 
	 * @return array
	 */
	private function load($id) {
		global $DB;
		$data = array();
		// ������������ ��������, ������� ���������� ��������
		if (isset($_SESSION['ActionError'][$this->table['id']]) && !empty($_SESSION['ActionError'][$this->table['id']])) {
			$data = $this->loadFromError();
		} elseif (empty($id)) {
			$data = $this->loadFromGET();
		} elseif (count($id) == 1) {
			$data = $this->loadFromTable(implode(",", $id));
		}
		

		/**
		 * ����������, ����� �������� ����� null. ���������� �������� null ��� �� Null ����� �� ������� $this->data
		 * ���������� ��-�� ���� ��� ����� ���� ��� ���� �� ����� ������ ����� ��������, �� ������ ���� ������������ ����� ������� NULL,
		 * (��� ������ ���� ���������� ������� ����). ���� ��������������� ����.
		 * 
		 * ��� �����, ������� ��������� � ������� ����� ����, ��� ��������� ������. ��������� ���� ������� ���� � ��
		 * ����������� � ������ ����������� NULL ��������
		 */
		reset($data); 
		while (list($field,$value) = each($data)) { 
			 if (is_null($value)) {
			 	$this->null_values[$field] = 1;
			 }
		}
		
		// ��������� ������
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			if (!$field['is_real'] || isset($data[$field_name])) {
				continue;
			}
			
			if ($field['data_type'] == 'date' && $field['field_type'] == 'hidden') {
				// ��������, ������� ������ ����� � SQL ������
				$data[$field_name] = date('Y-m-d');
			} elseif ($field['data_type'] == 'date') {
				$data[$field_name] = date('d.m.Y');
			} elseif ($field['data_type'] == 'datetime' && $field['field_type'] == 'hidden') {
				// ��������, ������� ������ ����� � SQL ������
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
	 * ��������������� ���������� ����
	 *
	 * @param string $field
	 * @param string $param
	 * @param string $value
	 */
	public function overrideFieldParam($field, $param, $value) {
		$this->fields[$field][$param] = $value;
	}
	
	/**
	 * ������������� ��������, ������� ����� ���������� �� ������� �����
	 *
	 * @param string $field_name
	 * @param array $data (id, parent, real_id, name)
	 */
	public function setFKeyData($field_name, $data) {
		$this->fkey_data[$field_name] = $data;
	}
	
	
	/**
	 * ��������� �������� ��� ������ n:n (����� ������� �������)
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
	* ������������ ����
	* @param void
	* @return void
	*/
	public function parseFields() {
		global $DB;
		
		/**
		 * ��� ��� ���� ���� ������ ������� �� ���� ������� � �������, � ��������� ��� � ����� ������,
		 * �� ���������� ������������� ����� ������� ����, ����� ����, ��� ��� ����� ��������
		 */
		$skip_money_fields = array();
		
		// ������, � ������� ���������� ���� � ��������
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
			
			// ���������� ���� ������� �� �������� ��� ��������� ��������������
			if ($this->is_group_update && !$field['group_edit']) {
				continue;
			}
			
			// ���������� priority ����, ��� �� ������ ���������� ����� ��������������
			if ($field_name == 'priority' || $field['data_type'] == 'timestamp') {
				continue;
			}
			
			/**
			 * ������������ ���������� ����, ������ ��� ���� �� ������ ��������� ������ �� �����
			 * � �� ������������ ���� �������.
			 * @todo hidden ���� �� ����� ��������� � ���� �������� NULL? - ��������� ���.
			 */
			if ($field['cms_type'] == 'hidden') {
				$this->showHidden($field_name);
				continue;
			}
			
			/**
			 * ��������� ����� ��� � �������
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
				$this->tmpl_devider = $this->Template->iterate('/devider/', null, array('name' => 'default', 'title' => '�������', 'class' => $class));
			}
			
			// $this->current_row_param['title'] .= "<br><span class=comment>$field[cms_type]</span>";

			// ��������� ������������ ��� ���������� ����
			if ($field['is_obligatory']) {
				$this->current_row_param['title'] = '<font color="red">*</font>' . $this->current_row_param['title'];
			}

			// ������ ���� � ������
			if ($field['is_multilanguage'] && !is_file(SITE_ROOT.'design/cms/img/language/'.$field['language'].'.gif')) {
				$this->current_row_param['title'] .= ' ['.$field['language'].']';
			} elseif ($field['is_multilanguage']) {
				$this->current_row_param['title'] .= ' <img src="/design/cms/img/language/'.$field['language'].'.gif" width="16" height="12" border="0" alt="'.$field['language'].'" align="absmiddle">';
			}
			
			// �������� ����, �������� ������� = null
			if ($field['is_real'] && is_null($this->data[$field_name]) && $field['is_nullable'] && is_null($field['column_default'])) {
				// �������� $field[is_real] ��������� ��-�� ����, ��� $x = null; isset($x) == false;
				// ����, ������� �� ��������� ������ ���� null
				$this->current_row_param['null_checked'] = 'checked';
				$this->Template->iterate('/onload/', null, array('function'=>"set_null('".$this->table['name']."_$field_name', true);"));
			
			} elseif (!isset($this->null_values[$field_name])) {
				// ����, ������� �� ����������� ��� NULL ������������ �� ������ NULL ��������
				$this->current_row_param['null_checked'] = '';
				
			} elseif (isset($this->null_values[$field_name]) && in_array($field['pilot_type'], array('date', 'time'))) {
				// ��� ���, ���� ���� ����������� NULL �������� ��������� ������������� ��������� ����, ������� ������ ������������
				// ���� ������ ������� � NULL ����
				$this->current_row_param['null_checked'] = 'checked';
				$this->Template->iterate('/onload/', null, array('function'=>"set_null('".$this->table['name']."_$field_name', true);"));
				
			} elseif (isset($this->null_values[$field_name]) && (isset($this->data[$field_name]) && !empty($this->data[$field_name]))) {
				// ��� ����, ����� ���, ������� �������� ��������, ���� ���� � ��� ����������� NULL ����� ����������
				$this->current_row_param['null_checked'] = '';
				
			} else {
				// ��������� ���� - ��� NULL
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
					// ���������� ����, ������� ��� �������� � ���������������� ����������
					continue;
				} else {
					// ��������� ��, ��� ������ ���� ������ �����
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
				
				// �� ���������� ��� ����, ��� �� ����� �������� � ���������� � �� ����� �������� 
				// ��� update
				
			} elseif ($field['cms_type'] == 'fixed_open') {
				
				$this->showHidden($field_name);
				$this->showFixedOpen($field_name);
				
			} elseif ($field['cms_type'] == 'html') {
				
				$this->showHTML($field_name);
				
			} else {
				
				$this->showErrorField($field_name);
//				x($field);
//				trigger_error(cms_message('CMS', "���������� ���������� ������ ������ ���� %s. ����� ��������� ���������� �������� � �������� ������� `%s`.", $field_name, $this->table['name']), E_USER_ERROR);
			}
		}
	}
	
	/**
	* ������ ����, ������� ����� ���� ������� � �� ������������ ��������� � ������-����������
	* @param string $field_name
	* @param string value
	* @return void
	*/
	private function dummieField($field_name, $value) {
		$this->Template->iterate('/hidden/', null, array('name' => $this->table['id'].'[_dummie_fields_]['.$field_name.']', 'value' => $value));
	}

	/**
	* ���� checkbox
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
	* ���� EXT SELECT
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
	 * ���� BIG SELECT
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
	* ���� EXT SELECT MULTIPLE
	* @param string $field_name
	* @return void
	*/
	private function showExtMultiple($field_name) {
		global $DB, $TmplDesign;
		
		$info = cmsTable::getFkeyNNInfo($this->fields[$field_name]['table_id'], $this->fields[$field_name]['fk_table_id'], $this->fields[$field_name]['fk_link_table_id']);
		
		// ������ ���� ������������ � ���� ������� ������������ checkbox'��
		$this->dummieField($field_name, '');		
		
		// ���������� �������� ������� ������� ������, ��� ������������� �������
		$parent_tables = cmsTable::getParentTables($this->fields[$field_name]['fk_table_id']);
		$row = $this->current_row_param;
		
		$TmplDesign->iterate('/onload/', null, array('function' => 'extMultipleOpen_'.$field_name.'();'));
		$tmpl_row = $this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
			'field' => $field_name, // ���������� ��� �������� ����������� ����� ������� extMultipleOpen_$field_name
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
		
		// ���������� �������� ���������, ������� ���������� �������������
		if ($table['cms_type'] == 'list') {
			
			// �� ����������� �������
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
			
			trigger_error(cms_message('CMS', '��� ������, ������� ���� ������� ��������� �� ���� ���������� ���������� �������, � ������� ����� ��������� �����'), E_USER_ERROR);
		
		} else {
			
			$query = "select name from cms_table where id='".$this->fields[$field_name]['fk_link_table_id']."'";
			$fk_link_table_name = $DB->result($query);
			
			if (empty($fk_link_table_name)) {
				trigger_error(cms_message('CMS', '��������� ������������ �������� �������� ����� ��� ������� %s.%s', $this->table['name'], $field_name), E_USER_ERROR);
			}
			
			// ������� ������� �������
			$query = "
				SELECT id, `$table[fk_show_name]` AS name
				FROM `$table[table_name]`
				WHERE `$table[parent_field_name]`=0
				ORDER BY `$table[fk_order_name]` ASC
			";
			$data = $this->DBServer->query($query, 'id');
			
			// ���������� �������, ������� ���������� �������, ��� ��� �������� ������� �������� ���������� checkbox'�
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
			
			// ����������, ��� ����� �������� ����������� �������
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
	* ���� Option, ������������ ��� ������ ������������ enum �����
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
	 * ���� option
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
	* ������� ������� ����� text
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
	* ������� ������� ����� password_md5
	* @param string $field_name
	* @return void
	*/
	private function showPassword ($field_name) {
		/**
		 * ������������ ���� passwd_md5
		 * ���� ��� ����� ������ ��������� ������, � ��� �������� � �������, �� ���
		 * ����������� ���� passwd_old ����� ����������� �������� � �����������, ���������������� �������
		 * ��������� ������� ������ ������� ���������������� ������ � �������
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
	* ����� textarea
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
	 * ������� ��������� �������� ��� �������� ����� n:n
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
	 * ������� ��������� �������� ��� �������� ����� n:n
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
	 * ������� �������� ����������� � ���� <select>
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
			'null_text' => ($this->is_group_update) ? cms_message('CMS', '��� ���������') : cms_message('CMS', '�������� �����...')
		));
		
	}
	
	/**
	 * ������� �������� ����������� � ���� <select>
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
			'null_text' => ($this->is_group_update) ? cms_message('CMS', '��� ���������') : cms_message('CMS', '�������� �����...')
		));
	}

	/**
	 * ������� ����� ��� ������� ������
	 * 
	 * @param string $field_name
	 */
	private function showFile ($field_name) {
		if (is_array($this->data[$field_name]) && isset($this->data[$field_name]['extension'])) {
			// ��������� ������, ������� ���������� ����� ������������� ������
			$this->data[$field_name] = $this->data[$field_name]['extension'];
		}
		
		// ���� ������� ����, �� ������� ���������� � �����
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
				// �������� ��������
				$height = ($size[1] > 600) ? 600 : $size[1] + 15;
				$width = ($size[0] > 600) ? 600 : $size[0] + 17;
				$file_type = 'image';
			} else {
				// �������� ���-�� ������
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
	 * ������� ����� ��� ������� �������� ���������� ������
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
	 * ����� � ����� � ��������
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
	 * ����� � �����
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
	* ����� �� ��������
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
	* ������� Hidden ����
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
	 * ������� ���� ���� Money
	 *
	 * @param string $field_name
	 */
	private function showMoney($field_name) {
		// ���������� ���� currency � text
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
				'null_text' => ($this->is_group_update) ? cms_message('CMS', '��� ���������') : cms_message('CMS', '�������� �����...')
			)
		);
	}
	
	/**
	 * ������� ���� ���� "������������ ������"
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
	 * ���������� ������������ ����
	 * @param string $field
	 */
	private function showFixedOpen($field_name) {
		// ��� ����� ���� ���� ������ ������������� �������� NULL ���� ���� ��� ���� ��������� NULL ��������,
		// ��� ������� � ���, ��� ������ ���� ���������������
		$this->current_row_param['is_nullable'] = false;
		
		// ��� �����, ������� ��������� �� ������ ������� ������� ��������, � �� ������ id
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
	 * ���������� ����, ������������� HTML ����������
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
	 * ������� ��������� �� ������ ��� ����� ��� ������� ���������� ����������
	 * @param string $field_name
	 */
	private function showErrorField($field_name) {
		$this->Template->iterate('/devider/row/', $this->tmpl_devider, array(
				'type'=>'fixed_open', 
				'row' => $this->current_row_param,
				'value' => '<font color=red>���������� ���������� ��� ���� '.$field_name.'</font>'
			)
		);
	}
}
?>