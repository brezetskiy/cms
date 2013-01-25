<?php
/**
 * ��������� ��������� �������
 * @package Pilot
 * @subpackage FAQ
 * @author Rudenko Ilya <rudenko@id.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$name 		= globalVar($_POST['name'], '');
$email 		= globalVar($_POST['email'], '');
$question 	= globalVar($_POST['question'], '');

/**
 * ��������� CAPTCHA
 */
if (CMS_USE_CAPTCHA && !Auth::isLoggedIn() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', '����������� ������� ����� �� ��������'));
}

/**
 * ��������� ������������ ���������� ������
 */
if (!Auth::isLoggedIn()) {
	if (empty($email)) {
		Action::onError(cms_message('CMS', '�� ������ ����� e-mail'));
	} elseif (!preg_match(VALID_EMAIL, $_POST['email'])) {
		Action::onError(cms_message('CMS', '�� ������ ����� e-mail'));
	} elseif (empty($question)) {
		Action::onError(cms_message('FAQ', '�� �� ����� ����� ���������'));
	} elseif (empty($name)) {
		Action::onError(cms_message('FAQ', '�� �� ������� ������ �����'));
	}
} else {
	if (empty($question)) {
		Action::onError(cms_message('FAQ', '�� �� ����� ����� ���������'));
	}
}

/**
 * �������� ���������
 */
$Template = new TemplateDB('cms_mail_template', 'FAQ', 'question');
$_POST['name'] = stripslashes($_POST['name']);
$_POST['question'] = stripslashes($_POST['question']);
$Template->set($_POST);
if (Auth::isLoggedIn()) { 
	$Template->set($_SESSION['auth']);
}


$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('FAQ', '����� ���������� ������'), $Template->display());
$Sendmail->send(CMS_NOTIFY_EMAIL);

Action::setSuccess(cms_message('FAQ', '�������, ��� ������ ������'));

?>