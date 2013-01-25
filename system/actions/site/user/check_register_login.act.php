<?php
/** 
 * Проверка существования логина пользователя 
 * @package Pilot 
 * @subpackage User 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$login = $DB->escape(stripslashes(globalVar($_REQUEST['value'], '')));
$_RESULT['status'] = $_RESULT['message'] = '';

/**
 * Проверяем логин, если указан
 */
if (!empty($login)) {
	if (!preg_match(VALID_LOGIN, $login)) {
		$_RESULT['status'] = 'failed';
		$_RESULT['message'] = cms_message('CMS', 'Логин должен иметь длину от 1 до 20 символов и содержать только символы латиницы, цифры и симовлы _-.+!@#$%^~()');
		exit;
	} else {
		$query = "
			SELECT * FROM auth_user
			WHERE login = '$login' OR email = '$login'
		";
		$DB->query($query);
		
		if ($DB->rows != 0) {
			$_RESULT['status'] = 'failed';
			$_RESULT['message'] = cms_message('CMS', 'Пользователь с указанным логином уже существует');
			exit;
		}
		
		$_RESULT['status'] = 'ok';
		$_RESULT['message'] = cms_message('CMS', 'Логин свободен');
	}
}


?>