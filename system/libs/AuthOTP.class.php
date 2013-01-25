<?php
/** 
 * ����� ���������� �����������
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2011
 */ 

/**
 * ����� ���������� �����������
 */
class AuthOTP {
	
	/**
	 * ����������� 
	 *
	 * @param int $user_id
	 * @param string $pass
	 * @return bool
	 */
	static public function auth($user_id, $pass, $is_reserve=0, &$message=''){
		global $DB;
		
		$pass = trim($pass);
		
		/**
		 * ������������ �������������� ��������� ����� �������
		 */
		if(!empty($is_reserve)){
			if(empty($pass)){
				$message = "����������, ������� ��� �������";
				return false;
			}
		 
			if(!preg_match("/.{8}/", $pass)) return false;  
			$DB->delete("DELETE FROM auth_user_otp_code WHERE user_id = '$user_id' AND code = '$pass'");
			if($DB->rows > 0) return true;
			return false;
		}
		
		$config = $DB->query_row("SELECT otp_enable, otp_type, otp_cnt as cnt, otp_sign as sign FROM auth_user WHERE id = '$user_id'");
		if($DB->rows == 0){
			$message = "������������ �� ������";
			return false;
		}
		
		/**
		 * ���� ���������� ����������� ���������, ��������� ��� �� �����
		 */
		if(empty($config['otp_enable'])) return true;
		
		/**
		 * EToken
		 */
		if($config['otp_type'] == 'etoken'){
			if(!preg_match("/(\\d{6})$/", $pass)) return false;  	
			if(empty($config['cnt']) || empty($config['sign'])){
				$message = "������������ ������ OTP-���������� �������. ����������, �������������� ���������� ������ �������";
				return false;
			}
			
			if(empty($pass)){
				$message = "����������, ������� ��� �������";
				return false;
			}
			
			return self::authEToken($user_id, $pass, $config['sign'], $config['cnt']);
		
		/**
		 * Google Authenticator   
		 */	
		} elseif(in_array($config['otp_type'], array('android', 'iphone', 'java'))) {
			if(!preg_match("/(\\d{6})$/", $pass)) return false;  	
			if(empty($config['cnt']) || empty($config['sign'])){
				$message = "������������ ������ OTP-���������� �������. ����������, �������������� ���������� ������ �������";
				return false;
			}
			
			if(empty($pass)){
				$message = "����������, ������� ��� �������";
				return false;
			}
			
			return self::authGoogle($pass, $config['sign']);
			
		/**
		 * SMS �����������
		 */	   
		} elseif($config['otp_type'] == 'sms') {  
			if(empty($pass)){
				$message = "����������, ������� ��� �������";
				return false;
			}
			
			return self::authSms($pass, 'otp_confirm', $user_id, $message); 
		}
  
		return false; 
	}
	
	
	/**
	 * OTP �������� ��� ����������������� �������
	 */
	static function passAdmin() {
		unset($_SESSION['auth']);
		
		Action::setError("�������� ����������� ��������. ���������� ������� ���, ��������������� ����� OTP-�����������");
		$_SESSION['otp_admin'] = true; 
		
		header("Location:/index_admin_login.php?return_path=".CURRENT_URL_LINK);
		exit;
	}
	
	
	/**
	 * ������������� ���� �� $period �������, ��� ������������ ������ ��� ���������� ����� ���� ����������� ������
	 *
	 * @param int $user_id
	 * @param bigint $period
	 * @return void
	 */
	static public function setAccess($user_id, $period = 0){
		global $DB; 
		
		if(empty($period)) $period = time() + 86400 * 14;
		$access_key = gen_password(12);
		$domain = Auth::getCookieDomain(CMS_HOST);
		 
		setcookie('otp_access', '', 0, '/', $domain);
		$DB->insert("INSERT INTO auth_user_otp_access SET user_id = '$user_id', access = '$access_key'"); 
		setcookie('otp_access', $access_key, $period, '/', $domain);
	}
	
	
	/**
	 * ���������� ��� �������
	 *
	 * @param int $user_id
	 * @return bool
	 */
	static public function checkAccess($user_id){
		global $DB;
		
		$cookie_access = globalVar($_COOKIE['otp_access'], ''); 
		if(empty($cookie_access)) return false;
		
		$access = $DB->fetch_column("SELECT access, access FROM auth_user_otp_access WHERE user_id = '$user_id'");
		if($DB->rows == 0) return false; 
		
		return (in_array($cookie_access, $access)) ? true : false;
	}
	
	
	/**
	 * ���������� OTP ������
	 * @return void 
	 */
	static public function disable(){
		global $DB;
		
		$user_id = Auth::getUserId();
		
		$DB->update("UPDATE auth_user SET otp_enable = 0 WHERE id = '$user_id'");  
		$DB->delete("DELETE FROM auth_user_otp_access WHERE user_id = '$user_id'");
		$DB->delete("DELETE FROM auth_user_otp_code WHERE user_id = '$user_id'");
		
		/**
		 * ���������� �������������� ������ ������������
		 */
		$user = Auth::getInfo();
		
		$Template = new TemplateDB('cms_mail_template', 'User', 'otp_disabled');
		$Template->set('name', $user['name']);
		$Template->set('user', $user['email']);  
		$Template->set('date', date('d.m.Y H:i'));
		
		$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '����������� ����������� �� ����� %s ���������', CMS_HOST), $Template->display());
		$Sendmail->send($user['email'], true);  
	}
	
	
	
	/*****************************************************************************/
	/*					 ����������� ������ ������������� ����        	         */
	/*****************************************************************************/
		
	/**
	 * �������� OTP ������ ��� ������� ������
	 *
	 * @param int $user_id
	 * @param enum('site', 'admin') $source
	 */
	public static function sessionActivate($user_id, $source = 'site'){
		$_SESSION['otp']['enabled'] = true;
		$_SESSION['otp']['user_id'] = $user_id;
		$_SESSION['otp']['source'] = $source;
		  
		return true;
	}
		
	
	/**
	 * ��������, �������� �� OTP ������ ��� ������� ������
	 *
	 * @param int $user_id
	 */
	public static function isSessionActive(){
		if(
			empty($_SESSION['otp']['enabled']) || 
			empty($_SESSION['otp']['user_id']) || 
			empty($_SESSION['otp']['source'])
		) return false;
		
		return $_SESSION['otp'];
	}
	
	
	/**
	 * ��������� OTP ������ ��� ������� ������
	 *
	 * @param int $user_id
	 */ 
	public static function sessionClear(){
		unset($_SESSION['otp']);
		return true;
	}
	
	
	/**
	 * ����������� �������, ��� ���������� ��������� �������� OTP ������
	 * 
	 * @param int $user_id
	 * @param string $message
	 * @return bool
	 */  
	public static function clarify($user_id = 0) {
		global $DB, $TmplDesign;
		
		/**
		 * �������� OTP, ���� ��� ��� �� ��������
		 */
		$otp_data = self::isSessionActive();
		if(empty($otp_data)){ 
			$TmplDesign->iterate("/onload/", null, array('function' => "delta_error('".cms_message('User', "������ ���������. ����������, ��������� ������� �����������.")."');"));
			return false;
		}
		 
		$user_id = $otp_data['user_id'];
		$source = $otp_data['source']; 
		
		/** 
		 * ������ ������������, ������� �������� ������ OTP ������
		 */
		$user = $DB->query_row("SELECT id, otp_enable, otp_cnt, otp_type FROM auth_user WHERE id = '$user_id'");
		if($DB->rows == 0){
			$TmplDesign->iterate("/onload/", null, array('function' => "delta_error('".cms_message('User', "������������ ID:$user_id � ���� �� ������. ����������, ���������� � ������������.")."');"));
			return false;
		} 
		
		/**
		 * ���� �������� ������������ OTP ����������� ��� ���������������� ����,  
		 * � � ������������ ���� �� ��������� ������������ - ����������� ����� ����� �����
		 */   
		if(AUTH_OTP_ADMIN_ENABLE && $source == 'admin' && empty($user['otp_enable'])) {
			$TmplDesign->iterate("/onload/", null, array('function' => "delta_error('".cms_message('User', "�� �� ������� ����� � ���������������� ����. � ��� �� ��������� ����������� �����������.")."');"));
			return false;
		} 
			
		/**
		 * ��������� OTP ������ ������� SMS �����������:
		 */
		if($user['otp_type'] == 'sms') {
			 
			// ���������, ��� �� ��������� ��� 
			$code = AuthPhone::getLastCode('otp_confirm', $user_id, true);
			
			// ���� ��� ��� ��� ��������� � ������� �������������, �� ����� ������� ����� ����� ����
			if(!empty($code)) { 
				$TmplDesign->iterate("/onload/", null, array('function' => "delta_action('otp_code_check()', '".self::displayCodeForm($user_id)."', 'otp_session_clear()');"));
				return true;
			}
			  
			// ���� ��� ��� �� ��� ���������, ����� ������� ����� �������� ����
			$TmplDesign->iterate("/onload/", null, array('function' => "delta_action('otp_sms_auth_form()', '".self::displaySmsForm($user_id)."', 'otp_session_clear()');"));
			return true;
		}
		  
		/**
		 * ��������� OTP ������ ������� �������� - ������ ������� ����� ��� ����� ����
		 */
		$TmplDesign->iterate("/onload/", null, array('function' => "delta_action('otp_code_check()', '".self::displayCodeForm($user_id)."', 'otp_session_clear()');"));
		return true;
	}
	
	
	
	/*****************************************************************************/
	/*					  		  eToken PASS Functions        	                 */
	/*****************************************************************************/
	
	/**
	 * ����������� eToken
	 *
	 * @param int $user_id
	 * @param string $pass
	 * @param array $config
	 * @return bool
	 */
	static public function authEToken($user_id, $pass, $sign, $cnt){
		global $DB;
		
		for($i=0; $i<AUTH_OTP_ETOKEN_WINDOW; $i++){
			if(self::hotpEToken($sign, $cnt) == $pass){
				$DB->update("UPDATE auth_user SET otp_cnt = '{$cnt}' WHERE id = '$user_id'"); 
				return true;
			}
			
			$cnt++;
		}
		
		return false;
	}
  
	static private function hotpEToken($secret, $cnt, $digits = 6){
		$secret  = pack('H*', $secret);
		$sha1_hash = self::hmacEToken(pack("NN", 0, $cnt), $secret);
		$dwOffset = hexdec(substr($sha1_hash, -1, 1));
		$dbc1   = hexdec(substr($sha1_hash, $dwOffset * 2, 8 ));
		$dbc2   = $dbc1 & 0x7fffffff;
		$hotp   = $dbc2 % pow(10, $digits);
		return $hotp;
	}
	
	static private function hmacEToken($data, $key) {
		if(function_exists('hash_hmac')) return hash_hmac('sha1', $data, $key);
		if(strlen($key) > 64) $key = pack('H*', sha1($key));
		
		$key = str_pad($key, 64, chr(0x00));
		$ipad = str_repeat(chr(0x36), 64);
		$opad = str_repeat(chr(0x5c), 64);
		$hmac = pack('H*',sha1(($key^$opad).pack('H*',sha1(($key^$ipad).$data))));
		
		return bin2hex($hmac);
	}
	
	
	
	/*****************************************************************************/
	/*					    Google Authenticator Functions                       */
	/*****************************************************************************/
	
	/**
	 * ����������� Google Authenticator
	 *
	 * @param string $pass
	 * @param string $sign
	 * @return bool
	 */
	static public function authGoogle($pass, $sign){
		$counter_start = round(time() / 30);
		$counter_interval = round(AUTH_OTP_PROGRAM_WINDOW * 60 / 30);
		
		for ($i=$counter_interval; $i > 0; $i--) {
			$counter = $counter_start - $i;  
			$res = self::hotpGoogle($sign, $counter);
			if($res == $pass) return true;
		}
		  		
		for ($i=0; $i <= $counter_interval; $i++) {
			$counter = $counter_start + $i;  
			$res = self::hotpGoogle($sign, $counter);
			if($res == $pass) return true;
		}
		
		return false;
	}
	
	static private function hotpGoogle ($key, $counter) {
	    $cur_counter = array(0,0,0,0,0,0,0,0);
	    
	    for($i=7; $i>=0; $i--){
	        $cur_counter[$i] = pack ('C*', $counter);
	        $counter = $counter >> 8;
	    }
	    
	    $bin_counter = implode($cur_counter);
	    if (strlen ($bin_counter) < 8) $bin_counter = str_repeat (chr(0), 8 - strlen ($bin_counter)) . $bin_counter;
	    
	    $hash = hash_hmac ('sha1', $bin_counter, $key);
	    return self::truncateGoogle($hash); 
	}
	
	static private function truncateGoogle($hash, $length = 6){
	    foreach(str_split($hash,2) as $hex){
	    	$hmac_result[]=hexdec($hex);
	    }
	
	    $offset = $hmac_result[19] & 0xf;
	    
	    return (
	        (($hmac_result[$offset+0] & 0x7f) << 24 ) |
	        (($hmac_result[$offset+1] & 0xff) << 16 ) |
	        (($hmac_result[$offset+2] & 0xff) << 8 ) |
	        ($hmac_result[$offset+3] & 0xff)
	    ) % pow(10,$length);
	}
	
	
	
	/*****************************************************************************/
	/*					  		  	SMS Functions        	                	 */
	/*****************************************************************************/
		
	/**
	 * SMS �����������
	 *
	 * @param string $param_code
	 * @param int $user_id
	 * @param string &$message  
	 * @return bool
	 */ 
	static public function authSms($param_code, $action='otp_confirm', $user_id=0, &$message=''){
		global $DB;
		
		if (empty($user_id)) $user_id = Auth::getUserId();  
		if (empty($user_id)){
			$message = "����������, ���������������";
			return false;
		}
		
		/** 
		 * ��������� ���   
		 */
		$code = AuthPhone::getLastCode($action, $user_id, false);
		if (empty($code)) {
			$message = "��� ���������� �������� ��� �������";
			return false;
		}
		  
		if (!empty($code['confirmed'])) {    
			$message = "��� ���������� �������� ��� �������";
			return false;
		} 
		
		$code_id = $code['id'];
		$code_tstamp = convert_date("d.m.Y H:i:s", $code['code_tstamp']);
		$code_attempt = $code['attempt'] + 1;
	
		/**
		 * ��������� ���-�� ������� ����������� ����� 
		 */
		$DB->update("UPDATE auth_user_phone_code SET attempt = '$code_attempt' WHERE id = '$code_id'");
		 
		/**
		 * �������� ���-�� ������� ������������� ����
		 */
		if($code_attempt > AUTH_USER_PHONE_CONFIRM_ATTEMPT){
			$message = "��������� ���������� ������� ������������� ���� �������. ����������, ��������� ��� ��������";
			return false;
		} 
		 
		/** 
		 * �������� �������� ����
		 */
		if($code_tstamp < time() - 3600 * 12) {
			$message = "��� �������������, ��� ��� ��������� ���� �����, �������. ����������, ��������� ��� ��������";
			return false;
		} 
		   
		/**
		 * �������� ����
		 */
		if(trim($param_code) != $code['code']){
			$message = "��� ������������� ������ �������";
			return false;
		} 

		$DB->delete("UPDATE auth_user_phone_code SET confirmed = 1 WHERE id = '$code_id'"); 
		return true; 
	}
	 

	/**
	 * �������� ���� �������
	 *
	 * @param int $phone_id
	 * @param int $user_id
	 * @param string &$error
	 * @param string $action
	 * @return bool
	 */
	static public function createSmsCode($phone_id=0, $user_id=0, &$error="", $action='otp_confirm'){
		global $DB;
		   
		if (empty($user_id)) $user_id = Auth::getUserId();  
		if (empty($user_id)){
			$error = "����������, ���������������";
			return false;
		}
		
		/*
		$loyalty = Auth::getUserLoyalty($user_id);
		if(empty($loyalty['loyalty_id'])){
			$error = "������������ SMS ����������� ��������� ������ ������������� �� �������� �������� �� ����� ����������";
			return false;
		}  
		*/ 
		 
		$phone = AuthPhone::getPhone($phone_id, $user_id); 
		if(empty($phone)){
			$error = "��������� ����� �� ������";
			return false;
		}
		
		if(empty($phone['confirmed'])){
			$error = "��������� ����� �� �����������. �� ������ �������� � ����������� ����� �� �������� <a href=\"/User/Info/\" target=\"_blank\">���������� � ������������</a>";
			return false;
		} 
		 
		/**
		 * ��������� ��� 
		 */
		$code = AuthPhone::getPhoneCode($phone_id, $phone_id, $action, $user_id);
		if(!empty($code['code_tstamp']) && convert_date('d.m.Y H:i:s', $code['code_tstamp']) > time() - 60){  
			$error = "��� ������������� ����� ��������� ���� ���� ��� � ������. ��������� ��� ��� ��������� � {$code['code_tstamp']}";
			return false;
		} 
		
		/**
		 * ��������� ����������� �� �������
		 */
		if(!empty($phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $phone['sms_tstamp']) > time() - 60){  
			$error = "�� ������ ����� ��� ����� ��������� ���� ���� ��� � ������. ����� ���������� ��� {$phone['sms_tstamp']}";
			return false;
		}
			
		$code_id = AuthPhone::createCode($phone_id, $phone_id, $phone['phone'], 0, $action);
		if(empty($code_id)){   
			$error = "�� ������� ��������� ��� � ����� �������������. ����������, ��������� ������� ����� ��������� ����� (1 ���) ��� �������������� ���������� ������ �������";
			return false;
		}   
		
		return true;
	} 
	
	
	/**
	 * ����� ����� �������� ���� �� ���
	 * 
	 * @param int $user_id
	 * @return string
	 */
	public static function displaySmsForm($user_id){
		$TmplOtpPhone = new Template("user/otp/form_sms_phone");
	 
		/**  
		 * ���������� ����� ��������� ���������� �������   
		 */
		$phones = AuthPhone::getConfirmedPhones($user_id);
		$phones_count = count($phones);
		$TmplOtpPhone->set('otp_sms_auth_phones_count', $phones_count); 
		
		if($phones_count == 1){ 
			$phone = AuthPhone::getPhone($phone_id, $user_id);  
			$TmplOtpPhone->set('otp_sms_auth_phone', $phone); 
		} elseif($phones_count > 1) {  
			$TmplOtpPhone->iterateArray('/otp_sms_auth_phones/', null, $phones); 
		}
		 
		$sms_auth_phone_form = json_encode(@iconv('windows-1251', 'utf-8', $TmplOtpPhone->display()));
		$sms_auth_phone_form = substr($sms_auth_phone_form, 1, strlen($sms_auth_phone_form)-2);
		return $sms_auth_phone_form;
	}
	
	
	/**
	 * ����� ����� SMS �����������
	 * 
	 * @param int $user_id
	 * @param int $is_reserve
	 * @return string
	 */
	public static function displayCodeForm($user_id, $is_reserve = 0){
		global $DB;
		
		/**
		 * ��������� ������������
		 */
		$user = $DB->query_row("SELECT id, otp_enable, otp_cnt, otp_type FROM auth_user WHERE id = '$user_id'");
		if($DB->rows == 0){
			return false;
		}
		
		$TmplCode = new Template("user/otp/form_code");
		$TmplCode->set("otp_type", $user['otp_type']);
		$TmplCode->set("is_reserve", $is_reserve);   
		
		/**
		 * � ������ SMS �����������, ���������, ��� �� ��������� ���
		 */
		if($user['otp_type'] == 'sms'){ 
			$code = AuthPhone::getLastCode('otp_confirm', $user_id, true);
			$TmplCode->set("code", $code);
		}
		
		$code_form = json_encode(@iconv('windows-1251', 'utf-8', $TmplCode->display()));
		$code_form = substr($code_form, 1, strlen($code_form)-2);
		return $code_form;
	}
	
}


?>