<?php
/** 
 * Привелегии, которые назначены в модуле 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$module_id = globalVar($_GET['module_id'], 0);

function cms_filter($row) {
	$row['title'] = "<a href='./Rule/?action_id=$row[id]'>$row[title]</a>";
	return $row;
}

$query = "
	SELECT
		tb_action.id,
		tb_action.title_".LANGUAGE_CURRENT." AS title,
		if(is_default=1, '<input type=checkbox checked disabled>', '<input type=checkbox disabled>') as is_default,
		concat('<span class=comment>', group_concat(tb_group.name order by tb_group.name separator ', '), '</span>') as groups,
		tb_action.priority
	FROM auth_action AS tb_action
	LEFT JOIN auth_group_action as tb_relation on tb_relation.action_id=tb_action.id
	LEFT JOIN auth_group as tb_group on tb_group.id=tb_relation.group_id
	WHERE tb_action.module_id='$module_id'
	GROUP BY tb_action.id
	ORDER BY tb_action.priority
	
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('title', '30%');
$cmsTable->addColumn('groups', '40%', 'left', 'Роли с привилегией');
$cmsTable->addColumn('is_default', '10%', 'center');
echo $cmsTable->display();
unset($cmsTable);


// Таблицы, к которым есть доступ на просмотр
$query = "
	select distinct tb_change.table_id
	from auth_action_table_select as tb_change
	inner join auth_action as tb_action on tb_action.id=tb_change.action_id
	where tb_action.module_id='$module_id'
";
$checked_select = $DB->fetch_column($query, 'table_id', 'table_id');

// Таблицы, к которым есть доступ на редактирование
$query = "
	select distinct tb_change.table_id
	from auth_action_table_update as tb_change
	inner join auth_action as tb_action on tb_action.id=tb_change.action_id
	where tb_action.module_id='$module_id'
";
$checked_update = $DB->fetch_column($query, 'table_id', 'table_id');


// Определяем перечень таблиц
$query = "
	select
		tb_table.id,
		concat(tb_table.title_".LANGUAGE_CURRENT.", ' [', tb_table.name, ']') as name,
		tb_db.alias as db_alias
	from cms_table as tb_table
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	where 
		tb_table.module_id='$module_id'
		and tb_table.is_disabled=0
		and tb_table._table_type not in ('function', 'procedure', 'view')
	order by tb_db.alias, tb_table.name
";
$data = $DB->query($query);

$TmplContent->set('show_change', $DB->rows);
$prev_db = '';

reset($data); 
while (list(,$row) = each($data)) {
	$row['db_name'] = db_config_constant("name", $row['db_alias']); 
	 
	if ($prev_db == $row['db_name']) {
		$row['db_name'] = '';
	} else {
		$prev_db = $row['db_name'];
	}
	$row['checked_select'] = (isset($checked_select[ $row['id'] ])) ? 'checked' : '';
	$row['checked_update'] = (isset($checked_update[ $row['id'] ])) ? 'checked' : '';
	if (!isset($checked_select[ $row['id'] ]) || !isset($checked_update[ $row['id'] ])) {
		$row['name'] = "<font color=red>$row[name]</font>";
	}
	$TmplContent->iterate('/table/', null, $row);
}



// События к которым есть доступ
$query = "
	select distinct tb_event.event_id
	from auth_action_event as tb_event
	inner join auth_action as tb_action on tb_action.id=tb_event.action_id
	where tb_action.module_id='$module_id'
";
$checked = $DB->fetch_column($query, 'event_id', 'event_id');

// Определяем перечень событий
$query = "
	select
		tb_event.id,
		concat(tb_event.description_".LANGUAGE_CURRENT.", ' [', tb_event.name, ']') as name
	from cms_event as tb_event
	where tb_event.module_id = '$module_id'
	order by name
";
$data = $DB->query($query);
$TmplContent->set('show_event', $DB->rows);
reset($data); 
while (list(,$row) = each($data)) {
	$row['checked'] = (isset($checked[ $row['id'] ])) ? 'checked' : '';
	if (!isset($checked[ $row['id'] ])) {
		$row['name'] = "<font color=red>$row[name]</font>";
	}
	$TmplContent->iterate('/event/', null, $row);
}


// Определяем перечень разделов в админ части
$query = "
	select distinct
		tb_structure.url,
		tb_structure.id as id,
		tb_structure.id as real_id,
		tb_structure.structure_id as parent,
		concat('<input ', if(tb_view.action_id is not null, 'checked', ''), ' type=checkbox disabled name=view[] value=',tb_structure.id,' id=structure_',tb_structure.id,'><label for=structure_', tb_structure.id, ' ', if(tb_view.action_id is not null, '', 'style=\"color:red;\"'), '>',tb_structure.name_".LANGUAGE_CURRENT.", '</label>') as name
	from cms_structure as tb_structure
	left join auth_action_view as tb_view on tb_view.structure_id=tb_structure.id 
	left join auth_action as tb_action on tb_action.id=tb_view.action_id and tb_structure.module_id=tb_action.module_id
	where tb_structure.module_id='$module_id'
";
$data = $DB->query($query, 'id');
$TmplContent->set('show_view', $DB->rows);
$Tree = new Tree($data, $checked, 'list');
$TmplContent->set('view', $Tree->build());
reset($Tree->used);
while (list(,$row) = each($Tree->used)) {
	unset($data[$row]);
}

reset($data);
while (list(,$row) = each($data)) {
	$TmplContent->iterate('/structure/', null, $row);
}

?>