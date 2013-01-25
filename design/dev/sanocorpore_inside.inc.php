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

/*
 * Название страницы
 */
 
  $page_name = $Site->getInfo();
  $TmplDesign->set('page_name', $page_name['name']);
  
/**
 * Путь к странице
 */
$data = $Site->getPath('Главная');
$bread_cums = $data;

$id_news  = globalVar($_GET['news_url'], '');

/*Полный путь */
$bread_cums[count($bread_cums)-1]['url'] = substr($page_name['url'], strpos($page_name['url'],'/'))."/";
$TmplDesign->iterateArray('/path_full/', null, $bread_cums);

/*Путь бе последнего слова */
if ($id_news == ''){
	$bread_cums_last = array_pop($bread_cums);
	$TmplDesign->set('last_word_in_path', $bread_cums_last['name']);
}

$TmplDesign->iterateArray('/path/', null, $bread_cums);

if(isset($bread_cums_last['url']) && $bread_cums_last['url'] == '/news/'){
	$TmplDesign->set('show_news', false);
}
else $TmplDesign->set('show_news', true);


// верхнее меню
$current_page = $_GET['_REWRITE_URL'];


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
	$a = array(array('p', 'h'), array('p', 'r'), 'o');

	foreach ($row['submenu'] as $m){
		if (in_array($current_page, $m))
			$row['submenuactive'] = 'active';			
	}
	
	$TmplLeftMenu = $TmplDesign->iterate('/top_menu/', null, $row);	
	
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
			if ($current_page == $subrow['uniq_name'])
				$subrow['class'] = 'selected';
				
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