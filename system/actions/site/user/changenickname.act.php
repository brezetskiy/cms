<?php
/**
 * �������� ������ ������������
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$passwd = trim(globalVar($_POST['passwd'], ''));
$nickname = trim(globalVar($_POST['nickname'], ''));

$user_id = Auth::isLoggedIn();

if (empty($nickname)) {
	Action::onError(cms_message('CMS', '�� ������ ���'));
} elseif (!preg_match(VALID_LOGIN, $nickname)) {
	Action::onError(cms_message('CMS', '����������� ������ ���, ����� ������������ ������ ��������� �����, �����, ���� ������������� � ������� .+!@#$%^~()-'));
} 


$DB->result("SELECT id FROM auth_user WHERE nickname = '$nickname' AND id != '$user_id'");
if($DB->rows > 0){
	Action::onError(cms_message('CMS', '��������� ��� ��� ��������� �� ������ �������������'));
}

$DB->query("LOCK TABLES auth_user WRITE");

// ��������� ������������ �������� ������
$DB->query("SELECT id FROM auth_user WHERE id='$user_id' AND passwd='".md5($passwd)."'");
if ($DB->rows == 0) {
	// ����������� ������ ������
	Action::onError(cms_message('CMS', '����������� ������ ������'));
}

// ���������� ����
$DB->update("
	UPDATE auth_user SET nickname='$nickname' 
	WHERE id='$user_id' AND passwd='".md5($passwd)."'
");

$DB->query("UNLOCK TABLES");
Action::setSuccess(cms_message('CMS', '��� ������� ������'));


?>