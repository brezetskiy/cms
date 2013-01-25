<center><div style="position:absolute; top:20%; left:40%;"><img src="/design/ukraine/img/cp/loader.gif" border="0"></div></center>

<?php
/**
 * Обработчик авторизации посредством Google
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
	$_REQUEST['_return_path'] = (!empty($_SESSION['oid_widget']['return_path'])) ? $_SESSION['oid_widget']['return_path'] : HTTP_SCHEME . "://" . CMS_HOST;
}


/**
 * Отправка запроса на авторизацию
 */
$action = globalVar($_REQUEST["_a"], '');
if($action == 'auth'){
	$widget_name = globalVar($_REQUEST['_own'], ''); 
	AuthOID::redirectGoogle('', $widget_name);	
}

 

/**
 * Определяем виджет, с которым работаем
 */
$_SESSION['oid_widget_active'] = globalVar($_REQUEST['_own'], '');


/**
 * Проверка ответа 
 */
if(!AuthOID::authGoogle()){
	AuthOID::updateParentWindow('warning', AuthOID::getErrors());
	exit;
} 
     
AuthOID::updateParentWindow('success', AuthOID::getMessages()); 
exit;

?>