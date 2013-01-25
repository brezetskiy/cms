<?php
 
/**
 * Обработчик авторизации посредством OpenID
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 

/**
 * Обазательная строка для всех обработчиков виджета AuthOID
 */
if(empty($_REQUEST['_return_path'])){
	$_REQUEST['_return_path'] = (!empty($_SESSION['oid_widget']['return_path'])) ? $_SESSION['oid_widget']['return_path'] : HTTP_SCHEME . "://" . CMS_HOST ;
}
 
 
/**
 * Отправка запроса на авторизацию
 */
$action = globalVar($_REQUEST["_a"], '');
$widget_name = globalVar($_REQUEST['_own'], '');

if($action == 'auth'){  
	$openid_identifier = globalVar($_REQUEST['openid_identifier'], '');
	
	if (preg_match(VALID_EMAIL, $openid_identifier)){
		$openid_identifier = substr($openid_identifier, 0, strpos($openid_identifier, '@'));
	}
	
	$openid_provider = globalVar($_REQUEST['openid_provider'], '');
	$openid_link = globalVar($_REQUEST['openid_link'], '');
	    
	if (empty($openid_identifier)){
		AuthOID::updateParentWindow('error', (cms_message('User', "Пожалуйста, укажите ваш OpenID идентификатор на стороне провайдера")));
	}
	
	if (empty($openid_provider)){
		AuthOID::updateParentWindow('error', (cms_message('User', "Провайдер не определен.")));
	}
	
	if (!preg_match(VALID_FILE_NAME, $openid_identifier)){
		AuthOID::updateParentWindow('error', (cms_message('User', "Пожалуйста, укажите корректный OpenID идентификатор: состоит из латинских символов, цифр, знака подчеркивания, одинарного дефиса или точки, начинается с буквы и заканчиваться буквой или цифрой")));
	}
	
	$_SESSION['oid_openid_identifier'] = $openid_identifier;
	$_SESSION['oid_openid_provider'] = $openid_provider;
	 
	$openid_identifier = str_replace('{$identity}', $openid_identifier, $openid_link); 
	
	if (!AuthOID::redirectOpenID($openid_identifier, '', $widget_name)){
		AuthOID::updateParentWindow('error', (AuthOID::getErrors()));
	}
}

 
/**
 * Определяем виджет, с которым работаем
 */
$_SESSION['oid_widget_active'] = globalVar($_REQUEST['_own'], '');

    
/**
 * Проверка ответа 
 */ 
if(!AuthOID::authOpenID()){
	AuthOID::updateParentWindow('warning', AuthOID::getErrors());
	exit;
}
 
AuthOID::updateParentWindow('success', AuthOID::getMessages());
exit;


?>