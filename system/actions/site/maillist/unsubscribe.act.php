<?php
/**
 * Отписка пользователя от всех рассылок
 * 
 * Это событие вызывается только из тела письма. Когда пользователь кликает по ссылке
 * "отписаться от рассылки".
 * 
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$email = globalVar($_GET['email'], '');

$query = "select id from auth_user where email='$email'";
$user_id = $DB->result($query);
if ($DB->rows == 0) {
	echo cms_message('Maillist', 'Пользователь с указанным e-mail адресом не найден.');
	exit;
}

$query = "delete from maillist_user_category where user_id='$user_id'";
$DB->delete($query);
echo cms_message('Maillist', 'Вы успешно были отписаны от %d рассылок.', $DB->affected_rows);
exit;


?>