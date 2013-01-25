<?php
/** 
 * �������� �����
 * @package Pilot
 * @subpackage User
 * @author Marvaniuk Roman 
 * @copyright Delta-X, ltd. 2010
 */

$fio 	  = globalVar($_POST['fio'], "");
$phone    = globalVar($_POST['phone'], "");
$email    = globalVar($_POST['email'], "");
$tour    = globalVar($_POST['tour'], "");
$dop_info = globalVar($_POST['dop_info'], ""); 

if(empty($fio)){
	$_RESULT['layer_errors'] = " ";
	$_RESULT['layer_errors'] = "������: ��������� ��� �����������";
	exit;
}
if(empty($email)){
	$_RESULT['layer_errors'] = " ";
	$_RESULT['layer_errors'] = "������: ������ E-mail";
	exit;
}
if(preg_match(VALID_EMAIL,$email)==0){
	$_RESULT['layer_errors'] = " ";
	$_RESULT['layer_errors'] = "������: ����������� E-mail";
	exit;	
}
if(empty($tour)){
	$_RESULT['layer_errors'] = " ";
	$_RESULT['layer_errors'] = "������: ������� ��������� ������ ���������.";
	exit;
}

/**
 * ��������� CAPTCHA
 */
/*
if (!Auth::isLoggedIn() && CMS_USE_CAPTCHA && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', '������: ����������� ������� ����� �� ��������.'));
}
*/

$Template = new TemplateDB('cms_mail_template', 'User', 'order'); 
$Template->set('fio', $fio);
$Template->set('phone', $phone);
$Template->set('email', $email);
$Template->set('tour', $email);
$Template->set('dop_info', $dop_info);

$emails = explode(",", CMS_NOTIFY_EMAIL);
$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', 'Alloha: �������� neh.', CMS_HOST), $Template->display());

reset($emails);
while(list(, $email) = each($emails)){
	$Sendmail->send($email, true);   
}
$_RESULT['layer_errors'] = " ";
$_RESULT['layer_ok'] = "��������� ����������.";

?> 