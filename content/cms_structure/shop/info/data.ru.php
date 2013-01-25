<?php
/** 
 * Информация в справочниках
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 
$info_id = globalVar($_GET['info_id'], 0);

$query = "
	select 
		id,
		concat(name, ifnull(concat('<br><span class=comment>', description, '</span>'), '')) as name
	from shop_info_data
	where info_id='$info_id'
	order by name asc
";

$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('name', '80%');
echo $cmsTable->display();
unset($cmsTable);


?>