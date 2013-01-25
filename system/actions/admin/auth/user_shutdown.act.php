<?php
/** 
 * ¬ыбрасывает пользовател€ из системы 
 * @package Pilot 
 * @subpackage Auth
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008 
 */ 
$user_id = globalVar($_REQUEST['user_id'], 0);
$cookie_code = globalVar($_REQUEST['cookie_code'], '');

$DB->delete("delete from auth_online where user_id='$user_id' and cookie_code='$cookie_code'");
$DB->delete("delete from cvs_lock where admin_id='$user_id'");
