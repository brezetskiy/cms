<?php
/** 
 * Формирует CSV файлы для переводчика 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$table_name = globalVar($_REQUEST['table_name'], '');
$field_name = globalVar($_REQUEST['field_name'], '');

// Определяем название БД
$query = "
	select 
		tb_db.alias,
		tb_table.id as table_id,
		tb_table.name as table_name,
		tb_field.name as field_name,
		tb_table.interface_id
	from cms_db as tb_db
	inner join cms_table as tb_table on tb_table.db_id=tb_db.id
	inner join cms_field as tb_field on tb_field.table_id=tb_table.id
	where
		tb_field.name='$field_name' and
		tb_table.name='$table_name'
";
$info = $DB->query_row($query);
$info['db_name'] = db_config_constant("name", $info['alias']);  

// Определяем, по какому полю производить сортировку
$query = "
	select name
	from cms_field
	where
		table_id='$info[table_id]' and
		name='priority'
";
$order = $DB->result($query);
if ($DB->rows == 0) {
	$order = 'id';
}


// Определяем языки, которые должна поддерживать таблица
$query = "
	select tb_language.code
	from cms_language as tb_language
	inner join cms_language_usage as tb_relation on tb_relation.language_id=tb_language.id
	where
		tb_relation.interface_id='$info[interface_id]'
";
$languages = $DB->fetch_column($query);

$DBServer = DB::factory($info['alias']);

$fields = array();
reset($languages); 
while (list(,$language) = each($languages)) {
	 $fields[] = "$info[field_name]_$language";
}




header('Content-Type: application/x-zip-compressed');
header('Content-Disposition: attachment; filename="'.$info['table_name'].'_'.$info['field_name'].'.csv"');

$query = "
	select 
		id,
		".implode(",", $fields)."
	from `$info[db_name]`.`$info[table_name]`
	order by `$order` asc
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) {
	if ($index == 0) {
		echo implode(";", array_keys($row));
		echo "\n";
	}
	echo implode(";", $row);
	echo "\n";
}

exit;

?>