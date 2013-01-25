<?php

/** 
 * ������������� ������
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['phone'], 0);
$params_codes  = globalVar($_REQUEST['code'], array());
  

/**
 * �������� ������������
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('����������, ���������������');";
	exit;
} 


$phones = AuthPhone::getConfirmedPhones();
if(count($phones) >= 5){      
	$_RESULT['javascript'] = "delta_error('����������� �� ����� ���� �������������� ������� ��� ������ ������� ������');";
	exit;
}


/**
 * �������� ������
 */
//$DB->result("SELECT id FROM auth_user WHERE id = '$user_id' AND passwd = '".md5($params_passwd)."'");
//if($DB->rows == 0){
//	$_RESULT['javascript'] = "delta_error('������� ������ ������. ����������, ������� ������ ����� ���������������� ������� ������');";
//	exit;
//}


/**
 * �������� ���� �� ���������� �����
 */
$DB->result("SELECT id FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");
if($DB->rows == 0){
	$_RESULT['javascript'] = "delta_error('� ��� ��� ���� ��������� �� ��������� �����');";
	exit;
}


/**
 * ������ �����
 */
$codes = AuthPhone::getPhoneCodes($phone_id);
$code_title = (count($codes) > 1) ? "����" : "���";

if(empty($codes)){
	$_RESULT['javascript'] = "delta_error('��� ���������� ����������� ������ $code_title � ���� �� ���������".((count($codes) > 1) ? "�" : "").". ����������, ��������� $code_title ��������');";
	exit;
}


/**
 * �������� �����
 */
$is_new_confirmed = false;
$messages = array();

reset($codes);
while(list($code_id, $code) = each($codes)){
	
	/**
	 * �������� ��� ��� ��� ����������� 
	 */
	if(!empty($code['confirmed'])) continue;
	
	$phone_send_title = $code['phone_send_original'];
	$code_tstamp = convert_date("d.m.Y H:i:s", $code['code_tstamp']);
	$code_attempt = $code['attempt'] + 1;
	
	/**
	 * ��������� ���-�� ������� ����������� ����� 
	 */
	$DB->update("UPDATE auth_user_phone_code SET attempt = '$code_attempt' WHERE id = '$code_id'");
	 
	/**
	 * �������� ���-�� ������� ������������� ����
	 */
	if($code_attempt > AUTH_USER_PHONE_CONFIRM_ATTEMPT){
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "��������� ���������� ������� ������������� ��� ����, ��� ��� ��������� �� ����� $phone_send_title. ����������, ��������� ���� ��������");
		continue;
	} 
	
	/**
	 * �������� �������� ����
	 */
	if($code_tstamp < time() - 3600 * 12) {
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "���, ��� ��� ��������� �� ����� $phone_send_title �������. ����������, ��������� ���� ��������");
		continue;
	}
	
	/**
	 * �������� ����
	 */
	if(empty($params_codes[$code_id]) ||  trim($params_codes[$code_id]) != $code['code']){
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "������� ������ ��� �������������, ��� ��� ��������� �� ����� $phone_send_title");
		continue;
	}
	
	/**
	 * ������������ ���, ��� ������ ��� ��������
	 */
	$DB->update("UPDATE auth_user_phone_code SET confirmed = '1' WHERE id = '$code_id'");
	$codes[$code_id]['confirmed'] = 1;
	if(!$is_new_confirmed) $is_new_confirmed = true;
}
 

/**
 * ����� ������
 */
if(!empty($messages)){ 
	$_RESULT['javascript'] = "delta_message(".json_encode($messages).");";
	if($is_new_confirmed) $_RESULT['javascript'] .= "phone_load();";  
	exit;
}


/**
 * ���� ������ ��� - ��� ������, ��� ��� ���� ������������. ������������ ���������� �����
 */
$DB->update("UPDATE auth_user_phone SET confirmed = 1 WHERE id = '$phone_id'");
$_RESULT['javascript'] = "phone_load();"; 


?>