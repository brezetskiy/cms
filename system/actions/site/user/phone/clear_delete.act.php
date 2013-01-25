<?php

/** 
 * Отмена удаления
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['phone'], 0);
$is_resend = globalVar($_REQUEST['is_resend'], 0);


$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, авторизируйтесь.'); ";
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
 
 
$DB->delete("DELETE FROM auth_user_phone_code WHERE phone_id = '$phone_id' AND action = 'delete'");

 

if ($is_resend) {
	$_RESULT['javascript'] = "phone_load($phone_id);"; 
} else {
	$_RESULT['javascript']  = "delta_success('Удаление номера отменено');";  
	$_RESULT['javascript'] .= "phone_load(0);"; 
}



?>