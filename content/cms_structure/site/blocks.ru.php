<?php
/** 
 * Текстовые блоки на сайте
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$query = "
	SELECT 
		id,
		uniq_name,
		title_".LANGUAGE_SITE_DEFAULT." AS title
	FROM block
	ORDER BY title_".LANGUAGE_SITE_DEFAULT."
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('title', '60%');
$cmsTable->addColumn('uniq_name', '30%');
echo $cmsTable->display();
unset($cmsTable);

?>