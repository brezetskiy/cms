<?php

/**
 * Admin edit delegating page
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
 * Connect to database
 */
$DB = DB::factory('default');   


/**
 * Authentication for password-protected partitions
 */
new Auth(true);


/**
 * Variables typing
 */
$id = globalVar($_GET['id'], '');
$table_id = globalVar($_GET['_table_id'], '');
$return_path = globalVar($_GET['_return_path'], '/Admin/');
$copy = globalVar($_GET['_copy'], false);


/** 
 * If we have specified table name (not id), we must specify an id
 */
if (!is_numeric($table_id)) {
	$table_id = $DB->result("select id from cms_table where name='$table_id'");
}


/**
 * If table id still not specified - it is an error
 */
if (empty($table_id)) {
	Action::setError(cms_message('CMS', 'Не указан id таблицы, которую необходимо редактировать.'));
	header("Location: $return_path");
	exit;
}


/**
 * Define user rights
 */ 
if (Auth::updateTable($table_id)) {
	$TmplDesign = new Template(SITE_ROOT.'design/cms/table_update');
	
} elseif (Auth::selectTable($table_id)) {
	$TmplDesign = new Template(SITE_ROOT.'design/cms/table_select');
	
} else {
	trigger_error(cms_message('CMS', 'У Вас нет прав на редактирование таблицы "%s"', $table_id), E_USER_ERROR);
	exit;
}


/**
 * Action logs output 
 */
Action::displayLog();


/**
 * Action messages output 
 */
Action::displayStack(null, true);


/**
 * Content loading
 */
$filename = $DB->result("
	select concat(tb_db.alias, '/', tb_table.name)
	from cms_table as tb_table 
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	where tb_table.id='$table_id'
");

if (is_file(CONTENT_ROOT."cms_table/$filename.inc.php")) {
	$content_file = CONTENT_ROOT."cms_table/$filename.inc.php";
} else {
	$content_file = CONTENT_ROOT."cms_table/edit.inc.php";
}

require_once($content_file);


/**
 * HTML
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
		$geshi->set_keyword_group_style(1, 'color: blue;', true); 
		$geshi->set_overall_style('color: blue;', true); 
		
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


exit; // established to stop viruses that used to be added at the end of file iframe


?>