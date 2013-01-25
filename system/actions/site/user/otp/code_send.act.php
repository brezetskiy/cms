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
	$message = cms_message('User', "������ ���������.");
	$_SESSION['ActionReturn']['error'][md5($message)] = $message;
	$_RESULT['javascript'] = "document.location.reload();"; 
	exit;
} 
 

/**
 * ��������, �������� ������������ �������� ��������� ���
 */
$is_reserve = globalVar($_REQUEST['is_reserve']);


/**
 * ������������, ��� �������� ������ OTP ������
 */
$user_id = $otp_data['user_id'];
  

/**
 * ���� ������������ �� ���������� ��������� ����, ���������� ���
 */
if(empty($is_reserve)){
	
	/**
	 * ���������� �����, �� ������� ����� ��������� ��� � ����� ������� 
	 */
	$phone_id = globalVar($_REQUEST['phone'], 0);
	if (empty($phone_id)){
		$_RESULT['otp_sms_phone_form_error_block'] = "����������, ������� ����� ��������, �� ������� ����� ��������� ��� �������";
		$_RESULT['javascript']  = "delta_loader_clear(); "; 
		$_RESULT['javascript'] .= "$('#otp_sms_phone_form_error_block').show();";
		exit;
	} 
	
	 
	/** 
	 * �������� ���� �������
	 */
	$message = '';
	if(!AuthOTP::createSmsCode($phone_id, $user_id, $message)){ 
		$_RESULT['otp_sms_phone_form_error_block'] = cms_message('User', $message); 
		$_RESULT['javascript']  = "delta_loader_clear(); "; 
		$_RESULT['javascript'] .= "$('#otp_sms_phone_form_error_block').show();"; 
		exit;
	}
}

   
$_RESULT['javascript']  = "delta_loader_clear(); ";
$_RESULT['javascript'] .= "delta_action('otp_code_check()', '".AuthOTP::displayCodeForm($user_id, $is_reserve)."', 'otp_session_clear()'); ";      
exit;
 

?>