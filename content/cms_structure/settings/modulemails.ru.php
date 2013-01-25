<?php
/** 
 * Администрирование шаблонов писем для конкретного модуля 
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@id.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */

$query = "
	SELECT		
		tb_module.id, 
		concat('<a href=\"./MailTemplate/?module_id=', tb_module.id, '\">', tb_module.description_".LANGUAGE_CURRENT.", '</a>') as description,
		tb_module.name,
		count(tb_template.id) as count
	FROM cms_module as tb_module
	LEFT JOIN cms_mail_template as tb_template ON tb_template.module_id = tb_module.id
	GROUP BY tb_module.id
	ORDER BY tb_module.name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('title', "Шаблоны почтовых сообщений");
$cmsTable->addColumn('description', '40%', 'left', 'Модуль');  
$cmsTable->addColumn('name', '40%', 'left', 'Название');  
$cmsTable->addColumn('count', '20%', 'right', 'Кол-во шаблонов');  
echo $cms_view = $cmsTable->display();
unset($cmsTable);


/**
 * Фильтр предварительной обработки значений в таблице
 * @ignore
 * @param array $row
 * @return array
 */
function cms_prefilter($row) {
	$row['name'] = (!is_dir(SITE_ROOT."design/$row[name]/")) ?
		'<a style="color:gray;" href="./MailDesign/?group_id='.$row['id'].'">'.$row['name'].'</a>':
		'<a href="./MailDesign/?group_id='.$row['id'].'">'.$row['name'].'</a>';
	return $row;
}

$query = "SELECT * FROM site_template_group	ORDER BY name ASC";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('title', 'Группы дизайнов');
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('name', '30%');
$cmsTable->addColumn('title', '50%'); 
echo $cmsTable->display();


?>