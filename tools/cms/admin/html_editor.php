<?php
/**
* WYSIWYG Редактор
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

$event = globalVar($_GET['event'], '');
$table_name = globalVar($_GET['table_name'], '');
$id = globalVar($_GET['id'], 0);
$field_name = globalVar($_GET['field_name'], '');

header("Location:/tools/editor/editor.php?event=$event&table_name=$table_name&id=$id&field_name=$field_name");
exit;
?>