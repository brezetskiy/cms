<?php
/** 
 * Чистит таблицу cms_log_404
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$query = "TRUNCATE TABLE cms_log_404";
$DB->query($query);
 
?>