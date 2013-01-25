<?php
/**
* Группы баннеров
*
* @package Pilot
* @subpackage Banner
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

function cms_filter($row) {
	$row['uniq_name'] = "<a href='./slider/?group_id=$row[id]'>$row[uniq_name]</a>";
	return $row;
}

$query = "
	SELECT 
		id,
		uniq_name,
		name
	FROM banner_slidergroup
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('uniq_name', '30%');
$cmsTable->addColumn('name', '60%');
echo $cmsTable->display();
unset($cmsTable);
?>