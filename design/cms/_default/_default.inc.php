<?php
/** 
 * ���������� ������� ��� �����, ������������ �� ���������.
 * ������ ������ ������������ ��� "������" ��� ������ ������� �����.
 * require_once ����� ������� - ���������!
 * 
 * @package Pilot
 * @subpackage Site
 * @author Rudenko Ilya <rudenko@delta-x.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

// ������� ����
$data = $Site->getTopMenu();
$TmplDesign->iterateArray('/menu/', null, $data);

// ���� � ��������
$data = $Site->getPath('�������');
$TmplDesign->iterateArray('/path/', null, $data);

// ����� ����
$data = $Site->getLeftMenu();
$TmplDesign->iterateArray('/menu/', null, $data);




/**
 * ��� ������������� ������� ������� �����������
 * �� ������� ����������� �� ���������, � ������� ������������ �� ����� �������
 *
if (is_module('comment') && COMMENT_SITE_ENABLED) {
	$show_comments = $DB->result("SELECT allow_comments FROM site_structure WHERE id = '".SITE_STRUCTURE_ID."'");
	if ($show_comments == 'true') {
		$TmplDesign->set('show_comments', 'true');
		$TmplDesign->set('comments_table', 'site_structure');
		$TmplDesign->set('parent_id', SITE_STRUCTURE_ID);
		
		if (isset($_GET['show_comments']) || isset($_SESSION['ActionError']['comment']) || isset($_GET['_offset']['comments'])) {
			$TmplDesign->set('force_comments', 1);
		}
		
		$query = "
			SELECT COUNT(*) FROM comment 
			WHERE table_name = 'site_structure' AND parent_id = '".SITE_STRUCTURE_ID."' AND active = 'true'
		";
		$comments_count = $DB->result($query);
		$TmplDesign->set('comments_count', $comments_count);
		
		/**
		 * Captcha
		 *
		if (CMS_USE_CAPTCHA && !Auth::isLoggedIn()) {
			$TmplDesign->set('captcha_html', Captcha::createHtml(SITE_STRUCTURE_ID));
		}
	}
}

/**
 * ������� ��������
 *
if (is_module('rating') && isset($page_info['allow_rating']) && $page_info['allow_rating'] == 'true') {
	$Rating = new Rating('site_structure');
	$page_rating = array(
		'table_name' => 'site_structure',
		'item_id' => SITE_STRUCTURE_ID,
		'content' => $Rating->displayRatingBlock(SITE_STRUCTURE_ID, HTTP_IP, HTTP_LOCAL_IP, globalVar($_COOKIE['UniqueRatingId'], ''))
	);
	$TmplDesign->setGlobal('page_rating', $page_rating);
	$TmplDesign->iterate('/js/', null, array('url'=>'/js/rating/rating.js'));
}
*/

?>