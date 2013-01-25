<?php
/**
 * �������� ������ ������������
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$user_id = Auth::isLoggedIn();

$passwd = trim(globalVar($_POST['passwd'], ''));
$new_passwd = trim(globalVar($_POST['new_passwd'], ''));
$new_passwd_confirm = trim(globalVar($_POST['new_passwd_confirm'], ''));

if (empty($new_passwd)) {
	// �� ������ ������
	Action::onError(cms_message('CMS', '�� ������ ������'));
} elseif (!preg_match(VALID_PASSWD, $passwd)) {
	// ������� ������ �������� ������������ �������
	Action::onError(cms_message('CMS', '����������� ������ �������� ������, ����� ������������ ������ ��������� �����, �����, ���� ������������� � ������� +!@#$%^&*~()-'));
} elseif (!preg_match(VALID_PASSWD, $new_passwd)) { 
	// ����� ������ �������� ������������ �������	
	Action::onError(cms_message('CMS', '����������� ������ ������, ����� ������������ ������ ��������� �����, �����, ���� ������������� � ������� +!@#$%^&*~()-'));
} elseif ($new_passwd != $new_passwd_confirm) {
	// ��������� ������ - �� ����������
	Action::onError(cms_message('CMS', '��������� ������ - �� ���������'));
}


$DB->query("LOCK TABLES auth_user WRITE");

/**
 * ��������� ������������ �������� ������
 */
$DB->query("SELECT id FROM auth_user WHERE id='$user_id' AND passwd='".md5($passwd)."'");
if ($DB->rows != 1) {
	// ����������� ������ ������
	Action::onError(cms_message('CMS', '����������� ������ ������'));
}

/**
 * ���������� ���������� � ������������
 */
$DB->update("UPDATE auth_user SET passwd='".md5($new_passwd)."' WHERE id='$user_id' AND passwd='".md5($passwd)."'");
$DB->query("UNLOCK TABLES");

/**
 * ������ ������� �������
 */
Action::setSuccess(cms_message('CMS', '������ ��� ������� ������'));


?>