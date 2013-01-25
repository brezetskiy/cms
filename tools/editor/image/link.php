<?php 

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/image/link');

$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$id = globalVar($_GET['id'], 0);

$image_border = globalVar($_COOKIE['sw_img_border'], 0);
$thumb_width = globalVar($_COOKIE['sw_thumb_width'], 400);
$thumb_height = globalVar($_COOKIE['sw_thumb_height'], 300);

$watermark = globalVar($_COOKIE['sw_watermark'], '');


$TmplDesign->set('field_name', $field_name);
$TmplDesign->set('table_name', $table_name);
$TmplDesign->set('id', $id);
$TmplDesign->set('thumb_width', $thumb_width);
$TmplDesign->set('thumb_height', $thumb_height);

if($image_border == '1') {
	$TmplDesign->set('image_border', 'checked');
}
if($watermark == 'true') {
	$TmplDesign->set('watermark', 'checked');
}

echo $TmplDesign->display();
?>