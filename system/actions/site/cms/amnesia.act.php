<?php
/**
 * ����� ����������� �������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$email = globalVar($_POST['email'], '');

// ��������� ������������ ���������� ������
if (empty($email)) {
	// �� ������ ����� ��� e-mail �����
	Action::onError(cms_message('CMS', '�� ������ ����� e-mail'));
}

// ��������� CAPTCHA
if (!Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', '����������� ������� ����� �� ��������'));
}

// ��������� ������������
$query = "SELECT id, login, email FROM auth_user WHERE email='$email' or login='$email'";
$data = $DB->query_row($query);
if ($DB->rows == 0) {
	Action::onError(cms_message('CMS', '���������� ����� ������������ � ��������� �������'));
} elseif ($DB->rows != 1) {
	Action::onError(cms_message('CMS', '������� ����� ������ ������������ �� ����������� ���������'));
}

$auth_code = Misc::keyBlock(32, 1);

$query = "
	replace into auth_user_amnesia
	set
		user_id = '$data[id]',
		auth_code = '$auth_code',
		dtime = now()
";
$DB->insert($query);

// �������� ��� ��� �������������� ������ �� �����
$Template = new TemplateDB('cms_mail_template', 'cms', 'amnesia'); 
$Template->set($data);
$Template->set('auth_code', $auth_code);

//mail('brezetskiy.sergiy@gmail.com', 'subject', $Template->display());

$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '�������������� ������ - '.CMS_HOST), $Template->display());
$Sendmail->send($data['email'], true);

// ������ ��� ������� ��������� ��� �� e-mail �����
Action::setSuccess(cms_message('CMS', '�� ��������� ���� e-mail ���������� ���������� �� �������������� ������'));

?>