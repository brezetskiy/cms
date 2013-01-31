<?php
/**
 * Вывод списка всех извесиных языков и картинок с флагами
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

function cms_prefilter($row) {
	$file = Uploads::getFile('cms_language', 'file', $row['id'], $row['file']);
	if (is_file($file)) {
		$row['file'] = Uploads::htmlImage($file);
	}
	return $row;
}

$query = "
	SELECT 
		id, 
		code, 
		name_".LANGUAGE_CURRENT." AS name, 
		file 
	FROM cms_language
	ORDER BY name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('file', '10%', 'center');
$cmsTable->addColumn('code', '10%', 'center');
$cmsTable->addColumn('name', '50%');
echo $cmsTable->display();
unset($cmsTable);
?>