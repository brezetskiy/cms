<?php

/** 
 * Добавление телефонного номера
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */  
 
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError("Пожалуйста, авторизируйтесь", "User");
}


$phone = globalVar($_REQUEST['number'], '');
if(empty($phone)){  
	Action::onError("Пожалуйста, введите телефонный номер", "User");
}



$phone_original = trim($phone);
$phone = AuthPhone::parsePhone($phone_original);
if(!$phone){
	Action::onError("Некорректный формат телефонного номера", "User");
}
	
 
/**
 * Проверка на уникальность номера
 */
$DB->result("SELECT id FROM auth_user_phone WHERE phone = '$phone' AND user_id = '$user_id'");
if($DB->rows > 0){
	Action::onError("Указанный номер уже добавлен в список Ваших номеров", "User");
}


/**
 * Проверяем возможно ли подтвердить указанный номер
 */
$DB->insert(" 
	INSERT IGNORE INTO auth_user_phone 
	SET user_id = '$user_id',    
		phone = '$phone', 
		phone_original = '$phone_original',
		is_confirmable = '0'
");

Action::setSuccess("Телефонный номер успешно добавлен", "User");	 
$_RESULT['javascript'] = "phone_load();"; 


?>