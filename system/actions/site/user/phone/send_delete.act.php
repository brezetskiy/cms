<?php

/** 
 * Отправка кода подтверждения
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['id'], 0);


$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	echo "Пожалуйста, авторизируйтесь.";
	exit;
}


$phone = $DB->query_row("
	SELECT 
		phone, 
		confirmed,
		DATE_FORMAT(sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp  	
	FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");
if($DB->rows == 0){
	$_RESULT['javascript'] = "delta_error('Вы не являетесь владельцем указанного телефонного номера');";
	exit;
} 
 

/**  
 * Неподтвержденные номера удаляем без подтверждения
 */
if(empty($phone['confirmed'])){
	$_RESULT['javascript'] = "delta_error('Указанный номер не подтвержден. Его можно удалить без кода подтверждения');";
	exit;
}
 
 
if(!empty($phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $phone['sms_tstamp']) > time() - 60){  
	$_RESULT['javascript'] = "delta_error('На каждый номер СМС можно отправить лишь один раз в минуту. Время последнего СМС {$phone['sms_tstamp']}');"; 
	exit;
}


/**
 * Проверяем код
 */
$code = AuthPhone::getPhoneCode($phone_id, $phone_id, 'delete', $user_id);
if(!empty($code['code_tstamp']) && convert_date('d.m.Y H:i:s', $code['code_tstamp']) > time() - 3600){
	$_RESULT['javascript'] = "delta_error('Код подтверждения можно отправить лишь один раз в течении часа. Последний код был отправлен в {$code['code_tstamp']}');";
	exit;
}
   

/**
 * Обновляем код подтверждения
 */
if(!AuthPhone::createCode($phone_id, $phone_id, $phone['phone'], 0, 'delete')){
	$_RESULT['javascript'] = "delta_error('Не удалось отправить СМС с кодом подтверждения. Пожалуйста, повторите попытку через некоторое время');";
	exit;
} 
   
 
$_RESULT['javascript']  = "delta_success('Новый код подтверждения успешно отправлен');"; 
$_RESULT['javascript'] .= "phone_load();"; 


?>