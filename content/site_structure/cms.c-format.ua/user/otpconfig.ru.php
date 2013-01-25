<?php

/**
 * Настройка двухтапной проверки
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

 
$user_id = Auth::getUserId();	


$otp_config = $DB->query_row("SELECT otp_enable as is_enabled, otp_type as type FROM auth_user WHERE id = '$user_id'");

if(!empty($_SESSION['otp_step']) && !empty($_SESSION['otp_type'])) {
	$TmplDesign->iterate('/onload/', null, array('function' => "config_step(2, '{$_SESSION['otp_type']}', {$_SESSION['otp_step']}); "));
  
} elseif(!empty($otp_config['is_enabled'])){  
	$TmplDesign->iterate('/onload/', null, array('function' => "config_step(2, '{$otp_config['type']}', 0); "));

} else {
	$TmplDesign->iterate('/onload/', null, array('function' => "config_step(2, 'disable', 0); ")); 
} 
 


?>