<?php

/** 
 * ���������� ���������� ������������ 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */


/**
 * ������� �����, ������� �������� �� ��������
 *
 * @param int $param_id
 */
function delete_file($param_id) {
	global $user_id, $OLD;
	
	if (!isset($OLD[$param_id]) || !in_array($OLD[$param_id]['data_type'], array('file', 'image'))) return false;
	$file = UPLOADS_ROOT.'auth_user_data/'.Uploads::getIdFileDir($user_id).'/'.$param_id.'.'.$OLD[$param_id]['value_char'];
	
	if (is_file($file)) {
		return unlink($file);
	} else {
		return false;
	}
}


/**
 * �������� ������
 */
$user_id = Auth::getUserId();
$user_email = strtolower(trim(globalVar($_POST['user_email'], '')));

$user_password = trim(globalVar($_POST['user_password'], ''));
$user_group = trim(globalVar($_POST['user_group'], 0));
 
$values = globalVar($_POST['param'], array());
$delete = globalVar($_POST['delete'], array());

$current_params = globalVar($_POST['current_params'], "");
$current_params = explode(",", $current_params);

$register_ip = constant('HTTP_IP');
$register_local_ip = constant('HTTP_LOCAL_IP'); 

$auth_code = strtolower(Misc::randomKey(32));
 

/**
 * ���������� ������ ��� ������������
 */
$user = $DB->query_row("
	SELECT IF(TRIM(name) != '', name, login) as name, passwd, login, email 
	FROM auth_user WHERE id = '$user_id'
");


/**
 * ��������� ������������ ��������� ������
 */
if (!AUTH_DATA_CHANGE_CONFIRM && !preg_match(VALID_PASSWD, $user_password)) {
	// ������ �������� ������������ �������
	Action::onError(cms_message('CMS', '����������� ������ ������, ����� ������������ ������ ��������� �����, �����, ���� ������������� � ������� +!@#$%^&*~()-'));
}

if (empty($user_email)) {
	// �� ������ e-mail �����
	Action::onError(cms_message('CMS', '�� ������ ����� e-mail'));
} elseif (!preg_match(VALID_EMAIL, $user_email)) {
	// ����������� ������ e-mail �����
	Action::onError(cms_message('CMS', '����������� ������ e-mail �����'));
}


if(AUTH_USER_GROUP_EDITABLE && empty($user_group)){
	Action::onError(cms_message('CMS', '������� ������ ������������.'));
}
 
if(!AUTH_DATA_CHANGE_CONFIRM){
	$check = $DB->query_row("SELECT id, passwd FROM auth_user WHERE id='$user_id'"); 
	if ($DB->rows != 1 || $check['passwd'] != md5($user_password)) {
		// ����������� ������ ������
		Action::onError(cms_message('CMS', '����������� ������ ������'));
	}
}


/**
 * ��������� ������� ������������� � ��������� e-mail �������
 */
$DB->query("SELECT id FROM auth_user WHERE (email='$user_email' OR login='$user_email') AND id!='$user_id'");
if ($DB->rows > 0) {
	Action::onError(cms_message('CMS', '
		������������ � ��������� e-mail ������� ��� ����������, 
		�������������� <a href="/User/Reminder/">������ ����������� ������.</a>
	'));
}


/**
 * ��������� ������ ���������� � ������������
 */
$OLD = $DB->query("
	select 
		tb_param.name as param_name,
		tb_data.*,
		case tb_data.data_type
			when 'char' then value_char
			when 'file' then value_char
			when 'image' then value_char
			when 'decimal' then value_decimal
			when 'bool' then value_int
			when 'fkey' then value_int
			when 'fkey_table' then value_int
			when 'date' then value_date
			else value_text
		end as value
	from auth_user_data as tb_data
	inner join auth_user_group_param as tb_param on tb_param.id=tb_data.param_id
	where tb_data.user_id='$user_id'  
", 'param_id');


/**
 * �������� ����������
 */
$params = $DB->query("select * from auth_user_group_param order by priority asc", 'uniq_name');


/**
 * ������������ ������ ���������, ��� ����������� �������������� ������ �������������
 */
$enabled_params = array();
$is_enabled = false;

reset($params);
while(list(, $row) = each($params)){
	if($row['data_type'] == "devider" && in_array($row['id'], $current_params)){
		$is_enabled = true;
		continue;
	} elseif($row['data_type'] == "devider" && !in_array($row['id'], $current_params)){
		$is_enabled = false;
		continue;
	}
	
	if($is_enabled) $enabled_params[] = $row;
}


/**
 * ����� ������ ��� ������������ ����� � ���������� �������������� ��� ������� �� �������� ������
 */
if(!empty($enabled_params)){
	
	reset($enabled_params);
	while (list(, $row) = each($enabled_params)) {
		$param_id = $row['id'];
		
		if (isset($values[$param_id])) $values[$param_id] = trim($values[$param_id]);  
		if (empty($values[$param_id]) && $row['required'] == 1) {
			Action::onError(cms_message('CMS', '�� ������� �������� ��������� "%s"', $row['name']));
		}
		
		if(!$row['is_editable'] && !empty($OLD[$param_id]) && $values[$param_id] != $OLD[$param_id]['value']){
			Action::onError(cms_message('CMS', '������� �������� ��������������� ���� "%s"', $row['name']));
		}  elseif(!$row['is_editable'] && empty($OLD[$param_id]) && !empty($values[$param_id])) { 
			Action::onError(cms_message('CMS', '������� �������� ��������������� ���� "%s"', $row['name']));
		}
	}
}


/**
 * ���������� ��������������� ������������ �����
 */
if(!empty($enabled_params)){
	$defined_template = AUTH_USER_NAME;
	$auth_user_name   = ""; 
	
	$auto_names = array();
	$auto_name_templates = explode("|", $defined_template);
	
	reset($auto_name_templates);
	while(list($name_id, $template) = each($auto_name_templates)){
		$auto_names[$name_id] = $template;
		
		reset($enabled_params);
		while (list(, $row) = each($enabled_params)) {
			$param_id = $row['id'];
			if(empty($values[$param_id])) continue;
			$auto_names[$name_id] = str_replace("%{$row['uniq_name']};", trim($values[$param_id]), $auto_names[$name_id]);
		}
		
		$auto_names[$name_id] = preg_replace("/%[a-zA-Z0-9_]+;/", "", $auto_names[$name_id]);
	} 
	
	reset($auto_names);
	while(list(, $name) = each($auto_names)){
		$name = trim($name);
		
		if(!empty($name)){
			$auth_user_name = $name;
			break;
		}
	}
	
	if(empty($auth_user_name)) Action::onError(cms_message('CMS', '�� ������� ������������ ��� ������������.'));
} else {
	$auth_user_name = $user_email;
}


/**
 * ��������� ������ � ��������� ������ � ������� 
 */
$history_id = $DB->insert("
	INSERT INTO auth_user_history 
	SET user_id 	  = '$user_id',
		user_group_id = '$user_group',    
		auth_code     = '".addslashes($auth_code)."',
		login		  = '$user_email',
		email		  = '$user_email',
		ip 			  = '$register_ip',
		local_ip 	  = '$register_local_ip',
		name          = '$auth_user_name'
");
$history_data = array();

/**
 * �������� ������� �� �������� ������
 */
if(!empty($delete)){
	reset($delete);
	while(list(, $param_id) = each($delete)){
		$history_data[] = "('$history_id', '$param_id', 'delete_file', null, null, null, null)";
	}
}


if(!empty($enabled_params)){
	
	reset($enabled_params);
	while(list(, $row) = each($enabled_params)){
		$param_id = $row['id'];
		
		/**
		 * ��������� ������
		 */
		if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
			
			/**
			 * ���������� �����
			 */
			$extension = strtolower(Uploads::getFileExtension($_FILES['param']['name'][$param_id]));
			
			$file = UPLOADS_ROOT.'auth_user_history_data/'.Uploads::getIdFileDir($history_id)."/$param_id.$extension";
			$url  = substr($file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
			
			Uploads::moveUploadedFile($_FILES['param']['tmp_name'][$param_id], $file);
			
			$DB->insert("
				insert into auth_user_history_data (`history_id`,`param_id`, `data_type`, `value_char`, `value_text`)
				values ('$history_id', '$param_id', 'file', '$extension', '$url')
			"); 
		}
		
		$value = (!empty($values[$param_id])) ? $values[$param_id] : "";
		
		/**
		 * �������� ������������� �������� ������ � ������� ���������
		 */
		if(empty($OLD[$param_id]['value']) && empty($value)) continue;
		if(!empty($OLD[$param_id]['value']) && $OLD[$param_id]['value'] == $value) continue;
			
		if ($row['data_type'] == 'char') {
			// ��������� ����
			$history_data[] = "('$history_id', '$param_id', 'char', null, null, '$value', null)";
			
		} elseif ($row['data_type'] == 'decimal') {
			// ���������� ��������
			$history_data[] = "('$history_id', '$param_id', 'decimal', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."')";
			
		} elseif ($row['data_type'] == 'bool') {
			// Checkbox
			if(!empty($value)){
				$history_data[] = "('$history_id', '$param_id', 'bool', null, '".intval($value)."', null, null)";
			} else {
				$history_data[] = "('$history_id', '$param_id', 'bool', null, '0', null, null)";
			}
			
		} elseif ($row['data_type'] == 'fkey') {
			// ������� ����
			if(!empty($value)){
				$foreign_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id='$value'");
				$history_data[] = "('$history_id', '$param_id', 'fkey', null, '$value', '".addslashes($foreign_key_value)."', null)";
			} else {
				$history_data[] = "('$history_id', '$param_id', 'fkey', null, '0', null, null)";
			}
			
		} elseif ($row['data_type'] == 'fkey_table') {
			// ������� ����: ��-�������
			if(!empty($value)){
				$fkey_table_info = $DB->query_row("select UPPER(db_alias) as db_alias, table_name from cms_table_static where id='$value'");
				$fkey_table_value = db_config_constant("name", $fkey_table_info['db_alias']) . '.' . $fkey_table_info['table_name'];  
				  
				$history_data[] = "('$history_id', '$param_id', 'fkey_table', null, '$value', '".addslashes($fkey_table_value)."', null)";
			} else {
				$history_data[] = "('$history_id', '$param_id', 'fkey_table', null, '0', null, null)";
			} 
			
		} elseif ($row['data_type'] == 'multiple') {  
			// ������� ������������ ����
			$DB->insert("
				insert ignore into auth_user_multiple (`user_id`,`param_id`,`data_id`)
				values ('{$row['user_id']}', '$param_id', '".implode("'), ('$user_id', '$param_id', '", $value)."')
			");      
			
			$foreign_multiple_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id in (0".implode(",", $value).")");
			$history_data[] = "('$history_id', '$param_id', 'multiple', '".addslashes($foreign_multiple_key_value)."', null, null, null)";  
			
		} elseif ($row['data_type'] == 'text') {
			// �����
			$history_data[] = "('$history_id', '$param_id', 'text', '$value', null, null, null)";
			
		} elseif ($row['data_type'] == 'html') {
			// HTML
			$history_data[] = "('$history_id', '$param_id', 'html', '$value', null, null, null)";
		}
	}
	
	if (!empty($history_data)) {
		$DB->insert("
			insert into auth_user_history_data (`history_id`,`param_id`, `data_type`, `value_text`,`value_int`,`value_char`,`value_decimal`) 
			values ".implode(",", $history_data)."
		");
	}
}


/**
 * ������� ������������� ��������� ������
 */
if(AUTH_DATA_CHANGE_CONFIRM){

	/**
	 * ��������� ������
	 */
	$TmplMail = new Template(TEMPLATE_ROOT."/user/data_change_confirm");
	$TmplMail->set("user_name", $user['name']);
	$TmplMail->set("auth_code", $auth_code);
	
	/**
	 * �������� ������� ���������, ��� ������ �� ���������
	 */
	$send_confirmation_to = $user['email'];
	 
	if($user['email'] != $user_email){
		$user['email'] = "<font color=\"#ff0000\"><i>{$user['email']}</i></font>";
		$user_email = "<font color=\"#ff0000\"><i>$user_email</i></font>";
	}
	
	$TmplMail->set("old_email", $user['email']);
	$TmplMail->set("new_email", $user_email);
	
	if(!empty($enabled_params)){
		
		reset($enabled_params);
		while(list(, $row) = each($enabled_params)){
			$param_id = $row['id'];
			
			$value = (!empty($values[$param_id])) ? $values[$param_id] : "";
			$old_value = (isset($OLD[$param_id]['value'])) ? $OLD[$param_id]['value'] : ""; 
	
			$comparison['param_name'] = $row['name'];
			$comparison['old_value'] = $old_value;
			$comparison['new_value'] = $value;
			
			/**
			 * ��������� ������
			 */
			if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
				$comparison['old_value']  = $old_value; 
				$comparison['new_value']  = (!empty($_FILES['param']['name'][$param_id]))?$_FILES['param']['name'][$param_id]:"";
			}
			 
			/**
			 * ��������� ���������� ������
			 */
			if ($row['data_type'] == 'bool') {
				$comparison['old_value'] = ($old_value != 1) ? "���" : "��";
				$comparison['new_value'] = ($value != 1) ? "���" : "��";
				
			} elseif ($row['data_type'] == 'fkey' || $row['data_type'] == 'multiple' || $row['data_type'] == 'fkey_table') {
				// ������� ����
				$comparison['old_value'] = $DB->result("select name from auth_user_info_data where info_id='{$row['info_id']}' and id = '$old_value'");
				$comparison['new_value'] = $DB->result("select name from auth_user_info_data where info_id='{$row['info_id']}' and id = '$value'");
			} 
			  
			if($comparison['old_value'] != $comparison['new_value']){
				$comparison['old_value'] = "<font color=\"#ff0000\"><i>{$comparison['old_value']}</i></font>";
				$comparison['new_value'] = "<font color=\"#ff0000\"><i>{$comparison['new_value']}</i></font>";
			}
			
			$TmplMail->iterate("/data/", null, $comparison);
		}
	}
	
	/**
	 * ���������� ������ 
	 */
	$Sendmail = new Sendmail(CMS_MAIL_ID, "������ ������������� ��������� ���������", $TmplMail->display());
	if($Sendmail->send($send_confirmation_to, true)){
		Action::setSuccess(cms_message('CMS', "�� ��� email ���������� ������ � �������� � ������������� ��������� ���������."));  
		Action::finish(true);
	} 
	
	Action::onError("�������� ������ � ��������� ������ � �������� � ������������� ��������� ���������. ����������, ��������� � �������������.");
} 
	

/**
 * ��������� ������� �� ����� ��������� ����������
 */
$DB->query("LOCK TABLES auth_user WRITE, site_structure_site_alias WRITE");


/**
 * �������� �������� ����������
 */
$DB->update("
	UPDATE auth_user 
	SET login = '$user_email', 
		user_group_id = '$user_group', 
		email = '$user_email', 
		name = '$auth_user_name'
	WHERE id = '$user_id'
");

$DB->query("UNLOCK TABLES");


/**
 * ������� �����, ������� �������� �� ��������
 */
reset($delete); 
while (list(, $param_id) = each($delete)) {
	delete_file($param_id);
}


/**
 * ������� ������ ����������
 */
$DB->delete("delete from auth_user_data where user_id = '$user_id'");


/**
 * ��������� �������� ������������
 */
if(!empty($enabled_params)){
	
	reset($enabled_params);
	while (list(, $row) = each($enabled_params)) {
		$param_id = $row['id'];
		
		/**
		 * ���������� �������������� ����, ��� ������� �� �������� ������
		 */
		if (empty($values[$param_id]) && !in_array($row['data_type'], array('file', 'image'))) continue;
		$value = (isset($values[$param_id])) ? $values[$param_id] : 0;  
		
		/**
		 * ��������� ������
		 */
		if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
			
			/**
			 * ���������� �����
			 */
			$extension = strtolower(Uploads::getFileExtension($_FILES['param']['name'][$param_id]));
			
			$file = UPLOADS_ROOT.'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$param_id.$extension";
			$url = substr($file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
			
			Uploads::moveUploadedFile($_FILES['param']['tmp_name'][$param_id], $file);
			
			$DB->insert("
				insert into auth_user_data (`user_id`,`param_id`,`value_char`, `value_text`)
				values ('$user_id', '$param_id', '$extension', '$url')
			");
		}
		
		
		/**
		 * ��������� ���������� ������
		 */
		if ($row['data_type'] == 'char') {
			// ��������� ����
			$insert[] = "('$user_id', '$param_id', null, null, '$value', null, null)";
			
		} elseif ($row['data_type'] == 'decimal') {
			// ���������� ��������
			$insert[] = "('$user_id', '$param_id', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."', null)";
			
		} elseif ($row['data_type'] == 'bool') {
			// Checkbox
			if(!empty($value)){
				$insert[] = "('$user_id', '$param_id', null, '".intval($value)."', null, null, null)";
			} else {
				$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
				
			}  
		} elseif ($row['data_type'] == 'fkey') {
			// ������� ����
			if(!empty($value)){
				$foreign_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id='$value'");
				$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($foreign_key_value)."', null, null)";
			} else {
				$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
			}  
			
		} elseif ($row['data_type'] == 'fkey_table') {
			// ��/�������
			if(!empty($value)){ 
				$fkey_table_info = $DB->query_row("select UPPER(db_alias) as db_alias, table_name from cms_table_static where id='$value'");
				$fkey_table_value = db_config_constant("name", $fkey_table_info['db_alias']) . '.' . $fkey_table_info['table_name'];  
				
				$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($fkey_table_value)."', null, null)";
			} else {
				$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
			} 
			
		} elseif ($row['data_type'] == 'multiple') {
			// ������� ������������ ����
			$DB->insert("
				insert ignore into auth_user_multiple (`user_id`,`param_id`,`data_id`)
				values ('$user_id', '$param_id', '".implode("'), ('$user_id', '$param_id', '", $value)."')
			");      
			
			$foreign_multiple_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id in (0".implode(",", $value).")");
			$insert[] = "('$user_id', '$param_id', '".addslashes($foreign_multiple_key_value)."', null, null, null, null)";
			
		} elseif ($row['data_type'] == 'text') { 
			// �����
			$insert[] = "('$user_id', '$param_id', '$value', null, null, null, null)";
			
		} elseif ($row['data_type'] == 'html') {
			// HTML
			$insert[] = "('$user_id', '$param_id', '$value', null, null, null, null)";
			
		} elseif ($row['data_type'] == 'date') {
			$insert[] = "('$user_id', '$param_id', null, null, null, null,'$value')";
		}
	}
	
	if (!empty($insert)) {
		$DB->insert("
			insert into auth_user_data (`user_id`,`param_id`,`value_text`,`value_int`,`value_char`,`value_decimal`,`value_date`) 
			values ".implode(",", $insert)."
		");
	}
}
 

/**
 * �������������� �����
 */      
if (AUTH_LOGIN_ON_REGISTER) {
	Auth::login($user_id, false, null);
}


/**
 * �������� �������������� ������, � ��������� ����, ��� ������������ ������� ������ � ����
 */
if (CMS_NOTIFY_EMAIL!='') {
	$mailto = CMS_NOTIFY_EMAIL;
	require_once(ACTIONS_ROOT.'site/user/notification.inc.php');
}


Action::setSuccess(cms_message('CMS', "�����������, ���� ������ ������� ��������."));

