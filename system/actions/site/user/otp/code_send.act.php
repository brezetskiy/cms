<?php

/**
 * Отправка кода
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
	$message = cms_message('User', "Сессия завершена.");
	$_SESSION['ActionReturn']['error'][md5($message)] = $message;
	$_RESULT['javascript'] = "document.location.reload();"; 
	exit;
} 
 

/**
 * Проверка, возможно пользователь запросил резервный код
 */
$is_reserve = globalVar($_REQUEST['is_reserve']);


/**
 * Пользователь, что пытается пройти OTP защиту
 */
$user_id = $otp_data['user_id'];
  

/**
 * Если пользователь не использует резервные коды, отправляем смс
 */
if(empty($is_reserve)){
	
	/**
	 * Телефонный номер, на который нужно отправить смс с кодом доступа 
	 */
	$phone_id = globalVar($_REQUEST['phone'], 0);
	if (empty($phone_id)){
		$_RESULT['otp_sms_phone_form_error_block'] = "Пожалуйста, укажите номер телефона, на который будет отправлен код доступа";
		$_RESULT['javascript']  = "delta_loader_clear(); "; 
		$_RESULT['javascript'] .= "$('#otp_sms_phone_form_error_block').show();";
		exit;
	} 
	
	 
	/** 
	 * Отправка кода доступа
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