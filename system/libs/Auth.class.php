<?php
/**
 * Класс авторизации
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Copyright 2008, Delta-X ltd.
 */

/**
 * Обработчик событий
 * @package Pilot
 * @subpackage CMS
 */
class Auth {
	
	/**
	 * Зоны
	 * Список используется для определения является указанное имя доменом или субдоменом 
	 * @var array
	 */
	protected static $domain_zones = array(
		'biz','biz.ua','cc','co.cc','co.ua','com','com.ua','crimea.ua','dj','dn.ua','dp.ua',
		'gov.ua','if.ua','in.ua','info','kh.ua','kharkov.ua','kiev.ua','ks.ua','lg.ua','lugansk.ua','lutsk.ua','lviv.ua',
		'me','mk.ua','name','net','net.ua','od.ua','org','org.ua','pl.ua','poltava.ua','pp.ua','ru','rv.ua','sebastopol.ua',
		'su','sumy.ua','te.ua','tv','vn.ua','yalta.ua','zp.ua','zt.ua','ua',
	);
	
	/**
	 * Конструктор класса
	 * @param bool $admin_only - доступ только пользователям с административными правами
	 * @return object
	 */
	public function __construct($admin_only) {
		global $DB;
		
		// Отсылает заголовки браузеру и запускает сессию
		if (headers_sent($file, $line)) {
			trigger_error(cms_message('CMS', 'До запуска модуля авторизации нельзя начинать вывод данных. Вывод начался в файле %s (%d)', $file, $line), E_USER_ERROR);
		}
		Header("Cache-Control: no-cache");
		Header('Last-Modified: '.date('D, d M Y H:i:s', time()).' GMT');
		Header("Pragma: no-cache");
		Header('Expires: ' . gmdate('D, d M Y H:i:s', date('U')-(86400*8)) . ' GMT');
		
		// Сессия в куке умирает как только пользователь закроет окно браузера,
		// какое-либо время ставить - ЗАПРЕЩЕНО, так как мы не знаем время на компьютере клиента
		session_set_cookie_params(0, '/', Auth::getCookieDomain(CMS_HOST));
		session_cache_limiter('nocache');
		
		if (session_id() == '') session_start();
		
		if ($user_id = self::isLoggedIn()) {
			// пользователь на сайте
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
				// Пользователя отключил администратор
				self::logout(cms_message('CMS', 'Сессия была завершена, пожалуйста, войдите на сайт снова'), $admin_only);
				
			} elseif (HTTP_IP != $ip['ip'] || HTTP_LOCAL_IP != $ip['local_ip']) {
				// У пользователя изменился IP, удаляем сессию
				self::logout(cms_message('CMS', 'IP адрес Вашего компьютера был изменен (%s[%s] -> %s[%s]).', HTTP_IP, HTTP_LOCAL_IP, $ip['ip'], $ip['local_ip']), $admin_only);
				
			} elseif ($ip['tstamp'] + AUTH_TIMEOUT < time()) {
				// У пользователя изменился IP, удаляем сессию
				self::logout(cms_message('CMS', 'Время сессии истекло, сессия была автоматически завершена через %d секунд неактивности, последнее действие зафиксировано %s.', AUTH_TIMEOUT, date('d.m.Y H:i:s', $ip['tstamp'])), $admin_only);
				
			} else {
				// Добавляем в статистику время последнего посещения
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
			// Пытаемся зайти по куке
			$this->cookieLogin();
		}
		
		// Блокируем доступ к админке
		if ($admin_only && !self::isAdmin()) {
			self::logout(cms_message('cms', 'Доступ к разделу имеют только администраторы'), true);
		}
	}
	
	/**
	 * Авторизация пользователя по кукам
	 * @return bool
	 */
	private function cookieLogin() {
		global $DB;
		
		/**
		 * Параметры бедутся не из REQUEST, чтобы GET и POST имел более высокий приоритет, чем COOKIE
		 * 
		 * В FF flash ролик закачки файлов не может получить информацию с кук и не определяет
		 * авторизирован пользователь или нет. Поэтому мы добавляем переменные auth_id и auth_code 
		 * в $_POST
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
		
		// Куки не установлены, параметры get'ом не переданы - пользователь не может автоматически войти в систему
		if (is_null($auth_id)) {
			return false;
		}
		
		// Определяем id пользователя по коду сессии
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
			// Запрошенной сесии нет, невозможно авторизировать по куке
			return false;
		}
		
		// Периодически чистим таблицу с куками
		if (rand(0, 100) > 90) {
			$query = "DELETE FROM auth_online WHERE tstamp < NOW() - INTERVAL 30 DAY";
			$DB->delete($query);
		}
		
		/**
		 * При переходе по авторизационной ссылке фиксируем IP посетителя
		 */
		if (is_null($data['ip'])) {
			/**
			 * У пользователя может уже существовать сессия с этого же IP при переходе по 
			 * авторизационной ссылке. В таком случае используем существующую сессию.
			 * Иначе будет duplicate key
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
	 * Проверка прав на редактирование контента, блокирует также попытки редактировать файлы - скрипты
	 * @param string $table_name
	 * @param int $edit_id
	 * @return bool
	 */
	public static function editContent($table_name, $edit_id) {
		global $DB;
		if (IS_DEVELOPER) return true;
		if (!self::isAdmin() || !($user_id = self::isLoggedIn())) return false;
		
		// Проверяем право на редактирование информации в таблице
		if (!self::updateTable($table_name)) {
			return false;
		}
		
		// Для site_structure проверяем специальные права доступа
		if ($table_name == 'site_structure') {
			// Блокируем изменение файлов-скриптов через редактор
			return self::structureAccess($edit_id);
		} else {
			return true;
		}
	}
	
	/**
	 * Проверка - авторизировался пользователь или нет. 
	 * Если пользователь авторизирован, то возвращает его id, если нет, то false
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
	 * Проверка, имеет ли пользователь административные привилегии
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
	 * Возвращает данные о пользователе или пустой массив, если пользователь не залогинился
	 * 
	 * @return array
	 */
	static function getInfo() {
		return (Auth::isLoggedIn()) ? $_SESSION['auth'] : array();
	}
	
	/**
	 * Возвращает id пользователя или 0, если пользователь не авторизован
	 *
	 * @return int
	 */ 
	static function getUserId() {
		return (Auth::isLoggedIn()) ? $_SESSION['auth']['id'] : 0;
	}
	
	/**
	 * Возвращает true, если пользователь пытается 
	 * совершать плохие поступки
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
			 * Если пользователь еще не пытался логиниться - все ok
			 */
			return false;
		} else {
			/**
			 * Если пользователь уже пытался логиниться - считаем его хакером,
			 * если последние 5 попыток входа с его IP были неудачными
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
	 * Завершаем сессию
	 * @param void
	 * @return void
	 */
	static function logout($message = '', $admin_only = false) {
		global $DB;
		
		if (!empty($message)) {
			Action::setError($message);
		}
		
		// Очищаем куки
		$domain = self::getCookieDomain(CMS_HOST);
		setcookie('auth_id', '', 0, '/', $domain);
		setcookie('auth_code', '', 0, '/', $domain);
		setcookie(session_name(), '', time() - 86400, '/', $domain);
		session_destroy();
		
		if (isset($_SESSION['auth']['id'])) {
			// Снимаем все блокировки для данного пользователя
			$query = "delete from cvs_lock where admin_id='".$_SESSION['auth']['id']."'";
			$DB->delete($query);
			
			// удаляем все ссесии, время которых истекло
			$query = "delete from cvs_lock where dtime < now() - interval ".AUTH_TIMEOUT." second";
			$DB->delete($query);
			
			// Выходим из онлайна
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
	 * Производит авторизацию пользователя. Возвращает true, если авторизация прошла успешно. В противном случае возвращает false
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
		
		// Проверяем, может ли пользователь коннектиться под своим IP
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
		
		// Добавляем в таблицу запись о том, что пользователь находится онлайн
		$query = "REPLACE INTO auth_online (user_id, ip, local_ip, cookie_code, auth_group_id) VALUES ('$user_id', '".HTTP_IP."', '".HTTP_LOCAL_IP."', '$cookie_code', '".Auth::getGroup()."')";
		$DB->insert($query);
		
		// В логи записываем информацию о входе пользователя
		self::logLogin($user_id, $tstamp);
		
		$_SESSION['auth'] = $info;
		$_SESSION['auth']['ip'] = HTTP_IP;
		$_SESSION['auth']['local_ip'] = HTTP_LOCAL_IP;
		$_SESSION['auth']['cookie_code'] = $cookie_code;
		$_SESSION['auth']['login_tstamp'] = $tstamp;
		if(!empty($switcher_id)) $_SESSION['auth']['switcher_id'] = $switcher_id;
		
		// Устанавливаем куки
		$cookie_expire = ($remember) ? time() + 86400 *30 : 0;
		setcookie('auth_id', $user_id, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		setcookie('auth_code', $cookie_code, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		setcookie('auth_switcher_id', $switcher_id, $cookie_expire, '/', Auth::getCookieDomain(CMS_HOST));
		
		return true;
	}
	
	/**
	 * Информация о пользователе
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
		 * Второстепенные данные
		 */
		$values = self::getUserData($user_id);
		$info = array_merge($info, $values);
		
		/**
		 * Телефонные номера
		 */
		$info['phone'] = AuthPhone::getMainPhone($user_id, true);
		
		/**
		 * Прячем пароль
		 */
		unset($info['passwd']);
		return $info;
	}
	
	
	/**
	 * Добавляет запись о том, что пользователь зашёл в систему
	 * Добавляется не только внутри данного класса, но и при входе пользователя на сайт
	 * 
	 */
	static public function logLogin($user_id, $tstamp, $login = '', $passwd = '') {
		global $DB;
		
		// Нужно для корректного отображения результатов фильтра в админ-панели для таб. auth_log
		// Барин М.В. <barin@delta-x.ua>, 23.02.2011 
		if(!empty($user_id) && empty($login)){
			$user = $DB->query_row("SELECT login, '-' as passwd FROM auth_user WHERE id = '$user_id'"); 
			$login  = (!empty($user['login'])) ? $user['login'] : '';  
			$passwd = (!empty($user['passwd'])) ? $user['passwd'] : '';
		}
		
		// Добавление данных в базу
		$query = "INSERT INTO auth_log (user_id, ip, login, passwd, login_dtime, logout_dtime) VALUES ('$user_id', '".HTTP_IP."', '".$DB->escape($login)."', '".$DB->escape($passwd)."', from_unixtime($tstamp), from_unixtime($tstamp))";
		$DB->insert($query);
	}
	  
	/**
	 * Создает код авторизации пользователя и возвращает его
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
	 * Пользователи сайта
	 */
	static public function getPrevLoginTimestamp() {
		global $DB;
		
		if (!Auth::isLoggedIn()) {
			return time() - 86400;
		} else {
			// Определяем дату последнего входа пользователя на сайт
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
	 * Проверяем права доступа для групп
	 *
	 * @param array $groups
	 * @return bool
	 */
	static function groupPrivileges($groups) {
		
		if (empty($groups)) {
			// нет ограничения по группам
			return true;
		} elseif (!Auth::isLoggedIn()) {
			// пользователь не залогинился
			return false;
		}
		
		// список групп на проверку
		if (!is_array($groups)) {
			$groups = preg_split("/,/", $groups, -1, PREG_SPLIT_NO_EMPTY);
		}
		
		if (in_array($_SESSION['auth']['group_id'], $groups)) {
			// есть пересекающиеся группы
			return true;
		} else {
			// нет пересекающихся групп
			return false;
		}
	}
	

	/**
	 * Проверяем права пользователя any, registered, checked, confirmed
	 * 
	 * @param string $privilage
	 * @param string $reject_reason
	 * @return bool
	 */
	static function privileges($privilage, &$reject_reason = '') {
		$reject_reason = '';
		
		if (!Auth::isLoggedIn() && $privilage != 'any') {
			// только для зарегистрированных пользователей
			$reject_reason = 'login';
			return false;
		} elseif ($privilage == 'checked' && !$_SESSION['auth']['checked']) {
			// только для проверенных администратором пользователей
			$reject_reason = 'checked';
			return false;
		} elseif ($privilage == 'confirmed' && (!$_SESSION['auth']['confirmed'] || !$_SESSION['auth']['checked'])) {
			// только для тех, кто подтвердил свой email
			$reject_reason = 'confirmed';
			return false;
		} elseif($privilage == 'client' && !$_SESSION['auth']['is_client']){
			// только для тех, кто является клиентом хостинга
			$reject_reason = 'client';
			return false; 
		} else {
			// Всё ок
			return true;
		}
	}
	
	
	/**
	 * Администраторы
	 */
	
	
	/**
	 * Проверка прав на редактирование таблицы
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
	 * Проверка прав на просмотр таблицы
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
	 * Проверяем, есть ли право доступа к редактированию раздела сайта
	 *
	 * @param int $id
	 */
	public static function structureAccess($id) {
		global $DB;
		
		if (IS_DEVELOPER) return true;
		if(!self::isAdmin()) return false;
		$user = self::getInfo();
		
		// Если для группы не введено ни одного ограничения на редактирование сайта,
		// то открываем доступ
		$query = "SELECT * FROM auth_group_structure WHERE group_id='$user[group_id]' LIMIT 1";
		$DB->query($query);
		if ($DB->rows == 0) return true;
		
		// Проверка доступа к конкретному разделу
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
	 * Проверка прав на запуск event файла
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
	 * Возвращает значения второстепенных параметров о пользователе
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
	 * Возвращает имя домена, на которое необходимо ставить куки
	 * Отрезает из имени хоста часть субдомена, возвращает только домен
	 * Основывается на списке зон в $domain_zones
	 * @param string $host
	 */
	public static function getCookieDomain($host) {
		$host_without_zone = preg_replace("~\.(".implode('|', self::$domain_zones).")\.?$~i", '', $host, -1, $replaced);
		if (!$replaced) {
			// Зона неизвестная, возвращаем просто запрошенный хост
			return $host;
		}
		
		$dot = strrpos($host_without_zone, '.');
		if ($dot === false) {
			// Указан домен без субдомена - возвращаем его с точкой
			return '.'.$host;
		} else {
			// Возвращаем адрес домена с отрезанным субдоменом
			return '.'.substr($host, $dot+1);
		}
	}
	
	
	/**
	 * Генератор ников для форума
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
	 * Возвращает форму авторизации
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
		 * Хакерам показываем капчу в форме логина
		 */
		if ($is_captcha && Auth::isHacker()) { 
			$structure_id = (defined('SITE_STRUCTURE_ID')) ? SITE_STRUCTURE_ID : 0;
			$TmplLoginForm->set('captcha_html', Captcha::createHtml($structure_id));
		}
		  
		/**
		 * Вывод формы
		 */
		return $TmplLoginForm->display();
	}
	
	
}


?>