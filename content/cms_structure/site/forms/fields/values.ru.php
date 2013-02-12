<?php
/** 
 * Поля в формах на сайте
 * @package Pilot 
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$field_id = globalVar($_GET['field_id'], 0);

function cms_filter($row) {
	return $row;
}

$query = "
	SELECT 
		id,
		uniq_name,
		title_".LANGUAGE_SITE_DEFAULT." as title,
		priority
	FROM form_field_value
	where field_id = '$field_id'
	ORDER BY priority
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('title', '40%');
$cmsTable->addColumn('uniq_name', '20%');
echo $cmsTable->display();
unset($cmsTable);

?>