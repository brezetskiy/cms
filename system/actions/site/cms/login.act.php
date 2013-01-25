<?php

/** 
 * ����������� ������������ ����������������� ���������� 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008 
 */


/**
 * ���� �����������
 */ 
$_SESSION['auth_user_mode'] = 'login';

 
/**
 * �������� ������
 */
$login 	  = trim(globalVar($_POST['login'], ''));
$passwd   = trim(globalVar($_POST['passwd'], ''));
$remember = globalVar($_POST['remember'], 0);
 

/**
 * ����, ��� ���������� - ������ ��� ������ �� ������� ����� ��� �� ������� ���������������� ����
 */
$source = globalVar($_POST['source'], 'site');


/**
 * ��������� ������������ ���������� ������
 */
if (!preg_match(VALID_EMAIL, $login)) {
	Action::onError(cms_message('CMS', '������� ������ e-mail.'));
} 


/**
 * ���� ������������ ��� �����������, �� ������� ������������ ������
 */
if (isset($_SESSION['auth']['id']) && !empty($_SESSION['auth']['id'])){
	unset($_SESSION['auth']);
}


/**
 * ��������� CAPTCHA, ���� � ��� ������ �����
 */
if (Auth::isHacker() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', '����������� ������� ����� �� ��������'));
}

 
/**
 * ������ � �������
 */
$user = $DB->query_row("
	SELECT id, passwd, otp_enable, otp_cnt, otp_type 
	FROM auth_user 
	WHERE login='$login' or email='$login'
");

$user_id = (!empty($user['id'])) ? $user['id'] : 0;
 

/**
 * ������������ �� ������
 */
if (empty($user_id)) {
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', '������������ � ��������� e-mail �� ����������.')); 
}
 

/**
 * ������������ ������, �� �� ����� ������ ������
 */
if($user['passwd'] != md5($passwd)){
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', '����������� ������ ������ ��� ������������ '.$login));
}

 
/**
 * ������� ����������� �� ������� ���������������� ����.
 * ���� �������� ������������ OTP �����������, �������� ������ OTP-������
 */
if(AUTH_OTP_ADMIN_ENABLE && $source == 'admin' && !AuthOTP::checkAccess($user_id)){
	AuthOTP::sessionActivate($user_id, $source); 
	Action::finish();
}


/**
 * ������� ����������� �� ������� �����.
 * ���� ��� ������������ �������� OTP �����������, �������� ������ OTP-������
 */ 
if(!empty($user['otp_enable']) && !AuthOTP::checkAccess($user['id'])){
	AuthOTP::sessionActivate($user_id, $source); 
	Action::finish();
}

	
/**
 * ������� �����������
 */
$logged_in = Auth::login($user['id'], $remember, null);
if (!$logged_in) {
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', '������ � IP ������������ ��� ��� ������� �������� ���������������'));
}  
  
 
//Action::setSuccess(cms_message('User', "�����������, �� ������� ����������������"));


?>