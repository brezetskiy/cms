<?php
/**
 * Список существующих в системе модулей
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */


/**
 * Определяем ошибки в связи модулей со структурой сайта
 */
$query = "
	SELECT 
		GROUP_CONCAT(DISTINCT '(', tb_structure.id, ')', tb_structure.name_".LANGUAGE_SITE_DEFAULT." ORDER BY tb_relation.priority ASC SEPARATOR ' / ') AS name,
		GROUP_CONCAT(DISTINCT tb_module.name) AS modules,
		COUNT(DISTINCT tb_module.id) AS total
	FROM site_structure_relation AS tb_relation
	INNER JOIN site_structure AS tb_structure ON tb_structure.id=tb_relation.parent
	INNER JOIN cms_module_site_structure AS tb_module_relation ON tb_module_relation.structure_id=tb_relation.id
	INNER JOIN cms_module AS tb_module ON tb_module.id=tb_module_relation.module_id
	GROUP BY tb_relation.id
	HAVING total > 1
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	$TmplContent->set('show_site_error', true);
	$row['class'] = (!$index % 2) ? 'odd' : 'even';
	$row['name'] = preg_replace("/\([0-9]+\)/", '', $row['name']);
	$TmplContent->iterate("/row/", null, $row);
}


/**
 * Страницы, которые не привязаны к модулям
 */
$query = "
	select 
		tb_structure.id,
		tb_structure.structure_id,
		tb_structure.url
	from cms_structure as tb_structure
	left join cms_module as tb_module on tb_module.id=tb_structure.module_id
	where tb_module.id is null
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	$TmplContent->set('show_admin_wo_module', true);
	$row['class'] = (!($index % 2)) ? 'odd' : 'even';
	$TmplContent->iterate("/row/", null, $row);
}

function cms_filter($row) {
	$row['description'] = "<a href='./Info/?module_id=$row[id]'>$row[description]</a><br><span class=comment>Связан с модулями: ";
	$row['description'] .= empty($row['dependencies']) ? ' нет связей' : "<font color=black>$row[dependencies]</font>";
	$row['description'] .= "</span>";
	return $row;
}

$query = "
	SELECT 
		id, 
		name,
		description_".LANGUAGE_CURRENT." as description,
		(
			select group_concat(distinct t_module.name order by t_module.name separator ', ')
			from cms_module t_module
			inner join cms_module_dependency as t_relation on t_relation.dependency_id=t_module.id
			where
				t_relation.module_id = cms_module.id
		) as dependencies,
		if(obligatory=1, '<input type=checkbox checked disabled>', '<input type=checkbox disabled>') as obligatory
	FROM cms_module 
	ORDER BY name ASC
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('description', '50%');
$cmsTable->addColumn('obligatory', '10%', 'center');
echo $cmsTable->display();
unset($cmsTable);

?>