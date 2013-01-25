<?php
/** 
 * Подгрузка комментариев через AJAX 
 * @package Pilot 
 * @subpackage Comment 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

/**
 * Определяем языковой интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
 * Конфигурация
 */
require_once('../../system/config.inc.php');

$JsHttpRequest = new JsHttpRequest('windows-1251');

$DB = DB::factory('default');

define('PARENT_ID', globalVar($_REQUEST['parent_id'], 0));
define('TABLE', globalVar($_REQUEST['table'], ''));
define('OFFSET', globalVar($_REQUEST['offset'], 0));

$TmplDesign = new Template(SITE_ROOT.'templates/comment/comments');

/**
 * Нужно более универсально?
 */
if (TABLE == 'site_structure') {
	$show_comments = $DB->result("SELECT allow_comments FROM site_structure WHERE id = '".PARENT_ID."'");
} else {
	$show_comments = 'true';	
}

if ($show_comments == 'true') {
	$TmplDesign->set('comments_table', TABLE);
	$TmplDesign->set('parent_id', PARENT_ID);
	
	$Comment = new Comment();
	$comments = $Comment->getComments(TABLE, PARENT_ID, OFFSET, COMMENT_COUNT_PER_PAGE);
	$TmplDesign->set('comments_count', count($comments));
	$TmplDesign->set('comments_pages', Misc::pages($Comment->total, COMMENT_COUNT_PER_PAGE, 10, 'comments', false, true, 'comments', "showComments('".PARENT_ID."', '{\$offset}');", OFFSET));
	reset($comments); 
	while (list(,$row) = each($comments)) {
		$TmplDesign->iterate('/comment/', null, $row); 
	}
	
	/**
	 * Captcha - генерится на странице, а этот скрипт грузится аяксом
	 */
//	if (CMS_USE_CAPTCHA && !Auth::isLoggedIn()) {
//		$TmplDesign->set('captcha_html', Captcha::createHtml(PARENT_ID));
//	}
}

$_RESULT['page_comments'] = $TmplDesign->display();

?>