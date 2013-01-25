<?php
 
/**
 * ���������� ����������� ����������� OpenID
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
	$_REQUEST['_return_path'] = (!empty($_SESSION['oid_widget']['return_path'])) ? $_SESSION['oid_widget']['return_path'] : HTTP_SCHEME . "://" . CMS_HOST ;
}
 
 
/**
 * �������� ������� �� �����������
 */
$action = globalVar($_REQUEST["_a"], '');
$widget_name = globalVar($_REQUEST['_own'], '');

if($action == 'auth'){  
	$openid_identifier = globalVar($_REQUEST['openid_identifier'], '');
	
	if (preg_match(VALID_EMAIL, $openid_identifier)){
		$openid_identifier = substr($openid_identifier, 0, strpos($openid_identifier, '@'));
	}
	
	$openid_provider = globalVar($_REQUEST['openid_provider'], '');
	$openid_link = globalVar($_REQUEST['openid_link'], '');
	    
	if (empty($openid_identifier)){
		AuthOID::updateParentWindow('error', (cms_message('User', "����������, ������� ��� OpenID ������������� �� ������� ����������")));
	}
	
	if (empty($openid_provider)){
		AuthOID::updateParentWindow('error', (cms_message('User', "��������� �� ���������.")));
	}
	
	if (!preg_match(VALID_FILE_NAME, $openid_identifier)){
		AuthOID::updateParentWindow('error', (cms_message('User', "����������, ������� ���������� OpenID �������������: ������� �� ��������� ��������, ����, ����� �������������, ���������� ������ ��� �����, ���������� � ����� � ������������� ������ ��� ������")));
	}
	
	$_SESSION['oid_openid_identifier'] = $openid_identifier;
	$_SESSION['oid_openid_provider'] = $openid_provider;
	 
	$openid_identifier = str_replace('{$identity}', $openid_identifier, $openid_link); 
	
	if (!AuthOID::redirectOpenID($openid_identifier, '', $widget_name)){
		AuthOID::updateParentWindow('error', (AuthOID::getErrors()));
	}
}

 
/**
 * ���������� ������, � ������� ��������
 */
$_SESSION['oid_widget_active'] = globalVar($_REQUEST['_own'], '');

    
/**
 * �������� ������ 
 */ 
if(!AuthOID::authOpenID()){
	AuthOID::updateParentWindow('warning', AuthOID::getErrors());
	exit;
}
 
AuthOID::updateParentWindow('success', AuthOID::getMessages());
exit;


?>