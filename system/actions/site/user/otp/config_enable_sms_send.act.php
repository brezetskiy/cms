<?php

/**
 * Определение номера для подтверждения навтройки SMS авторизации
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

$user_id = Auth::getUserId();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, авторизируйтесь');";
	exit;
}


$phone_id = globalVar($_REQUEST['phone'], 0);
if(empty($phone_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, определите телефонный номер');";
	exit;
}

$phone = AuthPhone::getPhone($phone_id, $user_id);
if(empty($phone_id)){
	$_RESULT['javascript'] = "delta_error('Указанный номер не найден');";
	exit;
}
 

/**
 * Отправка СМС
 */
$message = ""; 
 
if(!AuthOTP::createSmsCode($phone_id, $user_id, $message, 'otp_confirm')) { 
	$_RESULT['javascript'] = "delta_error('$message');";
	exit;
}

$_RESULT['javascript']  = "config_step(3, 'sms', 0);";   
$_RESULT['javascript'] .= "delta_success('Код подтверждения успешно отправлен');";     


?>