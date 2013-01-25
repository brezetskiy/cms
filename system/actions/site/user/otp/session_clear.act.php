<?php

/**
 * Очистка сессии OTP авторизации
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */

/**
 * Проверяем сессию OTP защиты 
 */
if (!AuthOTP::isSessionActive()){
	$_RESULT['javascript'] = "message_close();"; 
	exit;
} 
 

/**
 * Очищаем сессию и выходим
 */
AuthOTP::sessionClear();
$_RESULT['javascript'] = "message_close();"; 

 
exit;


?>