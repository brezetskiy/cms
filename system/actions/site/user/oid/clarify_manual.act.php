<?php
/**
 * Обработчик формы авторизации / регистрации, что заложена внутри виджета oid/widget_box
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 
$_RESULT['javascript'] = "";


/**
 * Проверка сессии 
 */
if (empty($_SESSION['oid_widget']['name'])){
	$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Ваша сессия завершена.")."');";
	exit;
}

  
/**
 * Очистка устаревших параметров
 */
if (!empty($_SESSION['oid_clarify_auto'])){
	unset($_SESSION['oid_clarify_auto']);
}


/**
 * Текущее событие
 */
$action = globalVar($_REQUEST['action'], '');
if (empty($action)){
	$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Передан пустой параметр события. Пожалуйста, обратитесь в тех. поддержку.")."');";
	exit;
}


/**
 * Страница возврата
 */
$_return_path = globalVar($_REQUEST['_return_path'], '/User/Info/');


/**
 * Валидация Email при авторизации 
 */
if ($action == 'validate_email_auth'){
	
	/**
	 * Проверяем переданный email
	 */
	$email = globalVar($_REQUEST['email'], '');
	$_SESSION['oid_clarify_manual']['email'] = $email;
	
	if (empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Передан пустой e-mail.")."');";
		exit;
	}
	
	if (!preg_match(VALID_EMAIL, $email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Указан некорректный e-mail.")."');";
		exit;
	}
	
	/**
	 * Проверка наявности пользователя с указанным email
	 */
	$user = $DB->query_row("SELECT id, otp_enable, otp_type FROM auth_user WHERE login='$email' or email='$email'");
	if ($DB->rows == 0){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Указанный Вами e-mail не найден.")."');";
		exit;
	}
 
	/** 
	 * Открываем форму проверки пароля
	 */
	$_SESSION['oid_clarify_manual']['action'] = 'auth';
	$_SESSION['oid_clarify_manual']['user_id'] = $user['id']; 
	
	$_RESULT['javascript'] .= "delta_success('".cms_message('User', "Учетная запись <b>$email</b> обнаружена. Пожалуйста, уточните параметры.")."');";
	$_RESULT['javascript'] .= "oid_widget__box__clarify_manual_open('".$_SESSION['oid_widget']['name']."', 'auth', '');";
	
	/**
	 * Хакерам показываем капчу
	 */  
	if (Auth::isHacker()) {     
		$_RESULT['javascript'] .= "$('#oid_widget__".$_SESSION['oid_widget']['name']."_clarify_manual_auth_captcha').show();"; 
	}
	 
	exit;
		
	
/**
 * Валидация пароля и остальных параметров, что необходимы для авторизации текущего пользователя
 */
} elseif ($action == 'auth') {
			
	$remember = globalVar($_REQUEST['remember'], 0);
				
	/**
	 * Проверка сессии
	 */
	if (!empty($_SESSION['oid_clarify_manual'])){
		$user_id = globalVar($_SESSION['oid_clarify_manual']['user_id'], 0);
	}
	
	if (empty($user_id)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Ваша сессия завершена. Пожалуйста, нажмите <b>Отменить</b> и повторите попытку авторизации. Или воспользуйтесь услугами другого провайдера.")."');";
		exit;
	}
	  
	/**
	 * Хакерам показываем капчу
	 */    
	if (Auth::isHacker() && empty($_SESSION['oid_clarify_manual_captcha'])) { 
		$_SESSION['oid_clarify_manual_captcha'] = true;   
		 
		$_RESULT['oid_widget__captcha'] = Captcha::createHtml();   
		$_RESULT['javascript'] .= "$('#oid_widget__".$_SESSION['oid_widget']['name']."_clarify_manual_auth_captcha').show();"; 
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Превышено кол-во неудачных попыток входа. Пожалуйста, введите число на картинке.")."');";
		exit;
	}
	
	/**
	 * Проверяем CAPTCHA, если к нам пришел хакер
	 */
	if (Auth::isHacker() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
		$captcha_new_src = "/tools/cms/site/captcha.php?uid=".$_REQUEST['captcha_uid']."&refresh=".round(rand()*100000);
		
		$_RESULT['javascript'] .= "$('#img_captcha_uid').attr('src', '$captcha_new_src');";  
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Неправильно введено число на картинке.")."');";
		exit;
	}
	
	/**  
	 * Проверка пароля
	 */  
	$passwd = globalVar($_REQUEST['passwd'], '');
	if (empty($passwd)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Пожалуйста, введите пароль.")."');";
		exit;
	}
	
	$user = $DB->query_row("SELECT id, email, passwd, otp_enable, otp_type FROM auth_user WHERE id = '$user_id' AND passwd = '".md5($passwd)."'");
	if ($DB->rows == 0) {   
		Auth::logLogin(0, time(), $user_id, $passwd);
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Введен неверный пароль.")."');";
		exit;
	}
	
	/**
	 * OTP защита
	 */
	if(!empty($user['otp_enable']) && !AuthOTP::checkAccess($user['id'])){
		AuthOTP::sessionActivate($user_id); 
		
		$_RESULT['javascript'] .= "document.location.reload();";
		exit;   
	}
	 
	/**
	 * Авторизация
	 */
	$logged_in = Auth::login($user_id, $remember, null); 
	if (!$logged_in) {
		Auth::logLogin($user_id, time());
		
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Доступ с IP заблокирован или Ваш аккаунт отключен администратором.")."');";
		exit;
	} 
	  
	unset($_SESSION['oid_clarify_manual_captcha']); 
	 
	/**
	 * Устанавливаем куку на две недели
	 */
	if(!empty($user['otp_enable']) && $remember){
		AuthOTP::setAccess($user_id);  
	}
	
	/**
	 * Сообщение об успешной авторизации
	 */
	$message = cms_message('User', "Поздравляем, Вы успешно авторизировались");  
	$_SESSION['ActionReturn']['success'][md5($message)] = $message;

	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
	
	$_RESULT['javascript'] = "window.location = '$_return_path'";
	exit;
	
	
/**
 * Валидация Email при регистрации 
 */
} elseif ($action == 'validate_email_register'){
	
	/**
	 * Проверяем переданный email
	 */
	$email = globalVar($_REQUEST['email'], '');
	$_SESSION['oid_clarify_manual']['email'] = $email;
	
	if (empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Передан пустой e-mail.")."');";
		exit;
	}
	
	if (!preg_match(VALID_EMAIL, $email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Указан некорректный e-mail.")."');";
		exit;
	}
	
	/**
	 * Проверка наявности пользователя с указанным email
	 */
	$user = $DB->query_row("SELECT id, otp_enable FROM auth_user WHERE login='$email' or email='$email'");
	if ($DB->rows > 0){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Пользователь с указанным e-mail адресом уже существует.")."');";
		exit;
	}
		
	/**
	 * Открываем форму регистрации
	 */
	$_SESSION['oid_clarify_manual']['action'] = 'register';
	
	if (CMS_USE_CAPTCHA){
		$_RESULT['oid_widget__'.$_SESSION['oid_widget']['name'].'_clarify_manual_captcha_html'] = Captcha::createHtml();
	}
	
	$_RESULT['javascript'] .= "oid_widget__box__clarify_manual_open('".$_SESSION['oid_widget']['name']."', 'register', '".gen_password(8)."');";
	$_RESULT['javascript'] .= "delta_success('".cms_message('User', "Адрес <b>$email</b> свободен. Чтобы создать новую учетную запись, пожалуйста, заполните форму ниже и нажмите кнопку <b>Зарегистрироваться<b>.")."');";
	
	exit;
 
	
/**
 * Регистрация
 */
} elseif($action == 'register'){
	
	/**
	 * Проверка сессии
	 */
	if(!empty($_SESSION['oid_clarify_manual'])){
		$email = globalVar($_SESSION['oid_clarify_manual']['email'], '');
	}
	
	if(empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Ваша сессия завершена. Пожалуйста, нажмите <b>Отменить</b> и повторите попытку авторизации. Или воспользуйтесь услугами другого провайдера.")."');";
		exit;
	}
	 
	/**
	 * Проверка Captcha
	 */
	if (CMS_USE_CAPTCHA && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Неправильно введено число на картинке.")."');";
		exit;
	}
 
	$name = globalVar($_REQUEST['name'], ''); 
	$passwd = globalVar($_REQUEST['passwd'], ''); 
	
	$name = trim($name); 
	if(empty($name)){
		$name = $email;
	}
	
	$passwd = trim($passwd); 
	if(empty($passwd)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "Пожалуйста, введите Ваш пароль.")."');";
		exit;
	}

	$result = AuthOID::oidRegister(array('email' => $email, 'name' => $name, 'passwd' => $passwd));
	if(!$result){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', AuthOID::getErrors())."');";
		exit;
	}
	 
	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
	
	
	$message = cms_message('User', "Поздравляем, Вы успешно зарегистрировались");  
	$_SESSION['ActionReturn']['success'][md5($message)] = $message;  
	
	$_RESULT['javascript'] = "window.location = '$_return_path'";
	exit;
}



?>