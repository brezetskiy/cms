<?php
/**
* Подтверждение регистрации пользователя
* @package Pilot
* @subpackage User
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

$code = globalVar($_GET['code'], '');

if (!preg_match('/^[a-z0-9]{32}$/', $code)) {
	Action::onError(cms_message('CMS', 'Неправильный код подтверждения. Убедитесь, что Вы скопировали ссылку полностью'));
}

$query = "
	SELECT *
	FROM auth_user
	WHERE confirmation_code = '$code'
";
$user = $DB->query_row($query);
if ($DB->rows == 0) {
	
	// Неправильный код подтверждения
	Action::onError(cms_message('CMS', 'Неправильный код подтверждения. Убедитесь, что Вы скопировали ссылку полностью'));
	
} elseif ($user['confirmed']) {
	
	// Пользователь уже активировал аккаунт, и делает это еще раз
	Action::setSuccess(cms_message('CMS', 'Ваш аккаунт уже активирован. Воспользуйтесь формой для входа на сайт'));
	
} else {
	// активация аккаунта
	$query = "UPDATE auth_user SET confirmed=1 WHERE id='".$user['id']."'";
	$DB->update($query);
	
	
	// Разлогиниваем пользователя, что б перечиталась сессия
	if (Auth::isLoggedIn()) {
		$_SESSION['auth']['confirmed'] = 1;
		Action::setSuccess(cms_message('CMS', 'Спасибо, Ваш аккаунт активирован'));
	} else {
		Action::setSuccess(cms_message('CMS', 'Ваш аккаунт активирован. Теперь Вы можете войти на сайт'));
	}
	
}

?>