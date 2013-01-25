<?php

/**
 * Фильтр FAQ
 * 
 * @package Pilot
 * @subpackage Faq
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */


$group_id = globalVar($_REQUEST['group_id'], 0);
$search_request = globalVar($_REQUEST['search_request'], '');
$search_request = trim($search_request);

$TmplFaq = new Template('faq/filter');
$last_group = null;


/**
 * Список вопросов
 */
$Site = new Site(HTTP_URL, 'site_structure');
$FAQ = new FAQ(); 
$data = $FAQ->getList($group_id, $search_request);

reset($data);
while(list($number, $row) = each($data)) {
	
	/**
	 * Подсветка совпадений
	 */
	if(!empty($search_request) && strlen($search_request) > 2) {
		$search_request_words = preg_split("/[\s,]+/", $search_request);
		
		reset($search_request_words);
		while(list(, $word) = each($search_request_words)){
			$row['question'] = preg_replace("/($word)/i", "<span style='background-color:green; color:white; font-weight:bold;'>$1</span>", $row['question']);
		}
	}
	
	$row['number'] = $number + 1;
	if($row['group_id'] != $last_group) {
		$tmpl_group = $TmplFaq->iterate('/group/', null, $row);
		$last_group = $row['group_id'];
	}
	
	$TmplFaq->iterate('/group/question/', $tmpl_group, $row);
}

$_RESULT['faq_content'] = $TmplFaq->display();
$_RESULT['javascript'] = "faq_update_group($group_id); ";


?>