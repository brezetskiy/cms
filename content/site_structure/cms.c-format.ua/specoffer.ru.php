<?php
/**
 * Лента новостей
 * @package Pilot
 * @subpackage News
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$uniq_name  = globalVar($_GET['news_url'], '');
$TmplContent->set('show_subcont', globalVar($_GET['cont'], 0));
$order = globalVar($_GET['order'], 'date');

$type = globalVar($_GET['type'], 'specoffer'); 
$TmplContent->setGlobal('type', $type);

if(!empty($uniq_name)){
	$message_id = $DB->result("SELECT id FROM news_message WHERE uniq_name = '$uniq_name'");
	if (empty($message_id)) {
		$uniq_name .= ".html"; 
		$message_id = $DB->result("SELECT id FROM news_message WHERE uniq_name = '$uniq_name'");
	}
} else {
	$message_id = globalVar($_GET['id'], 0);
}

$News = new News(); 
$message = $News->getMessage($message_id); 
$types = $News->getAllTypes($Site->site_id);
if (!isset($types[$type])) {
	// Правило блокирует вывод списка новостей и новостей для рубрик, которые не находятся на сайте
	if (isset($_REQUEST['no_redirect'])) {
		echo "Error: Reload recursion";
		exit;
	}
	
	header("Location:./?no_reload=1");
	exit;
}

if (empty($message)){
	
	$specoffer = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('specoffer'));

	for ($i=0; $i<count($specoffer); $i++)
	{
		//x($specoffer);
		$uniq_name = $specoffer[$i]['type'];
		$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
		$news_url = $DB->result($query_news_url);
		if (!preg_match("/html/", $specoffer[$i]['url'])) $specoffer[$i]['url'] .= ".html"; 
		$specoffer[$i]['url'] = $news_url . $specoffer[$i]['url'];
		
		$specoffer[$i]['desc'] = $specoffer[$i]['announcement'];
		if (empty($specoffer[$i]['desc'])) {
			$specoffer[$i]['content'] = strip_tags($specoffer[$i]['content']);
			$specoffer[$i]['desc'] = substr_to_end_word($specoffer[$i]['content'], 0, 250);
		}

		if (!empty($specoffer[$i]['image_src'])) {
			$image = new Image(SITE_ROOT.'/'.$specoffer[$i]['image_src']);
			$len= $image->compress(150);
			$specoffer[$i]['image_compress_width'] = $len['width'];
			$specoffer[$i]['image_compress_height'] = $len['height'];
		}
	}

		$specoffer[count($specoffer)-1]['i'] =  1;
		$nw = (ceil(count($specoffer) / 3) + 2) * 370;
		$TmplContent->set('left_news_width', $nw);
		$TmplContent->iterateArray('/news/', null, $specoffer);
}

// Если есть новость с указаним id то выводим ее
if (!empty($message)){
	$TmplDesign->set('headline', '');
	$TmplDesign->set('title', $message['title']);
	$TmplDesign->set('keywords', $message['keywords']);
	$TmplDesign->set('description', $message['description']);
	$TmplDesign->set('path_current', $message['headline']);
	$TmplContent->set('image_src', $message['image_src']);  
	
	$TmplContent->set($message);
	if (isset($types[$type])) {
		$TmplContent->set('rubric_name', $types[$type]['name']);
	}
	$TmplContent->set('message_id', $message_id);
	$TmplContent->set('messagetypestitle', $message['type_link']);
	$TmplContent->set('messagetypesname', $message['type_link']);


	
	// Ссылки на управление новостью через тулбарину
	if (Auth::isAdmin()) {
		$Adminbar->addButton('cms_edit', 'news_message', $message['id'], 'Параметры новости', 'edit.gif');
		$Adminbar->addButton('editor', 'news_message', $message['id'], 'Редактировать новость', 'word.gif');
		$Adminbar->addButton('cms_add', 'news_message', $message['type_id'], 'Добавить новость', 'add.gif', 'type_id='.$message['type_id']);
		$Adminbar->addLink('/Admin/Site/News/?type_id='.$message['type_id'], 'Адимнистрирование', 'administrator.png');
	}
	
} 

?>