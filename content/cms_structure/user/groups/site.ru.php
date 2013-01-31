<?php
/**
 * Распределение доступа группам к сайту
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
$group_id = globalVar($_REQUEST['group_id'], 0);

$query = "select name from auth_group where id='$group_id'";
$group = $DB->result($query);
$TmplContent->set('group', $group);
$TmplContent->set('group_id', $group_id);


// Определяем перечень разделов в админ части
$query = "
	select 
		tb_structure.id as id,
		tb_structure.id as real_id,
		tb_structure.structure_id as parent,
		concat('<input ', if(tb_view.group_id is not null, 'checked', ''), ' type=checkbox name=structure_id[] value=',tb_structure.id,' id=structure_',tb_structure.id,'><label for=structure_', tb_structure.id, '>',tb_structure.name_".LANGUAGE_CURRENT.", '</label>') as name
	from site_structure as tb_structure
	left join auth_group_structure as tb_view on tb_view.structure_id=tb_structure.id and tb_view.group_id='$group_id'
";
$data = $DB->query($query, 'id');
$TmplContent->set('show_view', $DB->rows);
$Tree = new Tree($data, array(), 'list');
$TmplContent->set('structure', $Tree->build());

?>