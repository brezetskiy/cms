<?php
/**
 * Группы пользователей
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$group_id = globalVar($_GET['group_id'], 0);
$TmplContent->set('group_id', $group_id);

// Определяем название группы
$query = "select name from auth_group where id='$group_id'";
$group = $DB->result($query);
$TmplContent->set('group', $group);

// Определяем привелегии руппы
$query = "
	select action_id
	from auth_group_action
	where group_id='$group_id'
";
$action_list = $DB->fetch_column($query, 'action_id', 'action_id');

// Список привелегий
$query = "
	select
		tb_action.id,
		tb_action.title_".LANGUAGE_CURRENT." as title,
		tb_module.name as module
	from auth_action as tb_action
	inner join cms_module as tb_module on tb_module.id=tb_action.module_id
	where is_default=0
	order by tb_module.name asc
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$row['checked'] = (isset($action_list[$row['id']])) ? 'checked' : '';
	$row['module'] = make_subtitle('module', $row['module']);
	$TmplContent->iterate('/action/', null, $row);
}
?>
