<?php
/**
 * ������ ������������ �������� �� �� - ��������������� ������������ ��� ���
 * 
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ���� ������������ � ��������� e-mail ������� - ���������������, �� ��������� ��
 * � �� ����� � �������, �� ��������� �� ��������, �� ������� ��������� ������
 * ��������, �� ������� �� ����� ���� ��������.
 * 
 * ���� ������������ � ��������� e-mail ������� ��������������� �� �� �����,
 * �� ������������� ��� �� �������� ����� ������ � ������.
 * 
 * � ���� ������ ������������� ��� �� �������� � ���������, ��� �� �����
 * ������������� ���������������
 * 
 */

$email = globalVar($_REQUEST['email'], '');

// ��������� ������������ �������� e-mail ������
if (!preg_match(VALID_EMAIL, $email)) {
	Action::setError('����������� ������ e-mail �����');
	header("Location: /".LANGUAGE_URL."Maillist/?email=$email");
	exit;
}

// ���������, �� �������� �� ��� ������������ ���������������� ����
if (isset($_SESSION['auth']['email']) && $_SESSION['auth']['email'] == $email) {
	header("Location: /".LANGUAGE_URL."Maillist/");
	exit;
}

// ��� ���������� �������� ������������ ������� �� �������
Auth::logout();

// ���������, ��� �� ������������ � ����� e-mail ������� 
$query = "SELECT id FROM auth_user WHERE email='".addcslashes($email, "\'\\")."'";
$DB->query($query);
if ($DB->rows > 0) {
	Action::setError('��� �������� �� �������� ������� ���� ����� � ������');
	header("Location: /".LANGUAGE_URL."User/Login/?return_path=".urlencode("/".LANGUAGE_URL."Maillist/"));
	exit;
}


header("Location: /".LANGUAGE_URL."Maillist/?email=$email");
exit;

?>