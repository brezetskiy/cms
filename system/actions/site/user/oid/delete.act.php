<?php
/**
 * Отмена авторизации
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 
$delete = globalVar($_REQUEST['oid_delete'], array());
if(empty($delete)) Action::onError("Пожалуйста, отметьте записи, которые нужно удалить.");

$user_id = Auth::isLoggedIn();
if(empty($user_id)) Action::onError("Пожалуйста, авторизируйтесь.");
  
$DB->delete("DELETE FROM auth_user_oid_identity WHERE user_id = '$user_id' AND id IN (0".implode(',', $delete).")"); 
Action::setSuccess("Записи успешно удалены.");
  
?>