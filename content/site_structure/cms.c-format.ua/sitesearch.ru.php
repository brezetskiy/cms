<?php
/** 
 * Поиск по сайту с использованием Яндекс-ХМЛ 
 * @package Pilot 
 * @subpackage Search 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$search = stripslashes(globalVar($_GET['search'], ''));
$do = globalVar($_GET['do'], '');
$page = globalVar($_GET['_offset']['search'], 0)/YANDEXSEARCH_RESULTS_PER_PAGE;

function translate_error($code) {
	$errors = array(
		15 => array(
			'ru' => 'По Вашему запросу ничего не найдено',
			'uk' => 'По Вашому запиту нічого не знайдено',
			'en' => 'Your search query did not match any documents'
		),
		2 => array(
			'ru' => 'Задан пустой поисковый запрос',
			'uk' => 'Задано пустий запит',
			'en' => 'Empty search query specified'
		),
		1000 => array(
			'ru' => 'Извините, поиск временно не доступен',
			'uk' => 'Вибачте, пошук тимчасово не доступний',
			'en' => 'Sorry, search is temporary not available'
		)
	);
	
	switch ($code) {
	case 2 : 
		return cms_message('yandexsearch', 'Задан пустой поисковый запрос'); break;
		case 1 : 
		case 8 : 
		case 9 : 
		case 10 : 
		case 12 : 
		case 15 : 
			return cms_message('yandexsearch', 'По Вашему запросу ничего не найдено'); break;
		case 18 : 
		case 19 : 
		case 20 : 
		case 1000 :
			// Превышен лимит запросов, сервер недоступен, неправильный XML, etc... 
			return cms_message('yandexsearch', 'Извините, поиск временно не доступен'); break;
	}
}

function xml2html($string) {
	return htmlspecialchars(iconv('utf-8', CMS_CHARSET.'//IGNORE', $string));
}

function hilight($passages) {
	$passages = html_entity_decode($passages);
	$passages = preg_replace('~</passage>[\s\t\n\r]*<passage>~i', '<br>', $passages);
	$passages = preg_replace('~<hlword[^>]*>([^<]+)</hlword>~i', '<b>$1</b>', $passages);
	return strip_tags($passages, '<b><br>');
}

$TmplContent->set('search', htmlspecialchars($search, ENT_QUOTES));

if (!empty($do)) {
	
	try {
		
		if (empty($search)) {
			throw new Exception('err', 2);
		}
		
		$TmplSearch = new Template(SITE_ROOT.'templates/yandexsearch/request');
		$TmplSearch->set('query', htmlspecialchars($search.'<< host="'.YANDEXSEARCH_INDEXED_HOST .'"', ENT_QUOTES));
		$TmplSearch->set('page', $page);
		
		$request = '<?xml version="1.0" encoding="windows-1251"?>'.$TmplSearch->display();
		$Download = new Download();
		$response = @$Download->post('http://xmlsearch.yandex.ru/xmlsearch/', array('text' => $request));
		if ($response === false) {
			throw new Exception('err', 18);
		}
		
		$xml = @simplexml_load_string($response);
		if ($xml === false) {
			throw new Exception('err', 18);
		}
		
		if (isset($xml->response->error)) {
			throw new Exception('err', (int)$xml->response->error->attributes()->code);
		}
		
		if (isset($xml->response->results->grouping)) {
			$TmplContent->set('show_result', true);
			$total_rows = $xml->response->results->grouping->found[2];
			$TmplContent->set('docs_count', $total_rows);
			
			$counter = (int)$xml->response->results->grouping->page->attributes()->first;
			foreach ($xml->response->results->grouping->group as $result) {
				
				if (!($result->doc->title instanceof SimpleXMLElement)) continue;
				
				$arr = array(
					'title' => hilight(xml2html($result->doc->title->asXML())),
					'url' => xml2html($result->doc->url),
					'text' => hilight(xml2html($result->doc->passages->asXML())),
					'counter' => $counter
				);
				
				$arr['short_url'] = preg_replace('~^http://~i', '', $arr['url']);
				if (strlen($arr['short_url']) > YANDEXSEARCH_MAX_URL_LENGTH) {
					$arr['short_url'] = substr($arr['short_url'], 0, YANDEXSEARCH_MAX_URL_LENGTH).'...';
				}
				
				$TmplContent->iterate('/result/', null, $arr);
				$counter++;
			}
			
			if ($total_rows > YANDEXSEARCH_RESULTS_PER_PAGE) {
				$pages_list = Misc::pages(min($total_rows, YANDEXSEARCH_RESULTS_PER_PAGE*100), YANDEXSEARCH_RESULTS_PER_PAGE, 10, 'search', false, true);
				$TmplContent->iterate('/pages_list/', null, array('pages_list' => $pages_list));
			}
		}
		
	} catch (Exception $e) {
		$TmplContent->set('error', translate_error($e->getCode()));
	}
	
}

?>