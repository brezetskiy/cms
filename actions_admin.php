<?php

/** 
 * Admin actions main handler 
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
 * Authentication for password-protected partitions
 */
new Auth(true);


/**
 * Define type of variables
 */
$table_id = globalVar($_REQUEST['_table_id'], 0);
$add_more = globalVar($_POST['_add_more'], 0);
$no_refresh = globalVar($_POST['no_refresh'], 0);


/**
 * Commit the value of variable "do not update" to COOKIE array
 */
setcookie('no_refresh', $no_refresh, time() + 86400 * 30, '/', CMS_HOST);


/**
 * Set redirect link corresponding to "do not update" variable value
 */
if ($no_refresh) {
	$_REQUEST['_return_type'] = 'close';
}


/**
 * Commit the value of variable "add more" to COOKIE array
 */
setcookie('add_more', $add_more, time() + 86400 * 30, '/', CMS_HOST);


/**
 * Set redirect link corresponding to "add more" variable value
 */
if ($add_more) {
	$_REQUEST['_return_path'] = $_REQUEST['_error_path'];
	$_REQUEST['_return_type'] = 'self';
}


/**
 * Save extended requests log
 */
if (CMS_EXTENDED_LOG) {
	Action::saveExtendedLog('admin');
}

/**
 * Define action type
 */
if (!isset($_REQUEST['_event']) || empty($_REQUEST['_event'])) {
	Action::onError(cms_message('CMS', 'Не указано действие, которое необходимо выполнять.'));
	
} elseif (is_array($_REQUEST['_event'])) {
	$keys = array_keys($_REQUEST['_event']);
	
	/**
	 * Important! Do not add reset() instruction here.
	 * 
	 * There are some actions by default. They are defined as hidden inputs.
	 * These actions invoked by JavaScript after Form.submit().  
	 * Usually it is action of filtering the table with select lists.
	 */
	$event = end($keys);
	unset($keys);
	
} else {
	$event = $_REQUEST['_event'];
	$event = preg_replace('~/$~', '', $event);
}

/**
 * Check event file for existance
 */
if (!is_file(ACTIONS_ROOT.'admin/'.$event.'.act.php')) {
	Action::onError(cms_message('CMS', 'Не найден обработчик события %s', $event));
	
/** 
 * Checking user permissions
 */
} elseif (!Auth::actionEvent($event)) {
	Action::onError(cms_message('CMS', 'Доступ к %s событию - запрещен.', $event));
	
/** 
 * Executing script
 */
} else {
	Action::saveLog('Executing event admin/'.$event.'.act.php');
	require_once(ACTIONS_ROOT.'admin/'.$event.'.act.php');
}


/**
 * Action finish
 */
Action::finish();
exit; // established to stop viruses that used to be added at the end of file iframe


?>