<?php
/**
 * Вывод списка интерфейсов системы
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$query = "
	SELECT 
		id, 
		name,
		title_".LANGUAGE_CURRENT." AS title
	FROM cms_interface
	ORDER BY name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('name', '20%', 'center');
$cmsTable->addColumn('title', '50%');
echo $cmsTable->display();
unset($cmsTable);

/**
* Ссылки на изменение структуры БД
*/
$query = "SELECT id, name, title_".LANGUAGE_CURRENT." AS title FROM cms_interface ORDER BY name ASC";
$interface = $DB->query($query);

$del = array();
$add = array();

reset($interface);
while(list(,$row) = each($interface)) {
	$TmplContent->iterate('/add/', null, $row);
	$TmplContent->iterate('/del/', null, $row);
}

/**
 * Выводим колонки, которые относятся к интерфейсам
 */
$query = "SELECT id, name FROM cms_interface ORDER BY name";
$interface = $DB->fetch_column($query, 'id', 'name');

reset($interface);
while(list($interface_id, $name) = each($interface)) {
	$tmpl_interface = $TmplContent->iterate('/interface/', null, array('name'=>$name));
	$query = "set group_concat_max_len = 1000000";
	$DB->query($query);
	$query = "
		SELECT
			tb_table.id,
			tb_table.name AS table_name,
			GROUP_CONCAT(DISTINCT '<a href=\"/".LANGUAGE_URL."action/admin/sdk/translate_csv/?table_name=', tb_table.name ,'&field_name=', tb_field.name, '&_return_path=".CURRENT_URL_LINK."\">', tb_field.name SEPARATOR '</a>, ') AS field
		FROM cms_field AS tb_field
		INNER JOIN cms_table AS tb_table ON tb_table.id=tb_field.table_id
		WHERE 
			tb_table.interface_id='".$interface_id."'
			AND tb_field._is_multilanguage
		GROUP BY table_name
		ORDER BY table_name
	";
	$tables = $DB->query($query);
	
	reset($tables);
	while(list(,$row) = each($tables)) {
		$TmplContent->iterate('/interface/field/', $tmpl_interface, $row);
	}
}
?>