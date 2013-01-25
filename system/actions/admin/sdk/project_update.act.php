<?php
/**
 * Обновление информации о проекте
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$project_id = globalVar($_REQUEST['project_id'], 0);
$id = globalVar($_REQUEST['id'], 0);
$type = globalVar($_REQUEST['type'], '');
$checked = globalVar($_REQUEST['checked'], 0);

if ($type == 'module' && $checked == 1) {
	$query = "insert ignore into sdk_project_module (project_id, module_id) values ($project_id, $id)";
	$DB->insert($query);
} elseif ($type == 'module' && $checked == 0) {
	$query = "delete from sdk_project_module where project_id='$project_id' and module_id='$id'";
	$DB->delete($query);
} elseif ($type == 'site' && $checked == 1) {
	$query = "insert ignore into sdk_project_site (project_id, site_id) values ($project_id, $id)";
	$DB->insert($query);
} elseif ($type == 'site' && $checked == 0) {
	$query = "delete from sdk_project_site where project_id='$project_id' and site_id='$id'";
	$DB->delete($query);
}

$_RESULT['info'] = Module::distrib($project_id);

Action::setSuccess('Готово');
