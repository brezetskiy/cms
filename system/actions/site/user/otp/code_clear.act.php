<?php

/**
 * Очистка кода
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


/**
 * Проверяем сессию OTP защиты
 */ 
$otp_data = AuthOTP::isSessionActive();
if (empty($otp_data['user_id'])){
	$message = "Сессия завершена.";
	$_SESSION['ActionReturn']['error'][md5($message)] = $message;
	$_RESULT['javascript'] = "document.location.reload();"; 
	exit;
} 
 

/**
 * Пользователь, что пытается пройти OTP защиту
 */
$user_id = $otp_data['user_id']; 
AuthPhone::clearLastCode('otp_confirm', $user_id);    
  
$_RESULT['javascript']  = "delta_loader_clear(); ";   
$_RESULT['javascript'] .= "delta_action('otp_sms_auth_form()', '".AuthOTP::displaySmsForm($user_id)."', 'otp_session_clear()');";      
exit;
 

?>