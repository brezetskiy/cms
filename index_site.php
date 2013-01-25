<?php

/**
 * Main delegating page
 * 
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, 2004
 */
 
/**
 * Define the interface to support localization
 * @ignore 
 */
define('CMS_INTERFACE', 'SITE');
//die();
/**
 * Config
 */
require_once('system/config.inc.php');


if(false){
	//header("Location://Header('installation/index.php');
	
	header("Location: http://".$_SERVER['HTTP_HOST']."/installation/index.php");
}

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
 * Get information about current site
 */
$Site = new Site(HTTP_URL, 'site_structure');
define('SITE_STRUCTURE_ID', $Site->structure_id);
define('SITE_STRUCTURE_URL', strstr($Site->url, '/'));


/**
 * Statistics
 */
if (is_module('Stat') && isset($_SERVER['HTTP_USER_AGENT']) && isset($_SERVER['REQUEST_URI'])) {
	Stat::logSearchSpider($_SERVER['HTTP_USER_AGENT'], HTTP_IP, $_SERVER['REQUEST_URI'], CMS_HOST, $Site->site_id);
}


/**
 * Commit user navigates source
 */
$parsed_referer = @parse_url(globalVar($_SERVER['HTTP_REFERER'], ''));
if (!empty($parsed_referer['host'])) {
	
	/** 
	 * Cut internal transitions
	 */  
	$DB->query("select * from site_structure_site_alias where url = '".$DB->escape($parsed_referer['host'])."'");
	if ($DB->rows == 0) {
		setcookie('referer', globalVar($_SERVER['HTTP_REFERER'], ''), time() + 3600 * 24 * 365, '/', CMS_HOST);
		setcookie('refered_page', 'http://'.globalVar($_SERVER['HTTP_HOST'], '').globalVar($_SERVER['REQUEST_URI'], ''), time() + 3600 * 24 * 365, '/', CMS_HOST);
	}
}

$page_info = $Site->getInfo();


/**
 * If redirect URL is specified - redirect user at that address and stop operation
 */
if (!empty($page_info['substitute_url'])) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".preg_replace('~^/~', '/'.LANGUAGE_URL, $page_info['substitute_url']));
	exit;
}


/**
 * Redirect from page 
 */
if (isset($page_info['url'])) {
	$page_url = '/'.preg_replace('~^[^/]+/~', '', $page_info['url'].'/');
	if ($page_url != $_SERVER['REQUEST_URI'] && !preg_match('~\?~', $_SERVER['REQUEST_URI']) && !preg_match('~\.html?~', $_SERVER['REQUEST_URI'])) {
		
		/**
		 * Without slash "/"
		 */
		if (!preg_match('~/$~', $_SERVER['REQUEST_URI'])) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $page_url");
			exit; 
		}
	}
	  
	/** 
	 * Mixed case
	 */   
	if (!$Site->checkPageUrlCase()) {
		if(CMS_URL_CASE_SENSITIVE){
			$page_info = $Site->get404Info();  
		} else {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".strtolower($_SERVER['REQUEST_URI'])); 
			exit;
		}
	}
}


/**
 * Prolong the lifetime of the user's session
 * Create session for people who left site but now have returned
 */
new Auth(false);


/**
 * Load main design template 
 */
$TmplDesign = new Template(SITE_ROOT.'design/'.$page_info['template_design']);
$TmplDesign->set($page_info);


/**
 * Set source for OpenID widgets
 */
$_SESSION['oid_widget']['source'] = 'site';
	

/**
 * OTP protection
 */ 
if (AuthOTP::isSessionActive()) {
	AuthOTP::clarify();  
}


/**
 * Connect functions creating template variables. 
 * Functions should start before the content page will be created. 
 * It allows user to change the contents of page template inside that page
 */
if (is_file($page_info['template_parser'])) {
	require_once($page_info['template_parser']);
}


/**
 * Data for adminbar
 */
if (Auth::isAdmin()) {
	$Adminbar = new Adminbar(true); 
}


/** 
 * Define file name with php script
 */  
$content_file = CONTENT_ROOT.'site_structure/'.$Site->filename.'.';
$content_file .= (is_file($content_file.LANGUAGE_CURRENT.'.php')) ? LANGUAGE_CURRENT.'.php': LANGUAGE_SITE_DEFAULT.'.php';
$access = $Site->checkAccess($page_info['access_level'], $page_info['access_groups']);
$content = $php = '';


/**
 * Access denied
 */
if (!empty($access)) {
	$content = $access;
	
/**
 * Access allowed
 */	
} else {
	
	/**
	 * Output the version of static page for a specific date
	 */
	if (Auth::isAdmin() && globalVar($_GET['cvs_version'], 0) != 0) {
		$cvs_content = $Adminbar->loadCVS();
		
		if (!empty($cvs_content)) {
			$page_info['content'] = $cvs_content;
		}
	}

	/** 
	 * Process the content
	 */ 
	if (!empty($page_info['content'])) {
		$methods = get_class_methods('TemplateUDF');
		$page_info['content'] = preg_replace_callback("/{(".implode("|", $methods).")([^}]*)}/", array('Template', 'staticContentCallback'), str_replace('&quot;', '"', $page_info['content']));
		$content = id2url($page_info['content']);
	}

 	/** 
	 * Date of file last revision
	 */ 
	header('Last-Modified: '.date('D, d M Y H:i:s', $page_info['last_modified']).' GMT');
	
	if (is_file($content_file) && is_readable($content_file)) {
		ob_start();
		
		$template_language = Template::getLanguage(CONTENT_ROOT.'site_structure/'.$Site->filename);
		if (false !== $template_language) {
			$TmplContent = new Template(CONTENT_ROOT.'site_structure/'.$Site->filename, $template_language);
		}
		
		require_once($content_file);
		
		if (isset($TmplContent) && $TmplContent instanceof Template) {
			echo id2url($TmplContent->display());
			unset($TmplContent);
		}
		
		$php = ob_get_clean();
	} 
	
	if (!empty($php)) {
		$content = preg_replace("/<hr[^>]*>/", $php, $content, 1, $count);
		if ($count == 0) {
			$content .= $php;
		}
		
	} elseif (empty($php)) {
		$content = Misc::pagedContent($content);
	}
}


/**
 * Action messages output
 */
Action::displayStack();


/**
 * Clean action error session 
 */
if (isset($_SESSION)) {
	unset($_SESSION['ActionReturn']);
	unset($_SESSION['ActionError']);
}
	

/**
 * Cross-Site authorization
 */
if (Auth::isLoggedIn()) {
	
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
			and auth_group_id = '".$Site->auth_group_id."'
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


/**
 * Page content
 */
$TmplDesign->set('content', $content);

$dat = getrusage();
$utime_after = ($dat["ru_utime.tv_sec"] * 1000000 + $dat["ru_utime.tv_usec"]) / 1000000;
$stime_after = ($dat["ru_stime.tv_sec"] * 1000000 + $dat["ru_stime.tv_usec"]) / 1000000;
$content = $TmplDesign->display();


/**
 * AdminBar
 */
if (Auth::isAdmin() && isset($Adminbar) && $Adminbar instanceof Adminbar) {

	// Standart administrative panel is displayed only if no other buttons have been identified
	if (empty($Adminbar->buttons) && isset($page_info['structure_id'])) {
		$Adminbar->addButton('editor', 'site_structure', SITE_STRUCTURE_ID, cms_message("CMS", "Редактировать"), 'word.gif');
		$Adminbar->addButton('cms_edit', 'site_structure', SITE_STRUCTURE_ID, cms_message("CMS", "Параметры"), 'edit.gif');
		$Adminbar->addButton('cms_add', 'site_structure', $page_info['structure_id'], cms_message("CMS", "Добавить страницу"), 'add.gif', "structure_id=$page_info[structure_id]");
		$Adminbar->addButton('cms_add', 'site_structure', SITE_STRUCTURE_ID, cms_message("CMS", "Добавить подраздел"), 'copy.png', "structure_id=".SITE_STRUCTURE_ID);
		$Adminbar->addLink('/Admin/Site/Structure/?structure_id='.$page_info['structure_id'], cms_message("CMS", "Управление"), 'administrator.png');
		$Adminbar->cvs('site_structure', SITE_STRUCTURE_ID);
		
		if(IS_DEVELOPER){  
			$script_url = strtolower(CONTENT_ROOT."site_structure/".$Site->url.".".LANGUAGE_CURRENT);
			
			if(is_file("$script_url.php")){
				$Adminbar->addButton('editor_php', 'site_structure', SITE_STRUCTURE_ID, cms_message("CMS", "Редактировать код"), 'php.png');
			}
			
			if(is_file("$script_url.tmpl")){
				$Adminbar->addButton('editor_tmpl', 'site_structure', SITE_STRUCTURE_ID, cms_message("CMS", "Редактировать шаблон"), 'tmpl.png');
			}
		}
	}

	$content = $Adminbar->display($content);
}


/**
 * Caching pages
 */
Cache::save($content);		

$stat = '';
ob_start();

if (1 || (IS_DEVELOPER && DEBUG)) {
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
		$geshi->set_keyword_group_style(1, 'color: blue;', true); 
		$geshi->set_overall_style('color: blue;', true); 
		echo $geshi->parse_code(); 
		
	} while($counter < 100);
	
	$stat = ob_get_clean();
	
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
}


echo mod_deflate($content.$stat);
exit; // established to stop viruses that used to be added at the end of file iframe
