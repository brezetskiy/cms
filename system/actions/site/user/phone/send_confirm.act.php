<?php

/** 
 * �������� ���� �������������
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

$phone_id = globalVar($_REQUEST['id'], 0);
$error = ''; 


/**
 * ���������� ���� �������������
 */
if(!AuthPhone::sendPhoneConfirmation($phone_id, 0, $error)){
	$_RESULT['javascript'] = "delta_error('$error');";
	exit;
}
 

$_RESULT['javascript']  = "delta_success('���� ������������� ������� ����������');"; 
$_RESULT['javascript'] .= "phone_load();"; 


?>