<?php
/** 
 * Сохранение параметров пользователя 
 * @package Pilot 
 * @subpackage Auth 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */


/**
 * Удаляет файлы, которые помечены на удаление
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


$user_id = globalVar($_POST['user_id'], 0);

$values = globalVar($_POST['param'], array());
$delete = globalVar($_POST['delete'], array());


/**
 * Загружаем старую информацию о пользователе
 */
$OLD = $DB->query("
	select tb_data.*
	from auth_user_data as tb_data
	inner join auth_user_group_param as tb_param on tb_param.id=tb_data.param_id
	where tb_data.user_id='$user_id'
", 'param_id');


/**
 * Удаляем файлы, которые помечены на удаление
 */
reset($delete); 
while (list(, $param_id) = each($delete)) {
	delete_file($param_id);
}


/**
 * Удаляем старую информацию
 */
$DB->delete("delete from auth_user_data where user_id='$user_id'");
$DB->delete("delete from auth_user_multiple where user_id='$user_id'");


/**
 * Перечень параметров
 */
$params = $DB->query("
	select * from auth_user_group_param 
	where id IN (0".implode(', ', array_keys($values)).")
	order by priority asc
");
   
 
/**
 * Изменяем свойства пользователя
 */
reset($params);
while (list(, $row) = each($params)) {
	$param_id = $row['id'];
	
	/**
	 * Выдаём ошибку для обязательных полей
	 */
	if (isset($values[$param_id]) && empty($values[$param_id]) && $row['required']) {
		Action::setWarning(cms_message('Auth', 'Не указано значение параметра "%s"', $row['name']));
		continue;
	}
	
	/**
	 * Пропускаем необязательные поля, для которых не переданы данные
	 */
	if (empty($values[$param_id]) && !in_array($row['data_type'], array('file', 'image'))) continue;
	$value = (isset($values[$param_id])) ? $values[$param_id] : 0;  

	/**
	 * Обработка файлов
	 */
	if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
		
		/**
		 * Закачиваем файлы
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
	 * Добавляем переданные данные
	 */
	if ($row['data_type'] == 'char') {
		// Текстовое поле
		$insert[] = "('$user_id', '$param_id', null, null, '$value', null, null)";
		
	} elseif ($row['data_type'] == 'decimal') {
		// Десятичное значение
		$insert[] = "('$user_id', '$param_id', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."', null)";
		
	} elseif ($row['data_type'] == 'bool') {
		// Checkbox
		if(!empty($value)){
			$insert[] = "('$user_id', '$param_id', null, '".intval($value)."', null, null, null)";
		} else {
			$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
			
		}    
	} elseif ($row['data_type'] == 'fkey') { 
		// Внешний ключ
		if(!empty($value)){
			$foreign_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id='$value'");
			$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($foreign_key_value)."', null, null)";
		} else {
			$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
		}  
		
	} elseif ($row['data_type'] == 'fkey_table') {
		// БД/таблица
		if(!empty($value)){
			$fkey_table_info = $DB->query_row("select UPPER(db_alias) as db_alias, table_name from cms_table_static where id='$value'");
			$fkey_table_value = db_config_constant("name", $fkey_table_info['db_alias']) . '.' . $fkey_table_info['table_name'];  
			
			$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($fkey_table_value)."', null, null)";
		} else {
			$insert[] = "('$user_id', '$param_id', 'null', 0, null, null, null)";
		} 
		
	} elseif ($row['data_type'] == 'multiple') {
		// Внешнее многозначное поле
		$DB->insert("
			insert ignore into auth_user_multiple (`user_id`,`param_id`,`data_id`)
			values ('$user_id', '$param_id', '".implode("'), ('$user_id', '$param_id', '", $value)."')
		");      
		
		$foreign_multiple_key_value = $DB->result("select group_concat(name) from auth_user_info_data where id in (0".implode(",", $value).")");
		$insert[] = "('$user_id', '$param_id', '".addslashes($foreign_multiple_key_value)."', null, null, null, null)";
		
	} elseif ($row['data_type'] == 'text') { 
		// Текст
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


Action::setSuccess(cms_message('CMS', "Поздравляем, ваши данные успешно изменены."));



?>