<?php
/** 
 * Удаляение ошибок из таблицы лога по файлу
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$file = globalVar($_GET['file'], '');

$query = "DELETE FROM cms_log_error WHERE file = '$file'";
$DB->delete($query);
  
Action::setSuccess("Очищен лог ошибок для файла $file");

?>