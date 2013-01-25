<?php
/** 
 * Проверка существования e-mail пользователя 
 * @package Pilot
 * @subpackage User
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$email = $DB->escape(stripslashes(globalVar($_REQUEST['value'], '')));
$_RESULT['status'] = $_RESULT['message'] = '';

/**
 * Проверяем e-mail, если указан
 */
if (!empty($email)) {
	if (!preg_match(VALID_EMAIL, $email)) {
		$_RESULT['status'] = 'failed';
		$_RESULT['message'] = cms_message('CMS', 'Неправильно указан e-mail адрес');
		exit;
	} else {
		$query = "
			SELECT * FROM auth_user
			WHERE login = '$email' OR email = '$email'
		";
		$DB->query($query);
		
		if ($DB->rows != 0) {
			$_RESULT['status'] = 'failed';
			$_RESULT['message'] = cms_message('CMS', 'Пользователь с указанным e-mail адресом уже существует');
			exit;
		}
		
		$_RESULT['status'] = 'ok';
		$_RESULT['message'] = cms_message('CMS', 'Адрес E-mail свободен для регистрации');
	}
} else {
	$_RESULT['status'] = 'failed';
	$_RESULT['message'] = cms_message('CMS', 'Не указан адрес e-mail');
}


?>