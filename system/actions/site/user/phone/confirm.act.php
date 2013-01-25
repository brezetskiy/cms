<?php

/** 
 * Подтверждение номера
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['phone'], 0);
$params_codes  = globalVar($_REQUEST['code'], array());
  

/**
 * Проверка пользователя
 */
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, авторизируйтесь');";
	exit;
} 


$phones = AuthPhone::getConfirmedPhones();
if(count($phones) >= 5){      
	$_RESULT['javascript'] = "delta_error('Допускается не более пяти подтвержденных номеров для каждой учетной записи');";
	exit;
}


/**
 * Проверка пароля
 */
//$DB->result("SELECT id FROM auth_user WHERE id = '$user_id' AND passwd = '".md5($params_passwd)."'");
//if($DB->rows == 0){
//	$_RESULT['javascript'] = "delta_error('Неверно указан пароль. Пожалуйста, введите пароль вашей пользовательской учетной записи');";
//	exit;
//}


/**
 * Проверка прав на телефонный номер
 */
$DB->result("SELECT id FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");
if($DB->rows == 0){
	$_RESULT['javascript'] = "delta_error('У вас нет прав владельца на указанный номер');";
	exit;
}


/**
 * Список кодов
 */
$codes = AuthPhone::getPhoneCodes($phone_id);
$code_title = (count($codes) > 1) ? "коды" : "код";

if(empty($codes)){
	$_RESULT['javascript'] = "delta_error('Для указанного телефонного номера $code_title в базе не обнаружен".((count($codes) > 1) ? "ы" : "").". Пожалуйста, отправьте $code_title повторно');";
	exit;
}


/**
 * Проверка кодов
 */
$is_new_confirmed = false;
$messages = array();

reset($codes);
while(list($code_id, $code) = each($codes)){
	
	/**
	 * Возможно код уже был подтвержден 
	 */
	if(!empty($code['confirmed'])) continue;
	
	$phone_send_title = $code['phone_send_original'];
	$code_tstamp = convert_date("d.m.Y H:i:s", $code['code_tstamp']);
	$code_attempt = $code['attempt'] + 1;
	
	/**
	 * Обновляем кол-во попыток подтвердить номер 
	 */
	$DB->update("UPDATE auth_user_phone_code SET attempt = '$code_attempt' WHERE id = '$code_id'");
	 
	/**
	 * Проверка кол-ва попыток подтверждения кода
	 */
	if($code_attempt > AUTH_USER_PHONE_CONFIRM_ATTEMPT){
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "Превышено количество попыток подтверждения для кода, что был отправлен на номер $phone_send_title. Пожалуйста, отправьте коды повторно");
		continue;
	} 
	
	/**
	 * Проверка возраста кода
	 */
	if($code_tstamp < time() - 3600 * 12) {
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "Код, что был отправлен на номер $phone_send_title устарел. Пожалуйста, отправьте коды повторно");
		continue;
	}
	
	/**
	 * Проверка кода
	 */
	if(empty($params_codes[$code_id]) ||  trim($params_codes[$code_id]) != $code['code']){
		$messages['error'][] = @iconv('windows-1251', 'utf-8', "Неверно указан код подтверждения, что был отправлен на номер $phone_send_title");
		continue;
	}
	
	/**
	 * Подтверждаем код, что прошел все проверки
	 */
	$DB->update("UPDATE auth_user_phone_code SET confirmed = '1' WHERE id = '$code_id'");
	$codes[$code_id]['confirmed'] = 1;
	if(!$is_new_confirmed) $is_new_confirmed = true;
}
 

/**
 * Вывод ошибок
 */
if(!empty($messages)){ 
	$_RESULT['javascript'] = "delta_message(".json_encode($messages).");";
	if($is_new_confirmed) $_RESULT['javascript'] .= "phone_load();";  
	exit;
}


/**
 * Если ошибок нет - это значит, что все коды подтверждены. Подтверждаем телефонный номер
 */
$DB->update("UPDATE auth_user_phone SET confirmed = 1 WHERE id = '$phone_id'");
$_RESULT['javascript'] = "phone_load();"; 


?>