<?php

/** 
 * �������� ���� �������������
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['id'], 0);


$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	echo "����������, ���������������.";
	exit;
}


$phone = $DB->query_row("
	SELECT 
		phone, 
		confirmed,
		DATE_FORMAT(sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp  	
	FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");
if($DB->rows == 0){
	$_RESULT['javascript'] = "delta_error('�� �� ��������� ���������� ���������� ����������� ������');";
	exit;
} 
 

/**  
 * ���������������� ������ ������� ��� �������������
 */
if(empty($phone['confirmed'])){
	$_RESULT['javascript'] = "delta_error('��������� ����� �� �����������. ��� ����� ������� ��� ���� �������������');";
	exit;
}
 
 
if(!empty($phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $phone['sms_tstamp']) > time() - 60){  
	$_RESULT['javascript'] = "delta_error('�� ������ ����� ��� ����� ��������� ���� ���� ��� � ������. ����� ���������� ��� {$phone['sms_tstamp']}');"; 
	exit;
}


/**
 * ��������� ���
 */
$code = AuthPhone::getPhoneCode($phone_id, $phone_id, 'delete', $user_id);
if(!empty($code['code_tstamp']) && convert_date('d.m.Y H:i:s', $code['code_tstamp']) > time() - 3600){
	$_RESULT['javascript'] = "delta_error('��� ������������� ����� ��������� ���� ���� ��� � ������� ����. ��������� ��� ��� ��������� � {$code['code_tstamp']}');";
	exit;
}
   

/**
 * ��������� ��� �������������
 */
if(!AuthPhone::createCode($phone_id, $phone_id, $phone['phone'], 0, 'delete')){
	$_RESULT['javascript'] = "delta_error('�� ������� ��������� ��� � ����� �������������. ����������, ��������� ������� ����� ��������� �����');";
	exit;
} 
   
 
$_RESULT['javascript']  = "delta_success('����� ��� ������������� ������� ���������');"; 
$_RESULT['javascript'] .= "phone_load();"; 


?>