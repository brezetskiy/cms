<?php
/**
* ������ ������ � ������� �� ��������
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/
$test = globalVar($test, 0);
$message_list = globalVar($_REQUEST[$table_id]['id'], array());

// ��������� �������� �� ���������, ������� ����������� � �������
if (empty($message_list)) {
	Action::setError(cms_message('Maillist', '�� ������� ���������, ������� ���������� ��������.'));
	Action::onError();
}

$total_count = 0;
reset($message_list);
while(list(,$message_id) = each($message_list)) {
	// ���������� �� ������������ ������
	$query = "select subject from maillist_message where id='$message_id'";
	$subject = $DB->result($query);	
	
	// ��������� �� ����������� ���������� ������ ������
	$size = Maillist::getMessageSize($message_id);
	if ($size > MAILLIST_ATTACHMENT_MAX_SIZE) {
		Action::setError(cms_message('Maillist', '���������� ����� �������� %d ����, ������ �������� ������ "%s" - %d ����.', MAILLIST_ATTACHMENT_MAX_SIZE, $subject, $size));
		continue;
	}
	
	$total_count += Maillist::queue($message_id, $test);
	
	// ��������� ����������
	Action::setSuccess(cms_message('Maillist', '������ "%s" ����� ���������� %d �����������', $subject, $total_count));
}

?>