<?php
/**
* �������� � ���������
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/


/**
* ���������� ��������� ��� ��������� �������������������
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* ���������������� ����
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

/**
* ���������� ����������
* @ignore
*/
$id = globalVar($_GET['id'], 0);

$query = "
	SELECT 
		tb_log.table_name, 
		tb_log.field_name,
		tb_log.content,
		tb_log.edit_id,
		DATE_FORMAT(tb_log.dtime, '".LANGUAGE_DATE_SQL."') AS dtime,
		tb_user.login
	FROM cvs_log AS tb_log
	LEFT JOIN auth_user AS tb_user ON tb_user.id=tb_log.admin_id
	WHERE tb_log.id='$id'";
$info = $DB->query_row($query);

/**
* �������� ���� �������������� ������� �������������
*/
if (!Auth::editContent($info['table_name'], $info['edit_id'])) {
	echo cms_message('CMS', '� ��� ��� ���� �� �������� ��������� � ������� %s.', $info['table_name']);
	exit;
}

echo '<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
	<title>'.cms_message('CMS', '�������� ���������').' '.$info['login'].' '.$info['dtime'].'</title>
</BODY>
'.$info['content'].'
</BODY>
</HTML>';
?>