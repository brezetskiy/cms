<?php

/**
 * ����� ����������� ����������� OpenID �����������
 *
 * @package Pilot
 * @subpackage Auth
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


require_once(LIBS_ROOT.'openid/OpenID.class.php'); 
require_once(LIBS_ROOT.'openid/GoogleOpenID.class.php'); 
require_once(LIBS_ROOT.'openid/TwitterOAuth.class.php'); 
   

/**
 * ����� ����������� ����������� OpenID �����������
 *
 */
class AuthOID {
	
	
	/**
	 * ���������
	 * 
	 * @var array
	 */
	static private $messages = array();
	
	
	/**
	 * ������
	 *  
	 * @var array
	 */
	static private $errors = array();
	
	
	/**
	 * ����������, ������� ���������� ��������� �� ������� ����������
	 *
	 * @var array
	 */
	static private $info =  array('source' => '', 'id' => '', 'login' => '', 'email' => '', 'first_name' => '', 'last_name' => '');
	
	
	/**
	 * ���������� ���������� � ������������, ��� ����������� � ������ ����������
	 *
	 * @return array
	 */
	static public function getInfo(){
		$info = self::$info; 
		
		/**
		 * ����������, ��� ��������� ��������� ����� �����������.
		 * ������������� ����������� ���� email, login ��� id ������������ �� ������� ���������� � source - ��� ���������
		 */
		if((empty($info['id']) && empty($info['login']) && empty($info['email'])) || empty($info['source'])) {
			self::$errors[] = cms_message('User', "��������� ��������� ������ ����� ������. ����������, �������������� �������� ������� ���������� ".((!Auth::isLoggedIn()) ? "��� ��������������� � ������� ����������� ����� �����������" : ""));
			return false;
		}
		 
		/**
		 * ���������� ������������� ������������ �� ������� ����������
		 */ 
		$identity = (!empty($info['login'])) ? $info['login'] : '';
		if(empty($identity)) $identity = (!empty($info['email'])) ? $info['email'] : '';
		if(empty($identity)) $identity = $info['id'];
		$info['identity'] = $identity;
		
		/**
		 * ��� ������������
		 */ 
		$name_structure = array(); 
		if(!empty($info['first_name'])) $name_structure[] = $info['first_name'];
		if(!empty($info['last_name'])) $name_structure[] = $info['last_name'];
		$info['name'] = trim(implode(' ', $name_structure));
		
		return $info;
	}
	
	
	/**
	 * ���������� ������ ������, ��� �������� � ���� �����������
	 *
	 * @return array
	 */
	static public function getErrors($separator='<br/>', $need_array=false){
		return ($need_array) ? self::$errors : implode($separator, self::$errors);
	}
		
	 
	/**
	 * ���������� ������ ���������, ��� ���� ��������� � ���� �����������
	 *
	 * @return array
	 */ 
	static public function getMessages($separator='<br/>', $need_array=false){
		return ($need_array) ? self::$messages : implode($separator, self::$messages);
	}
	 
	
	/**
	 * ����������� ����-������
	 * 
	 * @param string $status
	 * @param string $message
	 * @return void
	 */
	static public function updateParentWindow($status, $message){
		$return_path = (!empty($_SESSION['oid_widget']['return_path'])) ? $_SESSION['oid_widget']['return_path'] : HTTP_SCHEME . "://" . CMS_HOST;
		
		echo "<script type='text/javascript'>";
	    echo "window.close();";      
	     
	    if(empty($message) || $status == 'success') {  
	    	$_SESSION['oid_widget']['reloaded'] = true;  
	    	echo "window.opener.location = '$return_path';";    
	    	
	    } else {   
	   		echo "window.opener.delta_error('".addslashes($message)."');";   
	    }  
	    
	    echo "</script>"; 
	    exit;
	}
	
	
	/**
	 * ���������� ������ �����������
	 *
	 * @return array
	 */ 
	static public function getProviders($providers = array()){
		global $DB;
		
		$providers = $DB->query("
			SELECT id, icon_inline, name_".LANGUAGE_CURRENT." as name
			FROM auth_user_oid_provider 
			WHERE active = 1 ".where_clause('uniq_name', $providers)."
			ORDER BY priority
		");	
		
		reset($providers);
		while(list($index, $row) = each($providers)){ 
			$providers[$index]['icon'] = "/".UPLOADS_DIR."auth_user_oid_provider/icon_inline/".Uploads::getIdFileDir($row['id']).".".$row['icon_inline'];
		}
		
		return $providers;
	}
	
	
	/**
	 * ���������� ����� ����� OpenID ��������������
	 *
	 * @param string $widget_name
	 * @param string $widget_type
	 * @param int $provider_id
	 * @return string 
	 */
	public static function displayOpenIDForm($widget_name, $widget_type, $provider_id){
		global $DB;
		
		/**
		 * ���������
		 */
		$provider = $DB->query_row("
			SELECT id, uniq_name, name_".LANGUAGE_CURRENT." as name, openid_link
			FROM auth_user_oid_provider 
			WHERE id = '$provider_id' AND openid_enable = 1
		");
		
		if($DB->rows == 0){
			return false;
		}
		 
		$TmplOpenIdForm = new Template("user/oid/widget_$widget_type/form_openid");
		$TmplOpenIdForm->setGlobal("oid_widget_uniq_name", $widget_name);   
		$TmplOpenIdForm->set("provider", $provider);
	
		$openid_form = json_encode(@iconv('windows-1251', 'utf-8', $TmplOpenIdForm->display()));
		$openid_form = substr($openid_form, 1, strlen($openid_form)-2); 
		return $openid_form;
	}
	
	
	/**
	 * ����� ����� ����������� ����������� ����������� �����������
	 *
	 * @param string $widget_name
	 * @param string $template
	 * @param string $return_path
	 * @param string $action
	 * @param string $providers_filter
	 * @return string
	 */
	static public function displayWidget($widget_name, $template = 'inline', $return_path = CURRENT_URL_FORM, $action = '', $providers_filter = ''){
		global $DB;
		 
		$templates = array('inline', 'context', 'box'); 
		 
		/**
		 * ����� ����������� ������
		 */
		if(!in_array($template, $templates)) {
			return "<div style='color:red; padding:10px; margin:10px; border:1px solid #ccc; width:500px;'>".
				   "������ <b>$template</b> �� ���������. �������� ��������� ���� ��������: ".implode(', ', $templates).
				   "</div>";
		}
		 
		if(empty($action) && $template == 'box') $action = 'auth';
		 
		$_SESSION['oid_widget']['name'] = $widget_name; 
		$_SESSION['oid_widget']['return_path'] = $return_path;  
		$_SESSION['oid_widget']['action'] = $action;
		
		/** 
		 * �������� ����������
		 */
		$providers_filter = array_filter(explode(',', $providers_filter));
		$providers = $DB->query("
			SELECT id, uniq_name, name_".LANGUAGE_CURRENT." as name, openid_enable, openid_link, icon_$template as icon
			FROM auth_user_oid_provider 
			WHERE active = 1 ".where_clause('uniq_name', $providers_filter)."
			ORDER BY priority
		");
		
		/**
		 * ���������� ������� ��� �������
		 */
		$TmplWidget = (!empty($action)) ? new Template('user/oid/widget_'.$template.'/'.$action) : new Template('user/oid/widget_'.$template);
		$TmplWidget->setGlobal('oid_widget_uniq_name', $widget_name); 
		 
		reset($providers);
		while(list($index, $row) = each($providers)){
			  
			/**
			 * ��������� �����������, ��� �������� ����� �������� OAuth 2.0
			 */
			if($row['uniq_name'] == 'facebook'){ 
				$_SESSION['oid_facebook_state'] = $widget_name.'__'.md5(uniqid(rand(), TRUE));  
				$_return_url = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/facebook/';       
				$row['dialog_url'] = "https://www.facebook.com/dialog/oauth?client_id=" . AUTH_OID_FACEBOOK_APP_ID . "&redirect_uri=$_return_url&state=" . $_SESSION['oid_facebook_state']."&_own=".$widget_name;
				
			} elseif($row['uniq_name'] == 'vkontakte'){
				$_return_url = urlencode(HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/vkontakte/?_own='.$widget_name);    
				$row['dialog_url'] = "http://api.vkontakte.ru/oauth/authorize?client_id="  . AUTH_OID_VKONTAKTE_APP_ID . "&redirect_uri=$_return_url&response_type=code";
			
			} elseif($row['uniq_name'] == 'twitter'){   
				$row['dialog_url'] = "/action/user/oid/twitter/?_a=auth&_own=".$widget_name; 
				 
			} elseif($row['uniq_name'] == 'google'){
				$row['dialog_url'] = "/action/user/oid/google/?_a=auth&_own=".$widget_name;
			
			} elseif($row['uniq_name'] == 'yandex'){ 
				 $_return_url = urlencode(HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/yandex/?_own='.$widget_name);    
				 // $row['dialog_url'] = "https://oauth.yandex.ru/authorize?client_id="  . AUTH_OID_YANDEX_APP_ID . "&redirect_uri=$_return_url&response_type=code&display=popup";
				 // �����: OAuth 2.0 ������ ���������� ������ 502 Bad Gateway. � ����� � ���� ��������� ������ ��� ����������, ��� OpenID ���������
			}
			   
			$row['icon'] = "/".UPLOADS_DIR."auth_user_oid_provider/icon_$template/".Uploads::getIdFileDir($row['id']).".".$row['icon'];
			$TmplWidget->iterate('/providers/', null, $row);
		}
		 
		/**
		 * ���� ��������������� ������ �� �������, ��������� ���������� � ��� ������
		 */
		if(empty($_SESSION['oid_widget_active']) || $_SESSION['oid_widget_active'] != $widget_name){
			return $TmplWidget->display();
		}
		 
		/**
		 * ����� ����� ��������� ������������ ���������� 
		 */ 
//		if(!empty($_SESSION['oid_clarify_auto'])){
//			$TmplWidget->setGlobal('oid_clarify_auto_enable', true);
//			$TmplWidget->setGlobal('oid_clarify_auto_action', (!empty($_SESSION['oid_clarify_auto']['action'])) ? $_SESSION['oid_clarify_auto']['action'] : "");
//			$TmplWidget->setGlobal('oid_clarify_auto_user_id', (!empty($_SESSION['oid_clarify_auto']['user_id'])) ? $_SESSION['oid_clarify_auto']['user_id'] : 0);
//			  
//			reset($_SESSION['oid_clarify_auto']);
//			while(list($param_name, $param_value) = each($_SESSION['oid_clarify_auto'])){
//				$TmplWidget->iterate('/clarify_hidden/', null, array('name' => $param_name, 'value' => $param_value));
//			} 
//		} 
		   
		/**
		 * ������� ������
		 */
		if(Auth::isHacker()){   
			$TmplWidget->set('manual_captcha_html', Captcha::createHtml());
		}
		
		/**
		 * Captcha ��� �����������
		 */
		if(CMS_USE_CAPTCHA && !empty($_SESSION['oid_clarify_manual']['action']) && $_SESSION['oid_clarify_manual']['action'] == 'register'){
			$TmplWidget->set('manual_captcha_html', Captcha::createHtml());
		}
		
		return $TmplWidget->display();
	}
	
	
	/**
	 * ����� ����������� ������ ��� ������ BOX - �������
	 *
	 * @return string
	 */ 
	static public function displayBoxStarter($action){ 
		$TmplBoxStarter = new Template('user/oid/widget_box');
		$TmplBoxStarter->set('action', $action);
		return $TmplBoxStarter->display();
	}
	
	
	/**
	 * �������� �������������� � ������������
	 *
	 * @param int $user_id
	 * @param string $identity
	 * @param int $provider_id
	 * @param string $provider_name
	 * @return bool
	 */
	static function bindProviderIdentity($user_id, $identity, $provider_id, $provider_name = ''){
		global $DB;
		
		$DB->query("LOCK TABLES auth_user_oid_identity write, auth_user_oid_identity as tb_identity read");
		
		$identity_user_id = $DB->result("
			SELECT user_id 
			FROM auth_user_oid_identity as tb_identity 
			WHERE provider_id = '$provider_id' 
				AND identity = '$identity'
		");
		
		if($identity_user_id == $user_id){ 
			$DB->query("UNLOCK TABLES");    
			self::$messages[] = cms_message('User', "������� ������ <b>$identity</b> ��� ��������� � ���");
			return true; 
		}
		
		if($DB->rows > 0){  
			$DB->query("UNLOCK TABLES");
			self::$errors[] = cms_message('User', "������� ������ <b>$identity</b> ��� ��������� � ������� ������������");
			return false;
		}
		
		$DB->insert("INSERT IGNORE INTO auth_user_oid_identity SET user_id = '$user_id', provider_id = '$provider_id', identity = '$identity'"); 
		$DB->query("UNLOCK TABLES");
		
		self::$messages[] = cms_message('User', "����� � ����� ������� ������� <b>$identity</b> �� ������� ���������� $provider_name ������� �����������");
		return true;
	}
	 
	
	/**
	 * ����������� ������ ����� �� ��������� ������, 
	 * ���������� ��� ����������� ����������� ������ �� �����������
	 *
	 * @return bool
	 */
	static private function oidLogin($register = true){
		global $DB;
		
		$_SESSION['auth_user_mode'] = "login";
	
		/**
		 * ����������, ��� ��������� ��������� ����� �����������.
		 */
		$info = self::getInfo();
		if(empty($info)) return false;
		
		$identity = $info['identity'];
		
		/**
		 * ���������
		 */
		$provider = $DB->query_row("SELECT id, name_".LANGUAGE_CURRENT." as name FROM auth_user_oid_provider WHERE uniq_name = '{$info['source']}'");
		if($DB->rows == 0) {
			self::$errors[] = cms_message('User', "��������� �� ���������. ����������, �������������� �������� ������� ���������� ".((!Auth::isLoggedIn()) ? "��� ��������������� � ������� ����������� ����� �����������" : ""));
			return false;
		}
		
		/**
		 * ���� ������������ ��� ������������� � �������, 
		 * ����������� ��� ���������� ������������� �� ������� ���������� � ������� ������� ������ ������������
		 */
		if(Auth::isLoggedIn()){
			return self::bindProviderIdentity(Auth::getUserId(), $identity, $provider['id'], $provider['name']);
		}
		
		/**
		 * ���� ������������ �� ������������� � �������, 
		 * ������������ ������� ������, ��� ������������� $identity
		 */
		$user = $DB->query_row("
			SELECT tb_user.id, tb_user.passwd, tb_user.otp_enable, tb_user.otp_type
			FROM auth_user as tb_user 
			INNER JOIN auth_user_oid_identity as tb_identity ON tb_identity.user_id = tb_user.id
			WHERE tb_identity.provider_id = '{$provider['id']}' 
				AND tb_identity.identity = '$identity'
		");
		
		if($DB->rows > 0){
			$source = (!empty($_SESSION['oid_widget']['source'])) ? $_SESSION['oid_widget']['source'] : 'site';
			
			/**
			 * OTP ������
			 */
			if( ( $user['otp_enable'] || $source == 'admin' ) && !AuthOTP::checkAccess($user['id']) ){ 
				AuthOTP::sessionActivate($user['id'], $source); 
				return false; 
			} 
			   
			$logged_in = Auth::login($user['id'], false, null);
			if (!$logged_in) {
				Auth::logLogin(0, time(), $identity, $user['passwd']); 
				self::$errors[] = cms_message('CMS', '������ � IP ������������ ��� ��� ������� �������� ���������������');
				return false;
			}
			
			return true;
		}
		 
		/**
		 * ���� � ���� �� ���������� ����� ����� ������� $identity � �����-���� ������� �������,
		 * ������ �������������� �������� �� email � login ������������� 
		 */
		$user_id = $DB->result("SELECT id FROM auth_user WHERE login = '$identity' OR email = '$identity'");
		if ($DB->rows > 0){
			self::$errors[] = cms_message('User', "������������ <b>$identity</b> ���������������, �� �� ��������� �� ������� ������� ���������� <b>{$provider['name']}</b>. ����������, ��������������� � ������� ����������� ����� ����������� � �������� ����� ����� �������� �������� �� ��������  <a href='/User/OpenID/'>��������� OpenID �����������</a>. ��� �������������, �������������� <a href='/User/Reminder/'>������ ����������� ������</a>");
			return false;
		}
		 
		self::$errors[] = cms_message('User', "������� ������ <b>$identity</b> ���������� <b>{$provider['name']}</b> �� ���������� �� �� ����� �� �������������. ����������, ��������������� � ������� ����������� ����� ����������� � �������� ����� ����� �������� �������� �� ��������  <a href='/User/OpenID/'>��������� OpenID �����������</a>. ��� �������������, �������������� <a href='/User/Reminder/'>������ ����������� ������</a>");
		return false;
	}
	
	
	/**
	 * ����������� ������������ � ������� ������ OpenID ����������
	 *
	 * @param array $info
	 * @return mixed 
	 */
	static public function oidRegister($info){
		global $DB;
		 
		$_SESSION['auth_user_mode'] = "register";
		
		/**
		 * ������� �� ������ ��������� �� ��������� � ���������� ������� �����������
		 */
		if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
		if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
		
		/**
		 * ����������, ��� ��������� ��������� ����� �����������.
		 */
		if(empty($info)) $info = self::getInfo();
		if(empty($info)) return false;
		
		$identity = (!empty($info['identity'])) ? $info['identity'] : $info['email'];
		
		/**
		 * ���������
		 */
		if(!empty($info['source'])){
			$provider = $DB->query_row("SELECT id, name_".LANGUAGE_CURRENT." as name FROM auth_user_oid_provider WHERE uniq_name = '{$info['source']}'");
		
			/**
			 * ��������, �� ��������� �� ������� ������ ���������� � ������-���� ������������
			 */
			$user = $DB->query_row("
				SELECT tb_user.id
				FROM auth_user as tb_user 
				INNER JOIN auth_user_oid_identity as tb_identity ON tb_identity.user_id = tb_user.id
				WHERE tb_identity.provider_id = '{$provider['id']}'
					AND tb_identity.identity = '$identity'
			");
			 
			if($DB->rows > 0){
				self::$errors[] = cms_message('User', "������� ������ <b>$identity</b> ���������� <b>{$provider['name']}</b> ��� ���������� �� ������ �������������");
				return false;
			}
		}
		
		/**
		 * ��������� ������������ ����������
		 */
		if(empty($info['email']) || (empty($info['name']))){
			$_SESSION['oid_clarify_auto'] = $info;  
			$_SESSION['oid_clarify_auto']['action'] = 'register';
			return false;
		}
		
		$user_name  = globalVar($info['name'], '');
		$user_name  = trim($user_name);
		$user_email = strtolower(trim(globalVar($info['email'], '')));
		$user_login = globalVar($info['login'], '');
		
		if(empty($user_login)) $user_login = $user_email;
		
		$confirm_code   = strtolower(Misc::randomKey(32));
		$user_password 	= (!empty($info['passwd'])) ? $info['passwd'] : gen_password(8);
		$user_group 	= $DB->result("SELECT id FROM auth_user_group WHERE uniq_name = 'payer_phisical'");
		if(empty($user_group)) $user_group = 2;
		
		$register_ip    = constant('HTTP_IP');
		$register_local_ip = constant('HTTP_LOCAL_IP');
		
		$cookie_referer 	 = substr(globalVar($_COOKIE['referer'], ''), 0, 255);
		$cookie_refered_page = substr(globalVar($_COOKIE['refered_page'], ''), 0, 255);
		$cookie_referral_hit = substr(globalVar($_COOKIE['partner_hit'], ''), 0, 32);
		$cookie_referral_id  = globalVar($_COOKIE['partner'], 0);
 
		/**
		 * ��������� ������������ ���������� ������
		 */
		if(empty($user_name)) self::$errors[] = cms_message('CMS', "�� ������� ���");
		if(empty($user_email)) self::$errors[] = cms_message('CMS', "�� ������ e-mail �����");
		if(!preg_match(VALID_EMAIL, $user_email) && !empty($user_email)) self::$errors[] = cms_message('CMS', "����������� ������ e-mail �����"); 
		if(!empty(self::$errors)) return false;
		
		$DB->query("SELECT id FROM auth_user WHERE email='$user_email' OR login='$user_email'");
		if ($DB->rows > 0){  
			self::$errors[] = cms_message('CMS', "������������ <b>$user_email</b> ��� ���������������. �������������� <a href='/User/Reminder/'>������ ����������� ������</a>"); 
			return false;
		}
		
		$DB->query("LOCK TABLES auth_user WRITE, auth_user_history WRITE, site_structure_site_alias WRITE");
		
		/**
		 * ���������� ����, �� ������� �������������� ������������
		 */
		$site_id = $DB->result("select site_id from site_structure_site_alias where url = '".globalVar($_SERVER['HTTP_HOST'], '')."'", 0);
		
		/**
		 * ��������� �������� ������ � ������������
		 */
		$user_id = $DB->insert("
			INSERT INTO auth_user 
			SET login 				= '$user_login',
				user_group_id 		= '$user_group', 
				email 				= '$user_email',
				passwd 				= '".md5($user_password)."',
				passwd_crypt 		= '".crypt($user_password)."',    
				confirmation_code 	= '$confirm_code',
				site_id 			= '$site_id',
				registration_dtime  = NOW(),  
				register_ip 		= '$register_ip',
				register_local_ip 	= '$register_local_ip',
				referer 			= '".$DB->escape($cookie_referer)."',
				refered_page 		= '".$DB->escape($cookie_refered_page)."',
				name                = '$user_name'
		");
		
		/**
		 * ��������� ������������ � �������
		 */  
		$history_id = $DB->insert("
			INSERT INTO auth_user_history 
			SET user_id   = '$user_id',
				auth_code = '$confirm_code', 
				ip 		  = '$register_ip',
				local_ip  = '$register_local_ip',
				name      = '$user_name' 
		");

		$DB->query("UNLOCK TABLES");
		unset($_SESSION['oid_clarify_auto']);  
		
		/**
		 * ��������� ��� � ������� ������������ ���������
		 * �������������� ����, �� ����� ����
		 */
		$name_params = $DB->fetch_column("SELECT id, uniq_name FROM auth_user_group_param WHERE uniq_name IN ('firstname', 'lastname')", 'uniq_name', 'id');
		if(!empty($info['first_name']) && !empty($name_params['firstname'])){
			$DB->insert("insert ignore into auth_user_data (`user_id`,`param_id`,`value_char`) values ('$user_id', '{$name_params['firstname']}', '".addslashes($info['first_name'])."')");
			$DB->insert("insert ignore into auth_user_history_data (`history_id`,`param_id`,`data_type`,`value_char`) values ('$history_id', '{$name_params['firstname']}', 'char', '".addslashes($info['first_name'])."')");
			
		}	  
		if(!empty($info['last_name']) && !empty($name_params['lastname'])){
			$DB->insert("insert ignore into auth_user_data (`user_id`,`param_id`,`value_char`) values ('$user_id', '{$name_params['lastname']}', '".addslashes($info['last_name'])."')");
			$DB->insert("insert ignore into auth_user_history_data (`history_id`,`param_id`,`data_type`,`value_char`) values ('$history_id', '{$name_params['lastname']}', 'char', '".addslashes($info['last_name'])."')");
		} 
		
		/**
		 * �������� ����������� ��������������
		 */
		if (CMS_NOTIFY_EMAIL != '') {
			$mailto = CMS_NOTIFY_EMAIL; 
			require_once(ACTIONS_ROOT.'site/user/notification.inc.php');
		}
		 
		/**
		 * �������� ����������� ������������
		 */
		$Template = new TemplateDB('cms_mail_template', 'User', 'registration_notify');
		$Template->set('name', $user_name);
		$Template->set('email', $user_email);
		$Template->set('passwd', $user_password);
		
		$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '����������� �� %s', CMS_HOST), $Template->display());
		$Sendmail->send($user_email, true);
		   
		/**
		 * �������� �����������
		 */
		if (is_module('billing') && BILLING_AUTO_CREATE_CONTRAGENT){
			$contragent_id = $DB->insert("insert into billing_contragent set type = 'phisical', contact_person = '$user_name', contact_email = '$user_email'");	
			$DB->insert("insert into billing_contragent_user set user_id = '$user_id', contragent_id = '$contragent_id'");
			Billing::saveContragentVersion($contragent_id);
		}
		
		/** 
		 * �������� ���������� � ������������
		 */
		if(!empty($provider) && !empty($info['identity'])){ 
			$provider_id = $DB->result("SELECT id FROM auth_user_oid_provider WHERE uniq_name = '{$info['source']}'");
			self::bindProviderIdentity($user_id, $info['identity'], $provider_id);
		}
		
		/**
		 * �����������
		 */
		$logged_in = Auth::login($user_id, false, null);
		if (!$logged_in) {  
			Auth::logLogin(0, time(), $user_email, $user_password);
			self::$errors[] = cms_message('CMS', '������ � IP ������������ ��� ��� ������� �������� ���������������');
			return false;
		} 
		 
		self::$messages[] = cms_message('CMS', '�����������, �� ������� ������������������.');
		return true;
	}
	
	
	/******************************************************************************/
	/*								    FACEBOOK								  */
	/******************************************************************************/
	
	/**
	 * Facebook ����������� 
	 * 
	 * @param string $code
	 * @return mixed
	 */
	static public function authFacebook($code){
		$state_respond = globalVar($_REQUEST['state'], '');
		$state_session = globalVar($_SESSION['oid_facebook_state'], '');
		
		$widget_name = trim(substr($state_session, 0, strpos($state_session, '__')));
		
		/**
		 * CSRF ������
		 */
		if(empty($state_respond) || empty($state_session) || $state_respond != $state_session){
			self::$errors[] = cms_message('User', "��������� �������� ����������� �������. ��������, �� ����� ������� CSRF - ��� ���� �� ����������� ���-������, ������������ ���������� ��������� HTTP");
			return false;
		}
		 
		$params = null;
		
		$_return_url = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/facebook/';  
		$token_url = "https://graph.facebook.com/oauth/access_token?" . "client_id=" . AUTH_OID_FACEBOOK_APP_ID . "&redirect_uri=" . urlencode($_return_url) . "&client_secret=" . AUTH_OID_FACEBOOK_APP_SECRET . "&code=" . $code;
		$response = @file_get_contents($token_url);
		
		parse_str($response, $params);
		$graph_url = "https://graph.facebook.com/me?access_token=" . $params['access_token'];
		$user = json_decode(@file_get_contents($graph_url));

		if(empty($user->id)){  
			self::$errors[] = cms_message('User', "����������� ����� ������� ������ Facebook ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		} 
		 
		/**
		 * ���� ���������� � ������������
		 */
		self::$info['id'] = $user->id;
		self::$info['source'] = 'facebook';
		self::$info['email']  = (!empty($user->email)) ? $user->email : '';  
		self::$info['login']  = self::$info['email'];  
		self::$info['first_name'] = (!empty($user->first_name)) ? trim(@iconv('utf-8', 'windows-1251', $user->first_name)) : '';
		self::$info['last_name']  = (!empty($user->last_name)) ? trim(@iconv('utf-8', 'windows-1251', $user->last_name)) : '';
		
		unset($_SESSION['oid_facebook_state']); 
		$_SESSION['oid_widget_active'] = $widget_name;
		
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}
	
	
	/******************************************************************************/
	/*								    VKONTAKTE								  */
	/******************************************************************************/
		
	/**
	 * Vkontakte ����������� 
	 *
	 * @param string $code
	 * @return mixed
	 */
	static public function authVKontakte($code){
		$params = null;
		
		$_return_url = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/vkontakte/';  
		$token_url = "https://api.vkontakte.ru/oauth/access_token?" . "client_id=" . AUTH_OID_VKONTAKTE_APP_ID . "&client_secret=" . AUTH_OID_VKONTAKTE_APP_SECRET . "&code=" . $code;
		$response = @file_get_contents($token_url);
		 
		$params = json_decode($response);
		$graph_url = "https://api.vkontakte.ru/method/getProfiles?uid=" . $params->user_id . "&access_token=" . $params->access_token;
		$user = json_decode(@file_get_contents($graph_url));
		
		if(empty($user->response[0]->uid)){  
			self::$errors[] = cms_message('User', "����������� ����� ������� ������ ��������� ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		} 
		
		/**
		 * ���� ���������� � ������������
		 */ 
		self::$info['id'] = $user->response[0]->uid;
		self::$info['source'] = 'vkontakte';
		self::$info['first_name'] = (!empty($user->response[0]->first_name)) ? trim(@iconv('utf-8', 'windows-1251', $user->response[0]->first_name)) : '';
		self::$info['last_name']  = (!empty($user->response[0]->last_name)) ? trim(@iconv('utf-8', 'windows-1251', $user->response[0]->last_name)) : '';
		 
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}	
	
	
	/******************************************************************************/
	/*								     GOOGLE 								  */
	/******************************************************************************/
	
	/**
	 * ������� ������� �� ����������� Google ��������. ����� ���������� ������������� �� �������� $return_path
	 *
	 * @param string $return_path
	 * @return void
	 */
	static public function redirectGoogle($return_path = '', $widget_name = ''){  
		if(empty($return_path)) $return_path = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/google/?_own='.$widget_name;
		
		$handle = globalVar($_COOKIE['oid_google_handle'], '');
		if(empty($handle)){
			$handle = GoogleOpenID::getAssociationHandle(); 
			$domain = Auth::getCookieDomain(CMS_HOST);
			$cookie_expire = time() + 86400 * 14;  
			 
			setcookie('oid_google_handle', $handle, $cookie_expire, '/', $domain);
		}
		
  		$GoogleGateway = GoogleOpenID::createRequest($return_path, $handle, true);
  		$GoogleGateway->redirect();
	}
	
	
	/**
	 * Google ����������� 
	 * 
	 * @return mixed
	 */
	static public function authGoogle(){
		$info = array('id' => null, 'email' => null, 'first_name' => null, 'last_name' => null, 'birthday' => null);
		
		$GoogleResponse = GoogleOpenID::getResponse();
		if(!$GoogleResponse->success()){
			self::$errors[] = cms_message('User', "����������� ��������");
			return false;
		} 
		  
		/**
		 * ���� ���������� � ������������
		 */
		self::$info['id'] = trim($GoogleResponse->identity());
    	self::$info['email'] = trim($GoogleResponse->email());
    	self::$info['login'] = self::$info['email'];
		self::$info['source'] = 'google';
		
    	if(empty(self::$info['id'])){ 
			self::$errors[] = cms_message('User', "����������� ������ �������� Google ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		}
    	  
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}
	
	
	/******************************************************************************/
	/*								     YANDEX 								  */
	/******************************************************************************/
	
	/**
	 * Yandex ����������� 
	 *
	 * @param string $code
	 * @return mixed
	 */
	static public function authYandex($code){
		$Download = new Download();
		
		$request = array(
			'grant_type' => 'authorization_code',
			//'client_id'  => AUTH_OID_YANDEX_APP_ID,
			'code'  	 => $code,
		);
		
		$response = $Download->post('https://oauth.yandex.ru/token', $request);
		$params = json_decode($response);
		
		/**
		 * ���������� �����������, ��� �� �� ���������� ���� ��������
		 */
		$Download->setHeader("Authorization", "OAuth ".$params->access_token);
		$profile = $Download->get("https://api-yaru.yandex.ru/me/");
		
		// 502 Bad Gateway
		if(strpos($Download->getErrorMessage(), '502') !== FALSE){
			self::$errors[] = cms_message('User', "� ��������� ������ Yandex �� ��������. ����������, ��������� ������� ����������� ����� ��� �������������� �������� ������� ����������");
			return false;
		}
		 
		// TODO: ������� ����� �� �������� ����������� �� ������� �������
		x($profile);
		exit;
		
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}	
	
	
	/******************************************************************************/
	/*								     TWITTER 								  */
	/******************************************************************************/
	
	/**
	 * ������� ������� �� ����������� Twitter ��������. ����� ���������� ������������� �� �������� $return_path
	 *
	 * @param string $return_path
	 * @return void
	 */
	static public function redirectTwitter($return_path = '', $widget_name = ''){  
		if(empty($return_path)) $return_path = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/twitter/?_own='.$widget_name;
		
		$connection = new TwitterOAuth(AUTH_OID_TWITTER_APP_ID, AUTH_OID_TWITTER_APP_SECRET);
		$request_token = $connection->getRequestToken($return_path);
		
		$_SESSION['oid_twitter_request_token'] = $request_token['oauth_token'];
		$_SESSION['oid_twitter_request_token_secret'] = $request_token['oauth_token_secret'];
		
		if($connection->http_code != 200) {
			self::$errors[] = cms_message('User', "����������� ����������� ��������. ���������� ����������� � Twitter. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		}
		 
		$url = $connection->getAuthorizeURL($_SESSION['oid_twitter_request_token']);
		
		header("Accept: application/xrds+xml"); 
	    header("Location: $url"); 
	    exit;
	}
	
	
	/**
	 * Twitter ����������� 
	 * 
	 * @return mixed
	 */
	static public function authTwitter(){
		if(empty($_SESSION['oid_twitter_request_token']) || empty($_SESSION['oid_twitter_request_token_secret'])){
			self::$errors[] = cms_message('User', "���� ������ ���������. ����������, ��������� ������� ����������� ��� �������������� �������� ������� ����������");
			return false;
		}
		
		$connection = new TwitterOAuth(AUTH_OID_TWITTER_APP_ID, AUTH_OID_TWITTER_APP_SECRET, $_SESSION['oid_twitter_request_token'], $_SESSION['oid_twitter_request_token_secret']);
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
		 
		unset($_SESSION['oid_twitter_request_token']);
		unset($_SESSION['oid_twitter_request_token_secret']);

		if (empty($access_token) || empty($access_token['oauth_token']) || empty($access_token['oauth_token_secret'])) {
			self::$errors[] = cms_message('User', "����������� ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false; 
		}
		
		$connection = new TwitterOAuth(AUTH_OID_TWITTER_APP_ID, AUTH_OID_TWITTER_APP_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$TwitterResponse = $connection->get('account/verify_credentials');
		
		/**
		 * ���� ���������� � ������������
		 */
		self::$info['id'] = (!empty($TwitterResponse->id)) ? $TwitterResponse->id : 0; 
    	self::$info['login'] = (!empty($TwitterResponse->screen_name)) ? trim($TwitterResponse->screen_name) : '';
    	self::$info['first_name'] = (!empty($TwitterResponse->name)) ? trim(iconv('utf-8', 'windows-1251', $TwitterResponse->name)) : '';
		self::$info['source'] = 'twitter'; 
		
    	if(empty(self::$info['id'])){  
			self::$errors[] = cms_message('User', "����������� ������ �������� Twitter ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		}
    	  
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}
	
	
	/******************************************************************************/
	/*							   OPENID PROVIDERS 							  */
	/******************************************************************************/
		
	/**
	 * ������� ������� �� ����������� ��������. ����� ���������� ������������� �� �������� $return_path
	 *
	 * @param string $openid_identifier
	 * @param string $return_path
	 * @return void
	 */
	static public function redirectOpenID($openid_identifier, $return_path = '', $widget_name = ''){  
		if(empty($return_path)) $return_path = HTTP_SCHEME . "://" . CMS_HOST . '/action/user/oid/openid/?_own='.$widget_name;
		
		$openid = new LightOpenID(HTTP_SCHEME . "://" . CMS_HOST);
		$openid->identity = $openid_identifier; 
		
		if(empty($openid_identifier)){
			self::$errors[] = cms_message('User', "����������, ������� ��� Open ID");
			return false;
		} 
		
		$openid->returnUrl = $return_path;
		$openid->required = array('namePerson', 'contact/email', 'birthDate');
    	
    	/**
    	 * ��������� ������������� �������
    	 */
    	try {
	    	$openid->discover($openid_identifier);
    	} catch(ErrorException $e) {
		    self::$errors[] = cms_message('User', $e->getMessage());
			return false;
		}

	  	header("Accept: application/xrds+xml"); 
	    header('Location: ' . $openid->authUrl());
	    exit;
	}
	
	
	/**
	 * Open ID ����������� 
	 * 
	 * @return mixed
	 */
	static public function authOpenID(){
		$openid = new LightOpenID(HTTP_SCHEME . "://" . CMS_HOST); 
		
		if($openid->mode == 'cancel') {
		    self::$errors[] = cms_message('User', "����������� �� ���������. �� ���������� ������������ ������ � ����������� ����������");
			return false;
		} 

		if(!$openid->validate()) {
		    self::$errors[] = cms_message('User', "����������� ����������� ��������. ����������, ��������� ������� ����� ��� �������������� �������� ������� ����������");
			return false;
		}
    	
		if(empty($_SESSION['oid_openid_identifier']) || empty($_SESSION['oid_openid_provider'])) {
		    self::$errors[] = cms_message('User', "������ ���������. ����������, ��������� ������� �����������");
			return false;
		}
		
		$attributes = $openid->getAttributes();
		self::$info['login']  = $_SESSION['oid_openid_identifier'];
    	self::$info['email']  = (!empty($attributes['contact/email'])) ? trim($attributes['contact/email']) : ''; 
    	self::$info['first_name'] = (!empty($attributes['namePerson'])) ? trim(iconv('utf-8', 'windows-1251', $attributes['namePerson'])) : '';  
    	self::$info['source'] = $_SESSION['oid_openid_provider']; 
    	
    	unset($_SESSION['oid_openid_identifier']);
    	unset($_SESSION['oid_openid_provider']);
    	 
		if($_SESSION['oid_widget']['action'] == 'register') return self::oidRegister();
		return self::oidLogin();
	}
}
