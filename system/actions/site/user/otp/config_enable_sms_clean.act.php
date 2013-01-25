<?php

/**
 * �������� ��� � ����� �������������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

function otp_handle_error($message){
	global $_RESULT;
	
	$_RESULT['javascript'] = "delta_error('$message');";
	exit;
}


$user_id = Auth::getUserId();
if(empty($user_id)){
	otp_handle_error("������ ���������. ����������, ���������������");
}

   
AuthPhone::clearLastCode('otp_confirm', $user_id);
$_RESULT['javascript'] = "config_step(2, 'sms', 0); ";    


?>