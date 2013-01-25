<?php
/** 
 * Список валют 
 * @package Pilot
 * @subpackage Currency 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$query = "
	SELECT 
		id,
		code,
		singular_".LANGUAGE_CURRENT." AS singular,
		active
	FROM currency_list
	ORDER BY active ASC, code ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('code', '15%', 'center');
$cmsTable->addColumn('singular', '30%');
$cmsTable->addColumn('active', '15%', 'center');
$cmsTable->setColumnParam('active', 'editable', true);
echo $cmsTable->display();
unset($cmsTable);
?>