<?php
/** 
 * Жкспорт структуры БД 
 * @package Pilot 
 * @subpackage SDK 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$query = "select id, concat(name, '@', host, ' (', alias, ')') from cms_db where type='mysqli'";
$data = $DB->fetch_column($query);
$TmplContent->set('db_list', $data);

?>