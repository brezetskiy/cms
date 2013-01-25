<?php
/**
 * Подгрузка ответов FAQ
 * @package Pilot
 * @subpackage FAQ
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$id = globalVar($_REQUEST['id'], 0);
$search_request = globalVar($_REQUEST['search_request'], '');

$FAQ = new FAQ();
$FAQ->hit($id);
$question = $FAQ->getQuestion($id);

if (!empty($question)) {
	
	/**
	 * Подсветка совпадений
	 */
	if(!empty($search_request) && strlen($search_request) > 2) {
		$search_request_words = preg_split("/[\s,]+/", $search_request);
		
		reset($search_request_words); 
		while(list(, $word) = each($search_request_words)){
			$question['answer'] = preg_replace("/($word)/i", "<span style='background-color:green; color:white; font-weight:bold;'>$1</span>", $question['answer']);
		}
	}
	
	$_RESULT['q'.$id] = $question['answer']."<br>[ <a href=\"/faq/$question[uniq_name].html\" target=\"_blank\">Открыть в новом окне</a> ] [ <a href=\"javascript:void(0);\" onclick=\"faq_load_question($id);return false;\">Свернуть</a> ]<br/><br/>";
}

exit;
?>