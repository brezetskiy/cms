<?php
/**
 * ����� �����������
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Copyright 2008, Delta-X ltd.
 */

/**
 * ���������� �������
 * @package Pilot
 * @subpackage CMS
 */
class Auth {
	
	/**
	 * ����
	 * ������ ������������ ��� ����������� �������� ��������� ��� ������� ��� ���������� 
	 * @var array
	 */
	protected static $domain_zones = array(
		'biz','biz.ua','cc','co.cc','co.ua','com','com.ua','crimea.ua','dj','dn.ua','dp.ua',
		'gov.ua','if.ua','in.ua','info','kh.ua','kharkov.ua','kiev.ua','ks.ua','lg.ua','lugansk.ua','lutsk.ua','lviv.ua',
		'me','mk.ua','name','net','net.ua','od.ua','org','org.ua','pl.ua','poltava.ua','pp.ua','ru','rv.ua','sebastopol.ua',
		'su','sumy.ua','te.ua','tv','vn.ua','yalta.ua','zp.ua','zt.ua','ua',
	);
	
	/**
	 * ����������� ������
	 * @param bool $admin_only - ������ ������ ������������� � ����������������� �������
	 * @return object
	 */
	public function __construct($admin_only) {
		global $DB;
		
		// �������� ��������� �������� � ��������� ������
		if (headers_sent($file, $line)) {
			trigger_error(cms_message('CMS', '�� ������� ������ ����������� ������ �������� ����� ������. ����� ������� � ����� %s (%d)', $file, $line), E_USER_ERROR);
		}
		Header("Cache-Control: no-cache");
		Header('Last-Modified: '.date('D, d M Y H:i:s', time()).' GMT');
		Header("Pragma: no-cache");
		Header('Expires: ' . gmdate('D, d M Y H:i:s', date('U')-(86400*8)) . ' GMT');
		
		// ������ � ���� ������� ��� ������ ������������ ������� ���� ��������,
		// �����-���� ����� ������� - ���������, ��� ��� �� �� ����� ����� �� ���������� �������
		session_set_cookie_params(0, '/', Auth::getCookieDomain(CMS_HOST));
		session_cache_limiter('nocache');
		
		if (session_id() == '') session_start();
		
		if ($user_id = self::isLoggedIn()) {
			// ������������ �� �����
			$user = self::getInfo();
			
			if (isset($_POST['auth_code'])) {
				$user['cookie_code'] = $_POST['auth_code'];
			}
			
			$query = "
				select ip, local_ip, unix_timestamp(tstamp) as tstamp
				from auth_online 
				where 
					user_id='$user_id' and
					cookie_code='$user[cookie_code]' and
					auth_group_id='".Auth::getGroup()."'
					/* AND tstamp > NOW() - interval ".AUTH_TIMEOUT." second */
			";
			$ip = $DB->query_row($query);
			if ($DB->rows <> 1) {
				// ������������ �������� �������������
				self::logout(cms_message('CMS', '������ ���� ���������, ����������, ������� �� ���� �����'), $admin_only);
				
			} elseif (HTTP_IP != $ip['ip'] || HTTP_LOCAL_IP != $ip['local_ip']) {
				// � ������������ ��������� IP, ������� ������
				self::logout(cms_message('CMS', 'IP ����� ������ ���������� ��� ������� (%s[%s] -> %s[%s]).', HTTP_IP, HTTP_LOCAL_IP, $ip['ip'], $ip['local_ip']), $admin_only);
				
			} elseif ($ip['tstamp'] + AUTH_TIMEOUT < time()) {
				// � ������������ ��������� IP, ������� ������
				self::logout(cms_message('CMS', '����� ������ �������, ������ ���� ������������� ��������� ����� %d ������ ������������, ��������� �������� ������������� %s.', AUTH_TIMEOUT, date('d.m.Y H:i:s', $ip['tstamp'])), $admin_only);
				
			} else {
				// ��������� � ���������� ����� ���������� ���������
				$query = "
					update auth_log set  
						logout_dtime = now()
					where 
						user_id='".$_SESSION['auth']['id']."' and 
						login_dtime=from_unixtime('".$_SESSION['auth']['login_tstamp']."')
				";
				$DB->update($query);
				$query = "
					update auth_online set tstamp=now()
					where 
						user_id='$user_id' and 
						ip='".HTTP_IP."' and 
						local_ip='".HTTP_LOCAL_IP."' and 
						auth_group_id='".Auth::getGroup()."'
				";
				$DB->update($query);
			}
		} else {
			// �������� ����� �� ����
			$this->cookieLogin();
		}
		
		// ��������� ������ � �������
		if ($admin_only && !self::isAdmin()) {
			self::logout(cms_message('cms', '������ � ������� ����� ������ ��������������'), true);
		}
	}
	
	/**
	 * ����������� ������������ �� �����
	 * @return bool
	 */
	private function cookieLogin() {
		global $DB;
		
		/**
		 * ��������� ������� �� �� REQUEST, ����� GET � POST ���� ����� ������� ���������, ��� COOKIE
		 * 
		 * � FF flash ����� ������� ������ �� ����� �������� ���������� � ��� � �� ����������
		 * ������������� ������������ ��� ���. ������� �� ��������� ���������� auth_id � auth_code 
		 * � $_POST
		 */
		$auth_id = $auth_code = null;
		if (isset($_GET['auth_id']) && isset($_GET['auth_code'])) {
			$auth_id = (int)$_GET['auth_id'];
			$auth_code = $_GET['auth_code'];
			$auth_source = 'get';
		} elseif (isset($_POST['auth_id']) && isset($_POST['auth_code'])) {
			$auth_id = (int)$_POST['auth_id'];
			$auth_code = $_POST['auth_code'];
			$auth_source = 'post';
		} elseif (isset($_COOKIE['auth_id']) && isset($_COOKIE['auth_code'])) {
			$auth_id = (int)$_COOKIE['auth_id'];
			$auth_code = $_COOKIE['auth_code'];
			$auth_source = 'cookie';
		}
		
		$switcher_id = globalVar($_COOKIE['auth_switcher_id'], 0);
		
		// ���� �� �����������, ��������� get'�� �� �������� - ������������ �� ����� ������������� ����� � �������
		if (is_null($auth_id)) {
			return false;
		}
		
		// ���������� id ������������ �� ���� ������
		$query = "
			SELECT user_id, cookie_code, ip
			FROM auth_online
			WHERE 
				user_id='$auth_id' and
				cookie_code='$auth_code' and
				auth_group_id='".Auth::getGroup()."' and
				((ip='".HTTP_IP."' and local_ip='".HTTP_LOCAL_IP."') or (ip is null)) and
				tstamp > NOW() - interval ".AUTH_TIMEOUT." second
		";
		$data = $DB->query_row($query);

		if ($DB->rows != 1) {
			// ����������� ����� ���, ���������� �������������� �� ����
			return false;
		}
		
		// ������������ ������ ������� � ������
		if (rand(0, 100) > 90) {
			$query = "DELETE FROM auth_online WHERE tstamp < NOW() - INTERVAL 30 DAY";
			$DB->delete($query);
		}
		
		/**
		 * ��� �������� �� ��������������� ������ ��������� IP ����������
		 */
		if (is_null($data['ip'])) {
			/**
			 * � ������������ ����� ��� ������������ ������ � ����� �� IP ��� �������� �� 
			 * ��������������� ������. � ����� ������ ���������� ������������ ������.
			 * ����� ����� duplicate key
			 */
			$query = "
				select cookie_code 
				from auth_online
				where 
					user_id = '$auth_id' and
					ip = '".HTTP_IP."' and
					local_ip = '".HTTP_LOCAL_IP."' and
					tstamp > NOW() - interval ".AUTH_TIMEOUT." second
			";
			$current_cookie_code = $DB->result($query);
			
			if ($DB->rows == 1) {
				return self::login($data['user_id'], true, $current_cookie_code, $switcher_id);
			}
			
			$query = "
				update auth_online
				set
					ip = '".HTTP_IP."',
					local_ip = '".HTTP_LOCAL_IP."'
				where 
					user_id='$auth_id' and
					cookie_code='$auth_code' and
					ip is null
			";
			$DB->update($query); 
		}
		
		return self::login($data['user_id'], true, $data['cookie_code'], $switcher_id);
	}
	
	/**
	 * �������� ���� �� �������������� ��������, ��������� ����� ������� ������������� ����� - �������
	 * @param string $table_name
	 * @param int $edit_id
	 * @return bool
	 */
	public static function editContent($table_name, $edit_id) {
		global $DB;
		if (IS_DEVELOPER) return true;
		if (!self::isAdmin() || !($user_id = self::isLoggedIn())) return false;
		
		// ��������� ����� �� �������������� ���������� � �������
		if (!self::updateTable($table_name)) {
			return false;
		}
		
		// ��� site_structure ��������� ����������� ����� �������
		if ($table_name == 'site_structure') {
			// ��������� ��������� ������-�������� ����� ��������
			return self::structureAccess($edit_id);
		} else {
			return true;
		}
	}
	
	/**
	 * �������� - ��������������� ������������ ��� ���. 
	 * ���� ������������ �������������, �� ���������� ��� id, ���� ���, �� false
	 *
	 * @return mixed
	 */
	static function isLoggedIn() {
		if (isset($_SESSION['auth']['id']) && !empty($_SESSION['auth']['id']) && $_SESSION['auth']['id'] > 0) {
			return $_SESSION['auth']['id'];
		}
		if (isset($_SESSION['auth'])) {
			unset($_SESSION['auth']);
		}
		return false;
	}
	
	
	/**
	 * ��������, ����� �� ������������ ���������������� ����������
	 * @return boolean
	 */
	static function isAdmin() {
		if (!self::isLoggedIn()) {
			return false;
		} else {
			$info = self::getInfo();
			return $info['is_admin'];
		}
	}
	
	/**
	 * ���������� ������ � ������������ ��� ������ ������, ���� ������������ �� �����������
	 * 
	 * @return array
	 */
	static function getInfo() {
		return (Auth::isLoggedIn()) ? $_SESSION['auth'] : array();
	}
	
	/**
	 * ���������� id ������������ ��� 0, ���� ������������ �� �����������
	 *
	 * @return int
	 */ 
	static function getUserId() {
		return (Auth::isLoggedIn()) ? $_SESSION['auth']['id'] : 0;
	}
	
	/**
	 * ���������� true, ���� ������������ �������� 
	 * ��������� ������ ��������
	 * 
	 * @param bool $_hard
	 * @return boolean
	 */
	static public function isHacker($_hard = false) {
		global $DB;
		
		if (Auth::isLoggedIn() && !$_hard) {
			return false;  
		}
		
		$login_stat = $DB->query("select * from auth_log where ip = '".HTTP_IP."' order by login_dtime desc limit 5");
		if (rand(0,1000) > 950) {
			$DB->delete("delete from auth_log where login_dtime < now() - interval 1 month");
		}
	
		if ($DB->rows==0) {
			/**
			 * ���� ������������ ��� �� ������� ���������� - ��� ok
			 */
			return false;
		} else {
			/**
			 * ���� ������������ ��� ������� ���������� - ������� ��� �������,
			 * ���� ��������� 5 ������� ����� � ��� IP ���� ����������
			 */
			reset($login_stat);
			while (list(,$row) = each($login_stat)) {
				if ($row['user_id'] > 0) {
					return false;
				}
			}
			return true;
		}
	}
	
	
	/**
	 * ��������� ������
	 * @param void
	 * @return void
	 */
	static function logout($message = '', $admin_only = false) {
		global $DB;
		
		if (!empty($message)) {
			Action::setError($message);
		}
		
		// ������� ����
		$domain = self::getCookieDomain(CMS_HOST);
		setcookie('auth_id', '', 0, '/', $domain);
		setcookie('auth_code', '', 0, '/', $domain);
		setcookie(session_name(), '', time() - 86400, '/', $domain);
		session_destroy();
		
		if (isset($_SESSION['auth']['id'])) {
			// ������� ��� ���������� ��� ������� ������������
			$query = "delete from cvs_lock where admin_id='".$_SESSION['auth']['id']."'";
			$DB->delete($query);
			
			// ������� ��� ������, ����� ������� �������
			$query = "delete from cvs_lock where dtime < now() - interval ".AUTH_TIMEOUT." second";
			$DB->delete($query);
			
			// ������� �� �������
			if (isset($_SESSION['auth']['cookie_code'])) {
				$query = "DELETE FROM auth_online WHERE user_id='".$_SESSION['auth']['id']."' and cookie_code='".$_SESSION['auth']['cookie_code']."'";
			} else {
				$query = "DELETE FROM auth_online WHERE user_id='".$_SESSION['auth']['id']."'";
			}
			$DB->delete($query);
		}
		
		unset($_SESSION);
		
		if ($admin_only) {
			header("Location:/index_admin_login.php?return_path=".CURRENT_URL_LINK);
			exit;
		}
	}
	
	/**
	 * ���������� ����������� ������������. ���������� true, ���� ����������� ������ �������. � ��������� ������ ���������� false
	 *
	 * @param int $user_id
	 * @param bool $remember
	 * @param string $cookie_code
	 * @param int $switcher_id
	 * @return bool
	 */
	static public function login($user_id, $remember = false, $cookie_code = '', $switcher_id = 0) {
		global $DB;
		
		if (empty($cookie_code)) {
			$cookie_code = Misc::keyBlock(30, 1, '');
		}
		
		$info = self::info($user_id);
		if (empty($info)) {
			return false;
		}
		
		// ���������, ����� �� ������������ ������������ ��� ����� IP
		if (!empty($info['allow_ip'])) {
			$query = "
				select count(*)
				from auth_user_allow_ip
				where
					user_id='$user_id' and
					((inet_aton('".HTTP_IP."') >= ip_from and inet_aton('".HTTP_IP."') <= ip_to) or (inet_aton('".HTTP_LOCAL_IP."') >= ip_from and inet_aton('".HTTP_LOCAL_IP."') <= ip_to))
				limit 1
			";
			$is_listed = $DB->result($query);
			if ($is_listed == 0) {
				return array();
			}
		}
		$tstamp = time();
		
		// ��������� � ������� ������ � ���, ��� ������������ ��������� ������
		$query = "REPLACE INTO auth_online (user_id, ip, local_ip, cookie_code, auth_group_id) VALUES ('$user_id', '".HTTP_IP."', '".HTTP_LOCAL_IP."', '$cookie_code', '".Auth::getGroup()."')";
		$DB->insert($query);
		
		// � ���� ���������� ���������� � ����� ������������
		self::logLogin($user_id, $tstamp);
		
		$_SESSION['auth'] = $info;
		$_SESSION['auth']['ip'] = HTTP_IP;
		$_SESSION['auth']['local_ip'] = HTTP_LOCAL_IP;
		$_SESSION['auth']['cookie_code'] = $cookie_code;
		$_SESSION['auth']['login_tstamp'] = $tstamp;
		if(!empty($switcher_id)) $_SESSION['auth']['switcher_id'] = $switcher_id;
		
		// ������������� ����
		$cookie_expire = ($remember) ? time() + 86400 *30 : 0;
		setcookie('auth_id', $user_id, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		setcookie('auth_code', $cookie_code, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		setcookie('auth_switcher_id', $switcher_id, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		
		return true;
	}
	
	/**
	 * ���������� � ������������
	 *
	 * @param int $user_id
	 * @return array
	 */
	static public function info($user_id) {
		global $DB;
		
		$query = "
			select 
				tb_user.*,
				tb_group.is_admin,
				tb_group.uniq_name as group_uniq_name
			from auth_user as tb_user
			left join auth_group as tb_group on tb_user.group_id = tb_group.id
			where tb_user.id='$user_id' and tb_user.active=1
		";
		$info = $DB->query_row($query);
		
		/**
		 * �������������� ������
		 */
		$values = self::getUserData($user_id);
		$info = array_merge($info, $values);
		
		/**
		 * ���������� ������
		 */
		$info['phone'] = AuthPhone::getMainPhone($user_id, true);
		
		/**
		 * ������ ������
		 */
		unset($info['passwd']);
		return $info;
	}
	
	
	/**
	 * ��������� ������ � ���, ��� ������������ ����� � �������
	 * ����������� �� ������ ������ ������� ������, �� � ��� ����� ������������ �� ����
	 * 
	 */
	static public function logLogin($user_id, $tstamp, $login = '', $passwd = '') {
		global $DB;
		
		// ����� ��� ����������� ����������� ����������� ������� � �����-������ ��� ���. auth_log
		// ����� �.�. <barin@delta-x.ua>, 23.02.2011 
		if(!empty($user_id) && empty($login)){
			$user = $DB->query_row("SELECT login, '-' as passwd FROM auth_user WHERE id = '$user_id'"); 
			$login  = (!empty($user['login'])) ? $user['login'] : '';  
			$passwd = (!empty($user['passwd'])) ? $user['passwd'] : '';
		}
		
		// ���������� ������ � ����
		$query = "INSERT INTO auth_log (user_id, ip, login, passwd, login_dtime, logout_dtime) VALUES ('$user_id', '".HTTP_IP."', '".$DB->escape($login)."', '".$DB->escape($passwd)."', from_unixtime($tstamp), from_unixtime($tstamp))";
		$DB->insert($query);
	}
	  
	/**
	 * ������� ��� ����������� ������������ � ���������� ���
	 * @param int $user_id
	 * @return string
	 */
	static public function createAuthCode($user_id) {
		global $DB;
		
		$code = Misc::keyBlock(30, 1, '');
		$query = "REPLACE INTO auth_online (user_id, cookie_code, auth_group_id) VALUES ('$user_id', '$code', '".Auth::getGroup()."')";
		$DB->insert($query);
		return $code;
	}
	
	/**
	 * ������������ �����
	 */
	static public function getPrevLoginTimestamp() {
		global $DB;
		
		if (!Auth::isLoggedIn()) {
			return time() - 86400;
		} else {
			// ���������� ���� ���������� ����� ������������ �� ����
			$query = "
				select unix_timestamp(max(logout_dtime))
				from auth_log
				where
					user_id='".$_SESSION['auth']['id']."'
				and login_dtime!=from_unixtime('".$_SESSION['auth']['login_tstamp']."')
			";
			$lastlogin = $DB->result($query);
			return ($DB->rows == 0 || empty($lastlogin)) ? time() - 86400 : $lastlogin;
		}
	}
	
	/**
	 * ��������� ����� ������� ��� �����
	 *
	 * @param array $groups
	 * @return bool
	 */
	static function groupPrivileges($groups) {
		
		if (empty($groups)) {
			// ��� ����������� �� �������
			return true;
		} elseif (!Auth::isLoggedIn()) {
			// ������������ �� �����������
			return false;
		}
		
		// ������ ����� �� ��������
		if (!is_array($groups)) {
			$groups = preg_split("/,/", $groups, -1, PREG_SPLIT_NO_EMPTY);
		}
		
		if (in_array($_SESSION['auth']['group_id'], $groups)) {
			// ���� �������������� ������
			return true;
		} else {
			// ��� �������������� �����
			return false;
		}
	}
	

	/**
	 * ��������� ����� ������������ any, registered, checked, confirmed
	 * 
	 * @param string $privilage
	 * @param string $reject_reason
	 * @return bool
	 */
	static function privileges($privilage, &$reject_reason = '') {
		$reject_reason = '';
		
		if (!Auth::isLoggedIn() && $privilage != 'any') {
			// ������ ��� ������������������ �������������
			$reject_reason = 'login';
			return false;
		} elseif ($privilage == 'checked' && !$_SESSION['auth']['checked']) {
			// ������ ��� ����������� ��������������� �������������
			$reject_reason = 'checked';
			return false;
		} elseif ($privilage == 'confirmed' && (!$_SESSION['auth']['confirmed'] || !$_SESSION['auth']['checked'])) {
			// ������ ��� ���, ��� ���������� ���� email
			$reject_reason = 'confirmed';
			return false;
		} elseif($privilage == 'client' && !$_SESSION['auth']['is_client']){
			// ������ ��� ���, ��� �������� �������� ��������
			$reject_reason = 'client';
			return false; 
		} else {
			// �� ��
			return true;
		}
	}
	
	
	/**
	 * ��������������
	 */
	
	
	/**
	 * �������� ���� �� �������������� �������
	 * @param mixed $table_name_id
	 * @return bool
	 */
	public static function updateTable($table_name_id) {
		global $DB;
		
		if (IS_DEVELOPER) return true;
		if(!self::isAdmin()) return false;
		$user_id = self::isLoggedIn();
		if (!$user_id) return false;
		
		$table_id = cmsTable::getIdByAlias($DB->db_alias, $table_name_id);
		
		$query = "
			(
				SELECT tb_change.table_id
				FROM auth_user AS tb_user
				INNER JOIN auth_group_action AS tb_g_a ON tb_g_a.group_id=tb_user.group_id
				INNER JOIN auth_action_table_update AS tb_change ON tb_change.action_id=tb_g_a.action_id
				WHERE tb_user.id='$user_id' AND tb_change.table_id='$table_id'
			) UNION (
				SELECT tb_change.table_id
				FROM auth_action AS tb_action
				INNER JOIN auth_action_table_update AS tb_change ON tb_change.action_id=tb_action.id
				WHERE tb_action.is_default=1 AND tb_change.table_id='$table_id'
			) LIMIT 1
		";
		$DB->query($query);
		return ($DB->rows == 0) ? false : true;
	}
	
	/**
	 * �������� ���� �� �������� �������
	 * @param mixed $table_name_id
	 * @return bool 
	 */
	public static function selectTable($table_name_id) {
		global $DB;
		
		if (IS_DEVELOPER) return true;
		if(!self::isAdmin()) return false;
		$user_id = self::isLoggedIn();
		if (!$user_id) return false; 
		
		$table_id = cmsTable::getIdByAlias($DB->db_alias, $table_name_id);
		
		$query = "
			(
				SELECT tb_change.table_id
				FROM auth_user AS tb_user
				INNER JOIN auth_group_action AS tb_g_a ON tb_g_a.group_id=tb_user.group_id
				INNER JOIN auth_action_table_select AS tb_change ON tb_change.action_id=tb_g_a.action_id
				WHERE tb_user.id='$user_id' AND tb_change.table_id='$table_id'
			) UNION (
				SELECT tb_change.table_id
				FROM auth_action AS tb_action
				INNER JOIN auth_action_table_select AS tb_change ON tb_change.action_id=tb_action.id
				WHERE tb_action.is_default=1 AND tb_change.table_id='$table_id'
			) LIMIT 1
		";
		$DB->query($query);
		return ($DB->rows == 0) ? false : true;
	}
		
	/**
	 * ���������, ���� �� ����� ������� � �������������� ������� �����
	 *
	 * @param int $id
	 */
	public static function structureAccess($id) {
		global $DB;
		
		if (IS_DEVELOPER) return true;
		if(!self::isAdmin()) return false;
		$user = self::getInfo();
		
		// ���� ��� ������ �� ������� �� ������ ����������� �� �������������� �����,
		// �� ��������� ������
		$query = "SELECT * FROM auth_group_structure WHERE group_id='$user[group_id]' LIMIT 1";
		$DB->query($query);
		if ($DB->rows == 0) return true;
		
		// �������� ������� � ����������� �������
		$query = "
			SELECT tb_structure.id
			FROM auth_group_structure AS tb_link
			INNER JOIN site_structure_relation AS tb_relation ON tb_relation.parent=tb_link.structure_id
			INNER JOIN site_structure AS tb_structure ON tb_structure.id=tb_link.structure_id
			WHERE 
					tb_link.group_id='$user[group_id]'
				AND tb_relation.id='$id';
		";
		$DB->query($query);
		return ($DB->rows > 0) ? true : false;
	}

	/**
	 * �������� ���� �� ������ event �����
	 * @param string $evenf_file
	 * @return bool
	 */
	public static function actionEvent($event_file) {
		global $DB;
		
		if (IS_DEVELOPER) return true;
		if(!self::isAdmin()) return false;
		$user_id = self::isLoggedIn();
		if (!$user_id) return false; 

		$query = "
			(
				SELECT tb_event.id
				FROM cms_event tb_event
				INNER JOIN cms_module tb_module ON (tb_event.module_id = tb_module.id)
				INNER JOIN auth_action_event tb_action2event ON (tb_event.id = tb_action2event.event_id)
				INNER JOIN auth_group_action tb_group2action ON (tb_action2event.action_id = tb_group2action.action_id)
				INNER JOIN auth_user tb_user ON (tb_group2action.group_id = tb_user.group_id)
				WHERE
					tb_user.id='$user_id'
					AND LOWER(CONCAT(tb_module.name, '/', tb_event.name))='$event_file'
			) UNION (
				SELECT tb_event.id
				FROM cms_event tb_event
				INNER JOIN cms_module tb_module ON (tb_event.module_id = tb_module.id)
				INNER JOIN auth_action_event tb_action2event ON (tb_event.id = tb_action2event.event_id)
				INNER JOIN auth_action tb_action ON (tb_action2event.action_id = tb_action.id)
				WHERE
					tb_action.is_default=1
					AND LOWER(CONCAT(tb_module.name, '/', tb_event.name))='$event_file'
			) LIMIT 1
		";
		$DB->query($query);
		return ($DB->rows == 0) ? false : true;
	}
	
	
	static private function getGroup() {
		global $_cms_auth_group;
		return (isset($_cms_auth_group[strtolower(CMS_HOST)])) ? $_cms_auth_group[strtolower(CMS_HOST)] : 0;
	}
	

	/**
	 * ���������� �������� �������������� ���������� � ������������
	 * @param int $user_id
	 * @param mixed $params
	 * @return array
	 */
	static public function getUserData($user_id = 0, $params = array()){
		global $DB;  
		
		if (empty($user_id)) $user_id = self::getUserId(); 
		  
		$query = "
			SELECT 
				tb_param.uniq_name as param,
				case tb_value.data_type
					when 'char' then tb_value.value_char
					when 'file' then tb_value.value_char
					when 'image' then tb_value.value_char
					when 'decimal' then tb_value.value_decimal
					when 'bool' then tb_value.value_int
					when 'fkey' then tb_value.value_int
					when 'fkey_table' then tb_value.value_int
					else tb_value.value_text  
				end as value
			FROM auth_user_group_param as tb_param
			LEFT JOIN auth_user_data as tb_value ON tb_param.id = tb_value.param_id and tb_value.user_id = '$user_id'
			WHERE tb_param.data_type != 'devider'
			".where_clause("tb_param.uniq_name", $params)."  
		";
		$user_params = $DB->fetch_column($query);
		return $user_params;
	}
	
	
	/**
	 * ���������� ��� ������, �� ������� ���������� ������� ����
	 * �������� �� ����� ����� ����� ���������, ���������� ������ �����
	 * ������������ �� ������ ��� � $domain_zones
	 * @param string $host
	 */
	public static function getCookieDomain($host) {
		$host_without_zone = preg_replace("~\.(".implode('|', self::$domain_zones).")\.?$~i", '', $host, -1, $replaced);
		if (!$replaced) {
			// ���� �����������, ���������� ������ ����������� ����
			return $host;
		}
		
		$dot = strrpos($host_without_zone, '.');
		if ($dot === false) {
			// ������ ����� ��� ��������� - ���������� ��� � ������
			return '.'.$host;
		} else {
			// ���������� ����� ������ � ���������� ����������
			return '.'.substr($host, $dot+1);
		}
	}
	
	
	/**
	 * ��������� ����� ��� ������
	 *
	 * @param string $email
	 * @return string
	 */
	public static function createNickname($email){ 
		global $DB;
		
		$nickname = strtolower(substr($email, 0, strpos($email, '@'))); 
		$nickname_possible_duplicates = $DB->query("  
			SELECT id, email, nickname FROM auth_user 
			WHERE LOWER(TRIM(SUBSTRING(email, 1, LOCATE('@', email)-1))) = '$nickname'
				OR nickname = '$nickname' 
		"); 
		 
		if($DB->rows > 0){
			$nickname_real_duplicates = array();
			
			reset($nickname_possible_duplicates);
			while(list($index, $row) = each($nickname_possible_duplicates)){
				if(empty($row['nickname'])) continue;  
				if(strpos($row['nickname'], $nickname) === FALSE) continue;
				
				$nickname_number = str_replace($nickname, '', $row['nickname']);
				if(!preg_match('/^[0-9]*$/', $nickname_number) && !empty($nickname_number)){
					continue;
				}
				
				if(empty($nickname_number)) $nickname_number = 1;  
				$nickname_real_duplicates[] = (int) $nickname_number;
			}
			   
			rsort($nickname_real_duplicates);
			
			$nickname_counter = (!empty($nickname_real_duplicates[0])) ? $nickname_real_duplicates[0] + 1 : 1;
			$nickname = $nickname.sprintf("%02d", $nickname_counter);
		}
		
		return $nickname;
	}
	
	
	/**
	 * ���������� ����� �����������
	 *
	 * @param bool $is_admin
	 * @param bool $is_captcha
	 * @param array $params
	 * @return string
	 */  
	public static function displayLoginForm($is_admin = false, $is_captcha = true, $params = array()){
		$TmplLoginForm = ($is_admin) ? new Template("user/login_form_admin") : new Template("user/login_form");
		$TmplLoginForm->setGlobal('is_captcha', $is_captcha); 
		   
		$params['headline'] = (!empty($params['headline'])) ? $params['headline'] : "";
		$TmplLoginForm->set($params); 
		 
		/**
		 * ������� ���������� ����� � ����� ������
		 */
		if ($is_captcha && Auth::isHacker()) { 
			$structure_id = (defined('SITE_STRUCTURE_ID')) ? SITE_STRUCTURE_ID : 0;
			$TmplLoginForm->set('captcha_html', Captcha::createHtml($structure_id));
		}
		  
		/**
		 * ����� �����
		 */
		return $TmplLoginForm->display();
	}
	
	
}


?>