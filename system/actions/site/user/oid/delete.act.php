<?php
/**
 * ������ �����������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 
$delete = globalVar($_REQUEST['oid_delete'], array());
if(empty($delete)) Action::onError("����������, �������� ������, ������� ����� �������.");

$user_id = Auth::isLoggedIn();
if(empty($user_id)) Action::onError("����������, ���������������.");
  
$DB->delete("DELETE FROM auth_user_oid_identity WHERE user_id = '$user_id' AND id IN (0".implode(',', $delete).")"); 
Action::setSuccess("������ ������� �������.");
  
?>