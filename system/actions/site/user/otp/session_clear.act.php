<?php

/**
 * ������� ������ OTP �����������
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */

/**
 * ��������� ������ OTP ������ 
 */
if (!AuthOTP::isSessionActive()){
	$_RESULT['javascript'] = "message_close();"; 
	exit;
} 
 

/**
 * ������� ������ � �������
 */
AuthOTP::sessionClear();
$_RESULT['javascript'] = "message_close();"; 

 
exit;


?>