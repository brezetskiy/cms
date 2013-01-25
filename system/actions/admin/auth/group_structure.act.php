<?php
/**
 * Устанавливает права доступа группе администраторов
 * @package Pilot
 * @subpackage Auth
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
$group_id = globalVar($_REQUEST['group_id'], 0);
$structure = globalVar($_REQUEST['structure_id'], array());

$query = "delete from auth_group_structure where group_id='$group_id'";
$DB->delete($query);

$insert = array();
reset($structure);
while (list(,$row) = each($structure)) {
	$insert[] = "('$group_id', '$row')";
	if (count($insert) > 200) {
		$query = "insert ignore into auth_group_structure (group_id, structure_id) values ".implode(", ", $insert);
		$DB->insert($query);
		$insert = array();
	}
}

if (!empty($insert)) {
	$query = "insert ignore into auth_group_structure (group_id, structure_id) values ".implode(", ", $insert);
	$DB->insert($query);
}

$_RESULT['action_message'] = 'Изменения сохранены';
?>