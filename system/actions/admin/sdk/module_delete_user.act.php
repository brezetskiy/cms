<?php
/**
 * ”даление пользователей при установке системы
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$groups = globalVar($_REQUEST['users'], array());

$query = "delete from auth_user where group_id in (0".implode(",", $groups).")";
$DB->delete($query);

$query = "delete from auth_group where id in (0".implode(",", $groups).")";
$DB->delete($query);

$query = "delete from auth_group_action where group_id in (0".implode(",", $groups).")";
$DB->delete($query);

$query = "delete from auth_group_structure where group_id in (0".implode(",", $groups).")";
$DB->delete($query);

?>