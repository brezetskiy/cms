<?php
/**
 * ������� ������������ �� ���� ��������
 * 
 * ��� ������� ���������� ������ �� ���� ������. ����� ������������ ������� �� ������
 * "���������� �� ��������".
 * 
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$email = globalVar($_GET['email'], '');

$query = "select id from auth_user where email='$email'";
$user_id = $DB->result($query);
if ($DB->rows == 0) {
	echo cms_message('Maillist', '������������ � ��������� e-mail ������� �� ������.');
	exit;
}

$query = "delete from maillist_user_category where user_id='$user_id'";
$DB->delete($query);
echo cms_message('Maillist', '�� ������� ���� �������� �� %d ��������.', $DB->affected_rows);
exit;


?>