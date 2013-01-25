<?php
/**
 * Список модулей, в которых есть параметры
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */


$query = "
	SELECT 
		tb_module.id, 
		concat('<a href=\"./Module/?module_id=', tb_module.id, '\">', tb_module.description_".LANGUAGE_CURRENT.", '</a>') as description,
		tb_module.name,
		count(tb_settings.id) as count
	FROM cms_module as tb_module
	inner join cms_settings as tb_settings on tb_settings.module_id=tb_module.id
	group by tb_module.id
	ORDER BY tb_module.name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('description', '30%', 'left', 'Модуль');
$cmsTable->addColumn('name', '30%', 'left', 'Модуль');
$cmsTable->addColumn('count', '30%', 'right', 'Параметров');
echo $cmsTable->display();
unset($cmsTable);

?>