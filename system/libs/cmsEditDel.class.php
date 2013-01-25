<?php
/**
* ����� ��������� ������� �� �������� ����� �� ��
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* ����� ��������� ������� �� �������� ����� �� ��, ����� ������� ��������� � �������� �����
* @package CMS
* @subpackage CMS
*/
Class cmsEditDel {
	
	/**
	 * ������� � �� ��������, ������� ���� �������, � ������� SQL
	 * @var array 
	 */
	private $delete_data = array();
	
	/**
	 * ������� � �� ��������, ������� ���������� ������� � ���� �������
	 * @var array
	 */
	private $delete_array = array();
	
	/**
	 * ������ �������� ����
	 * @var array
	 */
	private $OLD = array();
	
	
	/**
	 * ���������� � ��
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * ���������� � ��������
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * ���������� � ������ � �������
	 * @var array
	 */
	private $keys = array();
	private $field_key = array();
	
	/**
	 * ��������� ������� � �������
	 * @var aray
	 */
	private $columns_schema = array();
	
	/**
	 * ���������� � �������
	 * @var array
	 */
	private $table = array();
	
	/**
	 * ������ ����� ������� ���������� ������ ����� add_pre � add_post
	 * ��� �� ��� del_pre � del_post ����������. �������� ������� � ������������
	 * ����� $this->triggerGet() � $this->triggerSet()
	 * @var array
	 */
	private $trigger_data = array();
	
	/**
	 * ���� � ������-���������
	 * @var string
	 */
	private $triggers_root = '';
	
	/**
	 * ���� ���� UPLOAD
	 * @var array
	 */
	private $upload_fields = array();
	
	/**
	* ����������� ������
	*
	* @param int $table_id ���������� ����� �������
	* @param array $data - ���������� � ���, ����� ������ �������
	* @return object
	*/
	public function __construct($table_id, $data) {
		
		$this->table = cmsTable::getInfoById($table_id);
		$this->triggers_root = TRIGGERS_ROOT . $this->table['triggers_dir'];
		$this->fields = cmsTable::getFields($table_id); 
		$this->DBServer = db::factory($this->table['db_alias']);
		
		/**
		 * ���������� ����� UPLOAD �����
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
	* ������� ��������� � �������� �����
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
				
				// ������ � ����������� ����
				if ($path === false) continue;
				
				// ������� ��������
				if (is_dir(UPLOADS_ROOT.$path.'/'))  {
					Filesystem::delete(UPLOADS_ROOT.$path.'/');
					Action::setLog(cms_message('CMS', '������� ���������� � ���������� %s.', $path.'/'));
				}
			}				
		}
	} 

	/**
	 * ������� ��� �� �� � ���������� ��� id
	 * @param void
	 * @return array
	 */
	public function dbChange () {
		global $DB;
		
		if ($this->table['table_type'] == 'VIEW') {
			return 0;
		}
		
		if (!is_array($this->delete_data) || empty($this->delete_data)) {
			Action::setError(cms_message('CMS', '�� ������� ����, �� ���� ������� ����� ����������� �������� �����.'));
			Action::onError();
		}
		
		/**
		* ���������� �������, � ������� ��� ���� id
		* � ������� ��� ����� �����  InnoDB ������� ��������� �� �������� ����� � ��������� �����������
		* � ��� �������������� �� ������ ������� ��. ����� �������� �������� ������� ��������� � ��������
		* �����. ���������� �� ������, ��� ���� ����� �������� ���������� id �����, ������� ���� �� ��������
		* � ������ ����� ����, ��� ��� ���� ����� ���������� - ������� ��.
		*/
		if (!isset($this->fields['id'])) {
			return array();
		}
		
		/**
		 * �������� �� ������� ���� id ������� ��� ����, ��� � � �������� ����� ����������� �������, � �������
		 * ��� ���� id. ��� ���������� ��� ������ ������� ��������� ��� ����� N:N
		 */
		if (isset($this->fields['id'])) {
			$query = "
				SELECT * 
				FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE ".implode(' AND ', $this->delete_data);
			$delete_id = $this->DBServer->query($query, 'id');
		
			/**
			 * ������� ����� ��������� ������ �� ������� pre trigger
			 */
			if (is_file($this->triggers_root . 'delete_before.act.php')) {
				reset($delete_id);
				while (list($current_id, $this->OLD) = each($delete_id)) {
					include($this->triggers_root . 'delete_before.act.php');
				}
			}
		
			/**
			 * ����� ���������� ��������� before.
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
			 * ������� ��������� � �������� �����
			 */
			$this->deleteUploadedFiles($delete_id);
		}
				
		/**
		 * ��������� � ���� ������ �������, ������� ����� �������
		 */
		$query = "
			SELECT *
			FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
			WHERE ".implode(' AND ', $this->delete_data);
		$log = $this->DBServer->query($query);
		
		/**
		 * ������� ������ �� ��
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
		 * ���������� ���������� � ��������, ������� ���� �������
		 */
		Action::saveLog('Rows was deleted: '.serialize($log));
		unset($log);
		

		/**
		 * ��������� ��������� � CVS
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
		* ��������� ����� �������
		*/
		if ($this->DBServer->affected_rows == -1) {
			Action::setError(cms_message('CMS', '������ �� ��������. SQL ������ ������ ������ - %s.', $this->DBServer->error()));
			Action::onError();
		} else {
			Action::setLog(cms_message('CMS', '%s, ������� %d ����(��).', $this->table['title'], $this->DBServer->affected_rows));
		}
		
		/**
		* ������� ����� �������� ������ �� �������
		*/
		if (is_file($this->triggers_root . 'delete_after.act.php')) {
			reset($delete_id);
			while (list($current_id, $this->OLD) = each($delete_id)) {
				include($this->triggers_root . 'delete_after.act.php');
			}
		}
		
		// ��������� ��������� ������
		if (is_module('Search') && count($delete_id) > 0) {
			Search::delete($this->table['name'], array_keys($delete_id));
		}
		
		return array_keys($delete_id);
	}
}
?>