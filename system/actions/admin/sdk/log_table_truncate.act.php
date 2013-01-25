<?php
/** 
 * Чистит таблицу cms_log_error
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

$query = "TRUNCATE TABLE cms_log_error";
$DB->query($query);
 
?>