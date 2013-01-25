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


/**
 * Рейтинг пользователей в форуме
 */
if (23 == $Site->structure_id) {
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
			if(tb_user.name='', tb_user.name, tb_user.nickname) as name, 
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
 * Введение перерасчета валют
 */  
if(is_module('Currency') && CURRENCY_ENABLE){
	$TmplDesign->set('currency_form', Currency::getCurrencyGlobalForm());  
	$TmplDesign->iterate('/onload/', null, array('function' => "currency_change(".((!empty($_SESSION['currency_current'])) ? $_SESSION['currency_current'] : CURRENCY_DEFAULT).");"));
}


/**
 * OpenID
 */  
if(AUTH_OID_ENABLE){
	$TmplDesign->setGlobal('oid_widget__box__starter', AuthOID::displayBoxStarter('auth'));
}


/**
 * Список сравнения
 */

$id = globalVar($_REQUEST['id'], 0);

if(!empty($id)) unset($_SESSION['comparison_box'][$id]);
if(!isset($_SESSION['comparison_box'])) $_SESSION['comparison_box'] = array();
$products = globalVar($_SESSION['comparison_box'], array());

?>