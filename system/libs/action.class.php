<?php

/**
 * Actions response handler
 * 
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2012, Delta-X ltd.
 */

class Action {


	/**
	 * Variants of action messages
	 *
	 * @var array 
	 */
	private static $stacks = array('error', 'warning', 'success', 'info');


	/****************************************************************************************************/
	/*           								Basic handlers 	 									    */
	/****************************************************************************************************/
 
	/**
	 * Set message to stack 
	 * 
	 * @param string $stack (error, warning, success, info, log)
	 * @param string $message
	 * @return void
	 */
	private static function setMessageToStack($stack, $message, $module = "CMS") {
		global $_RESULT;
		
		$message = cms_message($module, $message);
		if  (defined('AJAX') && AJAX) {
			if (CMS_USE_DELTA_MESSAGE) {
				//$_SESSION['ActionReturn']['delta'][$stack][] = @iconv("windows-1251", "utf-8", $message);
                                //$_SESSION['ActionReturn']['delta'][$stack][] = $message;
                                $_RESULT['action_ok'.$stack] = $message; 
                                //x($message);
			} else {
				$_RESULT['action_'.$stack] = (isset($_RESULT['action_'.$stack])) ? $message+';'.$_RESULT['action_'.$stack] : $message;
			}
			
		} else {
			$_SESSION['ActionReturn'][$stack][md5($message)] = $message;
		}
	}


	/**
	 * Define is there any message in the stack
	 * 
	 * @param string $stack (error, warning, success, info, log)
	 * @return bool
	 */
	private static function isStack($stack) {
		global $_RESULT;
		
		if  (defined('AJAX') && AJAX) {
			if (CMS_USE_DELTA_MESSAGE) {
				return (isset($_SESSION['ActionReturn']['delta'][$stack]) && count($_SESSION['ActionReturn']['delta'][$stack]) > 0) ? true : false;
			} else {
				return (!empty($_RESULT['action_'.$stack])) ? true : false;
			}
			
		} else {
			return (isset($_SESSION['ActionReturn'][$stack]) && count($_SESSION['ActionReturn'][$stack]) > 0) ? true : false;
		}
	}


	/**
	 * Display messages from stack for AJAX requests
	 * 
	 * @return void
	 */
	public static function displayAjaxStack() {
		global $_RESULT;
		
		if (!Action::isMessage()) {
			return false;
		}
		
		/**
		 * Handling AJAX situation
		 */
		if  (defined('AJAX') && AJAX && CMS_USE_DELTA_MESSAGE) {
			if (empty($_RESULT['javascript'])) $_RESULT['javascript'] = "";  
			$_RESULT['javascript'] .= "delta_message(".json_encode($_SESSION['ActionReturn']['delta'])."); ";	
		}
		
		unset($_SESSION['ActionReturn']);
	}
	
	/**
	 * Display messages from stack for standard requests
	 *  
	 * @param object $Template = null
	 * @param bool $is_admin
	 * @return void
	 */
	public static function displayStack($Template = null, $is_admin = false) {
		global $TmplDesign;
		
		if (empty($Template)) $Template = $TmplDesign;
		$delta_messages = array();
		
		if (!Action::isMessage()) {
			unset($_SESSION['ActionReturn']);
			return false;
		}
		
		reset(Action::$stacks);
		while(list(, $stack) = each(Action::$stacks)){
			if (!Action::isStack($stack)) continue;
			
			reset($_SESSION['ActionReturn'][$stack]);
			while (list(, $message) = each($_SESSION['ActionReturn'][$stack])) {
				if(CMS_USE_DELTA_MESSAGE && !$is_admin) {
					$delta_messages[$stack][] = @iconv('windows-1251', 'utf-8', $message);
					continue;	
				} 
				 
				$Template->iterate("/$stack/", null, array('message' => $message));
			}
		}
		
		/** 
		 * Вывод delta-сообщений
		 */ 
		if (CMS_USE_DELTA_MESSAGE && !$is_admin && !empty($delta_messages) && is_array($delta_messages)){
			$Template->iterate('/onload/', null, array('function' => "delta_message(".json_encode($delta_messages).");"));  
		}
		  
		unset($_SESSION['ActionReturn']);
	}
	
	
	/**
	 * Define is there any message in any stack
	 * 
	 * @return bool
	 */
	private static function isMessage(){
		global $_RESULT;
		
		reset(Action::$stacks);
		while(list(, $stack) = each(Action::$stacks)) {
			if (Action::isStack($stack)) {
				return true;
			}
		}
		
		return false;
	}
	

	/**
	 * Construct header for return
	 * 
	 * @param bool $error - exit by error
 	 * @return void
	 */
	public static function finish($error = false) {
		global $_RESULT;
		
		$return_type = globalVar($_REQUEST['_return_type'], '');
		
		// If you remove this piece of code, PHP output buffer will absorb all of the errors, up to 4KB
		if  (!defined('AJAX') || !AJAX) {
			$data = ob_get_clean();
			
			if (strlen(trim($data)) > 1) {
				echo $data;
				exit;
			}
		}
		
		/**
		 * Determine the execution time
		 */
		$dat = getrusage();
		$utime_after = ($dat["ru_utime.tv_sec"] * 1000000 + $dat["ru_utime.tv_usec"]) / 1000000;
		$stime_after = ($dat["ru_stime.tv_sec"] * 1000000 + $dat["ru_stime.tv_usec"]) / 1000000;
		Action::setLog(cms_message('CMS', 'Время выполнения скрипта: %01.3f / %01.3f / %01.3f с.', getmicrotime() - TIME_TOTAL, $utime_after - TIME_USER, $stime_after - TIME_SYSTEM));

		/**
		 * Set the parameters that pass:
		 * - number of the last inserted record 
		 * - table number of action
		 * - action (insert or update) 
		 */
		if (isset($GLOBALS['_event_insert_id']) && isset($GLOBALS['_event_table_id']) && isset($GLOBALS['_event_type'])) {
			$query_string = '_event_insert_id='.$GLOBALS['_event_insert_id'].'&_event_table_id='.$GLOBALS['_event_table_id'].'&_event_type='.$GLOBALS['_event_type'];
			$_REQUEST['_return_path'] = (strpos($_REQUEST['_return_path'], '?') !== false) ? $_REQUEST['_return_path'].'&'.$query_string : $_REQUEST['_return_path'].'?'.$query_string;
		}
		
		/**
		 * Error handling and return for ajax requests
		 */
		if (defined('AJAX') && AJAX) {
			Action::displayAjaxStack();
			exit;
		}

		/**
		 * If _return_path is not specified, then just leave
		 */
		if (!isset($_REQUEST['_return_path']) || empty($_REQUEST['_return_path'])) {
			exit;
		}
		
		if ($error == true || isset($_SESSION['ActionReturn']['error']) || isset($_SESSION['ActionReturn']['warning'])) {
			header('Location: '.$_REQUEST['_return_path'].'#event');
			exit;
		}
		
		if (isset($_REQUEST['_return_anchor']) && !empty($_REQUEST['_return_anchor'])) {
			$_REQUEST['_return_path'].= '#'.$_REQUEST['_return_anchor'];
		}
		  
		switch ($return_type) {
			case 'self':  // Checkbox "Add more" is selected
				header('Location: '.$_REQUEST['_return_path']);
				break;
			case 'close': // Checkbox "Not update" is selected
				echo "<html><head></head><body><script language=JavaScript> window.close(); </script></body></html>";
				break;
			case 'popup':
				echo "<html><head></head><body><script language=JavaScript> window.opener.location.href='".$_REQUEST['_return_path']."'; window.close(); </script></body></html>";
				break;
			case 'update_foreign_key':
				echo "<html><head></head><body> <script language=JavaScript> window.opener.refreshFKey('$_REQUEST[_update_form_id]', '$_REQUEST[_update_field_id]',  '$_REQUEST[_update_field_name]', '$GLOBALS[_event_insert_id]'); window.close(); </script></body></html>";
				break;
			default:
				if(!headers_sent()) {
					header('Location: '.$_REQUEST['_return_path']);
				}
				break;
		}
		
		exit;
	}


	/****************************************************************************************************/
	/*           								Error handlers 	 									    */
	/****************************************************************************************************/
	
	/**
	 * Set error message to stack 
	 * 
	 * @param string $message
	 * @param string $module - used for multilingual interface
	 * @return void
	 */
	public static function setError($message, $module = "CMS"){
		global $_RESULT;
		
		Action::setMessageToStack('error', $message, $module);
	}
	
	
	/**
	 * Define is there any error message in the stack
	 * 
	 * @return bool
	 */
	public static function isError(){
		global $_RESULT;
		
		return Action::isStack('error');
	}
	
	
	/**
	 * Display error messages from stack
	 * 
	 * @param string $message - if is not empty, set it to stack first.
	 * @param string $module - used for multilingual interface
	 * @return void
	 */
	public static function onError($message = '', $module = "CMS"){
		global $_RESULT;
		 
		/**
		 * Set current message to stack 
		 */
		if (!empty($message)) {
			Action::setError($message, $module);
		}  
		
		if (defined('AJAX') && AJAX) {
			Action::displayAjaxStack();
			exit; 
			 
		} else { 	
			$_SESSION['ActionError'] = $_REQUEST;
			array_walk_recursive($_SESSION['ActionError'], 'stripslashes_callback');
			
			if (isset($_REQUEST['_error_path'])) {
				$_REQUEST['_return_path'] = $_REQUEST['_error_path'];
			}
	
			Action::finish(true);
		}
	}
	
	
	/****************************************************************************************************/
	/*           							  Warning handlers										    */
	/****************************************************************************************************/
	
	/**
	 * Set warning message to stack 
	 * 
	 * @param string $message
	 * @param string $module - used for multilingual interface
	 */
	public static function setWarning($message, $module = "CMS") {
		global $_RESULT;
		
		Action::setMessageToStack('warning', $message, $module);
	}
	
	
	/**
	 * Define is there any warning message in the stack
	 * 
	 * @return bool
	 */
	private static function isWarning(){
		global $_RESULT;
		
		return Action::isStack('warning');
	}
	
	
	/****************************************************************************************************/
	/*           	              			    Success handlers										*/
	/****************************************************************************************************/
	
	/**
	 * Set success message to stack 
	 * 
	 * @param string $message
	 * @param string $module - used for multilingual interface
	 */
	public static function setSuccess($message, $module = "CMS") {
		global $_RESULT;
		
		Action::setMessageToStack('success', $message, $module);
	}
	
	
	/**
	 * Define is there any success message in the stack
	 * 
	 * @return bool
	 */
	private static function isSuccess(){
		global $_RESULT;
		
		return Action::isStack('success');
	}
	
	
	/****************************************************************************************************/
	/*           								  Info handlers									        */
	/****************************************************************************************************/
	
	/**
	 * Set info message to stack 
	 * 
	 * @param string $message
	 * @param string $module - used for multilingual interface
	 */
	public static function setInfo($message, $module = "CMS") {
		global $_RESULT;
		
		Action::setMessageToStack('info', $message, $module);
	}
	
	
	/**
	 * Define is there any info message in the stack
	 * 
	 * @return bool
	 */
	private static function isInfo(){
		global $_RESULT;
		
		return Action::isStack('info');
	}
	
	
	/****************************************************************************************************/
	/*           								Log handlers 										    */
	/****************************************************************************************************/
	
	/**
	 * Set log message to stack 
	 * 
	 * @param string $message
	 * @param string $module - used for multilingual interface
	 */
	public static function setLog($message, $module = "CMS") {
		global $_RESULT;
		
		$message = cms_message($module, $message);
		
		if  (defined('AJAX') && AJAX) {
			 $_RESULT['action_log'] = (isset($_RESULT['action_log'])) ? $message+';'.$_RESULT['action_log'] : $message;
		} else {
			$_SESSION['ActionReturn']['log'][md5($message)] = $message;
		}
	}
	
	
	/**
	 * Define is there any log in the stack
	 * 
	 * @return bool
	 */
	private static function isLog(){
		global $_RESULT;
		
		if  (defined('AJAX') && AJAX) {
			return (!empty($_RESULT['action_log'])) ? true : false;
		} else {
			return (isset($_SESSION['ActionReturn']['log']) && count($_SESSION['ActionReturn']['log']) > 0) ? true : false;
		}
	}
	 
	 
	/**
	 * Display logs
	 * 
	 * @return void
	 */
	public static function displayLog() {
		global $TmplDesign;
		
		if (!Action::isLog()) return false;
		
		$TmplDesign->set('show_responce_log', true);	
			
		reset($_SESSION['ActionReturn']['log']);
		while (list(, $log) = each($_SESSION['ActionReturn']['log'])) {
			$TmplDesign->iterate('/responce_log/', null, array('text' => $log));
		}
		  
		unset($_SESSION['ActionReturn']['log']);
	}
	
	
	/****************************************************************************************************/
	/*           							  Other methods 										    */
	/****************************************************************************************************/

	/**
	 * Saves all the changes that have occurred in the system log file
	 * 
	 * @param string $text
	 * @return void
	 */
	static function saveLog($text) {
		if (!is_dir(LOGS_ROOT.'event/')) {
			mkdir(LOGS_ROOT.'event/', 0777, true);
		}
		$fp = fopen(LOGS_ROOT.'event/'.date('Y_m').'.log', 'a');
		fwrite($fp, date('Y-m-d H:i:s')."\t".HTTP_IP."\t".HTTP_LOCAL_IP."\t".$_SESSION['auth']['login']."\t".str_replace(array("\n", "\r"), " ", trim($text))."\n");
		fclose($fp);
	}
	
	
	/**
	 * Saves the extended log of changes
	 * 
	 * @return void
	 */ 
	static function saveExtendedLog($source='admin') {
		if (!isset($_REQUEST['_event'])) return;
		 
		/**
		 * If click on the bottom of the table button "Save changes",  
		 * the event will be passed as an array with the coordinates [_event][cms / table_update] => array (x, y)
		 */ 
		if (is_array($_REQUEST['_event'])) {
			$_REQUEST['_event'] = array_keys($_REQUEST['_event']);
			$_REQUEST['_event'] = reset($_REQUEST['_event']);
		} 
		 
		$filename = LOGS_ROOT.'actions/'.date('Y-m-d').'/'.$source.'/'.preg_replace("/\\/$/", "", $_REQUEST['_event']).'.log'; 
		if (!is_dir(dirname($filename))) mkdir(dirname($filename), 0777, true);
		 
		$user_login = (!empty($_SESSION['auth']['login'])) ? str_replace(array("\n", "\r"), " ", trim($_SESSION['auth']['login'])) : 'root'; 
		
		$params = array('request' => $_REQUEST, 'session' => $_SESSION);
		$params = str_replace(array("\n", "\r"), " ", trim(serialize($params)));
		
		$fp = fopen($filename, 'a');  
		fwrite($fp, date('Y-m-d H:i:s')."\t".HTTP_IP."\t".HTTP_LOCAL_IP."\t$user_login\t<ACTION_PARAMS_START>$params<ACTION_PARAMS_END>\n");
		fclose($fp);
	}
	
}


?>