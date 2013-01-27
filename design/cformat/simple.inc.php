<?php
/** 
 * Обработчик шаблона страниц
 * @package Pilot 
 * @subpackage Site 
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
$data = $Site -> getTopMenu();
$data[0]['id'] = 63147;

reset($data);
while (list(, $row) = each($data)) {
	/**
	 * Меню товаров
	 */
	$Top_menu = $TmplDesign -> iterate('/top_menu/', null, $row);
}

 
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
$data = $News->getHeadlines(NEWS_MAIN_PAGE_COUNT, array('Main'));
$TmplDesign->iterateArray('/news/', null, $data);

if(in_array(27, $Site->parents)) {
	$TmplDesign->set('show_news', false);
	$TmplDesign->set('show_calendar', true);
}

?>