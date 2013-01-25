<?php
/**
 * ѕеречень страниц заблокированных редактором
 * @package Pilot
 * @subpackage CVS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

function cms_prefilter($row) {
	global $DB;
	
	$query = "select fk_show_name from cms_table_static where table_name='$row[table_name]' limit 1";
	$fk_show_name = $DB->result($query);
	
	$query = "select `$fk_show_name` from `$row[table_name]` where id='$row[edit_id]'";
	$row['edit_id'] = $DB->result($query);
	
	return $row;
}

$query = "
	select
		tb_lock.id,
		tb_lock.table_name,
		concat(tb_lock.table_name, '.', tb_lock.field_name) as field_name,
		tb_lock.edit_id,
		tb_user.login as admin_id,
		date_format(tb_lock.dtime, '".LANGUAGE_DATE_SQL." %H:%i') as dtime
	from cvs_lock as tb_lock
	left join auth_user as tb_user on tb_user.id=tb_lock.admin_id
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('field_name', '10%');
$cmsTable->addColumn('edit_id', '50%', 'left');
$cmsTable->addColumn('admin_id', '10%', 'left');
$cmsTable->addColumn('dtime', '10%');
echo $cmsTable->display();
unset($cmsTable);

?>