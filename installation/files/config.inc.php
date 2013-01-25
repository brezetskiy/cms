<?php
/**
* ���������������� ����
* @package Pilot
* @subpackage Includes
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/
error_reporting(E_ALL | E_STRICT);
ini_set('date.timezone', 'Europe/Kiev');
ini_set('magic_quotes_gpc', 1);
ini_set('magic_quotes_runtime', 0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
setlocale(LC_ALL, 'ru_RU.CP1251');
umask(0007); // rwx r-x r-x  Un-mask ��� ��� ��� = 1 �� ��� �������� ���������� ���� ��� ����� ���� (������������ �������� "NOT")
define('DEBUG', 1);
define('CMS_VERSION', '6.0.0');

// �������� ������������� � ��������������� ������������
$_sudoers = array('rudenko@delta-x.ua', 'eugen@delta-x.ua', 'barin@delta-x.ua', 'vovk@delta-x.ua', 'nick@delta-x.ua', 'tark@delta-x.ua', 'o.kunytska@gmail.com', 'brezetskiy.sergiy@gmail.com', '{user}');

if (ini_get('magic_quotes_gpc') != 1) {
	echo "Security error! You must enable magic_quotes_gpc in php.ini!";
	exit;
}

/**
* �������� ��������, ������� ������������� �� URL
* ������ ��������� � /
*
* �������� ��������, ������� ������������� �� DIR
* �� ������ ��������� �� ����� (/), �� ����������� 
* ������ �� ���� ������������
*
* ��������� *_ROOT ���������� � ������������� �� /
*/

/**
* ���������� ����� ���������� �������
* @param void
* @return float
*/
function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
function diffmicrotime() {
	static $prev_time = TIME_TOTAL;
	
	$microtime = getmicrotime();
	$return = $microtime - $prev_time;
	$prev_time = $microtime;
	return $return;
}


/**
 * ����� ������� �������
 *
 */
define('TIME_TOTAL', getmicrotime());

if (substr(PHP_OS, 0 , 3) == 'WIN') {
	define('TIME_USER', 0);
	define('TIME_SYSTEM', 0);
} else {
	$dat = getrusage();
	define('TIME_USER', ($dat["ru_utime.tv_sec"] * 1000000 + $dat["ru_utime.tv_usec"]) / 1000000);
	define('TIME_SYSTEM', ($dat["ru_stime.tv_sec"] * 1000000 + $dat["ru_stime.tv_usec"]) / 1000000);
	unset($dat);
}


/**
 * ��������� ����������� � ���� ������
 */
define('DB_DEFAULT_NAME', '{dbname}');
define('DB_DEFAULT_HOST', '{dbhost}');
define('DB_DEFAULT_LOGIN', '{dbuser}');
define('DB_DEFAULT_PASSWORD', '{dbpass}');
define('DB_DEFAULT_TYPE', 'mysqli'); 

/**
 * ��� ������� - http ��� https
 */
define('HTTP_SCHEME', ((isset($_SERVER['HTTP_SSL']) && $_SERVER['HTTP_SSL'] == 1)) ? 'https' : 'http');

/**
 * ���� � ����� �����
 * @name SITE_ROOT
 */
define('SITE_ROOT', str_replace('\\', '/', substr(__FILE__, 0, strpos(__FILE__, 'system'.DIRECTORY_SEPARATOR))));

/**
 * ���������� ���� � TMPDIR, ���� ������ ������� ������������ �� shell, �� �� ����, ��� �����
 * ����������� TMPDIR, ��� ����� � ��������� ini_get(uppload_tmp_dir)
 */
if (trim(ini_get('upload_tmp_dir')) != '') {
	putenv("TMPDIR=".ini_get('upload_tmp_dir'));
} else {
	putenv("TMPDIR=".SITE_ROOT.'tmp');
}

/**
* ���� � actions ������
* @name ACTIONS_ROOT
*/
define('ACTIONS_ROOT', SITE_ROOT . 'system/actions/');

/**
* ���� � ������ - ���������
* @name TRIGGERS_ROOT
*/
define('TRIGGERS_ROOT', SITE_ROOT . 'system/triggers/');

/**
* ���� � ��� ������
* @name LOGS_ROOT
*/
define('LOGS_ROOT', SITE_ROOT . 'system/logs/');

/**
* ���� � html ���������, ������� �������� ��������
* @name CONTENT_ROOT
*/
define('CONTENT_ROOT', SITE_ROOT . 'content/');

/**
* ���� � ����
* @name CACHE_ROOT
*/
define('CACHE_ROOT', SITE_ROOT . 'cache/');

/**
* ���� � �������� �����������
* @name LIBS_ROOT
*/
define('LIBS_ROOT', SITE_ROOT . 'system/libs/');

/**
* ���� � ����������� PEAR
* @name PEAR_ROOT
*/
define('PEAR_ROOT', SITE_ROOT . 'system/pear/');

/**
* ���� � ����������� PEAR
* @name PEAR_ROOT
*/
define('CVS_ROOT', SITE_ROOT . 'cvs/');

/**
* ���� � include ������
* @name INC_ROOT
*/
define('INC_ROOT', SITE_ROOT . 'system/inc/');

/**
* ���� �� �������� �������
* @name TEMPLATE_ROOT
*/
define('TEMPLATE_ROOT', SITE_ROOT . 'templates/');

/**
* ���� �� �������� �������
* @name DESIGN_ROOT
*/
define('DESIGN_ROOT', SITE_ROOT . 'design/');

/**
* ���� � ���������������� ������
* @name CONFIG_ROOT
*/
define('CONFIG_ROOT', SITE_ROOT . 'system/config/');

/**
* URL ��� ������� ������
* @name UPLOADS_DIR
*/
define('UPLOADS_DIR', 'uploads/');

/**
* ���� ��� ������� ������
* @name UPLOADS_ROOT
*/
define('UPLOADS_ROOT', SITE_ROOT . UPLOADS_DIR);

/**
* ���� � pid ������
* @name RUN_ROOT
*/
define('RUN_ROOT', SITE_ROOT . 'system/run/');

/**
* ���� � ��������� ������
* @name TMP_ROOT
*/
define('TMP_ROOT', SITE_ROOT . 'tmp/');

/**
* ���� � ������ ��������
* @name GRAPHS_ROOT
*/
//define('GRAPHS_ROOT', SITE_ROOT . 'system/graphs/');

/**
* ������, ������� ����� ��������� � ���� ������� ���������� � ����
* array(int $db_id => object, ...)
*/
$_db_connections = array();

/**
* ���������� ����� ������, � ������� �������� ����� ����������
*/
if (!isset($_GET['_start']) || empty($_GET['_start']) || $_GET['_start'] < 0) {
	$_GET['_start'] = 0;
}
$_GET['_start'] = (int)$_GET['_start'];

/**
* ����� ������, � ������� �������� �����
* @name PAGE_START
*/
define('PAGE_START', $_GET['_start']);
unset($_GET['_start']);

/**
* ���������� ���� PEAR ����� �� ����������, � ������� ��������� ����,
* � �� �� ������������ PEAR
*/
set_include_path(PEAR_ROOT.PATH_SEPARATOR.get_include_path());

/**
 * ������������� �������� �������, ������� ��������� � ���������� LIBS_ROOT
 */
function __autoload($class_name) {
	if (is_file(LIBS_ROOT.$class_name.'.class.php')) {
		require_once(LIBS_ROOT.$class_name.'.class.php');
	} elseif (is_file(LIBS_ROOT.strtolower($class_name).'.class.php')) {
		require_once(LIBS_ROOT.strtolower($class_name).'.class.php');
	} else {
		static $_LIBS_CACHE = array();
		if (!file_exists(CACHE_ROOT.'libs_cache.php')) {
			Misc::refreshLibsCache();
		}
		require_once(CACHE_ROOT.'libs_cache.php');
		if (isset($_LIBS_CACHE[strtolower($class_name)])) {
			require_once(LIBS_ROOT.$_LIBS_CACHE[strtolower($class_name)]);
		} else {
			Misc::refreshLibsCache();
			require(CACHE_ROOT.'libs_cache.php');
			if (isset($_LIBS_CACHE[strtolower($class_name)])) {
				require_once(LIBS_ROOT.$_LIBS_CACHE[strtolower($class_name)]);
			} else {
				trigger_error(cms_message('CMS', '������ ������������ ������ %s', $class_name), E_USER_ERROR);
			}
		}
	}
}

/**
 * �������
 */
require_once(LIBS_ROOT . 'global.class.php');

if (substr(PHP_OS, 0, 3) == 'WIN') {
	/**
	 * �������� ��� Windows �������
	 */
	require_once(LIBS_ROOT.'windows.class.php');
}

/**
 * ��������� ������������
 */
require_once(CACHE_ROOT . 'config.inc.php');

if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
	/**
	 * ���� �����
	 * @name CMS_HOST
	 */
	define('CMS_HOST', strtolower(CMS_HOST_DEFAULT));
} else {
	/**
	 * ���� �����
	 * @name CMS_HOST
	 * @ignore
	 */
	define('CMS_HOST', strtolower($_SERVER['HTTP_HOST']));
	/* 
		��� ������������ ������������ ������� �� �� ��������� ����� DrsDomain
	 	������� ������������ ����� ��������� - ������, ��� ��� ��������� ��������� ��������.
	 	xn-- ������ ���� ����������� � ������ �� ���������� � �������
	 	rudenko@delta-x.ua 29.07.2010
	define('CMS_HOST', DrsDomain::fromPunycode(strtolower($_SERVER['HTTP_HOST'])));
	*/
}

/**
 * ������������� ��������� user_agent ��� url-fopen-wrappers � ������ Download
 */
ini_set('user_agent', CMS_USER_AGENT);


/**
* URL �����
* @name CMS_URL
*/
define('CMS_URL', HTTP_SCHEME.'://'.CMS_HOST.'/');

/**
* URI �����
* @name CMS_URI
*/
define('CMS_URI', HTTP_SCHEME.'://'.CMS_HOST);




/**
* ���������, ������������� �� ��������� HTTP ����������
*/

/**
* ��������� IP �������
* @name HTTP_LOCAL_IP
*/
define('HTTP_LOCAL_IP', getIP());

/**
* IP ����� �������
* @name HTTP_IP
*/
define('HTTP_IP', globalVar($_SERVER['REMOTE_ADDR'], ''));

/**
* URL �����, ������������ �� mod_rewrite
* @name HTTP_URL
*/
define('HTTP_URL', globalVar($_GET['_REWRITE_URL'], ''));

/**
 * ����������� �����
 */
if (isset($GLOBALS['HTTP_RAW_POST_DATA']) && preg_match("/_language=(\w{2})/", $GLOBALS['HTTP_RAW_POST_DATA'], $matches)) {
	// ����������� ����� ��� ��������, ������� ��������� ����� Ajax 14.07.2011 rudenko@delta-x.ua
	$_REQUEST['_language'] = $matches[1];
}
$default_language = strtolower(globalVar($_REQUEST['_language'], ''));
if (!defined('CMS_INTERFACE')) {
	trigger_error('�� ���������� ��������� CMS_INTERFACE', E_USER_ERROR);
}
$available_languages = preg_split("/,/", constant('LANGUAGE_'.CMS_INTERFACE.'_AVAILABLE'), -1, PREG_SPLIT_NO_EMPTY);
/**
 * ���������, �������������� ���������� ���� ������ �����������.
 * ���� ���, �� ������������� ����, ������������ �� ��������� ��� ������� ����������.
 */
if (empty($default_language) || !in_array($default_language, $available_languages)) {
	$default_language = constant('LANGUAGE_'.CMS_INTERFACE.'_DEFAULT');
}
/**
 * ���������� ����, ������� ��������� � URL
 */
preg_match("/^\/?(".constant('LANGUAGE_'.CMS_INTERFACE.'_REGEXP').")\/?/i", HTTP_URL, $matches);
if (isset($matches[1]) && !empty($matches[1]) && in_array($matches[1], $available_languages)) {
	// ��������� � URL ���� �������������� ������
	define('LANGUAGE_URL', $matches[1] . '/');
	define('LANGUAGE_CURRENT', 	$matches[1]);
} else {
	// ��������� � URL ���� �� ����������� ������ ��� �� ������, ����� default
	define('LANGUAGE_CURRENT', 	$default_language);
	define('LANGUAGE_URL', '');
}
/**
 * ���������� ��������� ��� ������� �����
 */
if (is_file(CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php')) {
	require_once(CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php');
} else {
	trigger_error('���� � ������������ �������� ���������� - �� ������', E_USER_ERROR);
}

/**
 * ��������� ��������� CURRENT_FORM_URL � CURRENT_LINK_URL 
 * ������� ���������, ������� �������� ��������� ����������
 */
if (!defined('STDIN')) {
	$url = set_query_param($_SERVER['REQUEST_URI'], '_event_insert_id');
	$url = set_query_param($url, '_event_table_id');
	$url = set_query_param($url, '_event_type');
	
	/**
	 * ���������, ������������ ��� �������� url (��� ����)
	 * @name CURRENT_URL_FORM
	 */
	define('CURRENT_URL_FORM', str_replace('&amp;', '&', htmlspecialchars($url, ENT_QUOTES, LANGUAGE_CHARSET)));
	
	/**
	 * ���������, ������� �������� � ���� URL ��������,
	 * �� ��� �������� ����� (��� ����)
	 * @name NO_LANGUAGE_URL_FORM
	 */
	define('NO_LANGUAGE_URL_FORM', str_replace('&amp;', '&', htmlspecialchars(substr($url, strlen(LANGUAGE_URL)), ENT_QUOTES, LANGUAGE_CHARSET)));
	
	/**
	 * ���������, ������������ ��� �������� url (��� ������)
	 * @name CURRENT_URL_FORM
	 */
	define('CURRENT_URL_LINK', urlencode($url));
	
	/**
	 * ���������, ������� �������� � ���� URL ��������,
	 * �� ��� �������� ����� (��� �����)
	 * @name NO_LANGUAGE_URL_LINK
	 */
	define('NO_LANGUAGE_URL_LINK', urlencode(substr($url, strlen(LANGUAGE_URL))));
}

/**
 * ���������� ��������� IS_DEVELOPER, �������������� ������ ������
 * ��� ����������� ������ ��������� ��������������
 */
session_set_cookie_params(0, '/', Auth::getCookieDomain(CMS_HOST));
if (session_id() == '') session_start();

if (
	defined('STDIN') || 
	(isset($_SESSION['auth']['login']) && in_array($_SESSION['auth']['login'], $_sudoers))
) {
	define('IS_DEVELOPER', 1);
} else {
	define('IS_DEVELOPER', 0);
}

?>
