<?php
/**
 * Лента новостей
 * @package Pilot
 * @subpackage News
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$uniq_name  = globalVar($_GET['news_url'], '');
$TmplContent->set('show_subcont', globalVar($_GET['cont'], 1));
$order = globalVar($_GET['order'], 'date');

$type = globalVar($_GET['type'], 'main'); 
$TmplContent->setGlobal('type', $type);

if(!empty($uniq_name)){
	$message_id = $DB->result("SELECT id FROM news_message WHERE uniq_name = '$uniq_name'");
} else {
	$message_id = globalVar($_GET['id'], 0);
}

//x($message_id, true);
/**
 * Календерь
 */
$show_date = globalVar($_GET['show_date'], '0');
$show_news_date = 0;

if (empty($show_date)) {
	$show_date = time();
} else {
	if (!preg_match('~^[0-9]{4}-[0-9]{2}-[0-9]{2}$~', $show_date)) $show_date = date('Y-m-d');
	$show_date = explode('-',$show_date);
	$show_date = mktime(0,0,0,$show_date[1],$show_date[2],$show_date[0]);
	$show_news_date = $show_date;
	$TmplContent->set('remove_date_filter', true);
}

$month = date('n', $show_date);
$year  = date('Y', $show_date);
$data  = $DB->fetch_column("
	select dayofmonth(tb_message.date), concat('/News/?show_date=', tb_message.date)
	from news_message as tb_message
	inner join news_type as tb_type on tb_type.id = tb_message.type_id
	where tb_message.date >='".date('Y-m-d', mktime(0,0,0,$month, 1, $year))."' 
		and tb_message.date < '".date('Y-m-d', mktime(0,0,0,$month+1,1,$year))."'
		".where_clause("tb_type.uniq_name", $type)."
	group by dayofmonth(tb_message.date)
");

$html = TemplateUDF::calendar($param = array('links' => $data, 'show_month' => $show_date, 'current_date' => $show_date, 'type' => $type));
$TmplDesign->set('calendar', $html);

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
	// Список новостей 
	
	$type_list = array();
	reset($types);
	while (list(,$row) = each($types)) {
		$class = ($row['uniq_name'] == $type) ? 'selected': '';
		$type_list[] = "<a class='$class' href=\"$row[uniq_name].htm\">$row[name]</a> ($row[messages_count])";
	}
	
	$TmplContent->set('type', implode(" | ", $type_list));

	if (isset($types[$type])) {
		$TmplDesign->set('title', $types[$type]['title']);
		$TmplDesign->set('keywords', $types[$type]['keywords']);
		$TmplDesign->set('description', $types[$type]['description']);
	}
	if ($order == 'comment') {
		$data = $News->getHeadlines(NEWS_ARCHIVE_PAGE_COUNT, $type, true, $show_news_date, 'count_comments DESC');
	} else {
		$data = $News->getHeadlines(NEWS_ARCHIVE_PAGE_COUNT, $type, true, $show_news_date, 'date DESC');
	}
	if (count($data) == 1){
		$message_id = $data[0]['id'];
		$message = $News->getMessage($message_id);
	} else {
		reset($data);
		while (list(,$row) = each($data)) {
			if ($order == 'comment') {unset($row['subtitle_year']);} 
			$row['desc'] = $row['announcement'];
			if (empty($row['desc'])){
				if (strlen($row['subcontent']) > 350) { 
					$str = substr($row['subcontent'], 0, 350); 
					$b = str_word_count($str, 1); if ($b[0] === 'p') unset($b[0]);
					array_pop($b);
					$row['desc'] = implode(" ", $b) . '...'; 
				}
			}
			$TmplContent->iterate('/news/', null, $row); 
		}		
		$TmplContent->set('pages_list', Misc::pages($News->total, NEWS_ARCHIVE_PAGE_COUNT, 10, 0, true, true));
	}
}

// Если есть новость с указаним id то выводим ее
if (!empty($message)){
	$TmplDesign->set('headline', '');
	$TmplDesign->set('title', $message['title']);
	$TmplDesign->set('keywords', $message['keywords']);
	$TmplDesign->set('description', $message['description']);
	$TmplDesign->set('path_current', $message['headline']);
	
	$TmplContent->set($message);
	if (isset($types[$type])) {
		$TmplContent->set('rubric_name', $types[$type]['name']);
	}
	$TmplContent->set('message_id', $message_id);
	$TmplContent->set('messagetypestitle', $message['type_link']);
	$TmplContent->set('messagetypesname', $message['type_link']);
	
	$previous_list = $News->getNearbyMessages($message_id, $message['dbformat_date'], 3, 0);
	$next_list = $News->getNearbyMessages($message_id, $message['dbformat_date'], 3, 1);
	
	$previous = array();
	if(count($previous_list) > 0){
		for($i=0; $i<count($previous_list); $i++){
			if($previous_list[$i]['content'] == 1){
				$previous = $previous_list[$i];
				break;
			}
		}
	}
	
	$next = array();
	if(count($next_list) > 0){ 
		for($i=0; $i<count($next_list); $i++){
			if($next_list[$i]['content'] == 1){
				$next = $next_list[$i];
				break;
			}
		}
	}
	
	if(isset($_SESSION['ActionError'])) {
		$TmplContent->set($_SESSION['ActionError']);
	}

	// Выводим ссылку на предыдущее сообщение
	if (isset($previous['nearby_id']) && !is_null($previous['nearby_id'])){
		$TmplContent->set('is_previous', 1);
		$TmplContent->set('previous_url', $previous['url']);
		$TmplContent->set('previous_name', $previous['headline']);
	} else {
		$TmplContent->set('is_previous', 0);
	}
	
	// Выводим ссылку на следующее сообщение
	if (isset($next['nearby_id']) && !is_null($next['nearby_id'])){
		$TmplContent->set('is_next', 1);
		$TmplContent->set('next_url', $next['url']);
		$TmplContent->set('next_name', $next['headline']);
	} else {
		$TmplContent->set('is_next', 0);
	}
	
	$message['day_from'] = $message['day_from'];
	$message['month_from'] = $message['month_from'];
	
	
	// Выводим предыдущие сообщения по теме и следующие
	$nearby = $News->getHeadlines(5, $message['type_list']);
	$TmplContent->iterateArray('/nearby/', null, $nearby);
	
	
	// Ссылки на управление новостью через тулбарину
	if (Auth::isAdmin()) {
		$Adminbar->addButton('cms_edit', 'news_message', $message['id'], 'Параметры новости', 'edit.gif');
		$Adminbar->addButton('editor', 'news_message', $message['id'], 'Редактировать новость', 'word.gif');
		$Adminbar->addButton('cms_add', 'news_message', $message['type_id'], 'Добавить новость', 'add.gif', 'type_id='.$message['type_id']);
		$Adminbar->addLink('/Admin/Site/News/?type_id='.$message['type_id'], 'Адимнистрирование', 'administrator.png');
	}
	
} 

?>