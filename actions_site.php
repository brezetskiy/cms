<?php

/**
 * Actions main handler 
 * 
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, 2004
 */

 
ignore_user_abort(true);


/**
 * Define the interface to support localization
 * @ignore 
 */
define('CMS_INTERFACE', 'SITE');


/**
 * Config
 */
require_once('system/config.inc.php');


/**
 * Define is it asynchronous request
 */
if (isset($GLOBALS['HTTP_RAW_POST_DATA']) || isset($GLOBALS['JsHttpRequest'])) {
	define('AJAX', 1);
	$JsHttpRequest = new JsHttpRequest("windows-1251");
} else {
	define('AJAX', 0);
}


/**
 * Connect to database
 */
$DB = DB::factory('default');


/**
 * Save extended requests log
 */
if (CMS_EXTENDED_LOG) {
	Action::saveExtendedLog('site'); 
}


$event = globalVar($_REQUEST['_event'], '');
$event = preg_replace('~/$~', '', $event);


/**
 * Session start
 */
if (!isset($_SESSION)) {
	session_start();
}


/**
 * Чистим сессию от старых сообщений
 */
if (isset($_SESSION)) {
	if (isset($_SESSION['ActionReturn'])) unset($_SESSION['ActionReturn']);
	if (isset($_SESSION['ActionError'])) unset($_SESSION['ActionError']);
}


/**
 * Action is empty
 */
if (empty($event)) {
	Action::setError(cms_message('CMS', 'Не указано действие, которое необходимо выполнять.'));
	Action::onError();
} 


/** 
 * Invoke action script
 */
if (is_file(ACTIONS_ROOT.'site/'.$event.'.act.php')) {
	require_once(ACTIONS_ROOT.'site/'.$event.'.act.php');
	
} else {
	Action::setError(cms_message('CMS', 'Не найден обработчик события %s', $event));
	Action::onError();
}

Action::finish();

exit; // established to stop viruses that used to be added at the end of file iframe

?>
