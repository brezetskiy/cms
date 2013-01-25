<?php

/** 
 * �������� ����������� ������
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 


/**
 * ���������
 */
$phone_id = globalVar($_REQUEST['phone_id'], 0);

 
$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	$_RESULT['javascript'] = "delta_error('����������, ���������������.'); ";
	exit;
}


/**
 * ���������� ������ � ������, ��� ����� �������
 */
$phone = AuthPhone::getPhone($phone_id);
if(empty($phone)){
	$_RESULT['javascript'] = "delta_error('�� �� ��������� ���������� ���������� ����������� ������');";
	exit;
} 


/**
 * ���������������� ������ ������� ��� �������������
 */
$DB->insert("DELETE FROM auth_user_phone WHERE id = '$phone_id' AND user_id = '$user_id'");

$_RESULT['javascript']  = "delta_success('����� ������� ������');";
$_RESULT['javascript'] .= "phone_load();"; 
exit;



?>