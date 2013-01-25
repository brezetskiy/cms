<?php
/** 
 * Восстановление пароля пользователя
 * @package Pilot 
 * @subpackage User 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$auth_code = globalVar($_GET['auth_code'], '');
$user_id = globalVar($_GET['user_id'], '');

$TmplContent->set('auth_code', $auth_code);
$TmplContent->set('user_id', $user_id);

$TmplContent->set('captcha_html', Captcha::createHtml());

?>