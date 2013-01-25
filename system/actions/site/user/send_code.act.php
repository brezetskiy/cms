<?php
/** 
 * �������� ������������ ������ � ������������� e-mail ������ 
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

if (!isset($id)) {
	// ���� ������ ���������� ��� ����������� ������������, ��� �������� id ��� ��������
	$id = globalVar($_GET['id'], 0);
}

$query = "SELECT * FROM auth_user WHERE id='$id'";
$user = $DB->query_row($query);

if ($DB->rows == 0) {
	// ������������ � ����� ������� - �� ����������.
	Action::onError(cms_message('CMS', '������������ � ��������� ������� - �� ����������.'));
}

if ($user['confirmed'] == 'true') {
	// ������� ��� �����������
	Action::onError(cms_message('CMS', '������� ��� �����������. ������ � ��������� �������� �� ���� �������.'));
}

$Template = new TemplateDB('cms_mail_template', 'User', 'confirm');
$Template->set('email', $user['email']);
$Template->set('confirm_code', $user['confirmation_code']);
$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '����������� �� %s: ������������� e-mail', CMS_HOST), $Template->display());

$Sendmail->send($user['email']);

Action::setSuccess(cms_message('CMS', '�� ��������� ���� ����� E-mail ���� ������� ������ � �������� ������������� �����������. ��� ������� ����� ����������� ����� �������� �� ������, ��������� � ������.'));

?>