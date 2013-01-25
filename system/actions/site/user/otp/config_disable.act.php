<?php 

/**
 * Отключение OTP защиты
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * Проверка авторизации
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError(cms_message('User', "Пожалуйста, авторизируйтесь"));
}


/**
 * Необходимые данные
 */
$code = globalVar($_REQUEST['code'], '');
$is_reserve = globalVar($_REQUEST['reserve_code'], '');
$is_force = globalVar($_REQUEST['is_force'], 0); 


/**
 * Хакер 
 */   
if(Auth::isHacker(true) && $is_reserve){    
	Action::onError(cms_message('User', "Превышено кол-во попыток ввести код. Возможность использования резервных кодов заблокирована"));
}



/**
 * Проверяем, настроена ли и включена OTP конфигурация
 */
$otp_data = $DB->query_row("SELECT otp_type as type, otp_enable as is_enabled FROM auth_user WHERE id = '$user_id'"); 


/**
 * Проверка кода доступа 
 */ 
if(empty($code)){
	Action::onError(cms_message('User', "Пожалуйста, укажите код подтверждения"));
}

$message = "Код введен не верно. Пожалуйста, проверьте введенные данные и повторите попытку";

if($otp_data['type'] == 'sms' && empty($is_reserve)){    
	if(!AuthOTP::authSms($code, 'otp_delete', $user_id, $message)){   
		Action::onError(cms_message('User', $message));		
	}
} else {
	if(!AuthOTP::auth($user_id, $code, $is_reserve, $message)) {
		if($is_reserve) Auth::logLogin(0, time(), "ID:$user_id");    
		Action::onError(cms_message('User', $message));		
	}
}


/**
 * Если все проверки пройдены - отключаем OTP защиту
 */
AuthOTP::disable();

if(!empty($_SESSION['otp_disable_form'])) unset($_SESSION['otp_disable_form']);
if(!empty($_SESSION['otp_step'])) unset($_SESSION['otp_step']);
if(!empty($_SESSION['otp_type'])) unset($_SESSION['otp_type']);


/**
 * Если отмечен фалг, нужно сразу удалить и все настройки
 */
if($is_force){  
	require_once(ACTIONS_ROOT."site/user/otp/config_clean.act.php"); 
}


?>