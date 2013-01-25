<?php
/**
 * Обработка заданного вопроса
 * @package Pilot
 * @subpackage FAQ
 * @author Rudenko Ilya <rudenko@id.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$name 		= globalVar($_POST['name'], '');
$email 		= globalVar($_POST['email'], '');
$question 	= globalVar($_POST['question'], '');

/**
 * Проверяем CAPTCHA
 */
if (CMS_USE_CAPTCHA && !Auth::isLoggedIn() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', 'Неправильно введено число на картинке'));
}

/**
 * Проверяем правильность переданных данных
 */
if (!Auth::isLoggedIn()) {
	if (empty($email)) {
		Action::onError(cms_message('CMS', 'Не указан адрес e-mail'));
	} elseif (!preg_match(VALID_EMAIL, $_POST['email'])) {
		Action::onError(cms_message('CMS', 'Не указан адрес e-mail'));
	} elseif (empty($question)) {
		Action::onError(cms_message('FAQ', 'Вы не ввели текст сообщения'));
	} elseif (empty($name)) {
		Action::onError(cms_message('FAQ', 'Вы не указали своего имени'));
	}
} else {
	if (empty($question)) {
		Action::onError(cms_message('FAQ', 'Вы не ввели текст сообщения'));
	}
}

/**
 * Отсылаем сообщение
 */
$Template = new TemplateDB('cms_mail_template', 'FAQ', 'question');
$_POST['name'] = stripslashes($_POST['name']);
$_POST['question'] = stripslashes($_POST['question']);
$Template->set($_POST);
if (Auth::isLoggedIn()) { 
	$Template->set($_SESSION['auth']);
}


$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('FAQ', 'Часто задаваемый вопрос'), $Template->display());
$Sendmail->send(CMS_NOTIFY_EMAIL);

Action::setSuccess(cms_message('FAQ', 'Спасибо, Ваш вопрос принят'));

?>