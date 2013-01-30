<?php
/**
 * формируем список модулей 
 * которые находятся в системе 
 */

$query = "
	SELECT 
		tb_module.id, 
		concat('<a href=\"./Modparam/?module_id=', tb_module.id, '\">', tb_module.name, '</a>') as name,
		concat('<a href=\"./Modparam/?module_id=', tb_module.id, '\">Настроить</a>') as setup,
		tb_module.description_".LANGUAGE_CURRENT." as  description,
		count(tb_settings.id) as count
	FROM cms_module as tb_module
	left join cms_settings as tb_settings on tb_settings.module_id=tb_module.id
	group by tb_module.id
	ORDER BY tb_module.name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('name', '30%', 'left', 'Модуль');
$cmsTable->addColumn('description', '40%', 'left', 'Описание');
$cmsTable->addColumn('count', '30%', 'right', 'Параметров');
echo $cmsTable->display();
unset($cmsTable);





?>
