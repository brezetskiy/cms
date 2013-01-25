<?php
/**
* ���������� ����� � ��������, � ������� ��� �������� 
* ���������� �� ����� ��������
* @package Pilot
* @subpackage Actions_Admin
* @version 3.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/
$sort = globalVar($_REQUEST['id'], array());
$start = globalVar($_REQUEST['_start_row'], 0);

// ���������� � �������
$table = cmsTable::getInfoById($table_id);
if (empty($table)) {
	Action::onError(cms_message('CMS', '�� ������� �������, � ������� ���������� ��������� ����������'));
}

// ��������� ����� �� ��������� �������
if (!Auth::updateTable($table_id)) {
	Action::onError(cms_message('CMS', '� ��� ��� ���� �� ���������� �������� � ������� %s', $table['table_name']));
}

// ������ ����������
$DBServer = DB::factory($table['db_alias']);

if (is_file(TRIGGERS_ROOT.$table['triggers_dir'].'sort_before.act.php')) {
	require_once(TRIGGERS_ROOT.$table['triggers_dir'].'sort_before.act.php');
}

$query = "
	UPDATE `$table[table_name]`
	SET priority=FIND_IN_SET(id, '".implode(",", $sort)."')+$start
	WHERE id IN (0".implode(",", $sort).")
";
$DBServer->update($query);

if (is_file(TRIGGERS_ROOT.$table['triggers_dir'].'sort_after.act.php')) {
	require_once(TRIGGERS_ROOT.$table['triggers_dir'].'sort_after.act.php');
}

Action::setSuccess(':)');

exit;
?>