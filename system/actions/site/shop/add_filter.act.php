<?php
/**
 * Добавляет условие фильтрации
 * @package Pilot
 * @subpackage Shop
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$field_name = globalVar($_REQUEST['field_name'], '');
$value 		= globalVar($_REQUEST['value'], '');
$group_id 	= globalVar($_REQUEST['group_id'], 0);

$_SESSION['shop_filter'][$group_id][$field_name][$value] = $value;


?>