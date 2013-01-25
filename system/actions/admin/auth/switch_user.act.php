<?php
/** 
 * Выполнение авторизации на сайте под любым зарегистрированным пользователем 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$admin = Auth::getInfo();
$switch_id = globalVar($_REQUEST['switch_id'], 0);
 
$login = $DB->query_row("select login, otp_enable, group_id from auth_user where id = '$switch_id'");

/**
 * Если support пытается залогиниться под админом, сообщаем ему, что он не прав
 */  
if(!IS_DEVELOPER && $login['group_id'] == 5){
	Action::onError(cms_message('CMS', "У Вас нет права выполнить вход под указанным пользователем"));
}


// список пользователей под логином которых нельзя заходить. 
$user_block_switch = preg_split("/[^\d]+/", USER_BLOCK_SWITCH, -1, PREG_SPLIT_NO_EMPTY);
if ($DB->rows == 0) {
	Action::onError("Пользователь не найден");
} elseif (!IS_DEVELOPER && (in_array($login['login'], $_sudoers) || in_array($switch_id, $user_block_switch))) { // Под разработчиком может заходить только другой разработчик
	Action::onError("В доступе отказано");
}
    
//if(!empty($login['otp_enable'])){
//	Action::onError("Невозможно переключиться на учетную запись <b>{$login['login']}</b>. У пользователя включена двухэтапная проверка."); 
//}

Auth::logout();
Auth::login($switch_id, false, Misc::keyBlock(30, 1, ''), $admin['id']);
 
if (isset($_SESSION['auth']['login'])) {
	Action::setSuccess(cms_message('CMS', 'Выполнен вход на сайт с логином %s', $_SESSION['auth']['login']));
} else {
	Action::onError(cms_message('CMS', 'Невозможно выполнить вход с логином этого пользователя'));
}