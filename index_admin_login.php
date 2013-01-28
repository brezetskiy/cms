<?php

/**
 * Admin login page
 * 
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, 2008
 */


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
 * Connect to database
 */
$DB = DB::factory('default');


/**
 * Session start
 */
if (!isset($_SESSION)) {
	session_start();
}
 
 
/**
 * If user has already logged in - redirect him further
 */      
if (Auth::isLoggedIn()) {
	header("Location:/Admin/");
	exit;
}


$amnesia = globalVar($_REQUEST['amnesia'], 0);


/**
 * If user clicked an "amnesia"
 */
if ($amnesia) {
	$TmplDesign = new Template(SITE_ROOT.'design/cms/amnesia');
	$TmplDesign->set('captcha_html', Captcha::createHtml('admin'));
	
	
/**
 * If he did not
 */	
} else {
	$TmplDesign = new Template(SITE_ROOT.'design/cms/login');
	$TmplDesign->set('login_form', Auth::displayLoginForm(true));
}


/**
 * Set source for OpenID widgets
 */
$_SESSION['oid_widget']['source'] = 'admin';
	

/**
 * OTP protection
 */ 
if (AuthOTP::isSessionActive()) {
	AuthOTP::clarify();  
}


/**
 * Action messages output
 */
Action::displayStack();
  

/**
 * Clean action error session 
 */
if (isset($_SESSION)) {
	if (isset($_SESSION['ActionReturn'])) unset($_SESSION['ActionReturn']);
	if (isset($_SESSION['ActionError'])) unset($_SESSION['ActionError']);
}


/**
 * Output
 */
echo $TmplDesign->display();
exit; // established to stop viruses that used to be added at the end of file iframe


?>