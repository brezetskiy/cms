<?php
/** 
 * Отсылает пользователю письмо с подверждением e-mail адреса 
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

if (!isset($id)) {
	// Этот скрипт вызывается при регистрации пользователя, там параметр id уже определён
	$id = globalVar($_GET['id'], 0);
}

$query = "SELECT * FROM auth_user WHERE id='$id'";
$user = $DB->query_row($query);

if ($DB->rows == 0) {
	// Пользователя с таким логином - не существует.
	Action::onError(cms_message('CMS', 'Пользователя с указанным логином - не существует.'));
}

if ($user['confirmed'] == 'true') {
	// аккаунт уже подтвержден
	Action::onError(cms_message('CMS', 'Аккаунт уже подтвержден. Письмо с повторным запросом не было выслано.'));
}

$Template = new TemplateDB('cms_mail_template', 'User', 'confirm');
$Template->set('email', $user['email']);
$Template->set('confirm_code', $user['confirmation_code']);
$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', 'Регистрация на %s: подтверждение e-mail', CMS_HOST), $Template->display());

$Sendmail->send($user['email']);

Action::setSuccess(cms_message('CMS', 'На указанный Вами адрес E-mail было выслано письмо с просьбой подтверждения регистрации. Ваш аккаунт будет активирован после перехода по ссылке, указанной в письме.'));

?>