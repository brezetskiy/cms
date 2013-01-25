<?php

/**
 * �������� ����������� �������������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


/**
 * ������������ �������������
 */
if (Auth::isLoggedIn()) {
	header("Location: /User/");
	exit;
}

 
/**
 * ����� �����������
 */
$TmplContent->set('loginForm', Auth::displayLoginForm());


?>