<?php
/** 
 * Обработчик шаблона страниц ukraine.com.ua 
 * @package Pilot 
 * @subpackage Hosting 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */   
 
 
$TmplDesign->set('show_news', true);
$TmplDesign->set('show_calendar', false);

/**
 * Путь к странице
 */
$data = $Site->getPath('Главная');
$TmplDesign->iterateArray('/path/', null, $data);


/**
 * Верхнее меню
 */
/*
$data = $Site->getTopMenu();
$data[0]['id'] = 63147;

reset($data);
while(list(,$row) = each($data)) {
	$TmplDesign->iterate('/top_menu/', null, $row);
}
*/



/**
 * Новости слева
 */
$News = new News();
$data = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('main'));

for ($i=0; $i<count($data); $i++)
{
	$uniq_name = $data[$i]['type'];
	$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
	$news_url = $DB->result($query_news_url);
	if (!preg_match("/html/", $data[$i]['url'])) $data[$i]['url'] .= ".html"; 
	$data[$i]['url'] = $news_url . $data[$i]['url'];
	$data[$i]['desc'] = $data[$i]['announcement'];
	if (empty($data[$i]['desc'])){
		$data[$i]['content'] = strip_tags($data[$i]['content']);
		$data[$i]['desc'] = substr_to_end_word($data[$i]['content'], 0, 65);
	}
	$data[$i]['headline'] = substr_to_end_word($data[$i]['headline'], 0, 40);

	$data[$i]['i'] = ( ($i+1)%3 == 0) ? 0 : 1;
}

//x($data);
$data[count($data)-1]['i'] =  1;
$nw = (ceil(count($data) / 3) + 2) * 370;
$TmplDesign->set('left_news_width', $nw);
$TmplDesign->iterateArray('/news/', null, $data);

/**
 * Новости специальное предложение
 */
$News = new News();
$data_arr = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('specoffer'));

$data=$data_arr[0]; 
$uniq_name = $data['type'];
$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
$news_url = $DB->result($query_news_url);
if (!preg_match("/html/", $data['url'])) $data['url'] .= ".html"; 
$data['url'] = $news_url . $data['url'];
$data['desc'] = $data['announcement'];
if (empty($data['desc'])){
$data['content'] = strip_tags($data['content']);
$data['desc'] = substr_to_end_word($data['content'], 0, 100);
}

$words = str_word_count($data['headline'], 1);
if(!isset($words[1])){
$words =explode(" ", $data['headline']);
}
$data['title1']= $words[0];
$data['title2']=$words[1];

$TmplDesign->iterate('/news_specoffer/', null, $data);

/**
 * Статьи
 */
$News = new News();
$data_arr = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('article'));

$data=$data_arr[0]; 
$uniq_name = $data['type'];
$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
$news_url = $DB->result($query_news_url);
if (!preg_match("/html/", $data['url'])) $data['url'] .= ".html"; 

$data['url'] = $news_url . $data['url'];
$data['desc'] = $data['announcement'];
if (empty($data['desc'])){
$data['content'] = strip_tags($data['content']);
$data['desc'] = substr_to_end_word($data['content'], 0, 150);
}
$TmplDesign->iterate('/article/', null, $data); 


/**
 * Рейтинг пользователей в форуме
 */
if (in_array(23, $Site->parents)) {
	$TmplDesign->set('show_left_menu', false);
	$TmplDesign->set('show_payment', false);
	$TmplDesign->set('show_info', false);
	$TmplDesign->set('show_partner', false);
	$TmplDesign->set('show_news', false);
	$TmplDesign->set('show_forum_user_top', true);
	
	$query = "
		SELECT
			tb_user.id,
			tb_reputation.reputation,
			tb_user.login,
			if(tb_user.name='', tb_user.login, tb_user.name) as name, 
			COUNT(tb_message.id) as message_count
		FROM auth_user as tb_user
		INNER JOIN forum_message as tb_message ON tb_message.user_id = tb_user.id
		INNER JOIN forum_user_reputation as tb_reputation ON tb_reputation.user_id = tb_user.id
		GROUP BY tb_message.user_id
		ORDER BY reputation desc, message_count desc
		LIMIT 20
	";
	$users = $DB->query($query);
	reset($users);
	while (list($index, $row) = each($users)) {
		$row['index'] = $index + 1;
		$row['class'] = ($index % 2) ? 'odd': 'even';
		$TmplDesign->iterate('/forum_user_top/', null, $row);
	}
}


/**
 * Корзина

if(is_module('ShopOrder')){
	$Order = new ShopOrder();
	$TmplDesign->set("cart", $Order->getCart());
}
 */

/** 
 * Введение перерасчета валют
 */  
if(is_module('Currency') && CURRENCY_ENABLE){
	$TmplDesign->set('currency_form', Currency::getCurrencyGlobalForm());  
	$TmplDesign->iterate('/onload/', null, array('function' => "currency_change(".((!empty($_SESSION['currency_current'])) ? $_SESSION['currency_current'] : CURRENCY_DEFAULT).");"));
}


// верхнее меню


$not_clickable = array(75761);

$structure = array();

$query  = "
	SELECT 
		tb_relation.id, 
		tb_relation.parent,
		tb_structure.uniq_name,
		CONCAT(tb_structure.url, '/') as url,
		tb_structure.name_".LANGUAGE_CURRENT." as name
	FROM site_structure_relation as tb_relation
	INNER JOIN site_structure as tb_structure ON tb_structure.id = tb_relation.id
	WHERE tb_relation.parent IN (SELECT id FROM site_structure_relation WHERE parent = '63147') and find_in_set( 'top_menu', show_menu) > 0 
	ORDER BY tb_relation.priority ASC,tb_structure.priority ASC
";
$children_structure = $DB->query($query); 
reset($children_structure);
while(list(, $row) = each($children_structure)){
	if($row['id'] == $row['parent']) continue;
	$structure[$row['parent']][$row['id']] = $row;
}
//x($structure);
$data = $Site->getTopMenu();
//x($data);

reset($data);
$count=1;
//x($data);
while(list(, $row) = each($data)){
	$row['clickable'] = 1;
	$row['submenu']  = (!empty($structure[$row['id']])) ? $structure[$row['id']] : array();
	$row['show_submenu'] = (!empty($row['submenu'])) ? true : false;
	$row['count'] = $count;
	$count++;

	if (in_array($row['id'], $not_clickable))
	{	
		$row['clickable'] = 0;
		$row['url'] = current($row['submenu']);
		$row['url'] = $row['url']['url'];
		$row['url'] = substr($row['url'],strpos($row['url'],"/"));
	};	
	
	$TmplLeftMenu = $TmplDesign->iterate('/top_menu/', null, $row);	
	//	x($row);
	if(!empty($row['submenu']))
	{
		reset($row['submenu']);
		while(list(, $subrow) = each($row['submenu'])){
			$subrow['url'] = '/'.LANGUAGE_URL.substr($subrow['url'], strpos($subrow['url'], '/', 1) + 1);
			if (isset($subrow['id']))
			{
				$parent_id = $subrow['id'];
				$query = "SELECT
							`site_structure`.`uniq_name`,
							CONCAT(`site_structure`.`url`, '/') as url,
							`site_structure`.name_".LANGUAGE_CURRENT." as name
							FROM `site_structure`
						  WHERE `structure_id`='$parent_id'
						  ORDER BY `site_structure`.`priority` ASC
						 ";
				$data_in = $DB->query($query);
			};	

			if (count($data_in))
			{
				$subrow['show_menu'] = 1;
			}
			$TmplLeftMenuNextlevel = $TmplDesign->iterate('/top_menu/submenu/', $TmplLeftMenu, $subrow);
			
			if (count($data_in))
			{
				while(list(, $insubrow) = each($data_in)){
					$TmplDesign->iterate('/top_menu/submenu/nextlevel/', $TmplLeftMenuNextlevel, $insubrow);
				};
			};
		}
	};

}
unset($structure);

/**
 * вывод баннеров шапка
 */
 

$Banner_head1 = new Banner("header_slider_1", $Site->structure_id, $Site->parents);
$Banner_head2 = new Banner("header_slider_2", $Site->structure_id, $Site->parents);
$Banner_head3 = new Banner("header_slider_3", $Site->structure_id, $Site->parents); 
$Banner_head4 = new Banner("header_slider_4", $Site->structure_id, $Site->parents);
$Banner_head5 = new Banner("header_slider_5", $Site->structure_id, $Site->parents); 

$banners = array_merge($Banner_head1->select(), $Banner_head2->select(), $Banner_head3->select(), $Banner_head4->select(), $Banner_head5->select());

//x($banners);
reset($banners);
$i=1;
while (list(,$row) = each($banners)) {
	$row['i'] = $i++;
	$TmplDesign->iterate('/banners_slider/', null, $row);
}
 
 /**
 * вывод баннеров справа
 */
//x ($Site->structure_id);
$Banner1 = new Banner("right_banner_1", $Site->structure_id, $Site->parents);
$Banner2 = new Banner("right_banner_2", $Site->structure_id, $Site->parents);

$banners = array_merge($Banner1->select(), $Banner2->select());

reset($banners);
while (list(,$row) = each($banners)) {
	$TmplDesign->iterate('/banners_right/', null, $row);
}


/*
 * Форма обратной связи
 */
 /*
 $form = new FormLight("feedback_form");
 $TmplDesign->set('feedback_form_id', $form->form_id);
 $TmplDesign->set('feedback_title', $form->title);
 $TmplDesign->set('feedback_button', $form->button);
 //$TmplDesign->set('feedback_image_button', $form->image_button);
 $TmplDesign->set('feedback_uniq_name', 'feedback_form');
 
 //x($form);
 
 $data = $form->loadParam();
 if (empty($data)) {
	//return cms_message('Form', 'Невозможно найти форму %s', 'feedback_form');
 } 
 
 reset($data);
 while (list(,$row) = each($data)) {
	if (isset($_REQUEST[$row['uniq_name']])) {
		$row['default_value'] = $_REQUEST[$row['uniq_name']];
	} elseif (substr($row['uniq_name'], 0, 7) != 'passwd_' && isset($_SESSION['auth'][$row['uniq_name']])) {
		$row['default_value'] = $_SESSION['auth'][$row['uniq_name']];
	  }
	if ($row['type'] == 'hidden') {
		$TmplDesign->iterate('/feedback_hidden/', null, $row);
	} else {
		$tmpl_row = $TmplDesign->iterate('/feedback_row/', null, $row);
				
		reset($row['info']);
		while (list($key, $val) = each($row['info'])) {
				$TmplDesign->iterate('/feedback_row/feedback_info/', $tmpl_row, array('key' => $key, 'value' => $val, 'uniq_name' => $row['uniq_name']));
			}
		}
	}
*/	
	
/*
 * Голосование
 */ 
 
 		$Vote = new Vote($Site->structure_id);
		$info = $Vote->getInfo();
		// Нет голосования для данной страницы
		//if (empty($info)) return '';
		$answers = $Vote->getAnswers();
		
		//$Template->set($info);
		//$Template->set($param);
		$TmplDesign->iterateArray('/answer/', null, $answers);	
		
/** 
* Тренера
*/	

$query = "SELECT treners.*, treners_position.name as position FROM treners
			LEFT JOIN  treners_position on treners.position_id=treners_position.id WHERE public=1";
$trener = $DB->query($query);

reset($trener);
while (list($i, $row) = each($trener)){
	$treners_arr[$i] = $row;
	$treners_arr[$i]['i']=$i;
	$treners_arr[$i]['photo'] = "/uploads/" . Uploads::getStorage('treners', 'photo', $row['id']) . "." . $row['photo'];	
}
$TmplDesign->iterateArray('/treners/', null, $treners_arr);	
	

	
function substr_to_end_word($string, $pos_start, $pos_end)	{
	if (strlen($string) > $pos_end) {
		$string = substr($string, $pos_start, $pos_end);
		$b = str_word_count($string, 1); 
		array_pop($b);
		$string = implode(" ", $b) . '...';
	}
	
	return $string;
}
?>