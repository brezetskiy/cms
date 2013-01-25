<?php

/**
 * Отправка код 
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


$user_id = Auth::getUserId();  
if (empty($user_id)){
	$_RESULT['javascript'] = "delta_error('Пожалуйста, авторизируйтесь');";
	exit;
}
   
   
AuthPhone::clearLastCode('otp_delete', $user_id);
     
$source = globalVar($_REQUEST['source'], 'disable');
$_SESSION['otp_disable_form'] = "phone_$source";


$_RESULT['javascript'] = 'config_disable_sms_open();';

 
?>