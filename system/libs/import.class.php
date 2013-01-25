<?php
/** 
 * ����� ������� ������ �� ������� ���������� � ���� ������� 
 * @package Pilot
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

/** 
 * ����� ������� ������ � ���� ������� 
 * @package Pilot
 * @subpackage Import 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 
class Import {
	
	/**
	 * ������ ���������� � ��
	 * @var object
	 */
	private $DB;
	
	/**
	 * ��� �������, � ������� ���������� ������������� ������
	 * @var string
	 */
	private $destination_table = '';
	
	/**
	 * ����, ������� ���������� �� ������� ������
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * ������������ ����, ������� �� ���������� � ������� ����������
	 * @var bool
	 */
	private $force_fields = false;
	
	/**
	 * ��������������� �����������
	 * @var bool
	 */
	private $incremental = false;
	
	/**
	 * ��������� �������
	 * @var array
	 */
	private $table_structure = array();
	
	/**
	 * ��� ��������� �������
	 * @var string
	 */
	private $temporary_table = '';
	
	/**
	 * ���������� ������ ������� ������ ����� ���������� commit
	 * @var bool
	 */
	private $commited = false;
	
	/**
	 * ���������� �����, ������� ����� ��������� ��� ������ INSERT,
	 * ������������, �����, ����� ����������� ������� �������� � ����������
	 * ������ Got a packet bigger than 'max_allowed_packet' bytes
	 * @var int
	 */
	private $insert_step = 50;
	
	/**
	 * ������������ ����� � �������� ������� � ������� ����������
	 * @var array
	 */
	private $accordance = array();
	
	/**
	 * ����������� ������
	 * @param object $DB ������ ���� ������
	 * @param string $destination_table ��� �������, � ������� ����� ���������� ������
	 */
	public function __construct(DB $DB, $destination_table, $incremental=false, $force_fields=false, $insert_step=null) {
		$this->destination_table = $destination_table;
		$this->force_fields = $force_fields;
		$this->temporary_table = 'tmp_'.$destination_table;
		$this->DB = $DB;
		$this->incremental = $incremental;
		if (!empty($insert_step)) {
			$this->insert_step = $insert_step;
		}
		
		$query = "CREATE TEMPORARY TABLE `".$this->temporary_table."` LIKE `$destination_table`";
		$this->DB->query($query);
	}
	
	/**
	 * �������� ���� � �����������
	 * 
	 * @param string $source_field
	 * @param string $destination_field
	 */
	public function accordance($source_field, $destination_field) {
		$this->accordance[strtolower($source_field)] = $destination_field;
	}
	
	/**
	 * ���������� ������
	 * @param array $data - ����������, ������� ���������� ��������
	 * @param bool $insert_ignore - ������������ ������������� ����
	 * @param array $ignore_fields - ����, ������� ���� ������������
	 * @return ���������� ����� � �������
	 */
	public function data($data, $insert_ignore = false, $ignore_fields = array()) {
		// ���������� ������ ������� ������
		if ($this->commited == true) {
			trigger_error('Unable to call method after $this->commit has been called', E_USER_ERROR);
		}
		
		$insert_ignore = ($insert_ignore===true) ? ' IGNORE ' : '';
		
		// ���������� ���� ��� ������� ������
		if (empty($this->fields) && !empty($data)) {
			$fields = array_keys(reset($data));
			$fields = array_diff($fields, $ignore_fields);
			$this->defineStructure($fields);
			unset($fields);
		}

		
		// ���������� ������ ������ �� ��������� �������
		$insert = array();
		reset($data);
		while(list(,$row) = each($data)) {
			/**
			 * ��������� �����
			 */
			reset($row);
			while (list($field_name, $value) = each($row)) {
				if (isset($this->accordance[strtolower($field_name)])) {
					$destination_field = $this->accordance[$field_name];
				} else {
					$destination_field = $field_name;
				}
				
				// ������������� �����, ������� �� ���������� � ������� ����������
				if (!isset($this->table_structure[$destination_field])) {
					unset($row[$field_name]);
					continue;
				}
				
				// ���������� �����
				if (in_array($this->table_structure[$destination_field]['data_type'], array('float', 'double'))) {
					$value = str_replace(',', '.', $value);
				}
				
				// �������������
				$row[$field_name] = (is_null($value) && $this->table_structure[$field_name]['null']) ? 'NULL' : "'".$this->DB->escape($value)."'";
			}
			
			$insert[] = "(".implode(", ", $row).")";
			
			if (count($insert) > $this->insert_step) {
				$query = "
					INSERT $insert_ignore INTO `".$this->temporary_table."` (`".implode("`,`", $this->fields)."`)
					VALUES ".implode(",", $insert);
				$this->DB->insert($query);
				$insert = array();
			}
		}

		if (count($insert) > 0) {
			$query = "
				INSERT $insert_ignore INTO `".$this->temporary_table."` (`".implode("`,`", $this->fields)."`)
				VALUES ".implode(",", $insert);
			$this->DB->insert($query);
		}
		
		$query = "SELECT COUNT(*) FROM `".$this->temporary_table."`";
		return $this->DB->result($query);
	}
	
	
	/**
	 * ��������� ���������� ������� ������
	 * 
	 */
	public function commit() {
		// ���������� ������ ������� ������
		if ($this->commited == true) {
			trigger_error('Unable to call method after $this->commit has been called', E_USER_ERROR);
		} else {
			$this->commited = true;
		}
		
		$query = "LOCK TABLES `".$this->destination_table."` WRITE, `".$this->temporary_table."` READ";
		$this->DB->query($query);
		
		if ($this->incremental == false) {
			$query = "DELETE FROM `".$this->destination_table."`";
			$this->DB->delete($query);
		}
		
		// ���� ��� ������� � ����� data �� �������� ������, �� ������ ������� ���
		// ������ ���� ����� ���� �������. ������ �� INSERT �� �����������, ��� ��� �
		// �������� $this->fields �� �������� ��������
		if (!empty($this->fields)) {
			$query = "
				INSERT IGNORE INTO `".$this->destination_table."` (`".implode("`,`", $this->fields)."`)
				SELECT `".implode("`,`", $this->fields)."` FROM ".$this->temporary_table."
			";
			$this->DB->insert($query);
		}
		
		
		$query = "UNLOCK TABLES";
		$this->DB->query($query);
		
		
		$query = "DROP TABLE `".$this->temporary_table."`";
		$this->DB->query($query);
	}
	
	/**
	 * ���������� ��������� ��� ������� ������ � ��������� ������������ ��������
	 * �������� ������������� ��������
	 * 
	 * @param array $source_fields
	 */
	private function defineStructure($source_fields) {
		// ����������, ���� �� ��������� ������� � ��
		$query = "SHOW FULL COLUMNS FROM `$this->destination_table`";
		$table_structure = $this->DB->query($query);
		reset($table_structure);
		while(list(,$row) = each($table_structure)) {
			$this->table_structure[$row['field']]['data_type'] = (preg_match("/^([a-z]+)/i", $row['type'], $matches)) ? $matches[1] : $row['type'];
			$this->table_structure[$row['field']]['null'] = ($row['null'] == 'YES') ? true : false;
		}
		unset($table_structure);
		
		$destination_fields = array();
		reset($source_fields);
		while(list($index, $name) = each($source_fields)) {
			if (isset($this->accordance[$name])) {
				$destination_field = $this->accordance[$name];
			} else {
				$destination_field = $name;
			}
			
			if (!isset($this->table_structure[$destination_field])) {
				continue;
			}
			
			// ���������, ��� ��������� ������� ���� � �������
			unset($source_fields[$index]);
			
			// ������� ������� ��� ����������� ��������� �������
			$destination_fields[] = $destination_field;
		}
		
		if (!empty($source_fields) && $this->force_fields !== true) {
			trigger_error("Unknown column(s): ".implode(",", $source_fields)." in imported data. Compare field names in source data and destination table.", E_USER_ERROR);
		}
		
		$this->fields = $destination_fields;
	}
}
?>