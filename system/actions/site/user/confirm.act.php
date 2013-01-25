<?php
/**
* ������������� ����������� ������������
* @package Pilot
* @subpackage User
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

$code = globalVar($_GET['code'], '');

if (!preg_match('/^[a-z0-9]{32}$/', $code)) {
	Action::onError(cms_message('CMS', '������������ ��� �������������. ���������, ��� �� ����������� ������ ���������'));
}

$query = "
	SELECT *
	FROM auth_user
	WHERE confirmation_code = '$code'
";
$user = $DB->query_row($query);
if ($DB->rows == 0) {
	
	// ������������ ��� �������������
	Action::onError(cms_message('CMS', '������������ ��� �������������. ���������, ��� �� ����������� ������ ���������'));
	
} elseif ($user['confirmed']) {
	
	// ������������ ��� ����������� �������, � ������ ��� ��� ���
	Action::setSuccess(cms_message('CMS', '��� ������� ��� �����������. �������������� ������ ��� ����� �� ����'));
	
} else {
	// ��������� ��������
	$query = "UPDATE auth_user SET confirmed=1 WHERE id='".$user['id']."'";
	$DB->update($query);
	
	
	// ������������� ������������, ��� � ������������ ������
	if (Auth::isLoggedIn()) {
		$_SESSION['auth']['confirmed'] = 1;
		Action::setSuccess(cms_message('CMS', '�������, ��� ������� �����������'));
	} else {
		Action::setSuccess(cms_message('CMS', '��� ������� �����������. ������ �� ������ ����� �� ����'));
	}
	
}

?>