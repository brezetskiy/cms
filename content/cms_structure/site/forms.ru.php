<?php
/** 
 * Формы на сайте
 * @package Pilot 
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

function cms_filter($row) {
	$row['title'] = "<a href='./Fields/?form_id=$row[id]'>$row[title]</a>";
	return $row;
}

$query = "
	SELECT 
		id,
		uniq_name,
		html_editor(id, 'form', 'autoreply', 'Изменить') as reply,
		title_".LANGUAGE_SITE_DEFAULT." AS title
	FROM form
	ORDER BY title_".LANGUAGE_SITE_DEFAULT."
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('title', '60%');
$cmsTable->addColumn('uniq_name', '30%');
$cmsTable->addColumn('reply', '30%', 'center', 'Автоответ');
echo $cmsTable->display();
unset($cmsTable);

?>