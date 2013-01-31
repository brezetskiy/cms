<?php

/** 
 * Устанавливает парамерты доступа для определённого правила
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$action_id = globalVar($_REQUEST['action_id'], 0);
if (empty($action_id)) {
	header("Location:../");
	exit;
}


// Определяем название правила
$info = $DB->query_row("
	select 
		tb_action.title_".LANGUAGE_CURRENT." as action,
		tb_module.id as module_id,
		tb_module.name as module,
		tb_action.id as action_id
	from auth_action as tb_action
	inner join cms_module as tb_module on tb_module.id=tb_action.module_id
	where tb_action.id='$action_id'
");

$TmplContent->set($info);



// Таблицы, к которым есть доступ
$checked_update = $DB->fetch_column("select table_id from auth_action_table_update where action_id='$action_id'", 'table_id', 'table_id');
$checked_select = $DB->fetch_column("select table_id from auth_action_table_select where action_id='$action_id'", 'table_id', 'table_id');


// Определяем перечень таблиц
$data = $DB->query("
	select
		tb_table.id,
		concat(tb_table.title_".LANGUAGE_CURRENT.", ' [', tb_table.name, ']') as name,
		tb_db.alias as db_alias,
		group_concat(distinct if(tb_select_action.id='$action_id', concat('<font color=green>', tb_select_action.title_".LANGUAGE_CURRENT.", '</font>'), tb_select_action.title_".LANGUAGE_CURRENT.")  order by tb_select_action.title_".LANGUAGE_CURRENT." separator ', ') as select_actions,
		group_concat(distinct if(tb_update_action.id='$action_id', concat('<font color=green>', tb_update_action.title_".LANGUAGE_CURRENT.", '</font>'), tb_update_action.title_".LANGUAGE_CURRENT.") order by tb_update_action.title_".LANGUAGE_CURRENT." separator ', ') as update_actions
	from cms_table as tb_table
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	left join auth_action_table_select as tb_select on tb_select.table_id=tb_table.id
	left join auth_action as tb_select_action on tb_select_action.id=tb_select.action_id
	left join auth_action_table_update as tb_update on tb_update.table_id=tb_table.id
	left join auth_action as tb_update_action on tb_update_action.id=tb_update.action_id
	where tb_table.module_id='$info[module_id]'
		and tb_table.is_disabled=0
		and tb_table._table_type not in ('procedure', 'function')
	group by tb_table.id
	order by tb_db.alias, tb_table.name
");

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
	
	if (empty($row['select_actions']) || empty($row['update_actions'])) {
		$row['name'] = "<font color=red>$row[name]</font>";
	}
	
	$TmplContent->iterate('/table/', null, $row);
}











// События к которым есть доступ
$checked = $DB->fetch_column("select event_id from auth_action_event where action_id='$action_id'", 'event_id', 'event_id');

// Определяем перечень событий
$query = "
	select
		tb_event.id,
		concat(tb_event.description_".LANGUAGE_CURRENT.", ' [', tb_event.name, ']') as name,
		group_concat(distinct if(tb_action.id='$action_id', concat('<font color=green>', tb_action.title_".LANGUAGE_CURRENT.", '</font>'), tb_action.title_".LANGUAGE_CURRENT.") order by tb_action.title_".LANGUAGE_CURRENT." separator ', ') as actions
	from cms_event as tb_event
	left join auth_action_event as tb_relation on tb_event.id=tb_relation.event_id
	left join auth_action as tb_action on tb_action.id=tb_relation.action_id
	where tb_event.module_id = '$info[module_id]'
	group by tb_event.id
	order by tb_event.name
";
$data = $DB->query($query);
$TmplContent->set('show_event', $DB->rows);
reset($data); 
while (list(,$row) = each($data)) {
	$row['checked'] = (isset($checked[ $row['id'] ])) ? 'checked' : '';
//	if (!isset($checked[ $row['id'] ])) {
	if (empty($row['actions'])) {
		$row['name'] = "<font color=red>$row[name]</font>";
	}
	$TmplContent->iterate('/event/', null, $row);
}

  

$checked = $DB->fetch_column("select structure_id, structure_id as id from auth_action_view where action_id='$action_id'");

// Определяем перечень разделов в админ части
$query = "
	select 
		tb_structure.url,
		tb_structure.id,
		tb_structure.id as real_id,
		tb_structure.structure_id as parent,
		tb_view.action_id,
		tb_structure.name_".LANGUAGE_CURRENT." as name,
		group_concat(distinct if(tb_action.id='$action_id', concat('<font color=green>', tb_action.title_".LANGUAGE_CURRENT.", '</font>'), tb_action.title_".LANGUAGE_CURRENT.") order by tb_action.title_".LANGUAGE_CURRENT." separator ', ') as actions
	from cms_structure as tb_structure
	left join auth_action_view as tb_view on tb_view.structure_id=tb_structure.id
	left join auth_action as tb_action on tb_action.id=tb_view.action_id
	where tb_structure.module_id='$info[module_id]'
	group by tb_structure.id
";
$data = $DB->query($query, 'id');
reset($data);
while(list($index,$row) = each($data)) {
	$state = (isset($checked[ $row['id'] ])) ? 'checked' : '';
	$row['name'] = "<input $state type=checkbox name=view[] value=$row[id] id=structure_$row[id]><label for=structure_$row[id]>$row[name]</label>";
	if (empty($row['actions'])) {
		$data[$index]['name'] = "<font color=red>$row[name]</font>";
	} else {
		$data[$index]['name'] = "$row[name]<br><span class=comment>$row[actions]</span>";
	}
}
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