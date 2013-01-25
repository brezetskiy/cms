<?php

/** 
 * Авторизация пользователя административного интерфейса 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008 
 */


/**
 * Флаг авторизации
 */ 
$_SESSION['auth_user_mode'] = 'login';

 
/**
 * Основные данные
 */
$login 	  = trim(globalVar($_POST['login'], ''));
$passwd   = trim(globalVar($_POST['passwd'], ''));
$remember = globalVar($_POST['remember'], 0);
 

/**
 * Флаг, что определяет - запрос был сделан со стороны сайта или со стороны административной зоны
 */
$source = globalVar($_POST['source'], 'site');


/**
 * Проверяем правильность переданных данных
 */
if (!preg_match(VALID_EMAIL, $login)) {
	Action::onError(cms_message('CMS', 'Неверно введен e-mail.'));
} 


/**
 * Если пользователь уже залогинился, то удаляем существующую сессию
 */
if (isset($_SESSION['auth']['id']) && !empty($_SESSION['auth']['id'])){
	unset($_SESSION['auth']);
}


/**
 * Проверяем CAPTCHA, если к нам пришел хакер
 */
if (Auth::isHacker() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', 'Неправильно введено число на картинке'));
}

 
/**
 * Входим в систему
 */
$user = $DB->query_row("
	SELECT id, passwd, otp_enable, otp_cnt, otp_type 
	FROM auth_user 
	WHERE login='$login' or email='$login'
");

$user_id = (!empty($user['id'])) ? $user['id'] : 0;
 

/**
 * Пользователь не найден
 */
if (empty($user_id)) {
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', 'Пользователя с указанным e-mail не существует.')); 
}
 

/**
 * Пользователь найден, но не верно указан пароль
 */
if($user['passwd'] != md5($passwd)){
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', 'Неправильно указан пароль для пользователя '.$login));
}

 
/**
 * Попытки авторизации со стороны административной зоны.
 * Если включена обязательная OTP авторизация, включаем сессию OTP-защиты
 */
if(AUTH_OTP_ADMIN_ENABLE && $source == 'admin' && !AuthOTP::checkAccess($user_id)){
	AuthOTP::sessionActivate($user_id, $source); 
	Action::finish();
}


/**
 * Попытки авторизации со стороны сайта.
 * Если для пользователя включена OTP авторизация, включаем сессию OTP-защиты
 */ 
if(!empty($user['otp_enable']) && !AuthOTP::checkAccess($user['id'])){
	AuthOTP::sessionActivate($user_id, $source); 
	Action::finish();
}

	
/**
 * Попытка авторизации
 */
$logged_in = Auth::login($user['id'], $remember, null);
if (!$logged_in) {
	Auth::logLogin(0, time(), $login, $passwd);
	Action::onError(cms_message('CMS', 'Доступ с IP заблокирован или Ваш аккаунт отключен администратором'));
}  
  
 
//Action::setSuccess(cms_message('User', "Поздравляем, Вы успешно авторизировались"));


?>