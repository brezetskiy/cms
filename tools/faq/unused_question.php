<?php
/** 
 * Подгрузка вопроса в FAQ 
 * @package Pilot 
 * @subpackage FAQ 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

/**
 * Определяем интерфейс для поддержки интернационализации
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

ini_set('display_errors', 'on');

/**
 * Конфигурационный файл
 */
require_once('../../system/config.inc.php');

$DB = DB::factory('default');

$JsHttpRequest = new JsHttpRequest("windows-1251");

$id = globalVar($_REQUEST['id'], 0);

$query = "SELECT content_".LANGUAGE_CURRENT." as content FROM faq_question WHERE id = '$id'";
$content = $DB->result($query);

echo id2url($content);


exit;
?>