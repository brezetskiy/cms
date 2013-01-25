<?php
/**
* Окно, которое показывает картинку
* @package Pilot
* @subpackage Executables
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'SITE');

/**
* Configuration
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$parser = globalVar($_REQUEST['parser'], '');
$url = globalVar($_REQUEST['url'], '');

$src_file = UPLOADS_ROOT.$url;
$dst_file = SITE_ROOT."i/$parser/$url";
$dummy_file = SITE_ROOT.'i/'.$parser.'/1x1.gif';

if (is_file($dst_file)) {
	header('Content-Type: image/jpeg');
	echo readfile($dst_file);
	exit;
} 

$query = "select * from cms_image_size where uniq_name='$parser'";
$info = $DB->query_row($query);
if ($DB->rows == 0) {
//	header("HTTP/1.0 404 Not Found");
	echo "Parser \"$parser\" not found.";
	exit;
}
if (!is_file($src_file) && !is_file($dummy_file)) {
	Image::createDummy($info['width'], $info['height'], $dummy_file);
	$src_file = $dummy_file;
} elseif (!is_file($src_file)) {
	$src_file = $dummy_file;
}

$img = getimagesize($src_file);
if ($img[0] > $info['width'] || $img[1] > $info['height']) {
	$Image = new Image($src_file);
	$Image->jpeg_quality = $info['quality'];
	$Image->resize($info['width'], $info['height']);
	$Image->watermarkId($info['watermark_id']);
	$Image->save($dst_file);
	if($info['roundedges']) {
		$Image->addRoundEdges($info['round_radius_x'], $info['round_radius_y']);
	}
} else {
	$Image = new Image($src_file);
	$Image->jpeg_quality = $info['quality'];
	$Image->watermarkId($info['watermark_id']);
	if (false === $Image->save($dst_file)) {
		if (!is_dir(dirname($dst_file))) {
			mkdir(dirname($dst_file), 0777, true);
		}
		copy($src_file, $dst_file);
	}
	if($info['roundedges']) {
		$Image->addRoundEdges($info['round_radius_x'], $info['round_radius_y']);
	}
}

if (!is_file($dst_file) || !is_readable($dst_file)) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

header('Content-Type: image/jpeg');
echo readfile($dst_file);
?>