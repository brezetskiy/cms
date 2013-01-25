<?php
/** 
 * ���������� ����������� �� ����� ��� ����� ������������������ ������������� 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$admin = Auth::getInfo();
$switch_id = globalVar($_REQUEST['switch_id'], 0);
 
$login = $DB->query_row("select login, otp_enable, group_id from auth_user where id = '$switch_id'");

/**
 * ���� support �������� ������������ ��� �������, �������� ���, ��� �� �� ����
 */  
if(!IS_DEVELOPER && $login['group_id'] == 5){
	Action::onError(cms_message('CMS', "� ��� ��� ����� ��������� ���� ��� ��������� �������������"));
}


// ������ ������������� ��� ������� ������� ������ ��������. 
$user_block_switch = preg_split("/[^\d]+/", USER_BLOCK_SWITCH, -1, PREG_SPLIT_NO_EMPTY);
if ($DB->rows == 0) {
	Action::onError("������������ �� ������");
} elseif (!IS_DEVELOPER && (in_array($login['login'], $_sudoers) || in_array($switch_id, $user_block_switch))) { // ��� ������������� ����� �������� ������ ������ �����������
	Action::onError("� ������� ��������");
}
    
//if(!empty($login['otp_enable'])){
//	Action::onError("���������� ������������� �� ������� ������ <b>{$login['login']}</b>. � ������������ �������� ����������� ��������."); 
//}

Auth::logout();
Auth::login($switch_id, false, Misc::keyBlock(30, 1, ''), $admin['id']);
 
if (isset($_SESSION['auth']['login'])) {
	Action::setSuccess(cms_message('CMS', '�������� ���� �� ���� � ������� %s', $_SESSION['auth']['login']));
} else {
	Action::onError(cms_message('CMS', '���������� ��������� ���� � ������� ����� ������������'));
}