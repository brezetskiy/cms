<?php
/**
* Конфигурационный файл
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
setlocale(LC_ALL, 'ru_RU.utf-8');
umask(0007); // rwx r-x r-x  Un-mask там где бит = 1 то при создании директории этот бит будет снят (используется бинарное "NOT")
define('DEBUG', 1);
define('CMS_VERSION', '6.0.0');

// Перечень пользователей с неограниченными привилегиями
$_sudoers = array('brezetskiy.sergiy@gmail.com', 'anet@lectra.me');

if (ini_get('magic_quotes_gpc') != 1) {
	echo "Security error! You must enable magic_quotes_gpc in php.ini!";
	exit;
}

/**
* Значение констант, которые заканчиваются на URL
* должны начинатся с /
*
* Значения констант, которые заканчиваются на DIR
* НЕ должны начинатся со слеша (/), но обязательно 
* должны на него заканчиватся
*
* Константы *_ROOT начинаются и заканчиваются на /
*/

/**
* Определяет время выполнения скрипта
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
 * Время запуска скрипта
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
 * Настройки подключения к базе данных
 */
define('DB_DEFAULT_HOST', 'cformat.mysql.ukraine.com.ua');	
define('DB_DEFAULT_NAME', 'cformat_cmsgit');
define('DB_DEFAULT_LOGIN', 'cformat_cmsgit');
define('DB_DEFAULT_PASSWORD', '9v6eawvf');
define('DB_DEFAULT_TYPE', 'mysqli'); 


/**
 * Тип запроса - http или https
 */
define('HTTP_SCHEME', ((isset($_SERVER['HTTP_SSL']) && $_SERVER['HTTP_SSL'] == 1)) ? 'https' : 'http');

/**
 * Путь к корню сайта
 * @name SITE_ROOT
 */
define('SITE_ROOT', str_replace('\\', '/', substr(__FILE__, 0, strpos(__FILE__, 'system'.DIRECTORY_SEPARATOR))));

/**
 * Определяем путь к TMPDIR, если запуск скрипта производится из shell, то не факт, что будет
 * установлена TMPDIR, тем более в параметре ini_get(uppload_tmp_dir)
 */
if (trim(ini_get('upload_tmp_dir')) != '') {
	putenv("TMPDIR=".ini_get('upload_tmp_dir'));
} else {
	putenv("TMPDIR=".SITE_ROOT.'tmp');
}

/**
* Путь к actions файлам
* @name ACTIONS_ROOT
*/
define('ACTIONS_ROOT', SITE_ROOT . 'system/actions/');

/**
* Путь к файлам - триггерам
* @name TRIGGERS_ROOT
*/
define('TRIGGERS_ROOT', SITE_ROOT . 'system/triggers/');

/**
* Путь к лог файлам
* @name LOGS_ROOT
*/
define('LOGS_ROOT', SITE_ROOT . 'system/logs/');

/**
* Путь к html страницам, которые сохранил редактор
* @name CONTENT_ROOT
*/
define('CONTENT_ROOT', SITE_ROOT . 'content/');

/**
* Путь к кешу
* @name CACHE_ROOT
*/
define('CACHE_ROOT', SITE_ROOT . 'cache/');

/**
* Путь к основным библиотекам
* @name LIBS_ROOT
*/
define('LIBS_ROOT', SITE_ROOT . 'system/libs/');

/**
* Путь к библиотекам PEAR
* @name PEAR_ROOT
*/
define('PEAR_ROOT', SITE_ROOT . 'system/pear/');

/**
* Путь к библиотекам PEAR
* @name PEAR_ROOT
*/
define('CVS_ROOT', SITE_ROOT . 'cvs/');

/**
* Путь к include файлам
* @name INC_ROOT
*/
define('INC_ROOT', SITE_ROOT . 'system/inc/');

/**
* Путь ка шаблонам страниц
* @name TEMPLATE_ROOT
*/
define('TEMPLATE_ROOT', SITE_ROOT . 'templates/');

/**
* Путь ка шаблонам дизайна
* @name DESIGN_ROOT
*/
define('DESIGN_ROOT', SITE_ROOT . 'design/');

/**
* Путь к конфигурационным файлам
* @name CONFIG_ROOT
*/
define('CONFIG_ROOT', SITE_ROOT . 'system/config/');

/**
* URL для заливки файлов
* @name UPLOADS_DIR
*/
define('UPLOADS_DIR', 'uploads/');

/**
* Путь для заливки файлов
* @name UPLOADS_ROOT
*/
define('UPLOADS_ROOT', SITE_ROOT . UPLOADS_DIR);

/**
* Путь к pid файлам
* @name RUN_ROOT
*/
define('RUN_ROOT', SITE_ROOT . 'system/run/');

/**
* Путь к временным файлам
* @name TMP_ROOT
*/
define('TMP_ROOT', SITE_ROOT . 'tmp/');

/**
* Путь к файлам графиков
* @name GRAPHS_ROOT
*/
//define('GRAPHS_ROOT', SITE_ROOT . 'system/graphs/');

/**
* Путь к языковым файлам
* @name LANGUAGE_ROOT
*/
define('LANGUAGE_ROOT', SITE_ROOT . 'language/');

/**
* Массив, который будет содержать в себе объекты соединения с СУБД
* array(int $db_id => object, ...)
*/
$_db_connections = array();

/**
* Определяем номер строки, с которой начинать вывод информации
*/
if (!isset($_GET['_start']) || empty($_GET['_start']) || $_GET['_start'] < 0) {
	$_GET['_start'] = 0;
}
$_GET['_start'] = (int)$_GET['_start'];

/**
* Номер строки, с которой начинать вывод
* @name PAGE_START
*/
define('PAGE_START', $_GET['_start']);
unset($_GET['_start']);

/**
* Подключать надо PEAR файлы из директории, в которой находится сайт,
* а не из стандартного PEAR
*/
set_include_path(PEAR_ROOT.PATH_SEPARATOR.get_include_path());

/**
 * Автозагрузчик основных классов, которые находятся в директории LIBS_ROOT
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
				trigger_error(cms_message('CMS', 'Ошибка автозагрузки класса %s', $class_name), E_USER_ERROR);
			}
		}
	}
}

/**
 * Функции
 */
require_once(LIBS_ROOT . 'global.class.php');

if (substr(PHP_OS, 0, 3) == 'WIN') {
	/**
	 * Заглушки для Windows функций
	 */
	require_once(LIBS_ROOT.'windows.class.php');
}

/**
 * Подгрузка конфигурации
 * cache можна удалять, потому нужно проверить наличие файлы и, если его нету, создать.
 */
$query = "SELECT id, code, file FROM cms_language";

if (!file_exists(CACHE_ROOT . 'config.inc.php')){
    $fp = fopen(CACHE_ROOT . 'config.inc.php', 'w+');
    fclose($fp);   
    
    if (!copy('system/config.ini', CACHE_ROOT . 'config.inc.php')){
        die ("Не могу записать в папку кэша");
    }
    
} 

require_once(CACHE_ROOT . 'config.inc.php');
    

if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
	/**
	 * Хост сайта
	 * @name CMS_HOST
	 */
	define('CMS_HOST', strtolower(CMS_HOST_DEFAULT));
} else {
	/**
	 * Хост сайта
	 * @name CMS_HOST
	 * @ignore
	 */
	define('CMS_HOST', strtolower($_SERVER['HTTP_HOST']));
	/* 
		При формировании дистрибутива системы мы не переносим класс DrsDomain
	 	поэтому использовать такой синтаксис - нельзя, так как программа перестает работать.
	 	xn-- домены надо формировать в момент их добавления в систему
	 	rudenko@delta-x.ua 29.07.2010
	define('CMS_HOST', DrsDomain::fromPunycode(strtolower($_SERVER['HTTP_HOST'])));
	*/
}

/**
 * Устанавливает заголовок user_agent для url-fopen-wrappers и класса Download
 */
ini_set('user_agent', CMS_USER_AGENT);


/**
* URL сайта
* @name CMS_URL
*/
define('CMS_URL', HTTP_SCHEME.'://'.CMS_HOST.'/');

/**
* URI сайта
* @name CMS_URI
*/
define('CMS_URI', HTTP_SCHEME.'://'.CMS_HOST);




/**
* Константы, формирующиеся на основании HTTP заголовков
*/

/**
* Локальный IP клиента
* @name HTTP_LOCAL_IP
*/
define('HTTP_LOCAL_IP', getIP());

/**
* IP адрес клиента
* @name HTTP_IP
*/
define('HTTP_IP', globalVar($_SERVER['REMOTE_ADDR'], ''));

/**
* URL адрес, передаваемый из mod_rewrite
* @name HTTP_URL
*/
define('HTTP_URL', globalVar($_GET['_REWRITE_URL'], ''));

/**
 * Определение языка
 */
if (isset($GLOBALS['HTTP_RAW_POST_DATA']) && preg_match("/_language=(\w{2})/", $GLOBALS['HTTP_RAW_POST_DATA'], $matches)) {
	// Определение языка для запросов, которые поступают через Ajax 14.07.2011 rudenko@delta-x.ua
	$_REQUEST['_language'] = $matches[1];
}
$default_language = strtolower(globalVar($_REQUEST['_language'], ''));
if (!defined('CMS_INTERFACE')) {
	trigger_error('Не определена константа CMS_INTERFACE', E_USER_ERROR);
}
$available_languages = preg_split("/,/", constant('LANGUAGE_'.CMS_INTERFACE.'_AVAILABLE'), -1, PREG_SPLIT_NO_EMPTY);
/**
 * Проверяем, поддерживается переданный язык данным интерфейсом.
 * Если нет, то устанавливаем язык, используемый по умолчанию для данного интерфейса.
 */
if (empty($default_language) || !in_array($default_language, $available_languages)) {
	$default_language = constant('LANGUAGE_'.CMS_INTERFACE.'_DEFAULT');
}
/**
 * Определяем язык, который определен в URL
 */
preg_match("/^\/?(".constant('LANGUAGE_'.CMS_INTERFACE.'_REGEXP').")\/?/i", HTTP_URL, $matches);
if (isset($matches[1]) && !empty($matches[1]) && in_array($matches[1], $available_languages)) {
	// Указанный в URL язык поддерживается сайтом
	define('LANGUAGE_URL', $matches[1] . '/');
	define('LANGUAGE_CURRENT', 	$matches[1]);
} else {
	// Указанный в URL язык не принимается сайтом или не указан, берем default
	define('LANGUAGE_CURRENT', 	$default_language);
	define('LANGUAGE_URL', '');
}
/**
 * Определяем константы для данного языка
 */
if (is_file(CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php')) {
	require_once(CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php');
}
else if (is_file(LANGUAGE_ROOT . LANGUAGE_CURRENT . '/' . 'language.php')) {
        if (!copy(LANGUAGE_ROOT . LANGUAGE_CURRENT . '/' . 'language.php', CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php')) {
            die ("Не могу записать в папку кэша");
        }
        else require_once(CACHE_ROOT . 'language.'.LANGUAGE_CURRENT.'.php');
} else {
	trigger_error('Файл с определением языковых параметров - не найден', E_USER_ERROR);
}

/**
 * Определяя константы CURRENT_FORM_URL и CURRENT_LINK_URL 
 * убираем параметры, которые содержат служебную информацию
 */
if (!defined('STDIN')) {
	$url = set_query_param($_SERVER['REQUEST_URI'], '_event_insert_id');
	$url = set_query_param($url, '_event_table_id');
	$url = set_query_param($url, '_event_type');
	
	/**
	 * Константа, используемая для возврата url (для форм)
	 * @name CURRENT_URL_FORM
	 */
	define('CURRENT_URL_FORM', str_replace('&amp;', '&', htmlspecialchars($url, ENT_QUOTES, LANGUAGE_CHARSET)));
	
	/**
	 * Константа, которая содержит в себе URL страницы,
	 * но без указания языка (для форм)
	 * @name NO_LANGUAGE_URL_FORM
	 */
	define('NO_LANGUAGE_URL_FORM', str_replace('&amp;', '&', htmlspecialchars(substr($url, strlen(LANGUAGE_URL)), ENT_QUOTES, LANGUAGE_CHARSET)));
	
	/**
	 * Константа, используемая для возврата url (для ссылок)
	 * @name CURRENT_URL_FORM
	 */
	define('CURRENT_URL_LINK', urlencode($url));
	
	/**
	 * Константа, которая содержит в себе URL страницы,
	 * но без указания языка (для сслок)
	 * @name NO_LANGUAGE_URL_LINK
	 */
	define('NO_LANGUAGE_URL_LINK', urlencode(substr($url, strlen(LANGUAGE_URL))));
}

/**
 * Определяем константу IS_DEVELOPER, предварительно открыв сессию
 * для определения логина вошедшего администратора
 */
session_set_cookie_params(0, '/', Auth::getCookieDomain(CMS_HOST));
/*
 * index_site.php 41 строка - нужно ли тут создавать??
 */
if (session_id() == '') session_start();

if (
	defined('STDIN') || 
	(isset($_SESSION['auth']['login']) && in_array($_SESSION['auth']['login'], $_sudoers))
) {
	define('IS_DEVELOPER', 1);
} else {
	define('IS_DEVELOPER', 0);
}

if (!isset($DB)){
    require_once LIBS_ROOT . '/db.class.php';
    $DB = DB::factory('default');
};     
    
Install::updateMyConfig();

$query = "
    SELECT *
    FROM site_structure
";
x( $query );
x( $DB->query($query) );

?>
