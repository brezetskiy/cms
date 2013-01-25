<?php 
/**
* Выводит список картинок, браузер по директориям
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/attach');
$TmplDesign->set('id', globalVar($_GET['id'], 0));
$TmplDesign->set('table_name', globalVar($_GET['table_name'], ''));
$TmplDesign->set('field_name', globalVar($_GET['field_name'], ''));
$TmplDesign->set('max_size', ini_get('upload_max_filesize'));
echo $TmplDesign->display();
?>