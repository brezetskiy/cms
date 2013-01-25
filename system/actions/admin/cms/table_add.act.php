<?php
/**
 * Сохраняет значение для редактируемого ряда
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, 2008
 */

/**
 * Проверяем права доступа на обновление
 */
if (!Auth::updateTable($table_id)) {
	$query = "SELECT name, title_".LANGUAGE_CURRENT." AS title FROM cms_table WHERE id='".$table_id."'";
	$table = $DB->query_row($query);
	Action::setError(cms_message('CMS', 'У Вас нет прав на добавление значений в таблицу "%s" (%s)', $table['title'], $table['name']));
	Action::onError();
}

$_event_table_id = $table_id;
$data = $_REQUEST[$table_id];
$ajax_select = globalVar($_REQUEST['ajax_select'], array());


if (isset($data['id']) && !empty($data['id'])) {
	$_event_type = 'update';
	$id_list = preg_split("/[^\d]+/", $_REQUEST[$table_id]['id'], -1, PREG_SPLIT_NO_EMPTY);
	reset($id_list);
	while (list(,$id) = each($id_list)) {
		$data['id'] = $id;
		$cmsEditAdd = new cmsEditAdd($table_id, $data, 'edit', $_REQUEST['tmp_dir'], $ajax_select);
		$_event_insert_id = $cmsEditAdd->dbChange();
//		x($_event_insert_id);
		unset($cmsEditAdd);
	}
} else {
	$_event_type = 'insert';
	$cmsEditAdd = new cmsEditAdd($table_id, $_REQUEST[$table_id], 'edit', $_REQUEST['tmp_dir'], $ajax_select);
	$_event_insert_id = $cmsEditAdd->dbChange();
	unset($cmsEditAdd);
}

?>