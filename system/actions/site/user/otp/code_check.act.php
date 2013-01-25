<?php

/**
 * Проверка кода
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


/**
 * Параметры
 */
$remember = globalVar($_REQUEST['remember'], 0);
$otp_is_reserve = globalVar($_REQUEST['otp_reserve'], 0);

$otp_code = globalVar($_REQUEST['otp_value'], '');
if(empty($otp_code)){
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "Пожалуйста, укажите код доступа"); 
	$_RESULT['javascript']  = "delta_loader_clear(); ";
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}



/**
 * Хакер
 */
if(Auth::isHacker() && $otp_is_reserve){   
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "Превышено кол-во попыток ввести код. Возможность использования резервных кодов заблокирована");  
	$_RESULT['javascript']  = "otp_reserve_ban(); "; 
	$_RESULT['javascript'] .= "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}


/**
 * Проверка кода доступа 
 */
$message = "Код доступа введен неверно";

$_SESSION['otp_admin_passed'] = AuthOTP::auth($user_id, $otp_code, $otp_is_reserve, $message);
if(empty($_SESSION['otp_admin_passed'])){
	if($otp_is_reserve) Auth::logLogin(0, time(), "ID:$user_id");       
	
	$_RESULT['otp_code_form_error_block'] = cms_message('User', $message); 
	$_RESULT['javascript']  = "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
} 	


/**
 * Если проверка код успешно пройдена, авторизируем пользователя
 */
$logged_in = Auth::login($user_id, $remember, null);
if (!$logged_in) {
	Auth::logLogin(0, time(), "ID:$user_id");
	
	$_RESULT['otp_code_form_error_block'] = cms_message('User', "Доступ с IP заблокирован или Ваш аккаунт отключен администратором"); 
	$_RESULT['javascript']  = "delta_loader_clear(); "; 
	$_RESULT['javascript'] .= "$('#otp_code_form_error_block').show();"; 
	exit;
}


/**
 * Удаляем сессию OTP авторизации
 */
AuthOTP::sessionClear();   


/**
 * По желанию пользователя - устанавливаем куку на две недели
 */
if($remember){
	AuthOTP::setAccess($user_id);
}


/**
 * Сообщение об успешной авторизации 
 */
$message = cms_message('User', "Поздравляем, Вы успешно авторизировались");  
$_SESSION['ActionReturn']['success'][md5($message)] = $message;


$_RESULT['javascript'] = "document.location.reload();"; 
exit;
 

?>