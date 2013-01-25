<?php 

/**
 * ���������� OTP ������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * �������� �����������
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError(cms_message('User', "����������, ���������������"));
}


/**
 * ����������� ������
 */
$code = globalVar($_REQUEST['code'], '');
$is_reserve = globalVar($_REQUEST['reserve_code'], '');
$is_force = globalVar($_REQUEST['is_force'], 0); 


/**
 * ����� 
 */   
if(Auth::isHacker(true) && $is_reserve){    
	Action::onError(cms_message('User', "��������� ���-�� ������� ������ ���. ����������� ������������� ��������� ����� �������������"));
}



/**
 * ���������, ��������� �� � �������� OTP ������������
 */
$otp_data = $DB->query_row("SELECT otp_type as type, otp_enable as is_enabled FROM auth_user WHERE id = '$user_id'"); 


/**
 * �������� ���� ������� 
 */ 
if(empty($code)){
	Action::onError(cms_message('User', "����������, ������� ��� �������������"));
}

$message = "��� ������ �� �����. ����������, ��������� ��������� ������ � ��������� �������";

if($otp_data['type'] == 'sms' && empty($is_reserve)){    
	if(!AuthOTP::authSms($code, 'otp_delete', $user_id, $message)){   
		Action::onError(cms_message('User', $message));		
	}
} else {
	if(!AuthOTP::auth($user_id, $code, $is_reserve, $message)) {
		if($is_reserve) Auth::logLogin(0, time(), "ID:$user_id");    
		Action::onError(cms_message('User', $message));		
	}
}


/**
 * ���� ��� �������� �������� - ��������� OTP ������
 */
AuthOTP::disable();

if(!empty($_SESSION['otp_disable_form'])) unset($_SESSION['otp_disable_form']);
if(!empty($_SESSION['otp_step'])) unset($_SESSION['otp_step']);
if(!empty($_SESSION['otp_type'])) unset($_SESSION['otp_type']);


/**
 * ���� ������� ����, ����� ����� ������� � ��� ���������
 */
if($is_force){  
	require_once(ACTIONS_ROOT."site/user/otp/config_clean.act.php"); 
}


?>