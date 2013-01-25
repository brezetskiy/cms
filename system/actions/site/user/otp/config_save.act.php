<?php
 
/**
 * Сохранение параметров OTP защиты
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
 * Доступные типы защиты
 */
$active_types = array('etoken', 'android', 'iphone', 'java', 'sms');


/**
 * Проверка пользователя
 */
if(!Auth::isLoggedIn()){
	otp_handle_error("Вы не авторизированы");
}

$user = Auth::getInfo();
$user_id = $user['id']; 

$DB->result("SELECT id FROM auth_user WHERE id = '$user_id' AND otp_enable = '1'");
if($DB->rows > 0){
	otp_handle_error("OTP защита уже настроена для Вашей учетной записи"); 
}


/**
 * Проверка типа защиты
 */
$type = globalVar($_REQUEST['otp_type'], '');
if(empty($type)){
	otp_handle_error("Не определен способ генерации кодов");
}

if(!in_array($type, $active_types)){
	otp_handle_error("Передан неизвестный способ генерации кодов");
}


/**
 * Обработка  eToken
 */
if($type == 'etoken'){
	
	// Параметры
	$counter = globalVar($_REQUEST['otp_counter'], '');	
	$sign 	 = globalVar($_REQUEST['otp_sign'], '');	
	$code 	 = globalVar($_REQUEST['otp_code'], '');
	$counter = (int) preg_replace('/[^0-9]*/', '', $counter); 
	
	if(!preg_match('/^[0-9]*$/', $counter)){
		otp_handle_error("Пожалуйста, укажите значение счетчика, как целое число"); 
	}
	
	if(!preg_match('/^[0-9a-fA-F]*$/', $sign)){
		otp_handle_error("Секретный ключ может содержать только символы шестнадцатеричной системы счисления: <b>0123456789ABCDEF</b>"); 
	}
	
	// Проверка кода
	if(!AuthOTP::authEToken($user_id, $code, $sign, $counter)){ 
		otp_handle_error("Код проверки введен неверно. Пожалуйста, проверьте введенные Вами данные и повторите попытку"); 
	}
	
	
/**
 * Обработка мобильных устройств
 */
} elseif($type == 'android' || $type == 'iphone' || $type == 'java'){
	
	// Параметры
	$counter = round(time()/30);
	$sign 	 = (!empty($_SESSION['otp_sign'])) ? Base32::decode($_SESSION['otp_sign']) : '';  
	$code 	 = globalVar($_REQUEST['otp_code'], '');
	  
	// Проверка кода
	if(!AuthOTP::authGoogle($code, $sign)){  
		otp_handle_error("Код проверки введен неверно. Пожалуйста, проверьте введенные Вами данные и повторите попытку"); 
	}
	
	
/**
 * SMS авторизация
 */	
} elseif($type == 'sms'){
	  
	// Параметры 
	$counter = 0;
	$sign    = 'sms';      
	$code    = globalVar($_REQUEST['otp_code'], '');
	$message = '';   
	     
	// Проверка кода 
	if(!AuthOTP::authSms($code, 'otp_confirm', $user_id, $message)){   
		otp_handle_error($message);  
	}
} 


/**
 * Сохраняем данные в базе
 */
$DB->update("UPDATE auth_user SET otp_type = '$type', otp_cnt = '$counter', otp_sign = '$sign' WHERE id = '$user_id'");
$DB->delete("DELETE FROM auth_user_otp_code WHERE user_id = '$user_id'");
$DB->update("UPDATE auth_user SET otp_enable = 1 WHERE id = '$user_id'");
	
$_SESSION['otp_install'] = true;
if(!empty($_SESSION['otp_step'])) unset($_SESSION['otp_step']);
if(!empty($_SESSION['otp_type'])) unset($_SESSION['otp_type']);


$_RESULT['javascript'] = "window.location.reload(); ";


?>