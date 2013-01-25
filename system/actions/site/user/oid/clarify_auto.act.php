<?php 

/**
 * Завершение регистрации через OpenID
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


function handle_widget_error($message){
	global $_RESULT;
	 
	$_RESULT['oid_widget__error'] = "<div class='widget_error'>".cms_message('User', $message)."</div>";
	exit; 
}

function handle_widget_warning($message){
	global $_RESULT;
	 
	$_RESULT['oid_widget__warning'] = "<div class='widget_warning'>".cms_message('User', $message)."</div>";
	exit;
}
  

/**
 * Проверка сессии 
 */
if(empty($_SESSION['oid_widget']['name'])){
	echo "<div class='widget_error'>Ваша сессия завершена</div>";
	exit; 
}


if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);


/**
 * Параметры
 */
$_return_path = globalVar($_REQUEST['_return_path'], '/User/Info/');

$action = globalVar($_REQUEST['_a'], '');
$info   = globalVar($_REQUEST['info'], array());

/** 
 * Уточнение параметров в ходе регистрации
 */
if($action == 'register'){
	$email = globalVar($_REQUEST['clarify_email'], '');
	$name  = globalVar($_REQUEST['clarify_name'], '');
	$name  = trim($name);
	
	$info['email'] = (!empty($info['email']) && preg_match(VALID_EMAIL, $info['email'])) ? $info['email'] : $email;
	$info['name'] = (!empty($info['name'])) ? $info['name'] : $name; 
	
	if(empty($info['email'])) handle_widget_error("Пожалуйста, укажите корректный Email");
	if(empty($info['name'])) $info['name'] = $info['email'];
	 
	$result = AuthOID::oidRegister($info);
	if(!$result) handle_widget_error(AuthOID::getErrors());
	

	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
	Action::setSuccess(cms_message('CMS', "Поздравляем, вы успешно зарегистрировались"));
} 


$_RESULT['javascript'] = "window.location = '$_return_path'";


?>