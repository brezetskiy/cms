<?php

/**
 * Отправка смс с кодом подтверждения
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


$source = globalVar($_REQUEST['source'], 'disable');
$_SESSION['otp_disable_form'] = "phone_$source";


/**
 * Проверка пользователя
 */
$user_id = Auth::getUserId();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Сессия завершена. Пожалуйста, авторизируйтесь');";
	exit;
}


/**
 * Выбрано использование резерных кодов
 */  
$is_reserve = globalVar($_REQUEST['is_reserve'], 0);
if(!empty($is_reserve)){  
	$_SESSION['otp_disable_form'] = "reserve_$source";
	     
	$_RESULT['javascript']  = "config_disable_open();";   
	$_RESULT['javascript'] .= "switch_code(0);";  
	exit;   
} 


/**
 * Определение телефонного номера
 */
$phone_id = globalVar($_REQUEST['phone'], 0);
if(empty($phone_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, определите телефонный номер');";
	exit;
}


/**
 * Проверка телефонного номера
 */
$phone = AuthPhone::getPhone($phone_id, $user_id);
if(empty($phone_id)){
	$_RESULT['javascript'] = "delta_error('Указанный номер не найден');";
	exit;
}
    
 
/**
 * Отправка СМС
 */
$message = ""; 
 
if(!AuthOTP::createSmsCode($phone_id, $user_id, $message, 'otp_delete')) {  
	$_RESULT['javascript'] = "delta_error('$message');";
	exit;
}
			  
$_SESSION['otp_disable_form'] = "submit_$source";
 
$_RESULT['javascript'] = "delta_success('Код подтверждения успешно отправлен');"; 
$_RESULT['javascript'] .= "config_disable_open();"; 


?>