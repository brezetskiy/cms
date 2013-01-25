<?php

/**
 * ������� ���������� OTP ������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

/**
 * �������� ������������
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError("�� �� ��������������");
}

$is_enabled = $DB->result("SELECT otp_enable FROM auth_user WHERE id = '$user_id'");
if(!empty($is_enabled)) {
	Action::onError("������, ��� �������� ���������, ���������� <b>���������</b> ����� ����������� ��������");
}


/**
 * ������� �� ������ ������ ����� �������
 */
$otp_type = globalVar($_REQUEST['otp_type'], '');
if(!empty($otp_type)) {
	$mobile_types = array('mobile', 'android', 'iphone', 'java');
	if(in_array($otp_type, $mobile_types)) $otp_type = "mobile";
	
	$_SESSION['otp_step'] = 2;
	$_SESSION['otp_type'] = $otp_type;
}


/**
 * ������� ���������
 */
$DB->update("
	UPDATE auth_user 
	SET otp_enable = '0', 
		otp_type = NULL, 
		otp_cnt = NULL, 
		otp_sign = NULL 
	WHERE id = '$user_id'
");
 
Action::setSuccess("���� ��������� ����������� �������� �������. ����������, ���������� ����� ���������");

  
?>