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
 * Левое меню
 */
$data = $Site->getLeftMenu();
if (!empty($data)) {
	$menu_title = 0;
	if (count($Site->parents) == 2) {
		$menu_title = $Site->structure_id;
	} elseif (isset($Site->parents[count($Site->parents) - 1])) {
		$menu_title = $Site->parents[count($Site->parents) - 1];
	}
	
	$name = $DB->result("select name_".LANGUAGE_CURRENT." as name from site_structure where id='$menu_title'");
	$TmplDesign->set('unit', $name);
	$TmplDesign->iterateArray('/menu/', null, $data);
	$TmplDesign->set('show_left_menu', true);
	
} else { 
	$TmplDesign->set('show_left_menu', false);
}


/**
 * Новости
 */
$News = new News();
$data = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('company_news','world_news','articles','legislation'));
for ($i=0; $i<count($data); $i++)
{
	$uniq_name = $data[$i]['type'];
	$query_news_url = "SELECT rss_url FROM `news_type` WHERE `uniq_name`='$uniq_name' LIMIT 1";
	$news_url = $DB->result($query_news_url);
	$data[$i]['url'] = $news_url . $data[$i]['url'];
	$data[$i]['content'] = strip_tags($data[$i]['content']);
	$data[$i]['content'] = substr($data[$i]['content'], 0, 100);
}
$TmplDesign->iterateArray('/news/', null, $data);
/*
if(in_array(27, $Site->parents)) {
	$TmplDesign->set('show_news', false);
	$TmplDesign->set('show_calendar', true);
}*/


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
 */
if(is_module('ShopOrder')){
	$Order = new ShopOrder();
	$TmplDesign->set("cart", $Order->getCart());
}


/** 
 * Введение перерасчета валют
 */  
if(is_module('Currency') && CURRENCY_ENABLE){
	$TmplDesign->set('currency_form', Currency::getCurrencyGlobalForm());  
	$TmplDesign->iterate('/onload/', null, array('function' => "currency_change(".((!empty($_SESSION['currency_current'])) ? $_SESSION['currency_current'] : CURRENCY_DEFAULT).");"));
}


// верхнее меню

$not_clickable = array(75581, 57720, 43);

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
 * вывод баннеров
 * сперва шапку
 * потом под ней
 */
 
$Banner_head1 = new Banner("head_slider_1", $Site->structure_id, $Site->parents);
$Banner_head2 = new Banner("head_slider_2", $Site->structure_id, $Site->parents);
$Banner_head3 = new Banner("head_slider_3", $Site->structure_id, $Site->parents); 

$banners = array_merge($Banner_head1->select(), $Banner_head2->select(), $Banner_head3->select());

//x($banners);
reset($banners);
while (list(,$row) = each($banners)) {
	$TmplDesign->iterate('/banners_head/', null, $row);
}
 
//x ($Site->structure_id);
$Banner1 = new Banner("banner_left", $Site->structure_id, $Site->parents);
$Banner2 = new Banner("banner_center", $Site->structure_id, $Site->parents);
$Banner3 = new Banner("banner_right", $Site->structure_id, $Site->parents);

$banners = array_merge($Banner1->select(), $Banner2->select(), $Banner3->select());

//x($banners);

reset($banners);
while (list(,$row) = each($banners)) {
	$TmplDesign->iterate('/banners_afetr_head/', null, $row);
}


/*
 * Форма обратной связи
 */
 
 $form = new FormLight("feedback_form");
 $TmplDesign->set('feedback_form_id', $form->form_id);
 $TmplDesign->set('feedback_title', $form->title);
 $TmplDesign->set('feedback_button', $form->button);
 //$TmplDesign->set('feedback_image_button', $form->image_button);
 $TmplDesign->set('feedback_uniq_name', 'feedback_form');
 
 //x($form);
 
 $data = $form->loadParam();
 if (empty($data)) {
	return cms_message('Form', 'Невозможно найти форму %s', 'feedback_form');
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
	
/*
 * Голосование
 */ 
 
 		$Vote = new Vote($Site->structure_id);
		$info = $Vote->getInfo();
		//x($info);
		// Нет голосования для данной страницы
		//if (empty($info)) return '';
		$answers = $Vote->getAnswers();
		//x($answers);
		//$Template->set($info);
		//$Template->set($param);
		$TmplDesign->iterateArray('/answer/', null, $answers);


/**
 * Охранные услуги
 */
 
$service_id = '75581'; 
$structure_service = array(); 
$query_service  = "
	SELECT 
		tb_relation.id, 
		tb_relation.parent,
		tb_structure.uniq_name,
		CONCAT(tb_structure.url, '/') as url,
		tb_structure.name_".LANGUAGE_CURRENT." as name
	FROM site_structure_relation as tb_relation
	INNER JOIN site_structure as tb_structure ON tb_structure.id = tb_relation.id
	WHERE tb_relation.parent IN (SELECT id FROM site_structure_relation WHERE parent = '$service_id') and find_in_set( 'top_menu', show_menu) > 0 
	ORDER BY tb_relation.priority ASC,tb_structure.priority ASC
"; 
$service_structure = $DB->query($query_service);
while(list(, $row) = each($service_structure)){
	if($row['id'] == $row['parent']) continue;
	$structure_service[$row['parent']][$row['id']] = $row;
}
$row_service['submenu']  = (!empty($structure_service[$service_id])) ? $structure_service[$service_id] : array();
reset($row_service['submenu']);
while(list(, $subrow) = each($row_service['submenu'])){
	if ($subrow['id']==$Site->structure_id) $subrow['active_service'] = 1;
	else
	{
		if (in_array($subrow["id"], $Site->parents)) $subrow['active_service'] = 1;
	};
	$subrow['url'] = '/'.LANGUAGE_URL.substr($subrow['url'], strpos($subrow['url'], '/', 1) + 1); 
	
	if (isset($subrow['id']))
	{
		$parent_id = $subrow['id'];
		$query = "SELECT
					`site_structure`.`id`,
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
	
	$TmplLeftMenuSecondlevel = $TmplDesign->iterate('/service_menu/', null, $subrow);
	
	if (count($data_in))
	{
		while(list(, $insubrow) = each($data_in)){
			if (in_array($insubrow["id"], $Site->parents)) $insubrow['active_service'] = 1;
			$TmplDesign->iterate('/service_menu/submenu/', $TmplLeftMenuSecondlevel, $insubrow);
		};
	};	
}
unset($structure_service);		
		
?>