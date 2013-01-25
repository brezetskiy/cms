<?php

/**
 * �������� ����
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


/**
 * ��������� ������ OTP ������ 
 */
$otp_data = AuthOTP::isSessionActive();
if (empty($otp_data['user_id'])){
	$message = "������ ���������.";
	$_SESSION['ActionReturn']['error'][md5($message)] = $message;
	$_RESULT['javascript'] = "document.location.reload();"; 
	exit;
} 
 

/**
 * ������������, ��� �������� ������ OTP ������
 */
$user_id = $otp_data['user_id'];


/**
 * ���������
 */
$remember = globalVar($_REQUEST['remember'], 0);
$otp_is_reserve = globalVar($_REQUEST['otp_reserve'], 0);

$otp_code = globalVar($_REQUEST['otp_value'], '');
if(empty($otp_code)){
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "����������, ������� ��� �������"); 
	$_RESULT['javascript']  = "delta_loader_clear(); ";
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}



/**
 * �����
 */
if(Auth::isHacker() && $otp_is_reserve){   
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "��������� ���-�� ������� ������ ���. ����������� ������������� ��������� ����� �������������");  
	$_RESULT['javascript']  = "otp_reserve_ban(); "; 
	$_RESULT['javascript'] .= "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}


/**
 * �������� ���� ������� 
 */
$message = "��� ������� ������ �������";

$_SESSION['otp_admin_passed'] = AuthOTP::auth($user_id, $otp_code, $otp_is_reserve, $message);
if(empty($_SESSION['otp_admin_passed'])){
	if($otp_is_reserve) Auth::logLogin(0, time(), "ID:$user_id");       
	
	$_RESULT['otp_code_form_error_block'] = cms_message('User', $message); 
	$_RESULT['javascript']  = "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
} 	


/**
 * ���� �������� ��� ������� ��������, ������������ ������������
 */
$logged_in = Auth::login($user_id, $remember, null);
if (!$logged_in) {
	Auth::logLogin(0, time(), "ID:$user_id");
	
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "������ � IP ������������ ��� ��� ������� �������� ���������������"); 
	$_RESULT['javascript']  = "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}


/**
 * ������� ������ OTP �����������
 */
AuthOTP::sessionClear();   


/**
 * �� ������� ������������ - ������������� ���� �� ��� ������
 */
if($remember){
	AuthOTP::setAccess($user_id);
}


/**
 * ��������� �� �������� ����������� 
 */
$message = cms_message('User', "�����������, �� ������� ����������������");  
$_SESSION['ActionReturn']['success'][md5($message)] = $message;


$_RESULT['javascript'] = "document.location.reload();"; 
exit;
 

?>