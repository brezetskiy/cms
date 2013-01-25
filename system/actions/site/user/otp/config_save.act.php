<?php
 
/**
 * ���������� ���������� OTP ������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


function otp_handle_error($message){
	global $_RESULT;
	
	$_RESULT['javascript'] = "delta_error('".cms_message('User', $message)."');";
	exit; 
}

/**
 * ��������� ���� ������
 */
$active_types = array('etoken', 'android', 'iphone', 'java', 'sms');


/**
 * �������� ������������
 */
if(!Auth::isLoggedIn()){
	otp_handle_error("�� �� ��������������");
}

$user = Auth::getInfo();
$user_id = $user['id']; 

$DB->result("SELECT id FROM auth_user WHERE id = '$user_id' AND otp_enable = '1'");
if($DB->rows > 0){
	otp_handle_error("OTP ������ ��� ��������� ��� ����� ������� ������"); 
}


/**
 * �������� ���� ������
 */
$type = globalVar($_REQUEST['otp_type'], '');
if(empty($type)){
	otp_handle_error("�� ��������� ������ ��������� �����");
}

if(!in_array($type, $active_types)){
	otp_handle_error("������� ����������� ������ ��������� �����");
}


/**
 * ���������  eToken
 */
if($type == 'etoken'){
	
	// ���������
	$counter = globalVar($_REQUEST['otp_counter'], '');	
	$sign 	 = globalVar($_REQUEST['otp_sign'], '');	
	$code 	 = globalVar($_REQUEST['otp_code'], '');
	$counter = (int) preg_replace('/[^0-9]*/', '', $counter); 
	
	if(!preg_match('/^[0-9]*$/', $counter)){
		otp_handle_error("����������, ������� �������� ��������, ��� ����� �����"); 
	}
	
	if(!preg_match('/^[0-9a-fA-F]*$/', $sign)){
		otp_handle_error("��������� ���� ����� ��������� ������ ������� ����������������� ������� ���������: <b>0123456789ABCDEF</b>"); 
	}
	
	// �������� ����
	if(!AuthOTP::authEToken($user_id, $code, $sign, $counter)){ 
		otp_handle_error("��� �������� ������ �������. ����������, ��������� ��������� ���� ������ � ��������� �������"); 
	}
	
	
/**
 * ��������� ��������� ���������
 */
} elseif($type == 'android' || $type == 'iphone' || $type == 'java'){
	
	// ���������
	$counter = round(time()/30);
	$sign 	 = (!empty($_SESSION['otp_sign'])) ? Base32::decode($_SESSION['otp_sign']) : '';  
	$code 	 = globalVar($_REQUEST['otp_code'], '');
	  
	// �������� ����
	if(!AuthOTP::authGoogle($code, $sign)){  
		otp_handle_error("��� �������� ������ �������. ����������, ��������� ��������� ���� ������ � ��������� �������"); 
	}
	
	
/**
 * SMS �����������
 */	
} elseif($type == 'sms'){
	  
	// ��������� 
	$counter = 0;
	$sign    = 'sms';      
	$code    = globalVar($_REQUEST['otp_code'], '');
	$message = '';   
	     
	// �������� ���� 
	if(!AuthOTP::authSms($code, 'otp_confirm', $user_id, $message)){   
		otp_handle_error($message);  
	}
} 


/**
 * ��������� ������ � ����
 */
$DB->update("UPDATE auth_user SET otp_type = '$type', otp_cnt = '$counter', otp_sign = '$sign' WHERE id = '$user_id'");
$DB->delete("DELETE FROM auth_user_otp_code WHERE user_id = '$user_id'");
$DB->update("UPDATE auth_user SET otp_enable = 1 WHERE id = '$user_id'");
	
$_SESSION['otp_install'] = true;
if(!empty($_SESSION['otp_step'])) unset($_SESSION['otp_step']);
if(!empty($_SESSION['otp_type'])) unset($_SESSION['otp_type']);


$_RESULT['javascript'] = "window.location.reload(); ";


?>