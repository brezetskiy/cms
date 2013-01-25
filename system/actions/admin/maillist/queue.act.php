<?php
/**
* Ставит письмо в очередь на рассылку
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/
$test = globalVar($test, 0);
$message_list = globalVar($_REQUEST[$table_id]['id'], array());

// Проверяем выделено ли сообщение, которое добавляется в очередь
if (empty($message_list)) {
	Action::setError(cms_message('Maillist', 'Не выбрано сообщение, которое необходимо отослать.'));
	Action::onError();
}

$total_count = 0;
reset($message_list);
while(list(,$message_id) = each($message_list)) {
	// Информация об отправляемом письме
	$query = "select subject from maillist_message where id='$message_id'";
	$subject = $DB->result($query);	
	
	// Проверяем на максимально допустимый размер письма
	$size = Maillist::getMessageSize($message_id);
	if ($size > MAILLIST_ATTACHMENT_MAX_SIZE) {
		Action::setError(cms_message('Maillist', 'Допустимый объем вложений %d байт, размер вложений письма "%s" - %d байт.', MAILLIST_ATTACHMENT_MAX_SIZE, $subject, $size));
		continue;
	}
	
	$total_count += Maillist::queue($message_id, $test);
	
	// Формируем статистику
	Action::setSuccess(cms_message('Maillist', 'Письмо "%s" будет отправлено %d получателям', $subject, $total_count));
}

?>