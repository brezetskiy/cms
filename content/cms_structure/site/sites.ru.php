<?php
/**
* Сайты системы
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2008, Delta-X ltd.
*/

$query = "
	SELECT 
		tb_site.id,
		if(tb_site.active, tb_site.url, concat('<span style=\"text-decoration:line-through;\">', tb_site.url, '</span>')) as url,
		REPLACE(tb_site.aliases, '\n', '<br>') as aliases,
		tb_group.name as auth_group_id,
		tb_site.priority
	FROM site_structure_site as tb_site
	LEFT JOIN site_auth_group as tb_group on tb_group.id=tb_site.auth_group_id
	ORDER BY tb_site.active desc, tb_site.priority
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addEvent('update', '/action/admin/cms/site_update/', 1, 0, 0, '/design/cms/img/event/table/copy.gif', '/design/cms/img/event/table/copy_over.gif', 'Обновить данные', null);
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->addColumn('url', '30%', 'left', 'Основной адрес');
$cmsTable->addColumn('aliases', '30%');
$cmsTable->addColumn('auth_group_id', '30%');
echo $cmsTable->display();
unset($cmsTable);