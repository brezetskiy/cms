<?php 

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$image_border = globalVar($_COOKIE['sw_img_border'], 0);

$TmplDesign = new Template(SITE_ROOT.'templates/editor/image/edit');
$TmplDesign->set('table_name', globalVar($_GET['table_name'], ''));
$TmplDesign->set('field_name', globalVar($_GET['field_name'], ''));
$TmplDesign->set('id', globalVar($_GET['id'], 0));
$TmplDesign->set('thumb_width', globalVar($_COOKIE['sw_thumb_width'], 400));
$TmplDesign->set('thumb_height', globalVar($_COOKIE['sw_thumb_height'], 300));
if($image_border == '1') {
	$TmplDesign->set('image_border', 'checked');
}
echo $TmplDesign->display();
?>