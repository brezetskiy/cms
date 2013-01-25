<?php
/**
* Очистка очереди отправки сообщения
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$message_id = globalVar($_GET['message_id'], 0);

$query = "
	DELETE FROM maillist_queue
	WHERE message_id = '$message_id'
";
$DB->delete($query);

Action::setSuccess('Очередь очищена. Удалено '.$DB->affected_rows.' адресов.');

?>