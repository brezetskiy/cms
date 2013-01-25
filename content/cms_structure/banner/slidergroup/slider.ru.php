<?php
/**
* Список баннеров группы 
*
* @package Pilot
* @subpackage Banner
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$group_id = globalVar($_GET['group_id'], 0);

function image_filter($row) {
	global $sum_weight;
	$filename = Uploads::getFile('banner_slider', 'image', $row['id'], $row['image']);
	if (is_file($filename) && is_readable($filename)) {
		$image_size = getimagesize($filename);
		$url = Uploads::getURL($filename);
		$row['image'] = "<img src='".$url."'>";
		
	}else {
		$row['image'] = "<img src='/design/cms/img/banner/noimage.jpg'>";
	}
	
	
	return $row;
}


$query = "
	SELECT 
		id,
		active,
		title,
		image, priority
	FROM banner_slider
	WHERE group_id='$group_id'
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'image_filter');
$cmsTable->addColumn('title', '20%');
$cmsTable->addColumn('image', '20%', 'center');
$cmsTable->addColumn('active', '5%', 'center', 'Активен');
$cmsTable->setColumnParam('title', 'order', 'title');
$cmsTable->setColumnParam('active', 'editable', true);
echo $cmsTable->display();
unset($cmsTable);
?>