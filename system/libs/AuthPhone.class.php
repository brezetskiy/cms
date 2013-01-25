<?php

/**
 * ����� ������ � ����������� ��������
 * @package Pilot
 * @subpackage Auth
 * @author Barin Miha <barin@delta-x.ua>
 * @copyright Copyright 2012, Delta-X ltd.
 */
class AuthPhone {

	
	/**
	 * ������ �������� ����
	 *
	 * @param string $phone
	 * @param string $code
	 * @return bool
	 */
	public static function sendPhoneCode($phone, $code){
		$phone = str_replace('+', '', $phone);
		return Misc::sendSms($phone, '��� ��� �������������: '.$code.'. �������� ��� � ���� �����');
	}
	
	
	/**
	 * ���������� ����
	 *
	 * @param int $length
	 * @return int
	 */
	public static function generateCode($length){ 
		return rand(1000, pow(10, $length)-1);            
	}
	 
	
	/**
	 * ������ ������. ���������, ������� �� ����� � ���������� �������
	 *
	 * @param string $phone
	 * @return mixed
	 */ 
	public static function parsePhone($str){
		$phone = str_replace(array('(', ')', '-', ' '), '', trim($str));
		if(!preg_match('/\+[0-9]{11,12}/', $phone)) return false;
		$phone = str_replace('+0', '+', $phone);
		
		return $phone;
	}
	
	
	/**
	 * ������ ����������� ������ 
	 *
	 * @param string $phone
	 * @return array
	 */
	public static function parsePhoneStructure($phone){
		
		$phone = self::parsePhone($phone);
		if(!$phone) return false;
		
		$result = array();
		
		$result['number'] = substr($phone, -7);	
		if (empty($result['number']) || strlen($result['number']) != 7) return false;
		
		$phone = str_replace($result['number'], '', $phone);
		
		$result['prefix'] = substr($phone, -3);	
		if (empty($result['prefix']) || strlen($result['prefix']) != 3) return false;
		
		$phone = str_replace($result['prefix'], '', $phone);
		
		$result['code'] = str_replace('+', '', $phone);	
		if (empty($result['code'])) return false;
		
		return $result;
	}
	
	
	/**
	 * ���������, ����� �� ����������� ��������� �����
	 *
	 * @param string $phone
	 * @return bool
	 */
	public static function isConfirmable($phone){
		global $DB; 
		
		$phone = self::parsePhoneStructure($phone);   
		if(!$phone) return false;
		
		$sms_valid = $DB->result("
			SELECT sms_valid FROM auth_user_phone_operator 
			WHERE prefix = '{$phone['prefix']}' AND country_code = '{$phone['code']}'
		");
		
		if ($DB->rows == 0) return false;
		if (empty($sms_valid)) return false;
		
		return true; 
	}
	
	
	/**
	 * ����������� �������� ���� ������������� ����������� ������
	 *
	 * @param int $phone_id
	 * @param int $phone_send_id
	 * @param string $phone_send_number
	 * @param int $code_id
	 * @param string $action
	 * @return int
	 */
	public static function createCode($phone_id, $phone_send_id, $phone_send_number, $code_id=0, $action='confirm'){
		global $DB;
		
		$code = self::generateCode(4);   
		if(!self::sendPhoneCode($phone_send_number, $code)) return 0;
		  
		if(!empty($code_id)){
			$DB->update("UPDATE auth_user_phone_code SET code = '$code', attempt = '0', confirmed = '0', code_tstamp = NOW() WHERE id = '$code_id'"); 
			return $code_id;
		}       
		
		$DB->update("UPDATE auth_user_phone SET sms_tstamp = NOW() WHERE id = '$phone_send_id'");
		
		$code_id = $DB->insert("
			INSERT INTO auth_user_phone_code (action, phone_id, code, phone_send_id, code_tstamp, confirmed, attempt)
			VALUES ('$action', '$phone_id', '$code', '$phone_send_id', NOW(), 0, 0)
			ON DUPLICATE KEY UPDATE code = VALUES(code), code_tstamp = VALUES(code_tstamp), confirmed = VALUES(confirmed), attempt = VALUES(attempt)
		");
		   
		if(empty($code_id)) $code_id = $DB->result("
			SELECT id FROM auth_user_phone_code 
			WHERE action = '$action'   
				AND phone_id = '$phone_id' 
				AND phone_send_id = '$phone_send_id'
		");
		
		return $code_id;
	}
	
	
	/**
	 * ������������ � �������� ����� ������������� ����������� ������
	 *
	 * @param int $phone_id
	 * @param int $user_id
	 * @param string &$error
	 * @param bool $check_codes
	 * @param int $send_phone_id
	 * @return bool 
	 */
	public static function sendPhoneConfirmation($phone_id, $user_id=0, &$error, $check_codes=true, $send_phone_id=0){
		global $DB;
		
		/**
		 * ���������� ��������� 
		 */
		if (empty($user_id)) $user_id = Auth::getUserId();   
		if (empty($user_id)){
			$error = "����������, ���������������";
			return false;
		}
		
		/**
		 * �������� ��������� ���������� �������
		 */
		$phone = $DB->query_row("
			SELECT 
				phone,  
				phone_original, 
				CONCAT(SUBSTR(phone_original, 1, 8), ' xxx-xx-', SUBSTR(phone_original, -2)) as phone_public,
				confirmed, 
				DATE_FORMAT(sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp 
			FROM auth_user_phone 
			WHERE id = '$phone_id' AND user_id = '$user_id'
		");
		if($DB->rows == 0){
			$error = "�� �� ��������� ���������� ���������� ����������� ������";
			return false;  
		}
		
		if(!empty($phone['confirmed'])) {
			$error = "��������� ���������� ����� ��� �����������";
			return false;  
		}
		  
		if(!empty($phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $phone['sms_tstamp']) > time() - 60){  
			$error = "�� ������ ����� ��� ����� ��������� ���� ���� ��� � ������. ����� ���������� ��� {$phone['sms_tstamp']}.";
			return false;
		}
		
		/**
		 * ���� ���� ��� ������������ �����, �� ��� �� ���� ������������, ��������� ��
		 * ��������: ����������� ������ ����, ��� ��� �� ���� ������������, �������������� ���������������� �� �����
		 */
		$codes = ($check_codes) ? self::getPhoneCodes($phone_id, $user_id) : array();
		if(!empty($codes)){
			
			// ���������� ���������������� ����
			$unconfirmed_codes = array();
			
			reset($codes);
			while(list($code_id, $code) = each($codes)){
				if(empty($code['confirmed'])) $unconfirmed_codes[$code_id] = $code;
			}
			
			/**
			 * ���������, �������� ��� ��� ������ ���� ���� ������������, �� ��� ���������� ����� ��� ��� �� �����������
			 */ 
			if(empty($unconfirmed_codes)){
				$DB->update("UPDATE auth_user_phone SET confirmed = 1 WHERE id = '$phone_id'");    
				$error = "��� ���� ��� ���� ������������ �����. ��������� �������� ����� �� �����. ����������, ������� <a href=\"javascript:void(0);\" onclick=\"window.location.reload();\">�������� ��������</a>";
				return false;     
			}
			
			/**
			 * �� �������������� ���� ����������
			 */
			if(!empty($unconfirmed_codes)) {
			
				reset($unconfirmed_codes);
				while(list($code_id, $code) = each($unconfirmed_codes)){
					if(!empty($code['code_tstamp']) && convert_date('d.m.Y H:i:s', $code['code_tstamp']) > time() - 60){
						$error = "��� ������������� ����� ��������� ���� ���� ��� � ������"; 
						return false;
					}
				}
		 
				reset($unconfirmed_codes);
				while(list($code_id, $code) = each($unconfirmed_codes)){
					if(!AuthPhone::createCode($phone_id, $code['phone_send_id'], $code['phone_send'], $code_id)){
						$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$code['phone_send_original']}. ����������, ��������� ������� ����� ��������� �����";
						return false;
					}
				}
				
				return true;
			}
		}
		
		/**
		 * �������� ������ �������������� ������� ������������
		 */
		$confirmed_phones = self::getConfirmedPhones($user_id);
		$confirmed_phones_count = count($confirmed_phones); 
		
		/**
		 * �� ���� ����� ��� �� ��� �����������
		 */
		if($confirmed_phones_count == 0){
			
			// ������� ��� ������������� ��� ������, ��� ��������������
			if(!AuthPhone::createCode($phone_id, $phone_id, $phone['phone'])){
				$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$phone['phone_original']}. ����������, ��������� ������� ����� ��������� �����";
				return false;
			}
			
		/**
		 * ����������� ������ ���� �����	
		 */
		} elseif($confirmed_phones_count == 1){
			$confirmed_phone = array_pop($confirmed_phones);
			
			if(!empty($confirmed_phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $confirmed_phone['sms_tstamp']) > time() - 60){  
				$error = "�� ������ ����� ��� ����� ��������� ���� ���� ��� � ������. ����� ���������� ��� �� ����� {$confirmed_phone['phone_original']} - {$confirmed_phone['sms_tstamp']}.";
				return false;
			}
			
			$code_1 = self::generateCode(4);
			$code_2 = self::generateCode(4);
			
			// �������� ����� �� ���  
			if(!self::sendPhoneCode($phone['phone'], $code_1)){
				$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$phone['phone_original']}. ����������, ��������� ������� ����� ��������� �����";
				return false;
			}
			if(!self::sendPhoneCode($confirmed_phone['phone'], $code_2)){
				$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$confirmed_phone['phone_original']}. ����������, ��������� ������� ����� ��������� �����";
				return false;
			}
			
			$DB->update("UPDATE auth_user_phone SET sms_tstamp = NOW() WHERE id IN ('$phone_id', '{$confirmed_phone['id']}')");
			
			// ��������� ���� �������������
			$DB->insert("
				INSERT INTO auth_user_phone_code (phone_id, code, phone_send_id)
				VALUES  ('$phone_id', '$code_1', '$phone_id'), 
						('$phone_id', '$code_2', '{$confirmed_phone['id']}')
				ON DUPLICATE KEY UPDATE code = VALUES(code)
			");
		  
		/**
		 * ������������ ����� ������ ������
		 */
		} elseif($confirmed_phones_count > 1){
			if(empty($send_phone_id)){
				$error = "���������� ���������� �����, ��� �������� �������� ������������� �������� ������";
				return true;  
			}
			
			// �������� ��������� ������ ���������� �������
			$send_phone = self::getPhone($send_phone_id, $user_id);
			if($DB->rows == 0){
				$_RESULT['javascript']  = "delta_error('�� �� ��������� ���������� ����������� ������ {$send_phone['phone_original']}');"; 
				exit;  
			} 
			
			if(!empty($send_phone['sms_tstamp']) && convert_date('d.m.Y H:i:s', $send_phone['sms_tstamp']) > time() - 60){  
				$error = "�� ������ ����� ��� ����� ��������� ���� ���� ��� � ������. ����� ���������� ��� �� ����� {$send_phone['phone_original']} - {$send_phone['sms_tstamp']}.";
				return false;
			}
			
			$code_1 = self::generateCode(4);
			$code_2 = self::generateCode(4);
			
			// �������� ����� �� ���  
			if(!self::sendPhoneCode($phone['phone'], $code_1)){
				$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$phone['phone_original']}. ����������, ��������� ������� ����� ��������� �����";
				return false;
			}
			if(!self::sendPhoneCode($send_phone['phone'], $code_2)){
				$error = "�� ������� ��������� ��� � ����� ������������� �� ����� {$send_phone['phone_original']}. ����������, ��������� ������� ����� ��������� �����";
				return false;
			}
			
			$DB->update("UPDATE auth_user_phone SET sms_tstamp = NOW() WHERE id IN ('$phone_id', '$send_phone_id')");
			
			// ��������� ���� �������������
			$DB->insert("
				INSERT INTO auth_user_phone_code (phone_id, code, phone_send_id)
				VALUES  ('$phone_id', '$code_1', '$phone_id'), 
						('$phone_id', '$code_2', '$send_phone_id')
				ON DUPLICATE KEY UPDATE code = VALUES(code)
			");
		}
		 
		return true;
	} 
	
	
	/**
	 * ���������� ������ �������������� ������� ������������
	 *
	 * @param int $user_id 
	 * @return array
	 */
	public static function getConfirmedPhones($user_id=0){
		global $DB;
		
		if (empty($user_id)) $user_id = Auth::getUserId();   
		
		$phones = $DB->query("
			SELECT 
				id, 
				phone,
				phone_original, 
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_public,
				is_main,
				is_confirmable, 
				DATE_FORMAT(sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp
			FROM auth_user_phone as tb_phone  
			WHERE tb_phone.confirmed = 1 AND tb_phone.user_id = '$user_id'
			ORDER BY sms_tstamp DESC
		", 'id');
		
		return $phones;
	}
	
	 
	/**
	 * ���������� ������ ������� ������������, ��� ��� ������������ ��� ������� �������������
	 *
	 * @param int $user_id 
	 * @param bool $only_confirmable 
	 * @param bool $only_unconfirmable 
	 * @return array
	 */
	public static function getPhones($user_id=0, $only_confirmable = false, $only_unconfirmable = false){
		global $DB;
		
		if (empty($user_id)) $user_id = Auth::getUserId();   
		$where_confirmable = ($only_confirmable) ? " AND tb_phone.is_confirmable = '1' " : "";
		$where_unconfirmable = ($only_unconfirmable) ? " AND tb_phone.is_confirmable = '0' " : "";
		
		if($only_confirmable && $only_unconfirmable) {
			$where_confirmable = '';
			$where_unconfirmable = ''; 
		}
		   
		$phones = array();
		$phones_list = $DB->query("
			SELECT 
				tb_phone.id, 
				tb_phone.phone,
				tb_phone.phone_original, 
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_public,
				tb_phone.confirmed,
				tb_phone.is_main,
				tb_phone.is_confirmable,
				DATE_FORMAT(tb_phone.sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp
			FROM auth_user_phone as tb_phone  
			WHERE tb_phone.user_id = '$user_id' 
				$where_confirmable
				$where_unconfirmable
			ORDER BY tb_phone.sms_tstamp DESC
		", 'id');
		
		$confirmable_phones = ($only_confirmable) ? array_keys($phones_list) : array();
		 
		if(!$only_confirmable){
			reset($phones_list);
			while(list($phone_id, $phone) = each($phones_list)){
				if(!empty($phone['is_confirmable'])) $confirmable_phones[] = $phone_id;
			}
		}
		
		$codes_confirm = array();
		$code_delete = array();
		$code_otp_confirm = array();
		$code_otp_delete = array();
		
		$codes_list = $DB->query("
			SELECT 
				tb_code.id, 
				tb_code.phone_id, 
				tb_code.confirmed,
				tb_code.phone_send_id,
				tb_code.action, 
				DATE_FORMAT(tb_code.code_tstamp, '%d.%m.%Y %H:%i:%s') as code_tstamp,
				tb_phone.phone as phone_send,
				tb_phone.phone_original as phone_send_original, 
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_send_public 
			FROM auth_user_phone_code as tb_code
			INNER JOIN auth_user_phone as tb_phone ON tb_phone.id = tb_code.phone_send_id
			WHERE tb_code.phone_id IN (0".implode(', ', $confirmable_phones).")
		");
		
		reset($codes_list);
		while(list($index, $row) = each($codes_list)){
			if($row['action'] == 'confirm') $codes_confirm[$row['phone_id']][$row['id']] = $row;
			if($row['action'] == 'delete') $code_delete[$row['phone_id']] = $row; 
			if($row['action'] == 'otp_confirm') $code_otp_confirm[$row['phone_id']] = $row;
			if($row['action'] == 'otp_delete') $code_otp_delete[$row['phone_id']] = $row;
		} 
		
		reset($phones_list);
		while(list($index, $row) = each($phones_list)){  
			$phones[$row['id']] = $row;
			if(!empty($codes_confirm[$row['id']])) $phones[$row['id']]['code_confirm'] = $codes_confirm[$row['id']];
			if(!empty($code_delete[$row['id']])) $phones[$row['id']]['code_delete'] = $code_delete[$row['id']]; 
			if(!empty($code_otp_confirm[$row['id']])) $phones[$row['id']]['code_otp_confirm'] = $code_otp_confirm[$row['id']];
			if(!empty($code_otp_delete[$row['id']])) $phones[$row['id']]['code_otp_delete'] = $code_otp_delete[$row['id']];
		}
		 
		return $phones;
	}
	
	
	/**
	 * ���������� �������� ����� ������������. ������� ����� ��������� ������ �������������� �����.
	 * ���� �������� ����� �� ���������, ������������ ��������� ���������� �������������� ����� 
	 *
	 * @param int $user_id
	 * @param bool $is_string
	 * @return mixed
	 */
	public static function getMainPhone($user_id=0, $is_string = false){
		global $DB;
		
		if (empty($user_id)) $user_id = Auth::getUserId();   
		$phone = $DB->query_row("
			SELECT 
				tb_phone.id, 
				tb_phone.phone,
				tb_phone.phone_original,  
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_public,
				tb_phone.confirmed,
				tb_phone.is_confirmable,
				DATE_FORMAT(tb_phone.sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp
			FROM auth_user_phone as tb_phone  
			WHERE tb_phone.user_id = '$user_id'    
			ORDER BY tb_phone.is_main DESC, tb_phone.id DESC
			LIMIT 1  
		");

		if($is_string) return (!empty($phone['phone'])) ? $phone['phone'] : '';
		return (!empty($phone)) ? $phone : array();
	}
	
	
	/**
	 * ���������� ����� �� ID
	 *
	 * @param int $phone_id
	 * @param int $user_id
	 * @param bool $is_string
	 * @return array
	 */
	public static function getPhone($phone_id=0, $user_id=0, $is_string = false){
		global $DB;
		
		if (empty($user_id)) $user_id = Auth::getUserId();  
		if (empty($phone_id)) return self::getMainPhone($user_id);
		
		$phone = $DB->query_row("
			SELECT
				*,
				CONCAT(SUBSTR(phone_original, 1, 8), ' xxx-xx-', SUBSTR(phone_original, -2)) as phone_public,
				DATE_FORMAT(sms_tstamp, '%d.%m.%Y %H:%i:%s') as sms_tstamp  	
			FROM auth_user_phone 
			WHERE id = '$phone_id' AND user_id = '$user_id'
		");
		 
		if ($is_string) return (!empty($phone['phone'])) ? $phone['phone'] : '';
		return (!empty($phone)) ? $phone : array();
	}
	
	
	/**
	 * ���������� ���� ������������� ��������
	 *
	 * @param int $phone_id
	 * @param int $user_id
	 * @param bool $unconfirmed_only
	 * @param string $action  
	 * @return array
	 */ 
	public static function getPhoneCodes($phone_id, $user_id=0, $only_unconfirmed = false){
		global $DB;
		 
		if (empty($user_id)) $user_id = Auth::getUserId();   
		$unconfirmed_where = ($only_unconfirmed) ? " AND tb_code.confrmed = '0' " : "";
		
		$codes = $DB->query(" 
			SELECT 
				tb_code.id, 
				tb_code.code, 
				tb_code.attempt, 
				tb_code.confirmed, 
				DATE_FORMAT(tb_code.code_tstamp, '%d.%m.%Y %H:%i:%s') as code_tstamp,
				tb_code.phone_send_id,
				tb_phone.phone as phone_send, 
				tb_phone.phone_original as phone_send_original,
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_send_public
			FROM auth_user_phone_code as tb_code
			INNER JOIN auth_user_phone as tb_phone ON tb_phone.id = tb_code.phone_send_id
			WHERE tb_code.action = 'confirm'  
				AND tb_phone.user_id = '$user_id' 
				AND tb_code.phone_id = '$phone_id' 
				$unconfirmed_where 
		", 'id');
		
		return $codes;
	}
	
		
	/**
	 * ���������� ��� ��� ����������� �������
	 *
	 * @param int $phone_id
	 * @param int $phone_send_id
	 * @param string $action  
	 * @param int $user_id
	 * @param bool $is_valid
	 * @return array
	 */ 
	public static function getPhoneCode($phone_id, $phone_send_id, $action = 'confirm', $user_id=0, $is_valid=false){
		global $DB;
		 
		if (empty($user_id)) $user_id = Auth::getUserId();   
		
		$code = $DB->query_row(" 
			SELECT 
				tb_code.id, 
				tb_code.code, 
				tb_code.attempt, 
				tb_code.confirmed, 
				DATE_FORMAT(tb_code.code_tstamp, '%H:%i:%s') as code_time,
				DATE_FORMAT(tb_code.code_tstamp, '%d.%m.%Y %H:%i:%s') as code_tstamp,
				tb_phone.phone as phone_send, 
				tb_phone.phone_original as phone_send_original,
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_send_public
			FROM auth_user_phone_code as tb_code  
			INNER JOIN auth_user_phone as tb_phone ON tb_phone.id = tb_code.phone_send_id
			WHERE tb_code.action = '$action'  
				AND tb_code.phone_id = '$phone_id' 
				AND tb_code.phone_send_id = '$phone_send_id' 
				AND tb_phone.user_id = '$user_id' 
		");
		  
		if($is_valid) {
			if($DB->rows == 0) return false;
			if(!empty($code['confirmed'])) return false;
			if($code['attempt'] >= AUTH_USER_PHONE_CONFIRM_ATTEMPT) return false;
			if(convert_date("d.m.Y H:i:s", $code['code_tstamp']) < time() - 3600 * 12) return false;
		}
		
		return $code;
	}
	
	
	/**
	 * ���������� ��������� ��� ������������ ��� ����������� ��������
	 *
	 * @param int $user_id
	 * @param string $action  
	 * @param bool $is_valid
	 * @return array
	 */ 
	public static function getLastCode($action, $user_id = 0, $is_valid=true){
		global $DB;
		   
		if (empty($user_id)) $user_id = Auth::getUserId();   
		  
		$code = $DB->query_row("
			SELECT 
				tb_code.id, 
				tb_code.code, 
				tb_code.attempt,   
				tb_code.confirmed, 
				DATE_FORMAT(tb_code.code_tstamp, '%H:%i:%s') as code_time,
				DATE_FORMAT(tb_code.code_tstamp, '%d.%m.%Y %H:%i:%s') as code_tstamp,
				tb_code.phone_send_id,
				tb_phone.phone as phone_send, 
				tb_phone.phone_original as phone_send_original,
				CONCAT(SUBSTR(tb_phone.phone_original, 1, 8), ' xxx-xx-', SUBSTR(tb_phone.phone_original, -2)) as phone_send_public
			FROM auth_user_phone_code as tb_code  
			INNER JOIN auth_user_phone as tb_phone ON tb_phone.id = tb_code.phone_send_id
			WHERE tb_code.action = '$action'  
				AND tb_phone.user_id = '$user_id'
			ORDER BY tb_code.code_tstamp DESC
			LIMIT 1
		");
		
		if($is_valid) {
			if($DB->rows == 0) return false;
			if(!empty($code['confirmed'])) return false;
			if($code['attempt'] >= AUTH_USER_PHONE_CONFIRM_ATTEMPT) return false;
			if(convert_date("d.m.Y H:i:s", $code['code_tstamp']) < time() - 3600 * 12) return false;
		}
		
		return $code;
	}
	
	 
	/**
	 * ������ ���������� ����
	 * 
	 * @param int $user_id
	 * @param string $action  
	 * @return void
	 */
	public static function clearLastCode($action, $user_id = 0){
		global $DB;	 
		
		if (empty($user_id)) $user_id = Auth::getUserId(); 
		$code = self::getLastCode($action, $user_id);
		if (empty($code)) return true;
		
		$DB->update("UPDATE auth_user_phone_code SET confirmed = 1 WHERE id = '{$code['id']}'");
		return true;
	}
	
}


?>