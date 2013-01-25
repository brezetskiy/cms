<?php
/**
 * ���������� ����� ����������� / �����������, ��� �������� ������ ������� oid/widget_box
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 
$_RESULT['javascript'] = "";


/**
 * �������� ������ 
 */
if (empty($_SESSION['oid_widget']['name'])){
	$_RESULT['javascript'] .= "delta_error('".cms_message('User', "���� ������ ���������.")."');";
	exit;
}

  
/**
 * ������� ���������� ����������
 */
if (!empty($_SESSION['oid_clarify_auto'])){
	unset($_SESSION['oid_clarify_auto']);
}


/**
 * ������� �������
 */
$action = globalVar($_REQUEST['action'], '');
if (empty($action)){
	$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������� ������ �������� �������. ����������, ���������� � ���. ���������.")."');";
	exit;
}


/**
 * �������� ��������
 */
$_return_path = globalVar($_REQUEST['_return_path'], '/User/Info/');


/**
 * ��������� Email ��� ����������� 
 */
if ($action == 'validate_email_auth'){
	
	/**
	 * ��������� ���������� email
	 */
	$email = globalVar($_REQUEST['email'], '');
	$_SESSION['oid_clarify_manual']['email'] = $email;
	
	if (empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������� ������ e-mail.")."');";
		exit;
	}
	
	if (!preg_match(VALID_EMAIL, $email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������ ������������ e-mail.")."');";
		exit;
	}
	
	/**
	 * �������� ��������� ������������ � ��������� email
	 */
	$user = $DB->query_row("SELECT id, otp_enable, otp_type FROM auth_user WHERE login='$email' or email='$email'");
	if ($DB->rows == 0){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "��������� ���� e-mail �� ������.")."');";
		exit;
	}
 
	/** 
	 * ��������� ����� �������� ������
	 */
	$_SESSION['oid_clarify_manual']['action'] = 'auth';
	$_SESSION['oid_clarify_manual']['user_id'] = $user['id']; 
	
	$_RESULT['javascript'] .= "delta_success('".cms_message('User', "������� ������ <b>$email</b> ����������. ����������, �������� ���������.")."');";
	$_RESULT['javascript'] .= "oid_widget__box__clarify_manual_open('".$_SESSION['oid_widget']['name']."', 'auth', '');";
	
	/**
	 * ������� ���������� �����
	 */  
	if (Auth::isHacker()) {     
		$_RESULT['javascript'] .= "$('#oid_widget__".$_SESSION['oid_widget']['name']."_clarify_manual_auth_captcha').show();"; 
	}
	 
	exit;
		
	
/**
 * ��������� ������ � ��������� ����������, ��� ���������� ��� ����������� �������� ������������
 */
} elseif ($action == 'auth') {
			
	$remember = globalVar($_REQUEST['remember'], 0);
				
	/**
	 * �������� ������
	 */
	if (!empty($_SESSION['oid_clarify_manual'])){
		$user_id = globalVar($_SESSION['oid_clarify_manual']['user_id'], 0);
	}
	
	if (empty($user_id)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "���� ������ ���������. ����������, ������� <b>��������</b> � ��������� ������� �����������. ��� �������������� �������� ������� ����������.")."');";
		exit;
	}
	  
	/**
	 * ������� ���������� �����
	 */    
	if (Auth::isHacker() && empty($_SESSION['oid_clarify_manual_captcha'])) { 
		$_SESSION['oid_clarify_manual_captcha'] = true;   
		 
		$_RESULT['oid_widget__captcha'] = Captcha::createHtml();   
		$_RESULT['javascript'] .= "$('#oid_widget__".$_SESSION['oid_widget']['name']."_clarify_manual_auth_captcha').show();"; 
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "��������� ���-�� ��������� ������� �����. ����������, ������� ����� �� ��������.")."');";
		exit;
	}
	
	/**
	 * ��������� CAPTCHA, ���� � ��� ������ �����
	 */
	if (Auth::isHacker() && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
		$captcha_new_src = "/tools/cms/site/captcha.php?uid=".$_REQUEST['captcha_uid']."&refresh=".round(rand()*100000);
		
		$_RESULT['javascript'] .= "$('#img_captcha_uid').attr('src', '$captcha_new_src');";  
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "����������� ������� ����� �� ��������.")."');";
		exit;
	}
	
	/**  
	 * �������� ������
	 */  
	$passwd = globalVar($_REQUEST['passwd'], '');
	if (empty($passwd)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "����������, ������� ������.")."');";
		exit;
	}
	
	$user = $DB->query_row("SELECT id, email, passwd, otp_enable, otp_type FROM auth_user WHERE id = '$user_id' AND passwd = '".md5($passwd)."'");
	if ($DB->rows == 0) {   
		Auth::logLogin(0, time(), $user_id, $passwd);
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������ �������� ������.")."');";
		exit;
	}
	
	/**
	 * OTP ������
	 */
	if(!empty($user['otp_enable']) && !AuthOTP::checkAccess($user['id'])){
		AuthOTP::sessionActivate($user_id); 
		
		$_RESULT['javascript'] .= "document.location.reload();";
		exit;   
	}
	 
	/**
	 * �����������
	 */
	$logged_in = Auth::login($user_id, $remember, null); 
	if (!$logged_in) {
		Auth::logLogin($user_id, time());
		
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������ � IP ������������ ��� ��� ������� �������� ���������������.")."');";
		exit;
	} 
	  
	unset($_SESSION['oid_clarify_manual_captcha']); 
	 
	/**
	 * ������������� ���� �� ��� ������
	 */
	if(!empty($user['otp_enable']) && $remember){
		AuthOTP::setAccess($user_id);  
	}
	
	/**
	 * ��������� �� �������� �����������
	 */
	$message = cms_message('User', "�����������, �� ������� ����������������");  
	$_SESSION['ActionReturn']['success'][md5($message)] = $message;

	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
	
	$_RESULT['javascript'] = "window.location = '$_return_path'";
	exit;
	
	
/**
 * ��������� Email ��� ����������� 
 */
} elseif ($action == 'validate_email_register'){
	
	/**
	 * ��������� ���������� email
	 */
	$email = globalVar($_REQUEST['email'], '');
	$_SESSION['oid_clarify_manual']['email'] = $email;
	
	if (empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������� ������ e-mail.")."');";
		exit;
	}
	
	if (!preg_match(VALID_EMAIL, $email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������ ������������ e-mail.")."');";
		exit;
	}
	
	/**
	 * �������� ��������� ������������ � ��������� email
	 */
	$user = $DB->query_row("SELECT id, otp_enable FROM auth_user WHERE login='$email' or email='$email'");
	if ($DB->rows > 0){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "������������ � ��������� e-mail ������� ��� ����������.")."');";
		exit;
	}
		
	/**
	 * ��������� ����� �����������
	 */
	$_SESSION['oid_clarify_manual']['action'] = 'register';
	
	if (CMS_USE_CAPTCHA){
		$_RESULT['oid_widget__'.$_SESSION['oid_widget']['name'].'_clarify_manual_captcha_html'] = Captcha::createHtml();
	}
	
	$_RESULT['javascript'] .= "oid_widget__box__clarify_manual_open('".$_SESSION['oid_widget']['name']."', 'register', '".gen_password(8)."');";
	$_RESULT['javascript'] .= "delta_success('".cms_message('User', "����� <b>$email</b> ��������. ����� ������� ����� ������� ������, ����������, ��������� ����� ���� � ������� ������ <b>������������������<b>.")."');";
	
	exit;
 
	
/**
 * �����������
 */
} elseif($action == 'register'){
	
	/**
	 * �������� ������
	 */
	if(!empty($_SESSION['oid_clarify_manual'])){
		$email = globalVar($_SESSION['oid_clarify_manual']['email'], '');
	}
	
	if(empty($email)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "���� ������ ���������. ����������, ������� <b>��������</b> � ��������� ������� �����������. ��� �������������� �������� ������� ����������.")."');";
		exit;
	}
	 
	/**
	 * �������� Captcha
	 */
	if (CMS_USE_CAPTCHA && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "����������� ������� ����� �� ��������.")."');";
		exit;
	}
 
	$name = globalVar($_REQUEST['name'], ''); 
	$passwd = globalVar($_REQUEST['passwd'], ''); 
	
	$name = trim($name); 
	if(empty($name)){
		$name = $email;
	}
	
	$passwd = trim($passwd); 
	if(empty($passwd)){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', "����������, ������� ��� ������.")."');";
		exit;
	}

	$result = AuthOID::oidRegister(array('email' => $email, 'name' => $name, 'passwd' => $passwd));
	if(!$result){
		$_RESULT['javascript'] .= "delta_error('".cms_message('User', AuthOID::getErrors())."');";
		exit;
	}
	 
	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
	
	
	$message = cms_message('User', "�����������, �� ������� ������������������");  
	$_SESSION['ActionReturn']['success'][md5($message)] = $message;  
	
	$_RESULT['javascript'] = "window.location = '$_return_path'";
	exit;
}



?>