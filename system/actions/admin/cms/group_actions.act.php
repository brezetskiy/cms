<?php
/**
 * Сохраняет привелегии для пользователей через Ajax
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2008
 */
$actions = globalVar($_REQUEST['action'], array());
$group_id = globalVar($_REQUEST['group_id'], 0);

$query = "delete from auth_group_action where group_id='$group_id'";
$DB->delete($query);

$insert = array();
reset($actions);
while (list(,$action_id) = each($actions)) {
	$insert[] = "('$group_id', '$action_id')";
}
if (!empty($insert)) {
	$query = "insert into auth_group_action (group_id, action_id) values ".implode(",", $insert);
	$DB->insert($query);
}

// Отключаем всех пользователей, которые ыбли в этой группе
$query = "select id from auth_user where group_id='$group_id'";
$users = $DB->fetch_column($query);
$query = "delete from auth_online where user_id in (0".implode(",", $users).")";
$DB->delete($query);

Action::setSuccess(cms_message('CMS', 'Изменения успешно сохранены. Отключено %d пользователей для обновления настроек.', $DB->rows));

?>