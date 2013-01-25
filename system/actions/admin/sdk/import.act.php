<?php
/**
 * Импорт данных
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */


/**#@+
 * Типизируем переменные
 * @ignore
 */
define('FILE_XML', globalVar($_FILES['data']['tmp_name'], ''));
define('PARENT_ID', globalVar($_POST['parent_id'], 0));
/**#@-*/


/**
 * Определяем названия таблиц, которые используются в импорте
 */
function getTables($parser, $table_name, $attr = array()) {
	global $import_tables;
	
	if ($table_name != 'root') {
		$import_tables[$table_name] = $table_name;
	}
}

$export_tables = array();
$xml_data = file_get_contents(FILE_XML);
$xml_data = preg_replace("/^<\?xml[^>]+>[\s\n\r\t]*/i", '', $xml_data);
$xml_parser = xml_parser_create();
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 'UTF-8');
xml_set_element_handler($xml_parser, 'getTables', 'getTables'); 
// xml_set_character_data_handler($xml_parser, "characterData");
xml_parse($xml_parser, $xml_data, true) or die(sprintf("XML error: %s at line %d",  xml_error_string(xml_get_error_code($xml_parser)),  xml_get_current_line_number($xml_parser))); 
xml_parser_free($xml_parser);


/**
 * Блокируем таблицы
 */
$query = "LOCK TABLES `".implode("` READ, `", $import_tables)."` READ";
$DB->query($query);


/**
 * Создаем копии этих таблиц, с типом INNODB
 */
reset($import_tables);
while(list(,$table_name) = each($import_tables)) {
	
	$query = "CREATE TEMPORARY TABLE `tmp_".$table_name."` LIKE `".$table_name."`";
	$DB->query($query);
	
	$query = "INSERT INTO `tmp_".$table_name."` SELECT * FROM `".$table_name."`";
	$DB->insert($query);
	
	$query = "ALTER TABLE `tmp_".$table_name."` ENGINE=INNODB";
	$DB->query($query);
	
}



/**
 * Импортируем данные в таблицы
 */
$SQLImport = new SQLImport();
$SQLImport->start(FILE_XML, PARENT_ID);


/**
 * Копируем содержимое из временных таблиц в постоянные
 */
$query = "LOCK TABLES `".implode("` WRITE, `", $import_tables)."` WRITE";
$DB->query($query);



reset($import_tables);
while(list(,$table_name) = each($import_tables)) {
	// Чистим постоянную таблицу
	$query = "DELETE FROM `".$table_name."`";
//	$DB->delete($query);
	
	// Переносим данные
	$query = "INSERT IGNORE INTO `".$table_name."` SELECT * FROM `tmp_".$table_name."`";
	$DB->insert($query);
}

$query = "UNLOCK TABLES";
$DB->query($query);

?>