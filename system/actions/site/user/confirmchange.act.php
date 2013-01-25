<?php
/**
* Подтверждение изменения данных пользователя
*
* @package Pilot
* @subpackage User
* @version 3.0
* @author Miha Barin <barin@delta-x.com.ua>
* @copyright Copyright 2010, Delta-X ltd.
*/


$code = globalVar($_REQUEST['code'], '');


/**
 * Успешное завершение события
 * @param int $user_id
 */
function done($user_id){
	global $DB;
	
	/**
	 * Автоматический логин
	 */      
	if (AUTH_LOGIN_ON_REGISTER) {
		Auth::login($user_id, false, null);
	}

	/**
	 * Высылаем администратору письмо, с указанием того, что пользователь изменил данные о себе
	 */
	if (CMS_NOTIFY_EMAIL!='') {
		$mailto = CMS_NOTIFY_EMAIL;
		require_once(ACTIONS_ROOT.'site/user/notification.inc.php');
	}
	
	Action::setSuccess(cms_message('CMS', 'Поздравляем, ваши данные успешно изменены.'));
}


/**
 * Проверка корректности кода
 */
if (!preg_match('/^[a-z0-9]{32}$/', $code)) {
	Action::onError(cms_message('CMS', 'Неправильный код подтверждения. Убедитесь, что Вы скопировали ссылку полностью'));
}


/**
 * Вытягивание основных данных соответствующей коду записи в истории изменений
 */
$query = "SELECT * FROM auth_user_history WHERE auth_code = '$code'";
$history = $DB->query_row($query);

/**
 * Неправильный код подтверждения
 */
if ($DB->rows == 0) {	
	Action::onError(cms_message('CMS', 'Неправильный код подтверждения. Убедитесь, что Вы скопировали ссылку полностью'));
}


/**
 * Закрываем таблицы на время изменения информации
 */
$query = "LOCK TABLES auth_user WRITE, site_structure_site_alias WRITE";
$DB->query($query);


/**
 * Изменяем основную информацию
 */
$update_data = array();

if(!empty($history['user_group_id'])){
	$update_data[] = "user_group_id = '{$history['user_group_id']}'";
} 
if(!empty($history['login'])){
	$update_data[] = "login = '".strtolower(addslashes($history['login']))."'";
}
if(!empty($history['email'])){
	$update_data[] = "email = '".strtolower(addslashes($history['email']))."'";
} 
if(!empty($history['name'])){
	$update_data[] = "name = '".addslashes($history['name'])."'";  
} 

if(!empty($update_data)){
	$DB->update("UPDATE auth_user SET ".implode(",", $update_data)." WHERE id = '$history[user_id]'");
}


/**
 * Открываем таблицы
 */
$DB->query("UNLOCK TABLES");  


/**
 * Вытягивание второстепенных данных о соответствующей коду записи в истории изменений
 */
$query = "
	select 
		tb_data.param_id,
		tb_data.*,
		case tb_data.data_type
			when 'char' then value_char
			when 'file' then value_char
			when 'image' then value_char
			when 'decimal' then value_decimal
			when 'bool' then value_int
			when 'fkey' then value_int
			when 'fkey_table' then value_int 
			else value_text
		end as value
	from auth_user_history_data as tb_data
	where tb_data.history_id='$history[id]'   
";
$history_data = $DB->query($query); 
$params       = array();
$user_id 	  = $history['user_id'];   

if(empty($history_data)){
	done($user_id);
	Action::setSuccess(cms_message('CMS', 'Поздравляем, ваши данные успешно изменены.'));
	Action::finish();	
}

/**
 * Форимаруем массив ключей параметров и удаляем файлы, которые помечены на удаление
 */
reset($history_data);
while (list(, $row) = each($history_data)) {
	$param_id = $row['param_id'];
	$params[] = $param_id;
	
	if($row['data_type'] == "delete_file"){
		$query = "SELECT value_char as value  FROM auth_user_data WHERE user_id = '$history[user_id]' AND param_id = '$param_id'";
		$value = $DB->result($query);
		
		$file = UPLOADS_ROOT.'auth_user_data/'.Uploads::getIdFileDir($history['user_id']).'/'.$param_id.'.'.$value;
		if (is_file($file)) {
			unlink($file);
		}
	}
}


/**
 * Удаляем старую информацию
 */
$query = "delete from auth_user_data where user_id = '$history[user_id]' ".where_clause("param_id", $params);
$DB->delete($query);


/**
 * Добавляем свойства пользователя
 */
reset($history_data);
while (list(, $row) = each($history_data)) {
	$param_id = $row['param_id'];
	$value 	  = $row['value'];

	/**
	 * Обработка файлов
	 */
	if (in_array($row['data_type'], array('file', 'image'))) {
		$history_file = UPLOADS_ROOT.'auth_user_history_data/'.Uploads::getIdFileDir($history['id']).'/'.$param_id.'.'.$value;
		if(!is_file($history_file)){
			continue;
		}  
		
		// Закачиваем файлы
		$user_file = UPLOADS_ROOT.'auth_user_data/'.Uploads::getIdFileDir($history['user_id'])."/$param_id.$value";
		$url = substr($user_file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
		Uploads::moveUploadedFile($history_file, $user_file);
		Filesystem::copy($history_file, $user_file); 
		$query = "
			insert into auth_user_data (`user_id`,`param_id`,`value_char`, `value_text`)
			values ('{$history['user_id']}', '$param_id', '$value', '$url')
		";
		$DB->insert($query);
	}
	
	/**
	 * Добавляем переданные данные
	 */
	if ($row['data_type'] == 'char') {
		
		// Текстовое поле
		$insert[] = "('{$history['user_id']}', '$param_id', null, null, '".addslashes($value)."', null)";
	} elseif ($row['data_type'] == 'decimal') {
		
		// Десятичное значение
		$insert[] = "('{$history['user_id']}', '$param_id', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."')";
	} elseif ($row['data_type'] == 'bool') {
		
		// Checkbox
		if(!empty($value)){
			$insert[] = "('{$history['user_id']}', '$param_id', null, '".intval($value)."', null, null)";
		} else {
			$insert[] = "('{$history['user_id']}', '$param_id', 'null', 0, null, null)";
		} 
	} elseif ($row['data_type'] == 'fkey') {
		
		// Внешний ключ
		if(!empty($value)){
			$query = "select group_concat(name) from auth_user_info_data where id='$value'";
			$insert[] = "('{$history['user_id']}', '$param_id', null, '$value', '".addslashes($DB->result($query))."', null)";
		} else {
			$insert[] = "('{$history['user_id']}', '$param_id', 'null', 0, null, null)";
		} 
	} elseif ($row['data_type'] == 'fkey_table') {
		
		// БД/таблица
		if(!empty($value)){
			$fkey_table_info = $DB->query_row("select UPPER(db_alias) as db_alias, table_name from cms_table_static where id='$value'");
			$fkey_table_value = db_config_constant("name", $fkey_table_info['db_alias']) . '.' . $fkey_table_info['table_name'];  
			  
			$insert[] = "('{$history['user_id']}', '$param_id', null, '$value', '".addslashes($fkey_table_value)."', null)";
		} else {
			$insert[] = "('{$history['user_id']}', '$param_id', 'null', 0, null, null)";
		} 
	} elseif ($row['data_type'] == 'multiple') {
		
		// Внешнее многозначное поле
		$query = "
			insert ignore into auth_user_multiple (`user_id`,`param_id`,`data_id`)
			values ('{$history['user_id']}', '$param_id', '".implode("'), ('{$history['user_id']}', '$param_id', '", $value)."')
		";
		$DB->insert($query);      
		
		$query = "select group_concat(name) from auth_user_info_data where id in (0".implode(",", $value).")";
		$insert[] = "('{$history['user_id']}', '$param_id', '".addslashes($DB->result($query))."', null, null, null)";
	} elseif ($row['data_type'] == 'text') {
		
		// Текст
		$insert[] = "('{$history['user_id']}', '$param_id', '".addslashes($value)."', null, null, null)";
	} elseif ($row['data_type'] == 'html') {
		
		// HTML
		$insert[] = "('{$history['user_id']}', '$param_id', '".addslashes($value)."', null, null, null)";
	}
}
 
if (!empty($insert)) {
	$query = "insert into auth_user_data (`user_id`,`param_id`,`value_text`,`value_int`,`value_char`,`value_decimal`) values ".implode(",", $insert);
	$DB->insert($query);
}
	
done($user_id);


?>