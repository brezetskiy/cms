<?php 
/**
* Выводит список картинок, браузер по директориям
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(dirname(__FILE__).'/flash');
$TmplDesign->set('id', globalVar($_GET['id'], 0));
$TmplDesign->set('temp_id', globalVar($_GET['temp_id'], ''));
$TmplDesign->set('table_name', globalVar($_GET['table_name'], ''));
$TmplDesign->set('field_name', globalVar($_GET['field_name'], ''));
$TmplDesign->set('editor_name', globalVar($_GET['editor_name'], ''));
echo $TmplDesign->display();
?>