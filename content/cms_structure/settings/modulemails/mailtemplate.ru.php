<?php

/** 
 * Администрирование шаблонов писем для конкретного модуля 
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@id.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */

$module_id = globalVar($_REQUEST['module_id'], 0);

$query = "
	SELECT
		tb_template.id, 
		tb_template.uniq_name,
		if(
			tb_template.is_editor=1,
			html_editor(tb_template.id, 'cms_mail_template', 'content_".LANGUAGE_SITE_DEFAULT."', if(tb_template.name_".LANGUAGE_SITE_DEFAULT."='', tb_template.uniq_name, tb_template.name_".LANGUAGE_SITE_DEFAULT.")),
			text_editor(tb_template.id, 'cms_mail_template', 'content_".LANGUAGE_SITE_DEFAULT."', if(tb_template.name_".LANGUAGE_SITE_DEFAULT."='', tb_template.uniq_name, tb_template.name_".LANGUAGE_SITE_DEFAULT."))
		) AS name
	FROM cms_mail_template as tb_template
	INNER JOIN cms_module as tb_module ON tb_template.module_id = tb_module.id
	WHERE tb_module.id = $module_id
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('uniq_name', '20%');
$cmsTable->addColumn('name', '70%');  
echo $cms_view = $cmsTable->display();
unset($cmsTable);

?>