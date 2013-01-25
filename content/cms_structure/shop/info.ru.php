<?php
/** 
 * Справочники
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

function cms_filter($row) {
	$row['name'] = "<a href='./Data/?info_id=$row[id]'>$row[name]</a>";
	return $row;
}

$query = "
	select 
		id,
		uniq_name,
		name
	from shop_info
	order by name asc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('uniq_name', '40%');
$cmsTable->addColumn('name', '50%');
echo $cmsTable->display();
unset($cmsTable);


?>