<?php

/** 
 * Обновление структуры БД MS SQL 
 * @package Pilot 
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$db_alias = globalVar($_REQUEST['db_alias'], '');

$DBServer = DB::factory($db_alias);
$database = db_config_constant('name', $db_alias);
 

if ($DB->rows == 0) {
	Action::setError(cms_message('cms','Не найдена запрошенная БД'));
	Action::onError();
}

// Добавляем таблицы
$query = "select * from cms_schema.tables where table_catalog='$database'";
$data = $DBServer->query($query);
$insert = array();

reset($data); 
while (list(,$row) = each($data)) {
	$insert[] = "('$row[table_catalog]', '$row[table_name]', '$row[table_type]')";
}

if (!empty($insert)) {
	$query = "insert ignore into cms_schema_tables (db_name, table_name, table_type) values ".implode(",", $insert);
	$DB->insert($query);
}

// Добавляем процедуры
$query = "select * from cms_schema.routines where routine_catalog='$database'";
$data = $DBServer->query($query);
$insert = array();

reset($data); 
while (list(,$row) = each($data)) {
	$insert[] = "('$row[routine_catalog]', '$row[routine_name]', '$row[routine_type]')";
}

if (!empty($insert)) {
	$query = "insert ignore into cms_schema_tables (db_name, table_name, table_type) values ".implode(",", $insert); 
	$DB->insert($query);
}

// Добавляем колонки
$query = "select * from cms_schema.columns where table_catalog='$database'";
$data = $DBServer->query($query);
$insert = array();

reset($data); 
while (list(,$row) = each($data)) {
	$row['is_nullable'] = ($row['is_nullable'] == 'YES') ? 'true' : 'false';
	if (!empty($row['character_maximum_length'])) {
		$row['character_maximum_length'] = $row['character_maximum_length'];
	} elseif (!empty($row['numeric_precision'])) {
		$row['character_maximum_length'] = $row['numeric_precision'];
	} elseif (!empty($row['datetime_precision'])) {
		$row['character_maximum_length'] = $row['datetime_precision'];
	} elseif ($row['data_type'] == 'bit') {
		$row['character_maximum_length'] = 1;
	}
	
	if (preg_match("/_(".str_replace(",", "|", LANGUAGE_ALL_AVAILABLE).")$/", $row['column_name'], $matches)) {
		$row['column_pilot'] = substr($row['column_name'], 0, strlen($row['column_name']) - 3);
		$row['column_language'] = "'$matches[1]'";
	} else {
		$row['column_pilot'] = $row['column_name'];
		$row['column_language'] = "NULL";
	}
	
	$insert[] = "(
		'$row[table_catalog]',
		'$row[table_name]',
		'$row[column_name]',
		$row[column_language],
		'$row[column_pilot]',
		'$row[ordinal_position]',
		'$row[column_default]',
		'$row[is_nullable]',
		'$row[data_type]',
		'$row[character_maximum_length]',
		'$row[data_type]'
	)";
}

if (!empty($insert)) {
	$query = "insert ignore into cms_schema_columns (
		db_name, table_name, column_name, column_language, column_pilot, ordinal_position,
		column_default, is_nullable, data_type, character_max_length, column_type) values ".implode(",", $insert); 
	$DB->insert($query);
}


?>