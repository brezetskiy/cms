<?php
/**
 * ����� ��������� ������� ���������� ����� � ��
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@ukraine.com.ua>
 * @copyright Copyright 2004, Delta-X ltd.
 */

/**
 * ����� ��������� ������� ���������� ����� � ��
 * @package CMS
 * @subpackage CMS
 */
Class cmsEditAdd {
	
	/**
	 * ��� ������� update ��� insert
	 * @var string
	 */
	public $action_type = '';
	
	/**
	 * � ����� � ���, ��� ������� � �������� NEW ��� ���������� ��������� ��� 
	 * ����������� ���� �� �������� OLD, ��� ����, ��� � � ������ UPDATE ��������� ������ �� ������,
	 * ������� ������� ������ �� ����������. �� ��������� ����, ������� ��������� �� �������� OLD � 
	 * ���� �������
	 *
	 * @var array
	 */
	private $got_from_old = array();
	
	/**
	 * ����������, ������� ��������� � ������ / ��������� ����������
	 * @var array
	 */
	private $NEW = array();
	
	/**
	 * ��������, ������� �������� ��� NULL
	 *
	 * @var array
	 */
	private $nulls = array();
	
	/**
	 * ������ ���������� � ������, ������� �������������
	 * @var array
	 */
	private $OLD = array();
	
	/**
	 * ���������� � ���������� ������
	 * @var array 
	 */
	private $uploads = array();
	
	/**
	 * ���������, ������ ������ ������ � edit ��� view ������
	 * ��� ������ view �������� ������������ ���������� ����� ���������� ������ ��� ��� �����, ������� �������� �� ����������
	 *
	 * @var string
	 */
	private $update_form = 'edit';
	
	/**
	 * ������ �����, ������� �������� � CVS
	 * @var array
	 */
	private $cvs = array();
	
	/**
	 * ����� ���������� � CVS
	 *
	 * @var int
	 */
	private $cvs_transaction_id = 0;
	
	/**
	 * id ����, ������� �����������
	 * @var int
	 */
	private $row_id = 0;
	
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
	 * ���� � ������, ������� ���� �������� ���������, �� ����, ��� ������ �������� id
	 *
	 * @var string
	 */
	private $tmp_root = '';
	
	/**
	 * �������� ����� ajax_select. ������������ � ��� ������, ����� ������������ ������� ������ ��� �� ������� ��� �� �����������
	 * � � ������ ���������� ������ id
	 *
	 * @var array
	 */
	private $ajax_select = array();
	
	
	/**
	 * ����������� ������
	 * 
	 * @param int $table_id ���������� ����� ������� � ��
	 * @param array $data ����������, ������� ���������� ��������
	 * @return object
	 */
	public function __construct($table_id, $data, $update_form, $tmp_dir, $ajax_select) {
		global $DB;
		
		$this->table = cmsTable::getInfoById($table_id);
		$this->triggers_root = TRIGGERS_ROOT . $this->table['triggers_dir'];
		$this->fields = cmsTable::getFields($table_id);
		$this->DBServer = db::factory($this->table['db_alias']);
		$this->tmp_root = is_dir(TMP_ROOT.$tmp_dir) ? TMP_ROOT.$tmp_dir : '';
		$this->ajax_select = $ajax_select;
				
		// ������ ��������� ����������
		$this->update_form = $update_form;
		
		// ������������� id ����, ������� �������� �� ����������
		if (isset($data['id']) && !empty($data['id'])) {
			$this->row_id = $data['id'];
		}
		
		// ���������� ��� �������. �������� ��� �������� ������ �� empty($this->row_id), ��� ��� ����� insert
		// � ��� �������� ��������������� �������� last_inserted_id. ����� ���� ���������� ����� ���������� ��� ������� 
		// insert ��� upodate
		$this->action_type = (!empty($this->row_id)) ? 'update' : 'insert';
		
		// ���������, ������� ���������� �������� � �������
		$this->NEW = $this->buildNew($data);
		
		// ��������� �� OLD �������������� � NEW ����
		if ($this->action_type['update']) {
			$this->OLD = $this->buildOld();
			$this->mergeOldNew();
		}
		
		/**
		 * ���������� ����� ������ � ������������ � ������ ������ � MSSQL
		 * 
		 * �� ���������� � ������ ������ ����, ��� ��� ���� ����� �������� �������������
		 * � ������� � ������, � ����� ��� � ��� �������� regexp'�� ���������� ������,
		 * �� ������ ����� ������������� (����� ������, � ����� ������), � ��� �������� ��� �����
		 * ������ � ����� �����, � ������� ��� ����������
		 * 
		 * 14/10/2008 Rudenko Ilya: � ��-���� ��������� ��������, � ����� � ���, ��� ������ ������������ � ������ � ����������
		 * $_POST � $_GET, � ��� �������������� ���������� ������ ������
		 * 
		 * 2. �������� ���������� ���������� ��������� ��������
		 * 3. ����������� ����� �������
		 * 4. �������� ������������ �������� ���
		 * 
		 */
		$regexp_error = false;
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			// ���������� ����������� ����
			if (substr($field_name, 0, 1) == '_') {
				continue;
			}
			
			// ���������� hidden ����, ��� ������� �� ������ ���� ��������, �������� ���� priority
			if (!isset($this->NEW[$field_name]) && !isset($this->nulls[$field_name])) {
				continue;
			}
			
			// �������� ������ ��������
			if (!is_array($this->NEW[$field_name])) {
				$this->NEW[$field_name] = trim($this->NEW[$field_name]);
			}
			
			// �������� - ������ ���� ��� ���
			if ($field['is_obligatory'] && empty($this->NEW[$field_name])) {
				// �������� ������ �������� � ����, ������� �� ������ ���� ������
				$_SESSION['cmsEditError'][$field['id']] = cms_message('CMS', '���� "%s" ������������ ��� ����������.', $field['title']);
				$regexp_error = true;
			}
			
			 // �������� ���������� ���������� ������������ ����� ���������� 
			if (!empty($this->NEW[$field_name])	&& !empty($field['regular_expression'])	&& !preg_match($field['regular_expression'], $this->NEW[$field_name])) {
				$query = "SELECT error_message_".LANGUAGE_CURRENT." FROM cms_regexp WHERE id='".$field['regexp_id']."'";
				$_SESSION['cmsEditError'][$field['id']] = str_replace('[[title]]', '<b>"'.$field['title'].'"</b>', $DB->result($query));
				$regexp_error = true;
			}
			
			// �� ������� �� ��������� NULL ��������, ������ ���� ����� �������� �� �������������� ����������
			if (isset($this->nulls[$field_name]) && $field['is_nullable']) {
				continue;
			}
			
			if ($field['data_type'] == 'date') {
				
				// ��������������� ������ ���� � SQL
				$this->NEW[$field_name] = preg_replace("/^(\d+)\.(\d+)\.(\d+)$/", "\\3-\\2-\\1", $this->NEW[$field_name]); 
				
			} elseif ($field['data_type'] == 'datetime') {
				
				// ��������������� ������ ���� � SQL
				$this->NEW[$field_name] = preg_replace("/^(\d+)\.(\d+)\.(\d+)[\s\n\r\t]+(\d+):(\d+):(\d+)$/", "\\3-\\2-\\1 \\4:\\5:\\6", $this->NEW[$field_name]); 
				
			} elseif (is_array($this->NEW[$field_name]) && empty($field['fk_link_table_id'])) {
					
				// ��������������� ������ � ������, ���� ��� �� ������ ��� �������� ����� n:n
				$this->NEW[$field_name] = implode(',', $this->NEW[$field_name]);
					
			} elseif ($field['pilot_type'] == 'int' && empty($field['fk_link_table_id'])) {
				
				// ���������� ����� � ������������� ���������
				$this->NEW[$field_name] = (int)$this->NEW[$field_name];
				
			} elseif ($field['pilot_type'] == 'decimal') {

				// ��� ����� ���� float, decimal, double, dec, numeric ������� �������� �� �����
				$this->NEW[$field_name] = str_replace(',', '.', $this->NEW[$field_name]);
			}

			/**
			 * ���������, ��� �� ������� ������ (��� ������� md5)
			 */
			if (
				$field['field_type'] == 'passwd_md5'
				&& $this->NEW[$field_name] != ''
				&& $this->NEW[$field_name] == $this->NEW[$field_name.'_old_password']
			) {
				continue;
			}
			
			/**
			 * �������� ����� ������, �������������� ������ ��� ��������� ��������, ������� ��������� ������
			 */
			if ($field['field_type'] == 'passwd_md5') {
				$this->NEW[$field_name] = md5($this->NEW[$field_name]);
			}

		}
		
		/** 
		 * ���� ��������� ������ ��� �������� ������ ���������� ���������� �� 
		 * ������������ �� �������� ��������������
		 */
		if ($this->update_form == 'view' && !empty($_SESSION['cmsEditError'])) {
			reset($_SESSION['cmsEditError']);
			while(list(,$row) = each($_SESSION['cmsEditError'])) {
				Action::setError($row);
			}
			unset($_SESSION['cmsEditError']);
		}
		
		if ($regexp_error == true) {
			Action::onError();
		}
		
	}
	
	/**
	 * ��������� �������� ��� �������� $this->NEW 
	 *
	 */
	private function buildNew($data) {
		global $DB;
		
		/**
		 * ������������� �������� ��� �����, ������� �� �������� ���� ���������,
		 * � �������, ���� �� ������� �������, �� �� �������� - �� ����������
		 */
		if (isset($data['_dummie_fields_']) && is_array($data['_dummie_fields_'])) {
			$dummie = $data['_dummie_fields_'];
			reset($dummie);
			while(list($field, $value) = each($dummie)) {
				if (!isset($data[$field])) {
					$data[$field] = $value;
				}
			}
		}
		unset($data['_dummie_fields_']);
		
		/**
		 * ��������� ����� ajax_select. ��� ��������� ����� � ��� �������, ����� ������������ ��������� ������� 
		 * ������ � �� ������� �� ��������� ����
		 */
		reset($this->ajax_select);
		while (list($field,) = each($this->ajax_select)) {
			$value = reset($this->ajax_select[$field]);
			if (empty($data[$field])) {
				$query = "select table_name, fk_show_name from cms_table_static where id='".$this->fields[$field]['fk_table_id']."'";
				$fk_table = $DB->query_row($query);
				
				$query = "select id from `$fk_table[table_name]` where `$fk_table[fk_show_name]`='$value'";
				$data[$field] = $DB->result($query);
			}
		}
		
		/**
		 * ������������� �������� ���� NULL
		 */
		if (isset($data['_null_']) && is_array($data['_null_'])) {
			$this->nulls = $data['_null_'];
			reset($this->nulls); 
			while (list($field_name,) = each($this->nulls)) { 
				 $data[$field_name] = null;
			}
		}
		unset($data['_null_']);
		
		/**
		 * ������������ ���� �������, ���������� ������ ��� ������� ������
		 */
		$table_id = $this->table['id'];
		if (isset($_FILES[$table_id]['name']) && is_array($_FILES[$table_id]['name'])) {
			reset($_FILES[$table_id]['name']);
			while(list($field_name) = each($_FILES[$table_id]['name'])) {
				
				// ��������� ������ uploads
				$this->uploads[$field_name] = array(
					'name' => $_FILES[$table_id]['name'][$field_name]['file'],
					'type' => $_FILES[$table_id]['type'][$field_name]['file'],
					'tmp_name' => $_FILES[$table_id]['tmp_name'][$field_name]['file'],
					'error' => $_FILES[$table_id]['error'][$field_name]['file'],
					'size' => $_FILES[$table_id]['size'][$field_name]['file'],
					'extension' => Uploads::getFileExtension($_FILES[$table_id]['name'][$field_name]['file'])
				);
				
				
				/**
				 * ���������� ���������� �����, ������� ����� ��������� � ��
				 */
				if (isset($data[$field_name]['del']) && $data[$field_name]['del'] == 'true') {
					// ��� ���������� ���� - ������� ����
					$upload_file = Uploads::getFile($this->table['name'], $field_name, $this->row_id, $data[$field_name]['extension']);
					
					// ������� ����
					if (is_file($upload_file)) {
						unlink($upload_file);
						Action::setLog(cms_message('CMS', '������ ����������� � ���� %s ���� %s.', $field_name, Uploads::getURL($upload_file)));
					}
					$data[$field_name] = '';
				} elseif (!empty($this->uploads[$field_name]['extension'])) {
					// ������� ����� ����
					$data[$field_name] = $this->uploads[$field_name]['extension'];
				} else {
					// ������� ���������, ��������� ������ �������� ����
					$data[$field_name] = $data[$field_name]['extension'];
				}


				/**
				 * ��� �������� ������ �����, ������ �� ����� ��� ����� ���������� �� ���� �������
				 * ���� ����� ��������� �� �������� �������� ��
				 */
				if (empty($this->uploads[$field_name]['name'])) {
					unset($this->uploads[$field_name]);
				} elseif (!empty($this->uploads[$field_name]['error'])) {
					Action::setError(Uploads::check($this->uploads[$field_name]['error']));
				} else {
					Action::setLog(cms_message('CMS', '������� ���� %s.', $this->uploads[$field_name]['name']));
				}
			}
		}
		
		// ������� �������� ��� ������ ����� uniq_name
		if (isset($this->fields['uniq_name']) && empty($data['uniq_name']) && isset($data[$this->table['fk_show_name']]) && !empty($data[$this->table['fk_show_name']])) {
			$data['uniq_name'] = name2url($data[$this->table['fk_show_name']], $this->fields['uniq_name']['max_length']);
		}
		
		return $data;
	}
	
	/**
	 * ��������� �������� ��� �������� $this->OLD
	 *
	 */
	private function buildOld() {
		global $DB;
		
		// ���� �������, � ������� ���� ����� ����������� ������������� ���� id
		// � ����� �������� ��� ���������� ����� ������ ���� id �������� ��������
		// ��������� ��� �������� ������ � �� ��� ������ �����, ��� id ������ ������� ������������� ������
		$query = "
			SELECT *
			FROM `".$this->table['db_name']."`.`".$this->table['name']."`
			WHERE id='".$this->row_id."'
		";
		$return = $this->DBServer->query_row($query);
		if ($this->DBServer->rows == 0) {
			$this->action_type = 'insert';
			return array();
		}
		
		/**
		 * ��������� ����� n:n
		 */
		reset($this->fields);
		while(list($field_name, $row) = each($this->fields)) {
			if (empty($row['fk_link_table_name'])) {
				continue;
			}
			
			// ���������� �������� �������, ������� �������� id ������ � ������� �������
			// � �������, ������� �������� ��������
			$query = "
				SELECT tb_field.name
				FROM cms_field AS tb_field
				WHERE 
					tb_field.table_id='".$row['fk_link_table_id']."'
					AND tb_field.fk_table_id='".$this->table['id']."'
			";
			$where_field = $DB->result($query);
			$query = "
				SELECT tb_field.name
				FROM cms_field AS tb_field
				WHERE 
					tb_field.table_id='".$row['fk_link_table_id']."'
					AND tb_field.fk_table_id='".$row['fk_table_id']."'
			";
			$select_field = $DB->result($query);
			
			// ���������� ��� ��, � ������� ��������� ������� � �������� �������
			$fk_link_db_alias = $DB->result("
				SELECT tb_db.alias
				FROM cms_db AS tb_db
				INNER JOIN cms_table AS tb_table ON tb_table.db_id=tb_db.id
				WHERE tb_table.id='".$row['fk_link_table_id']."'
			");
			
			$fk_link_db_name = db_config_constant("name", $fk_link_db_alias); 
			
			// ������������ ������ �� �������, ������� �������� �����
			$query = "
				SELECT `$select_field` AS id
				FROM `$fk_link_db_name`.`".$row['fk_link_table_name']."`
				WHERE `$where_field`='".$this->row_id."'
			";
			$return[$field_name] = $DB->fetch_column($query);
			
			unset($fk_link_db_name);
		}
		
		return $return;
	}
	
	/**
	 * ������� ���������� ��������� � �������� NEW ����������� �������� �� �������� OLD,
	 * �������� ��� ���� � �������� $this->got_from_old ����, ������� ���������� � NEW
	 * �� ���� OLD
	 * 
	 * �� ���������� ������ ��������� ��� ����, ������� ���� � �������,
	 * ��� ��� ���� ����� �� �������, �� � ���������, ��� ��������� 
	 * ����������, ����� ���������� ������
	 */
	private function mergeOldNew() {
		reset($this->OLD);
		while(list($field_name,$val) = each($this->OLD)) {
			if (!isset($this->NEW[$field_name]) && !isset($this->nulls[$field_name])) {
				$this->NEW[$field_name] = $val;
				$this->got_from_old[$field_name] = $field_name;
				// ������ ����, ������� �� ���� �������� ������������ ������
				if (isset($this->fields[$field_name]['field_type']) && $this->fields[$field_name]['field_type'] == 'passwd_md5') {
					$this->NEW[$field_name.'_old_password'] = $this->NEW[$field_name];
				}
			}
		}
	}
	
	/**
	 * ��������� ��� �������� ���� � ��
	 * @param void
	 * @return int
	 */
	public function dbChange() {
		global $DB; // ����� $DB ������������ � ���������

		// ����������, �������� �� ������������� ������� �����������
		$recursive = false;
		if ($this->table['id'] == $this->table['parent_table_id']) {
			$recursive = true;
		}
		
		/**
		 * �� ���������� ���������
		 * ���������� �� �������� �� ������������ ������� ������������� ������ �����������
		 * ��������
		 */
		if ($recursive && $this->action_type == 'update') {
			if (empty($this->table['relation_table_name'])) {
				trigger_error('Please define param "Optimisation table" for table `'.$this->table['name'].'`', E_USER_ERROR);
				exit;
			}
			$query = "
				SELECT id 
				FROM `".$this->table['relation_table_name']."`
				WHERE
					parent = '".$this->row_id."'
					AND id != '".$this->row_id."'
					AND id = '".$this->NEW[ $this->table['parent_field_name'] ]."'
			";
			$this->DBServer->query($query);
			if ($this->DBServer->rows > 0 || $this->row_id == $this->NEW[ $this->table['parent_field_name'] ]) {
				Action::onError(cms_message('CMS', '������ ���������� ������ � ������ ����. �������� ������ ������������ ������.'));
			}
		}
		
		
		/**
		 * �������� ��������������� ���������.
		 */
		if (is_file($this->triggers_root . $this->action_type . '_before.act.php')) {
			require($this->triggers_root . $this->action_type . '_before.act.php');
			Action::setLog('����������� ������� '.$this->action_type.' before');
		}
		 
		/**
		 * ����� ���������� ���������.
		 * � ������, ���� ���� ��������� � ���������, �� ������� ������ �����
		 */
		if (
			$recursive &&
			$this->action_type == 'update' && 
			$this->NEW[ $this->table['parent_field_name'] ] != $this->OLD[ $this->table['parent_field_name'] ]
		) {
			$query = "CALL clean_relation('".$this->table['relation_table_name']."', '".$this->row_id."')";
			$DB->query($query);
		}
		
		/**
		 * ����������� ����� ������� �� ��������
		 */
		if ($this->action_type == 'insert') {
			$values = $this->buidInsertQuery();
		} else {
			$values = $this->buildUpdateQuery();
		}
		
		if (count($values) == 0) {
			return 0;
		}
		
		$this->checkDupFields();
		
		$query = "LOCK TABLES ".$this->table['name']." WRITE";
		$this->DBServer->query($query);
		
		if ($this->action_type == 'update') {
			
			$query = "UPDATE `".$this->table['db_name']."`.`".$this->table['name']."` SET ".implode(', ', $values)." WHERE id='".$this->row_id."'";
			$this->DBServer->update($query);
			Action::saveLog($query);
			if (IS_DEVELOPER) {
				Action::setLog(cms_message('CMS', '%s, �������� ��� #%d', $this->table['title'], $this->row_id));
			}
			
		} else {
			
			$query = "INSERT INTO `".$this->table['db_name']."`.`".$this->table['name']."` (`".implode('`, `', array_keys($values))."`) VALUES (".implode(",", $values).")";
			$this->NEW['id'] = $this->row_id = $this->DBServer->insert($query);
			Action::saveLog($query.' insert_id='.$this->row_id);
			if (IS_DEVELOPER) {
				Action::setLog(cms_message('CMS', '%s, �������� ��� #%d', $this->table['title'], $this->row_id));
			}
			
		}
		
		if (IS_DEVELOPER) {
			Action::setLog($query);
		}
		
		$this->DBServer->query("UNLOCK TABLES");
		
		/**
		 * ��� ������� ������ ����� swfUpload ��� ����� �������, ������� ��� � �������
		 * ��� ����������� � ���������� $this->tmp_root, ����� ���������� INSERT �������
		 * �� �������� id � ��������� �� � ��������������� ����������
		 */
		if ($this->action_type == 'insert' && !empty($this->tmp_root)) {
			$fields = Filesystem::getDirContent($this->tmp_root, false, true, false);
			reset($fields);
			while (list(,$field) = each($fields)) {
				$files = Filesystem::getDirContent($this->tmp_root.$field, false, false, true);
				reset($files);
				while (list(,$file) = each($files)) {
					Filesystem::rename($this->tmp_root.$field.$file, UPLOADS_ROOT.strtolower($this->table['name'].".$field/".Uploads::getIdFileDir($this->NEW['id']).'/'.$file), true);
				}
			}
			Filesystem::delete($this->tmp_root);
		}
		
		/**
		 * ��������� ������ ��� ���������� ������
		 */
		if ($this->DBServer->affected_rows == -1) {
			Action::setError(cms_message('CMS', '������ �� ��������. SQL ������ ������ ������ - %s.', $this->DBServer->error()));
			Action::onError();
		}
		
		/**
		 * ��������� ��������� � CVS
		 */
		if ($this->table['use_cvs']) {
			// ������ ����������
			$query = "
				insert into cvs_db_transaction (admin_id,table_id,event_type,row_id) 
				values ('".$_SESSION['auth']['id']."', '".$this->table['id']."', '".$this->action_type."', '".$this->row_id."')
			";
			$this->cvs_transaction_id = $DB->insert($query);
			reset($this->cvs); 
			while (list($field_name,$new_value) = each($this->cvs)) {
				// ���������� �������, ������� ������� ��� ������� ���� ����
				if (is_null($new_value)) {
					$pilot_type = 'null';
					$new_value = null;
				} else {
					$data_type = $this->fields[$field_name]['data_type'];
					$pilot_type = $this->fields[$field_name]['pilot_type'];
				}
				
				if ($this->action_type != 'insert' && $this->OLD[$field_name] == $new_value) {
					// ������� UPDATE ��������� ������ �� ����, ������� ���� ��������
					continue;
				}
				
				if (is_null($new_value)) {
					$new_value = 'true';
				}
				$field_language = (!empty($this->fields[ $field_name ]['field_language'])) ? "'".$this->fields[ $field_name ]['field_language']."'" : "NULL";
				
				$query = "
					insert into cvs_db_change (transaction_id, field_id, field_language, value_$pilot_type) 
					values ('".$this->cvs_transaction_id."', '".$this->fields[ $field_name ]['id']."', $field_language, '$new_value')
				";
				$DB->insert($query);
			}
		}
		
		/**
		 * ��������������� ��������� ������ ��� ����������� ������
		 */
		if ($recursive) {
			if (empty($this->table['relation_table_name'])) {
				trigger_error("Please define in `cms_table` name of relation table for recursive table `".$this->table['name']."`", E_USER_ERROR);
			}
			do {
				$query = "CALL build_relation('".$this->table['name']."', '".$this->table['parent_field_name']."', '".$this->table['relation_table_name']."', @total_rows)";
				$DB->query($query);
				
				$query = "SELECT @total_rows";
				$total_rows = $DB->result($query);
			} while ($total_rows > 0);
		}
		
				
		/**
		 * � ������ ��������� ���������� ���� � �������, ���������� ���������� ����� � ������� ����� �� ���������� /i/
		 */
		if (is_array($this->uploads)) {
			reset($this->uploads);
			while(list($field, $val) = each($this->uploads)) {
				$extension = Uploads::getFileExtension($val['name']);
				$upload_file = Uploads::getFile($this->table['name'], $field, $this->row_id, $extension);
				
				// ���������� ���������� ����
				Uploads::moveUploadedFile($val['tmp_name'], $upload_file);
				
				// ������� ����������� �� ���������� /i/
				$query = "select uniq_name from cms_image_size";
				$resized = $DB->fetch_column($query);
				reset($resized);
				while (list(,$uniq_name) = each($resized)) {
					$thumb = SITE_ROOT."i/$uniq_name/".$this->table['name']."/$field/".Uploads::getIdFileDir($this->row_id).'.'.$extension;
					if (is_file($thumb)) {
						unlink($thumb);
					}
				}
				
				
				// ���������, ����� ���� � �����, ��� ��� �������� ����� ���� ������������ � add_post ��������
				$this->uploads[$field]['tmp_name'] = $upload_file;
			}
		}
		
		$this->updateFKey();
		
		// ��������� �������� ���� URL
		if (isset($this->fields['uniq_name']) && isset($this->fields['url']) && !empty($this->table['relation_table_name'])) {
			$Structure = new Structure($this->table['table_name']);
			$parent_field_name = $this->table['parent_field_name'];
			if ($this->action_type == 'update' && ($this->NEW[$parent_field_name] != $this->OLD[$parent_field_name] || $this->NEW['uniq_name'] != $this->OLD['uniq_name'])) {
				$Structure->cleanURL($this->OLD['id']);
			}
			$Structure->updateURL();
			unset($Structure);
		}

		
		/**
		 * �������� POST ���������.
		 * ��������! ������ ��������� ��������� view! ���� ��� �������� view_after, 
		 * �� ����� �������� ������� update_after, � ���� ������ ����������� view_after.
		 */
		if (is_file($this->triggers_root . $this->action_type . '_after.act.php')) {
			require($this->triggers_root . $this->action_type . '_after.act.php');
			Action::setLog('����������� ������� '.$this->action_type.' before');
		}
				

		// ��������� ��������� ������
		if (is_module('Search')) {
			Search::update($this->table['name'], $this->row_id);
		} 

		// ���������� ����� ��������� ����������� ������
		return $this->row_id;
	}
	
	
	/**
	 * ����������, ����� ���� ���������� ��������� � �������, ��� ������ ���� ����,
	 * ��� ������� ������ ������, ���� ���� ���������� ����:
	 * 1. ������� �� ����� ����� DEFAULT_VALUE � �� ������ ��������� �������� NULL (TEXT, BLOB)
	 * 2. ������� ����� ��� timestamp
	 * 3. ������� �������� �������� ����������� ������ ������ � ������ ��������� (`priority`)
	 * @return array
	 */
	private function buidInsertQuery() {
		$value = array();   
		reset ($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			// ���������� �� ���������� ����, ����� ���, ������� �� ����� �������� �� ���������
			if (!isset($this->NEW[$field_name]) && $field['no_default_value']==0 && $field_name != 'priority') {
				continue;
			}
			
			// ���������� ��������
			if (!$field['is_real'] || $field['data_type'] == 'timestamp' || $field_name == 'id') {
				continue;
			}
			
			if ($field_name == 'priority') {
				// �������� ��� ���� priority
				$value[$field_name] = $this->getNextPriorityId();
				$this->cvs[$field_name] = $value[$field_name];
				
			} elseif ((!isset($this->NEW[$field_name]) || empty($this->NEW[$field_name])) && !$field['is_nullable'] && $field['no_default_value']) {
				// ������ �������� ��� �����, ������� �� ����� ����� default_value
				$value[$field_name] = "''";
				$this->cvs[$field_name] = '';
				
			} elseif ($field['is_nullable'] && isset($this->nulls[$field_name])) {
				// ���� �������� ��� NULL
				$value[$field_name] = "NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif($field['data_type'] == 'timestamp') {
				// �������� ��� ���� timestamp, ����� ����� �������� �� ��������� (�� ������ ��� ����� CURRENT_TIMESTAMP)
				$value[$field_name] = "NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif (isset($this->NEW[$field_name]) && (!empty($this->NEW[$field_name]) || strlen($this->NEW[$field_name]) != 0)) {
				// strlen ��������� ��-�� ����, ��� ��� �������� �������� 0 ��������� � ������� �������� �� ���������
				// �������� ��� ��������� ����
				$value[$field_name] = "'".$this->NEW[$field_name]."'";
				$this->cvs[$field_name] = $this->NEW[$field_name];
				
			} elseif ($field['data_type'] == 'set') {
				// �������� ��� ���� ���� set
				$value[$field_name] = "''";
				$this->cvs[$field_name] = '';
				
			} else {
				// ��� ������ �������� ������������ �������� � ������� �� ���������, ����������� � MySQL
				
			}
		}
		
		return $value;
	}
	
	/**
	 * ����������, ����� ���� ���������� ��������� � ������� ��� update �������
	 * @return array
	 */
	private function buildUpdateQuery() {
		global $DB;
		$value = array();
		
		// ���������� �� �������� � �������
		$query = "
			SELECT name, column_name, is_nullable, priority
			FROM cms_table_index
			WHERE table_id='".$this->table['id']."'
			ORDER BY name ASC, priority ASC
		";
		$keys = $DB->query($query);
		reset($keys); 
		while (list(,$row) = each($keys)) { 
			$uniq_key[$row['name']]['is_nullable'] = $row['is_nullable'];
			$uniq_key[$row['name']]['fields'][$row['priority']] = $row['column_name'];
			$uniq_column[$row['column_name']][] = $row['name'];
		}
		unset($keys);

		reset ($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			// ���������� ����, ������� ���� ��������� �� �������� $this->OLD
			// ���������� ���� ���� timestamp
			// ���������� ���� id
			// ���� priority �� �����������
			// ���������� ����, ������� �� ����������
			if (
				isset($this->got_from_old[$field_name]) || 
				$field['data_type'] == 'timestamp' ||
				$field['is_real'] == 0 || 
				$field_name == 'id' ||
				$field_name == 'priority' && $this->action_type == 'edit'
			) {
				continue;
			}
			
			if ($field['is_nullable'] && !empty($field['fk_table_id']) && empty($this->NEW[$field_name])) {
				// ��� ������� ������, ������� ������������ NULL, ��� �������� �������� 0 ������ NULL
				$value[$field_name] = "`$field_name`=NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif ($field['is_nullable'] && isset($this->nulls[$field_name])) {
				// ���� �������� ��� NULL
				$value[$field_name] = "`$field_name`=NULL";
				$this->cvs[$field_name] = NULL;
				
			} else if ($field['data_type'] == 'timestamp') {
				$value[$field_name] = "`$field_name`=CURRENT_TIMESTAMP";
				
			} else {
				$value[$field_name] = "`$field_name`='".$this->NEW[$field_name]."'";
				$this->cvs[$field_name] = $this->NEW[$field_name];
			}
		}
		return $value;
	}
	
	/**
	 * ���������� ����� � ������� ��������� ����� ������� �����, 
	 * ������� ����� ������������ ������ � ������ (N:N)
	 * @return void
	 */
	private function updateFKey() {
		global $DB;
		reset($this->fields);
		while (list($field_name, $field) = each($this->fields)) {
			
			// ���������� ��� �������, ������� �� ��������� � n:n ������
			if (empty($field['fk_link_table_id'])) {
				continue;
			}
			
			// ���������� ��� �������, ������� �� ������ ���� ���������
			if (!isset($this->NEW[$field_name])) {
				continue;
			}
			
			$info = cmsTable::getFkeyNNInfo($field['table_id'], $field['fk_table_id'], $field['fk_link_table_id']);
			
			// ������� ������ ��������
			$query = "DELETE FROM `$info[from_table]` WHERE `$info[where_field]`='$this->row_id'";
			$this->DBServer->delete($query);
			
			$fk = $cvs = array();
			if (is_array($this->NEW[$field_name])) {
				reset($this->NEW[$field_name]);
				while(list(,$val) = each($this->NEW[$field_name])) {
					$fk[] = "('".$this->row_id."', '$val')";
					$cvs[] = "('".$this->cvs_transaction_id."', '$field_name', '$val')";
				}
				if (!empty($fk)) {
					$query = "INSERT IGNORE INTO `$info[from_table]` (`$info[where_field]`, `$info[select_field]`) VALUES ".implode(",", $fk);
					$this->DBServer->insert($query);
					Action::setLog(cms_message('CMS', '��������� ������� ����� ��� ���� %s (%s)', $field_name, $this->DBServer->affected_rows));
				}
			}
			
			// ��������� ��������� � CVS
			if (!empty($this->cvs_transaction_id) && empty($cvs)) {
				// ������������ ����� ��� �������� �������� �����
				$query = "insert into cvs_db_fkey (transaction_id, field_name, fkey_id) values ('".$this->cvs_transaction_id."', '$field_name', NULL)";
				$DB->insert($query);
			} elseif (!empty($this->cvs_transaction_id)) {
				$query = "insert into cvs_db_fkey (transaction_id, field_name, fkey_id) values ".implode(",", $cvs);
				$DB->insert($query);
			}
		}
	}
	
	/**
	* ������������ �������� �� ������������ unique �������
	* @return array
	*/
	private function checkDupFields() {
		global $DB;
		
		$query = "
			select name, group_concat(column_name order by priority asc) as columns
			from cms_table_index as tb_index
			where tb_index.table_id='".$this->table['id']."'
			group by name
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$columns = preg_split("/,/", $row['columns'], -1, PREG_SPLIT_NO_EMPTY);
			$where = $error = array();
			reset($columns);
			while (list(,$column) = each($columns)) {
				$where[] = (isset($this->NEW[$column])) ? "`$column`='".$this->NEW[$column]."'": "`$column`=''";
				$error[] = $this->fields[$column]['title'];
			}
			
			$query = "
				SELECT * 
				FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE ".implode(" AND ", $where)."
			";
			$query .= ($this->action_type == 'update') ? " AND id!='".$this->row_id."'" : '';
			$result = $this->DBServer->query($query);
			if ($this->DBServer->rows > 0) {
				// ���������� �������� �������
				Action::setError(cms_message('CMS', '�� ��������� �������� ������������� ������. ��������� ������������ ���������� ����� "%s".', implode('", "', $error)));
				Action::onError();
			}
		}
	}
	
	/**
	* ���������� ��������� id � ������� priority � �������� �������
	* @param void
	* @return int
	*/
	private function getNextPriorityId() {
		$query = "SELECT IFNULL(MAX(priority) + 1, 1) AS next_priority FROM `".$this->table['db_name']."`.`".$this->table['name']."`";
		if (!empty($this->table['parent_field_name']) && isset($this->NEW[$this->table['parent_field_name']])) {
			$query .= " WHERE ".$this->table['parent_field_name']."='".$this->NEW[$this->table['parent_field_name']]."'";
		}
		return $this->DBServer->result($query, 1);
	}
}

?>