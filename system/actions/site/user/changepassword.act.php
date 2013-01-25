<?php
/**
 * Изменяет пароль пользователя
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$user_id = Auth::isLoggedIn();

$passwd = trim(globalVar($_POST['passwd'], ''));
$new_passwd = trim(globalVar($_POST['new_passwd'], ''));
$new_passwd_confirm = trim(globalVar($_POST['new_passwd_confirm'], ''));

if (empty($new_passwd)) {
	// не указан пароль
	Action::onError(cms_message('CMS', 'Не указан пароль'));
} elseif (!preg_match(VALID_PASSWD, $passwd)) {
	// текущий пароль содержит недопустимые символы
	Action::onError(cms_message('CMS', 'Неправильно указан основной пароль, можно использовать только латинские буквы, цифры, знак подчеркивания и символы +!@#$%^&*~()-'));
} elseif (!preg_match(VALID_PASSWD, $new_passwd)) { 
	// новый пароль содержит недопустимые символы	
	Action::onError(cms_message('CMS', 'Неправильно указан пароль, можно использовать только латинские буквы, цифры, знак подчеркивания и символы +!@#$%^&*~()-'));
} elseif ($new_passwd != $new_passwd_confirm) {
	// введенные пароли - не совпвадают
	Action::onError(cms_message('CMS', 'Введенные пароли - не совпадают'));
}


$DB->query("LOCK TABLES auth_user WRITE");

/**
 * Проверяем правильность указания пароля
 */
$DB->query("SELECT id FROM auth_user WHERE id='$user_id' AND passwd='".md5($passwd)."'");
if ($DB->rows != 1) {
	// Неправильно указан пароль
	Action::onError(cms_message('CMS', 'Неправильно указан пароль'));
}

/**
 * Обновление информации о пользователе
 */
$DB->update("UPDATE auth_user SET passwd='".md5($new_passwd)."' WHERE id='$user_id' AND passwd='".md5($passwd)."'");
$DB->query("UNLOCK TABLES");

/**
 * Пароль успешно изменен
 */
Action::setSuccess(cms_message('CMS', 'Пароль был успешно изменён'));


?>