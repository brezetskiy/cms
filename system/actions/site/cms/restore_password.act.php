<?php
/** 
 * �������������� ������ ������������ 
 * @package Pilot 
 * @subpackage User 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$user_id = globalVar($_POST['user_id'], 0);
$auth_code = globalVar($_POST['auth_code'], '');
$new_passwd = globalVar($_POST['new_passwd'], '');
$new_passwd_confirm = globalVar($_POST['new_passwd_confirm'], '');

if (empty($new_passwd)) {
	// �� ������ ������
	Action::onError(cms_message('CMS', '�� ������ ������'));
} elseif (!preg_match(VALID_PASSWD, $new_passwd)) { 
	// ����� ������ �������� ������������ �������	
	Action::onError(cms_message('CMS', '����������� ������ ������, ����� ������������ ������ ��������� �����, �����, ���� ������������� � ������� +!@#$%^&*~()-'));
} elseif ($new_passwd != $new_passwd_confirm) {
	// ��������� ������ - �� ����������
	Action::onError(cms_message('CMS', '��������� ������ - �� ���������'));
}

/**
 * ��������� CAPTCHA
 */
if (!Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', '����������� ������� ����� �� ��������'));
}

if (rand(0,1000)>900) {
	$DB->delete("delete from auth_user_amnesia where dtime < now() - interval 3 day");
}

$DB->query_row("
	select * from auth_user_amnesia
	where auth_code = '$auth_code' 
		and user_id = '$user_id' 
		and dtime > now() - interval 3 day
");

if ($DB->rows == 0) {
	Action::onError(cms_message('cms', '����������� ������ ��� �����������. �������� ����������� ������ ��� ��� � ��������� �� ������, ��������� � ������'));
}

$DB->update("update auth_user set passwd = '".md5($new_passwd)."' where id = '$user_id'");
$DB->delete("delete from auth_user_amnesia where user_id = '$user_id'");

Action::setSuccess('��� ������ �������. ������ �� ������ ����� �� ����, ��������� ����� ������');


?>