<center><div style="position:absolute; top:20%; left:40%;"><img src="/design/ukraine/img/cp/loader.gif" border="0"></div></center>

<?php
/**
 * ���������� ����������� ����������� Google
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * ������������ ������ ��� ���� ������������ ������� AuthOID
 */
if(empty($_REQUEST['_return_path'])){
	$_REQUEST['_return_path'] = (!empty($_SESSION['oid_widget']['return_path'])) ? $_SESSION['oid_widget']['return_path'] : HTTP_SCHEME . "://" . CMS_HOST;
}


/**
 * �������� ������� �� �����������
 */
$action = globalVar($_REQUEST["_a"], '');
if($action == 'auth'){
	$widget_name = globalVar($_REQUEST['_own'], ''); 
	AuthOID::redirectGoogle('', $widget_name);	
}

 

/**
 * ���������� ������, � ������� ��������
 */
$_SESSION['oid_widget_active'] = globalVar($_REQUEST['_own'], '');


/**
 * �������� ������ 
 */
if(!AuthOID::authGoogle()){
	AuthOID::updateParentWindow('warning', AuthOID::getErrors());
	exit;
} 
     
AuthOID::updateParentWindow('success', AuthOID::getMessages()); 
exit;

?>