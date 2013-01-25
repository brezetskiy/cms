<?php

/** 
 * Удаление телефонного номера
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 


/**
 * Параметры
 */
$phone_id = globalVar($_REQUEST['phone_id'], 0);

 
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, авторизируйтесь.'); ";
	exit;
}


/**
 * Определяем данные о номере, что нужно удалить
 */
$phone = AuthPhone::getPhone($phone_id);
if(empty($phone)){
	$_RESULT['javascript'] = "delta_error('Вы не являетесь владельцем указанного телефонного номера');";
	exit;
} 


/**
 * Неподтвержденные номера удаляем без подтверждения
 */
$DB->insert("DELETE FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");

$_RESULT['javascript']  = "delta_success('Номер успешно удален');";
$_RESULT['javascript'] .= "phone_load();"; 
exit;



?>