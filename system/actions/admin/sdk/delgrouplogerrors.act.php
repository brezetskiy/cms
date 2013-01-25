<?php
/** 
 * Удаляение ошибок из таблицы лога
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

$list = globalVar($_GET['id_list'], '');
 
$query = "DELETE FROM cms_log_error WHERE id IN (0,$list)"; 
$DB->delete($query);
 

Action::setSuccess("Запись успешно удалена.");


?>