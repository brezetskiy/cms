<?php
/**
* �������� ��������� �� ���������� ������������
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

// ������� �������� ���������� ��������� � ���� ��������
Action::onError();

$data = globalVar($_POST['data'], array());
$user = Auth::getInfo();

if (empty($user) || !isset($user['email']) || empty($user['email'])) {
	// ��������� ������ �����������, ���������� � ���������� �� �������������.
	Action::onError(cms_message('CMS', '��������� ������ �����������, ���������� � ���������� �� �������������.'));
}

/**
 * ������������ ������� ������� � �������� (registered, confirmed, checked)
 */
$access_level = array('registered');
if ($user['checked'] == 'true') {
	$access_level[] = 'checked';
}
if ($user['confirmed'] == 'true') {
	$access_level[] = 'confirmed';
}

$count = 0;
reset($data);
while (list($id) = each($data)) {
	
	// ����������, ���� �� � ������������ ����� �������� ��� ��������
	$query = "
		SELECT * 
		FROM maillist_message 
		WHERE 
			id = '".intval($id)."' AND 
			access_level IN ('".implode("', '", $access_level)."')
	";
	$DB->query($query);
	if ($DB->rows == 0) {
		continue;
	}
	
	// ��������� � ������� �� ��������
	$query = "
		INSERT INTO maillist_queue
		SET
			message_id = '".intval($id)."',
			email = '".$_SESSION['auth']['email']."',
			expire_dtime = NOW()
		ON DUPLICATE KEY UPDATE delivery = 'wait'
	";
	$DB->insert($query);
	$count++;
}

// �� ���������� � ������� �� ��������� ��������. ����� ���������� ��������� ���� �������� - %s.
if ($count > 0) {
	Action::setSuccess(cms_message('Maillist', '�� ���������� � ������� �� ��������� ��������.<br> ����� ���������� ��������� ���� �������� - %s.', $count));
} else {
	// �������� ��������, ������� �� �� ������ ��������.
	Action::onError(cms_message('Maillist', '�������� ��������, ������� �� �� ������ ��������.'));
}

?>