<?php
/**
 * Параметры, которые передаются в почтовое сообщение о данном пользователе
 * @package Pilot
 * @subpackage Param
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$message_id = globalVar($_REQUEST['message_id'], 0);
$email = globalVar($_REQUEST['email'], '');
$TmplContent->set('message_id', $message_id);
$TmplContent->set('email', $email);

$query = "select param from maillist_queue where message_id='$message_id' and email='$email'";
$param = $DB->result($query);
$param = unserialize($param);
reset($param);
while (list($key,$val) = each($param)) {
	$TmplContent->iterate('/row/', null, array('key' => $key, 'val' => $val));
}

?>