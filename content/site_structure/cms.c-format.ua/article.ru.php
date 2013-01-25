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

$type = globalVar($_GET['type'], 'article'); 
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
	
	$article = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('article'));

	for ($i=0; $i<count($article); $i++)
	{
		$uniq_name = $article[$i]['type'];
		$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
		$news_url = $DB->result($query_news_url);
		if (!preg_match("/html/", $article[$i]['url'])) $article[$i]['url'] .= ".html"; 
		$article[$i]['url'] = $news_url . $article[$i]['url'];
		
		$article[$i]['desc'] = $article[$i]['announcement'];
		if (empty($article[$i]['desc'])){
			$article[$i]['content'] = strip_tags($article[$i]['content']);
			$article[$i]['desc'] = substr_to_end_word($article[$i]['content'], 0, 250);
		}

		if (!empty($article[$i]['image_src'])) {
			$image = new Image(SITE_ROOT.'/'.$article[$i]['image_src']);
			$len= $image->compress(150);
			$article[$i]['image_compress_width'] = $len['width'];
			$article[$i]['image_compress_height'] = $len['height'];
		}
	}

		$article[count($article)-1]['i'] =  1;
		$nw = (ceil(count($article) / 3) + 2) * 370;
		$TmplContent->set('left_news_width', $nw);
		$TmplContent->iterateArray('/news/', null, $article);
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

#echo(ARTICLES_COUNT);

?>