<?php
/** 
 * Полный список фотографий в галерее 
 * @package Pilot 
 * @subpackage Gallery 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

/**
 * Class cmsShowGallery
 */
require_once(LIBS_ROOT.'cmsshow/gallery.class.php');

$query = "
	SELECT 
		id,
		photo,
		description_".LANGUAGE_CURRENT." AS description,
		priority
	FROM gallery_photo
	ORDER BY id DESC
";
$cmsTable = new cmsShowGallery($DB, $query, 9);
$cmsTable->setParam('show_columns', 3);
$cmsTable->setParam('image_field', 'photo');
echo $cmsTable->display();

?>