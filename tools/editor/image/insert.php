<?php 
/**
* Формирование формы для вставки картинки
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');


require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

/**
* Типизируем переменные
*/
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$id = globalVar($_GET['id'], 0);

$image_border = globalVar($_COOKIE['editor_img_border'], 0);
$thumb = globalVar($_COOKIE['editor_thumb'], 'make');

$thumb_width = globalVar($_COOKIE['editor_thumb_width'], 400);
$thumb_height = globalVar($_COOKIE['editor_thumb_height'], 300);

$hspace = globalVar($_COOKIE['editor_hspace'], '');
$vspace = globalVar($_COOKIE['editor_vspace'], '');

$watermark = globalVar($_COOKIE['editor_watermark'], '');


/**
* Обрабатываем шаблон
*/
$TmplDesign = new Template(SITE_ROOT.'templates/editor/image/insert');
$TmplDesign->set('table_name', $table_name);
$TmplDesign->set('field_name', $field_name);
$TmplDesign->set('id', $id);
$TmplDesign->set('thumb_width', $thumb_width);
$TmplDesign->set('thumb_height', $thumb_height);

$TmplDesign->set('hspace', $hspace);
$TmplDesign->set('vspace', $vspace);

switch ($thumb) {
	case 'none':
		$TmplDesign->set('thumb_none_checked', 'checked');
		$TmplDesign->set('thumb_make_checked', '');
		$TmplDesign->set('thumb_upload_checked', '');
		
		$TmplDesign->set('div_thumb_none_display', 'block');
		$TmplDesign->set('div_thumb_make_display', 'none');
		$TmplDesign->set('div_thumb_upload_display', 'none');
	break;
	
	case 'upload':
		$TmplDesign->set('thumb_none_checked', 'checked');
		$TmplDesign->set('thumb_make_checked', '');
		$TmplDesign->set('thumb_upload_checked', '');
		
		$TmplDesign->set('div_thumb_none_display', 'block');
		$TmplDesign->set('div_thumb_make_display', 'none');
		$TmplDesign->set('div_thumb_upload_display', 'none');
	break;
	
	default:
		$TmplDesign->set('thumb_none_checked', '');
		$TmplDesign->set('thumb_make_checked', 'checked');
		$TmplDesign->set('thumb_upload_checked', '');
		
		$TmplDesign->set('div_thumb_none_display', 'none');
		$TmplDesign->set('div_thumb_make_display', 'block');
		$TmplDesign->set('div_thumb_upload_display', 'none');
	break;
}

if($image_border == '1') {
	$TmplDesign->set('image_border', 'checked');
}

if($watermark == 'true') {
	$TmplDesign->set('watermark', 'checked');
}

echo $TmplDesign->display();
?>