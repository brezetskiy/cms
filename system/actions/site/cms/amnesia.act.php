<?php
/**
 * Форма напоминания паролей
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$email = globalVar($_POST['email'], '');

// Проверяем правильность переданных данных
if (empty($email)) {
	// не указан логин или e-mail адрес
	Action::onError(cms_message('CMS', 'Не указан адрес e-mail'));
}

// Проверяем CAPTCHA
if (!Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', 'Неправильно введено число на картинке'));
}

// Проверяем пользователя
$query = "SELECT id, login, email FROM auth_user WHERE email='$email' or login='$email'";
$data = $DB->query_row($query);
if ($DB->rows == 0) {
	Action::onError(cms_message('CMS', 'Невозможно найти пользователя с указанным логином'));
} elseif ($DB->rows != 1) {
	Action::onError(cms_message('CMS', 'Найдено более одного пользователя по запрошенным критериям'));
}

$auth_code = Misc::keyBlock(32, 1);

$query = "
	replace into auth_user_amnesia
	set
		user_id = '$data[id]',
		auth_code = '$auth_code',
		dtime = now()
";
$DB->insert($query);

// Отсылаем код для восстановление пароля на почту
$Template = new TemplateDB('cms_mail_template', 'cms', 'amnesia'); 
$Template->set($data);
$Template->set('auth_code', $auth_code);

//mail('brezetskiy.sergiy@gmail.com', 'subject', $Template->display());

$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', 'Восстановление пароля - '.CMS_HOST), $Template->display());
$Sendmail->send($data['email'], true);

// Пароль был успешно отправлен Вам на e-mail адрес
Action::setSuccess(cms_message('CMS', 'На указанный вами e-mail отправлены инструкции по восстановлению пароля'));

?>