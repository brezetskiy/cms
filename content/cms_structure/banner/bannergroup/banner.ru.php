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
	$filename = Uploads::getFile('banner_banner', 'image', $row['id'], $row['image']);
	if (is_file($filename) && is_readable($filename)) {
		$image_size = getimagesize($filename);
		$url = Uploads::getURL($filename);
		if ($image_size[2] == IMAGETYPE_SWF || $image_size[2] == IMAGETYPE_SWC) {
			$row['image'] = '<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" '.$image_size[3].' id="banner" ALIGN="">
								<PARAM NAME="movie" VALUE="'.$url.'">
								<PARAM NAME="quality" VALUE="high">
								<PARAM name="wmode" value="opaque">
								<PARAM NAME="bgcolor" VALUE="#FFFFFF">
								<EMBED wmode="opaque" src="'.$url.'" quality="high" bgcolor="#FFFFFF" '.$image_size[3].' NAME="banner" ALIGN="" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
							</OBJECT>';
		} else {
			$row['image'] = "<img src='".$url."'>";
		}
	} elseif (!empty($row['html'])) {
		$row['image'] = $row['html'];
	} else {
		$row['image'] = "<img src='/design/cms/img/banner/noimage.jpg'>";
	}
	
	$row['title'] = "<a href='./Stat/?banner_id=$row[id]'>$row[title]</a>";
	
	if ($row['active']) {
		$row['banner_weight'] = $row['weight']." (".round($row['weight']*100/$sum_weight,1)."%)";
	} else {
		$row['banner_weight'] = $row['weight'];
	}
	
	if (!$row['active']) {
		$row['banner_weight'] = "<span style='color:#999999'>$row[banner_weight]</span>";
		$row['title'] = "<span style='color:#999999'>$row[title]</span>";
	}
	
	return $row;
}

$query = "
	SELECT IFNULL(SUM(weight), 0) AS sum_weight
	FROM banner_banner 
	WHERE active=1 AND group_id='$group_id'
";
$sum_weight = $DB->result($query);

$query = "
	SELECT IFNULL(SUM(stat_view), 0) AS sum_stat
	FROM banner_banner 
	WHERE active=1 AND group_id='$group_id'
";
$sum_stat = $DB->result($query);

$query = "
	SELECT 
		id,
		active,
		title,
		weight,
		stat_view,
		stat_click,
		html,
		image
	FROM banner_banner
	WHERE group_id='$group_id'
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'image_filter');
$cmsTable->addColumn('title', '20%');
$cmsTable->addColumn('image', '20%', 'center');
$cmsTable->addColumn('banner_weight', '20%', 'center', 'Вес баннера');
$cmsTable->addColumn('stat_view', '10%');
$cmsTable->addColumn('stat_click', '10%');
$cmsTable->addColumn('active', '5%', 'center', 'Активен');
$cmsTable->setColumnParam('banner_weight', 'order', 'banner_weight');
$cmsTable->setColumnParam('title', 'order', 'title');
$cmsTable->setColumnParam('active', 'editable', true);
echo $cmsTable->display();
unset($cmsTable);
?>