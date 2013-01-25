<?php

/**
 * Очистка параметров OTP защиты
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

/**
 * Проверка пользователя
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError("Вы не авторизированы");
}

$is_enabled = $DB->result("SELECT otp_enable FROM auth_user WHERE id = '$user_id'");
if(!empty($is_enabled)) {
	Action::onError("Прежде, чем изменить настройки, необходимо <b>отключить</b> режим двухэтапной проверки");
}


/**
 * Переход на нужный раздел после очистки
 */
$otp_type = globalVar($_REQUEST['otp_type'], '');
if(!empty($otp_type)) {
	$mobile_types = array('mobile', 'android', 'iphone', 'java');
	if(in_array($otp_type, $mobile_types)) $otp_type = "mobile";
	
	$_SESSION['otp_step'] = 2;
	$_SESSION['otp_type'] = $otp_type;
}


/**
 * Удаляем настройки
 */
$DB->update("
	UPDATE auth_user 
	SET otp_enable = '0', 
		otp_type = NULL, 
		otp_cnt = NULL, 
		otp_sign = NULL 
	WHERE id = '$user_id'
");
 
Action::setSuccess("Ваши настройки двухэтапной проверки удалены. Пожалуйста, установите новые настройки");

  
?>