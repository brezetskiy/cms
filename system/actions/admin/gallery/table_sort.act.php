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

$sort = globalVar($_REQUEST['priority_list'], array());
$table_id = globalVar($_REQUEST['table_id'], 0);
$start = globalVar($_REQUEST['_start_row'], 0); // ����� ���� � �������� ���������� ����������

// ���������� � �������
$table = $DB->query_row("
	SELECT 
		tb_table.db_id, 
		tb_table.name, 
		tb_table.title_".LANGUAGE_CURRENT." AS title,
		tb_db.alias as db_alias 
	FROM cms_table as tb_table
	INNER JOIN cms_db as tb_db ON tb_db.id = tb_table.db_id
	WHERE id='".$table_id."'
");

if ($DB->rows == 0) {
	echo cms_message('CMS', '�� ������� �������, � ������� ���������� ��������� ����������');
	exit;
}

// ��������� ����� �� ��������� �������
if (!Auth::updateTable($table_id)) {
	echo cms_message('CMS', '� ��� ��� ���� �� ���������� �������� � ������� %s', $table['title']);
	exit;
}

// ������ ����������
$DBServer = DB::factory($table['db_alias']);
 
$query = "
	UPDATE ".$table['name']." 
	SET priority=FIND_IN_SET(id, '".implode(",", $sort)."')+".$start." 
	WHERE id IN (0".implode(",", $sort).")
";
$DBServer->update($query);

exit;
?>