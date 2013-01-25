<?php 

/**
 * ����������� ������� �������
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


$phone_id = globalVar($_REQUEST['phone'], 0);
$send_phone_id = globalVar($_REQUEST['second_phone'], 0);


$user_id = Auth::getUserId();
$error = ''; 


/**
 * ���������� ���� �������������
 */
if(!AuthPhone::sendPhoneConfirmation($phone_id, $user_id, $error, true, $send_phone_id)){
	$_RESULT['javascript'] = "delta_error('$error');";
	exit;
}

 
$_RESULT['javascript']  = "delta_success('���� ������������� ������� ����������');"; 
$_RESULT['javascript'] .= "phone_load();"; 


?>