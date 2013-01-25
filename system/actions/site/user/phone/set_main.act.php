<?php

/** 
 * Установка номера, как основного
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2012
 */ 

$phone_id = globalVar($_REQUEST['phone'], 0);


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
 

$DB->update("UPDATE auth_user_phone SET is_main = 0 WHERE user_id = '$user_id'");
$DB->update("UPDATE auth_user_phone SET is_main = 1 WHERE id = '$phone_id'");  


$_RESULT['javascript'] .= "phone_load();"; 


?>