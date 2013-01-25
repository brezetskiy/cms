<?php
/**
* Окно, в котором размещены два IFRAME, в одном из которых пользователь выбирает 
* картинку, а в другом просматривает ее
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
$dir = globalVar($_GET['dir'], '');
$field_name = globalVar($_GET['field_name'], '');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/server_image/preview');
$TmplDesign->set('field_name', $field_name);

if (!is_dir(UPLOADS_ROOT.$dir)) {
	exit;
}

$files = Filesystem::getAllSubdirsContent(UPLOADS_ROOT.$dir, true);

function resize($width, $height, $normal_width, $normal_heigth) {
	if ($normal_heigth < $height && $normal_width < $width) {
		return 100;
	}
	$new_width = 100 * $width / $normal_width;
	$new_height = 100 * $height / $normal_heigth;
	return ($new_height > $new_width) ? $new_width : $new_height;
}

$counter = -1;
$tmpl_tr = $TmplDesign->iterate('/tr/', null, array());
reset($files);
while(list(,$file) = each($files)) {
	$counter++;
	if ($counter >= 3) {
		$tmpl_tr = $TmplDesign->iterate('/tr/', null, array());
		$counter = 0;
	}
	
	$size = getimagesize($file);
	if (empty($size)) {
		$extension = Uploads::getFileExtension($file);
		$ico = (is_file(SITE_ROOT.'img/site/ico/'.strtolower($extension).'.gif')) ? 'img/shared/ico/'.strtolower($extension).'.gif' : 'img/shared/ico/file.gif';
		$size = getimagesize(SITE_ROOT.$ico);
		$TmplDesign->iterate('/tr/td/', $tmpl_tr, array(
			'url' => Uploads::getURL($file), 
			'image' => '/'.$ico,
			'width' => $size[0], 
			'height' => $size[1],
			'size' => $extension,
			'filesize' => number_format(filesize($file), 0, '', ' ')
		));
	} else {
		$compression = resize(100, 100, $size[0], $size[1]);
		$TmplDesign->iterate('/tr/td/', $tmpl_tr, array(
			'url' => Uploads::getURL($file), 
			'image' => Uploads::getURL($file), 
			'width' => intval($size[0] / 100 * $compression), 
			'height' => intval($size[1] / 100 * $compression),
			'size' => $size[0].' x '.$size[1],
			'filesize' => number_format(filesize($file), 0, '', ' ')
		));
		
	}
	
}


echo $TmplDesign->display();
?>