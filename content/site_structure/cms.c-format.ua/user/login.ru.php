<?php

/**
 * Страница авторизации пользователей
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


/**
 * Пользователь авторизирован
 */
if (Auth::isLoggedIn()) {
	header("Location: /User/");
	exit;
}

 
/**
 * Форма авторизации
 */
$TmplContent->set('loginForm', Auth::displayLoginForm());


?>