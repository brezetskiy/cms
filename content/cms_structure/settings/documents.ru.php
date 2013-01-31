<?php

/** 
 * Администрирование шаблонов документов
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@id.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */


function cms_prefilter($row){
	$row['file'] = "<a href=\"/uploads/cms_document/file/".Uploads::getIdFileDir($row['id']).".$row[file]\"><img src=\"/design/cms/img/download.png\" border=\"0\"></a>";
	
	return $row;
}

$query = "
	SELECT
		tb_document.id, 
		tb_module.name as modul, 
		tb_document.uniq_name,
		tb_document.description_".LANGUAGE_CURRENT." as description,
		tb_document.file
	FROM cms_document as tb_document
	LEFT JOIN cms_module as tb_module ON tb_module.id = tb_document.module_id
"; 
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('modul', '10%', 'center', 'Модуль');
$cmsTable->addColumn('uniq_name', '20%');
$cmsTable->addColumn('description', '50%');
$cmsTable->addColumn('file', '3%', 'center');  
echo $cms_view = $cmsTable->display();
unset($cmsTable);

?>