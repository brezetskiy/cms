<?php
/** 
 * Установка количества рядов, которые необходимо выводить на одной странице 
 * @package Pilot
 * @subpackage CmsView
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

$table_id = globalVar($_REQUEST['table_id'], 0);
$structure_id = globalVar($_REQUEST['structure_id'], 0);
$rows_per_page = globalVar($_REQUEST['rows_per_page'], CMS_VIEW);

setcookie('rows_per_page_'.$structure_id.'_'.$table_id, $rows_per_page, time() + 86400 * 30, '/');

?>