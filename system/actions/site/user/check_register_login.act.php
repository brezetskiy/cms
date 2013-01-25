<?php
/** 
 * �������� ������������� ������ ������������ 
 * @package Pilot 
 * @subpackage User 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$login = $DB->escape(stripslashes(globalVar($_REQUEST['value'], '')));
$_RESULT['status'] = $_RESULT['message'] = '';

/**
 * ��������� �����, ���� ������
 */
if (!empty($login)) {
	if (!preg_match(VALID_LOGIN, $login)) {
		$_RESULT['status'] = 'failed';
		$_RESULT['message'] = cms_message('CMS', '����� ������ ����� ����� �� 1 �� 20 �������� � ��������� ������ ������� ��������, ����� � ������� _-.+!@#$%^~()');
		exit;
	} else {
		$query = "
			SELECT * FROM auth_user
			WHERE login = '$login' OR email = '$login'
		";
		$DB->query($query);
		
		if ($DB->rows != 0) {
			$_RESULT['status'] = 'failed';
			$_RESULT['message'] = cms_message('CMS', '������������ � ��������� ������� ��� ����������');
			exit;
		}
		
		$_RESULT['status'] = 'ok';
		$_RESULT['message'] = cms_message('CMS', '����� ��������');
	}
}


?>