<?php
/**
 * Ёкспорт данных из таблицы
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 1993-2005
 */

$query = "SELECT id, name AS table_name FROM cms_table ORDER BY name ASC";
$tables = $DB->query($query);
reset($tables);
while(list(,$row) = each($tables)) {
	$TmplContent->iterate('/table/', null, $row);
}


?>