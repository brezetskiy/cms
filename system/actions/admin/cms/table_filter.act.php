<?php

/**
 * Установка значений фильтра для таблицы
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$structure_id = globalVar($_REQUEST['structure_id'], 0);
$instance_number = globalVar($_REQUEST['instance_number'], 0);

$admin_id = Auth::getUserId();
$filter = globalVar($_REQUEST['filter'], array());
 

$DB->delete("
	DELETE FROM cms_filter 
	WHERE admin_id='$admin_id' 
		AND structure_id='$structure_id'
		AND instance_number='$instance_number'
");


$insert = array();
$insert_query = "('$admin_id', '$structure_id', '$instance_number', '%s', '%s', '%s', %s, %s)";


reset($filter);
while (list($field_code, $field_filter) = each($filter)) {
	// Примечание: $field_code - это id поля и язык поля, которые разделены знаком подчёркивания
	
	if (empty($field_filter['condition'])) continue;
	if (!isset($field_filter[0]) && isset($field_filter['dummie'])) {
		$field_filter[0] = 0;
	} elseif (empty($field_filter[0])) {
		continue;
	}
	
	// Определяем параметры фильтрации
	$field_id = substr($field_code, 0, strpos($field_code, '_'));
	$field_language = substr($field_code, strpos($field_code, '_') + 1);
	$condition = urldecode($field_filter['condition']);
	if (isset($field_filter[0]) && is_array($field_filter[0])) {
		$value_1 = addslashes(trim(implode(',', $field_filter[0])));
	} elseif (isset($field_filter[0]) && !is_array($field_filter[0])) {
		$value_1 = addslashes(trim($field_filter[0]));
	} else {
		$value_1 = '';
	}
	$value_2 = (isset($field_filter[1])) ? addslashes(trim($field_filter[1])) : '';
	
	// Если указана дата, то меняем её формат на YYYY-mm-dd
	if (preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", $value_1, $matches)) $value_1 = "$matches[3]-$matches[2]-$matches[1]";
	if (preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", $value_2, $matches)) $value_2 = "$matches[3]-$matches[2]-$matches[1]";
	
	$insert[] = sprintf($insert_query, $field_id, $field_language, $condition, "'$value_1'", "'$value_2'");
}

// Добавляем значения в таблицу
if (!empty($insert)) {
	$query = "insert into cms_filter values ".implode(', ', $insert);
	$DB->insert($query);
}



?>