<?php
/** 
 * Подгружаемый выпадающий список 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

/**
 * Определяем языковой интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
 * Конфигурация
 */
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

// Аунтификация при  работе с запароленными разделами
new Auth(true);
$table_id = globalVar($_REQUEST['table_id'], 0);
$field_name = globalVar($_REQUEST['field_name'], '');
$search_text = globalVar($_REQUEST['q'], '');
$element_id = globalVar($_REQUEST['element_id'], '');

$fk_table_id = $DB->result("SELECT fk_table_id FROM cms_field WHERE table_id='$table_id' AND name = '".$DB->escape($field_name)."'");
$fk_table = cmsTable::getInfoById($fk_table_id);
$table = cmsTable::getInfoById($table_id);

$DBServer = DB::factory($fk_table['db_alias']);

$query = "
	SELECT id, `$fk_table[fk_show_name]` AS name 
	FROM `$fk_table[db_name]`.`$fk_table[name]` 
	WHERE `$fk_table[fk_show_name]` LIKE '$search_text%'
	ORDER BY `$fk_table[fk_order_name]` $fk_table[fk_order_direction]
	LIMIT 0, 100
";
$data = $DBServer->query($query);
if ($DBServer->rows == 0) {
	exit;
}

reset($data);
while (list(,$row) = each($data)) {
	echo preg_replace('~\|~', '', $row['name'])."|$row[id]|{$table['name']}_$field_name\n";
}

?>