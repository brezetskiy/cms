<?php
/**
 * Удаление шаблонов при установке системы
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$template = globalVar($_REQUEST['template'], array());

$query = "select * from site_template_group where id in (0".implode(",", $template).") and name not in ('_default', 'cms')";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	Filesystem::delete(SITE_ROOT.'design/'.$row['name']);
}

$query = "delete from site_template_group where id in (0".implode(",", $template).") and name not in ('_default', 'cms')";
$DB->delete($query);

$query = "delete from site_template where group_id in (0".implode(",", $template).")";
$DB->delete($query);
?>