<?php
/** 
 * �������� ������������� e-mail ������������ 
 * @package Pilot
 * @subpackage User
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$email = $DB->escape(stripslashes(globalVar($_REQUEST['value'], '')));
$_RESULT['status'] = $_RESULT['message'] = '';

/**
 * ��������� e-mail, ���� ������
 */
if (!empty($email)) {
	if (!preg_match(VALID_EMAIL, $email)) {
		$_RESULT['status'] = 'failed';
		$_RESULT['message'] = cms_message('CMS', '����������� ������ e-mail �����');
		exit;
	} else {
		$query = "
			SELECT * FROM auth_user
			WHERE login = '$email' OR email = '$email'
		";
		$DB->query($query);
		
		if ($DB->rows != 0) {
			$_RESULT['status'] = 'failed';
			$_RESULT['message'] = cms_message('CMS', '������������ � ��������� e-mail ������� ��� ����������');
			exit;
		}
		
		$_RESULT['status'] = 'ok';
		$_RESULT['message'] = cms_message('CMS', '����� E-mail �������� ��� �����������');
	}
} else {
	$_RESULT['status'] = 'failed';
	$_RESULT['message'] = cms_message('CMS', '�� ������ ����� e-mail');
}


?>