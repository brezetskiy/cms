<?php
/**
 * —крипт осуществл€ет проверку на то - зарегистрирован пользователь или нет
 * 
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ≈сли пользователь с указанным e-mail адресом - зарегистрирован, то переходим на
 * и он вошЄл в систему, то переходим на страницу, на которой указываем группы
 * рассылок, на которые он хочет быть подписан.
 * 
 * ≈сли пользователь с указанным e-mail адресом зарегистрирован но не вошЄл,
 * то перебрасываем его на страницу ввода логина и парол€.
 * 
 * ¬ ином случае перебрасываем его на страницу с рассылкой, где он будет
 * автоматически зарегистрирован
 * 
 */

$email = globalVar($_REQUEST['email'], '');

// ѕровер€ем правильность указани€ e-mail адреса
if (!preg_match(VALID_EMAIL, $email)) {
	Action::setError('Ќеправильно указан e-mail адрес');
	header("Location: /".LANGUAGE_URL."Maillist/?email=$email");
	exit;
}

// ѕровер€ем, не пытаетс€ ли сам пользоватещь зарегистрировать себ€
if (isset($_SESSION['auth']['email']) && $_SESSION['auth']['email'] == $email) {
	header("Location: /".LANGUAGE_URL."Maillist/");
	exit;
}

// ƒл€ дальнейших действий пользовател€ выводим из системы
Auth::logout();

// ѕровер€ем, нет ли пользовател€ с таким e-mail адресом 
$query = "SELECT id FROM auth_user WHERE email='".addcslashes($email, "\'\\")."'";
$DB->query($query);
if ($DB->rows > 0) {
	Action::setError('ƒл€ подписки на рассылку введите свой логин и пароль');
	header("Location: /".LANGUAGE_URL."User/Login/?return_path=".urlencode("/".LANGUAGE_URL."Maillist/"));
	exit;
}


header("Location: /".LANGUAGE_URL."Maillist/?email=$email");
exit;

?>