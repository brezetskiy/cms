<?php
/**
* ���������� ������, ������� �� ������� ��������� ������� � �������
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$message_id = globalVar($_GET['message_id'], 0);

$query = "
	UPDATE maillist_queue
	SET delivery = 'wait'
	WHERE message_id = '$message_id' AND delivery = 'error'
";
$DB->update($query);

Action::setSuccess(cms_message('Maillist', '��������� ��������� � �������: %d', $DB->affected_rows));
?>