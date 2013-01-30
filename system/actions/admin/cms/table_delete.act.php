<?php
/**
* Event occur when user press delete button
* @package Pilot
* @subpackage Actions_Admin
* @version 4.3
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

/**
* Define type of vars
*/
$table_id = globalVar($_REQUEST['_table_id'], 0);
$data = globalVar($_REQUEST[$table_id]['id'], array());

if (empty($data)) {
	Action::setError(cms_message('CMS', 'Не указаны ряды которые необходимо удалить'));
	Action::onError();
}



/**
* Privileges check
*/
if (!Auth::updateTable($table_id)) {
	$query = "SELECT name, title_".LANGUAGE_CURRENT." AS title FROM cms_table WHERE id='".$table_id."'";
	$info = $DB->query_row($query);
	Action::setError(cms_message('CMS', 'У Вас нет прав на удаление записей из таблицы "%s" (%s).', $info['title'], $info['name']));
	Action::onError();
}



remove_rows($table_id, array('id' => $data));

/**
* Removing rows from main and child tables
* @param int $table_id
* @param array $data
* @return void
*/
function remove_rows($table_id, $data) {
	$cmsEditDel = new cmsEditDel($table_id, $data);
	$delete_id = $cmsEditDel->dbChange();
	unset($cmsEditDel);
	
	if (empty($delete_id)) return;
	
	$child = getChildTables($table_id);
	reset($child);
	while (list($table_id, $field_name) = each($child)) {
		remove_rows($table_id, array($field_name => $delete_id));
	}
	
}


/**
 * Определяет дочерние таблицы
 * Таблицы, в которых поле parent ссылается на данную таблицу
 * 
 * @param int $table_id
 * @return array
 */
function getChildTables($table_id) {
	global $DB;
	
	$query = "
		SELECT tb_tables.id, tb_fields.name
		FROM cms_table AS tb_tables
		INNER JOIN cms_field AS tb_fields ON tb_fields.id = tb_tables.parent_field_id
		WHERE 
			tb_tables.parent_field_id != 0
			AND tb_fields.fk_table_id = '$table_id'
			AND fk_link_table_id = ''
	";
	return $DB->fetch_column($query, 'id', 'name');
}

?>