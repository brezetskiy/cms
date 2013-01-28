<?php

/**
 * Admin main delegating page
 * 
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, 2004
 */

 
/**
 * Define the interface to support localization
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');


/**
 * Config
 */
require_once('./system/config.inc.php');


/**
 * Force https, if it is specified in system settings
 */
if (AUTH_ADMIN_FORCE_HTTPS && $_SERVER['REQUEST_METHOD'] == 'GET' && HTTP_SCHEME == 'http') {
	header('Location: https://'.CMS_HOST.$_SERVER['REQUEST_URI']);
	exit;
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
 * OTP protection
 */  
if(AUTH_OTP_ADMIN_ENABLE && !AuthOTP::checkAccess(Auth::getUserId()) && empty($_SESSION['otp_admin_passed'])){
	AuthOTP::passAdmin();    
} 


/**
 * Get information about current site
 */
$Site = new Site($_GET['_REWRITE_URL'], 'cms_structure');
define('CMS_STRUCTURE_ID', $Site->structure_id);
define('CMS_STRUCTURE_URL', $Site->url.'/');


/**
 * Load main admin design template 
 */
$TmplDesign = new Template(SITE_ROOT.'design/cms/default');


/**
 * Define base url
 */
if (!isset($_SERVER['REDIRECT_URL'])) {
	$TmplDesign->setGlobal('base_url', '/Admin/'.$Site->url.'/');
} else {
	$TmplDesign->setGlobal('base_url', $_SERVER['REDIRECT_URL']);
}


/**
 * Define info about current partition 
 */
$page_data = $DB->query_row("
	SELECT 
		name_".LANGUAGE_CURRENT." AS name,
		module_id,
		IF(title_".LANGUAGE_CURRENT."='', name_".LANGUAGE_CURRENT.", title_".LANGUAGE_CURRENT.") AS title
	FROM cms_structure
	WHERE id='".CMS_STRUCTURE_ID."'
", false);

$TmplDesign->set($page_data);


/**
 * Action logs output 
 */
Action::displayLog();


/**
 * Action messages output 
 */
Action::displayStack(null, true);


/**
 * Connect functions creating template variables. 
 * Functions should start before the content page will be created. 
 * It allows user to change the contents of page template inside that page
 */
require_once(SITE_ROOT.'design/cms/default.inc.php');


/**
 * Content loading
 */
ob_start();


/** 
 * Define file name with php script
 */ 
$content_file = CONTENT_ROOT.'cms_structure/'.$Site->filename.'.'.LANGUAGE_CURRENT.'.php';

$DB->query("
	SELECT tb_view.structure_id
	FROM auth_action_view AS tb_view
	INNER JOIN auth_action AS tb_action ON tb_action.id = tb_view.action_id
	INNER JOIN auth_group_action AS tb_group_action ON tb_group_action.action_id = tb_action.id
	WHERE tb_group_action.group_id='".$_SESSION['auth']['group_id']."'
		AND tb_view.structure_id = '".CMS_STRUCTURE_ID."'
");

if (empty($Site->filename)) {
	
	/**
	 * The requested page does not exist, print message and block cache. 
	 * First, check the page existence, and then access. 
	 * All users must have access to 404 page
	 */
	echo '<BR><BR><BR><BR><center><B>'.cms_message('CMS', 'Запрошенной страницы не существует на сайте.').'</B></center>';
	
} elseif ($DB->rows == 0 && !IS_DEVELOPER) {
	
	/**
	 * Access denied
	 */
	echo '<BR><BR><BR><BR><center><B>'.cms_message('CMS', 'У Вас нет прав на просмотр данной страницы.').'</B></center>';
	
} elseif (!is_file($content_file) || !is_readable($content_file)) {
	
	/**
	 * File with the current page content not found, print message and block caching 
	 */
	echo '<BR><BR><BR><BR><center><B>'.cms_message('CMS', 'Не найден файл с содержимым страницы.').'</B></center>';

} else {
	$template_language = Template::getLanguage(CONTENT_ROOT.'cms_structure/'.$Site->filename);
	
	/** 
	 * Check is there any template for this page
	 */
	if (false !== $template_language) {
		$TmplContent = new Template(CONTENT_ROOT.'cms_structure/'.$Site->filename, $template_language);
		
		/**
		 * Action messages output
		 */
		Action::displayStack($TmplContent, true);
	}
	
	require_once($content_file);
	
	/** 
	 * Parse content. Then delete template handler
	 */
	if (isset($TmplContent) && $TmplContent instanceof Template) {
		echo $TmplContent->display();
		unset($TmplContent);
	}

}

unset($content_file);


/**
 * Cross-Site authorization
 */
if (Auth::isLoggedIn()) {
	
	/**
	 * Get info about current site
	 */
	$AES = new AES();
	$AES->key = $AES->makeKey(AUTH_CROSS_DOMAIN_AUTH_KEY);
	$AES->rnd = md5(microtime().HTTP_IP.rand(0,1000));
	$AES->crypted = '';
	
	$cross_domain_auth_str = 'auth-'.$_SESSION['auth']['id'].'-'.HTTP_IP.'-'.$_SESSION['auth']['cookie_code'];
	$cross_domain_auth_str_tokens = str_split($cross_domain_auth_str, 16);
	
	reset($cross_domain_auth_str_tokens); 
	while (list(,$row) = each($cross_domain_auth_str_tokens)) {
		$row = str_pad($row, 16, ' ', STR_PAD_RIGHT);
		$AES->crypted .= $AES->blockEncrypt($row, $AES->key); 
	}
	
	$sites = $DB->query("
		select * 
		from site_structure_site_alias 
		where spread_auth = 1 
			and auth_group_id != 0 
			and auth_group_id = '$Site->auth_group_id'
	");
	
	reset($sites); 
	while (list(,$site) = each($sites)) { 
		if ($site['url'] != CMS_HOST) {
			$TmplDesign->iterate('/cross_domain_auth/', null, array(
				'site'=>$site['url'], 
				'key'=>urlencode(base64_encode($AES->crypted)),
				'rnd' => $AES->rnd
			));
		}
	}
	unset($AES);
}


$DB->update("
	replace into cms_user_settings (user_id, name, value) 
	values ('".Auth::getUserId()."', 'last_visited_page', '".CURRENT_URL_FORM."')
");


/**
 * HTML
 */
$TmplDesign->set('content', ob_get_clean());


/**
 * Clean action error session 
 */
if (isset($_SESSION)) {
	if (isset($_SESSION['ActionReturn'])) unset($_SESSION['ActionReturn']);
	if (isset($_SESSION['ActionError'])) unset($_SESSION['ActionError']);
	if (isset($_SESSION['cmsEditError'])) unset($_SESSION['cmsEditError']);
}


$TmplDesign->set('mktime', date(LANGUAGE_DATETIME));

$stat = '';
ob_start();

if (IS_DEVELOPER && DEBUG) {
	$counter = 0;
	
	do {
		$counter++;
		$sql = $DB->debug_show();
		
		if ($sql === false) {
			break;
		}
		
		$geshi = new GeSHi($sql, 'SQL');
		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS); 
		
		echo $geshi->parse_code(); 
		
	} while($counter < 100);
		
	$stat = ob_get_clean();
}
	
$dat = getrusage();
$utime_after = ($dat["ru_utime.tv_sec"] * 1000000 + $dat["ru_utime.tv_usec"]) / 1000000;
$stime_after = ($dat["ru_stime.tv_sec"] * 1000000 + $dat["ru_stime.tv_usec"]) / 1000000;

$stat .= '
<!-- 
';

if (isset($DB->statistic)) {
	$stat .= 'SQL: select:'.$DB->statistic['select'].'; ';
	$stat .= 'multi:'.$DB->statistic['multi'].'; ';
	$stat .= 'insert:'.$DB->statistic['insert'].'; ';
	$stat .= 'update:'.$DB->statistic['update'].'; ';
	$stat .= 'delete:'.$DB->statistic['delete'].'; ';
	$stat .= 'other:'.$DB->statistic['other'].'; ';
}

$stat .= '
Full time: '.round(getmicrotime() - TIME_TOTAL, 5).' sec
User time: '.round($utime_after - TIME_USER, 5).' sec
Sys  time: '.round($stime_after - TIME_SYSTEM, 5).' sec
-->';

echo mod_deflate($TmplDesign->display() .$stat);
exit; // established to stop viruses that used to be added at the end of file iframe


?>