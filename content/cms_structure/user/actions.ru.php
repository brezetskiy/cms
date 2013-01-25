<?php
/**
 * —писок событий в системе
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

// ≈сли таблицу прописать сначала в одном модуле, а потом изменить еЄ принадлежность модулю, 
// то она пропадет из списка ошибок, но и во втором модуле прив€зана не будет,
// надо убирать такие св€зи
remove_wrong_tables('select');
remove_wrong_tables('update');

$query = "
	CREATE TEMPORARY TABLE tmp_error
	(
		/* ќпредел€ем модули, в которых есть непроставленные права доступа к просмотру таблиц */
		SELECT tb_table.module_id
		FROM cms_table tb_table
		LEFT JOIN auth_action_table_select tb_relation ON (tb_table.id = tb_relation.table_id)
		WHERE 
			tb_table.is_disabled = 0 
			AND tb_relation.action_id is null
			and tb_table._table_type not in ('function', 'procedure', 'view')
		GROUP BY tb_table.module_id
	) UNION (
		/* ќпредел€ем модули, в которых есть непроставленные права доступа к изменению таблиц */
		SELECT tb_table.module_id
		FROM cms_table tb_table
		LEFT JOIN auth_action_table_update tb_relation ON (tb_table.id = tb_relation.table_id)
		WHERE 
			tb_table.is_disabled = 0 
			AND tb_relation.action_id is null
			and tb_table._table_type not in ('function', 'procedure', 'view')
		GROUP BY tb_table.module_id
	) UNION (
		/* ћодули, в которых есть неприв€занные событи€ */
		SELECT tb_event.module_id
		FROM cms_event tb_event
		LEFT JOIN auth_action_event AS tb_relation ON (tb_event.id = tb_relation.event_id)
		WHERE tb_relation.event_id is null
		GROUP BY tb_event.module_id
	) UNION (
		/* ћодули, в которых не прив€заны разделы админинтерфейса */
		select distinct tb_structure.module_id
		from cms_structure as tb_structure
		left join auth_action_view as tb_view on tb_view.structure_id=tb_structure.id 
		left join auth_action as tb_action on tb_action.id=tb_view.action_id and tb_structure.module_id=tb_action.module_id
		where tb_action.id is null
	)
";
$DB->insert($query);

function cms_filter($row) {
	$row['description'] = "<a href='./Module/?module_id=$row[id]'>$row[description]</a>";
	return $row;
}

$query = "
	select 
		tb_module.id,
		if(tmp_error.module_id is null, tb_module.name, concat('<span style=\"color:red;font-weight:bold;\">', tb_module.name, '</span>')) as name,
		tb_module.description_".LANGUAGE_CURRENT." AS description,
		(select if(count(*)=0, '-', count(*)) from auth_action where module_id=tb_module.id) as count
	from cms_module as tb_module
	left join tmp_error on tb_module.id=tmp_error.module_id
	group by tb_module.id
	order by tb_module.name asc
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('name', '30%', 'left', 'ћодуль');
$cmsTable->addColumn('description', '60%', 'left', 'ќписание');
$cmsTable->addColumn('count', '10%', 'right', 'ѕривелегий');
echo $cmsTable->display();
unset($cmsTable);


function remove_wrong_tables($type) {
	global $DB;
	$query = "
		select tb_update.*
		from auth_action_table_$type as tb_update
		inner join cms_table as tb_table on tb_table.id=tb_update.table_id
		inner join auth_action as tb_action on tb_action.id=tb_update.action_id
		where tb_table.module_id != tb_action.module_id
	";
	$data = $DB->query($query);
	reset($data);
	while (list(,$row) = each($data)) {
		$query = "delete from auth_action_table_$type where table_id='$row[table_id]' and action_id='$row[action_id]'";
		$DB->delete($query);
	}
}
?>
<div class="context_help"><span style="color:red;font-weight:bold;"> расным цветом</span> выделены модули, в которых не распределены привилегии</div>
