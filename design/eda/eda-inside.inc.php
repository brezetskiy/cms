<?php
/**
 * description
 * @package eda
 * @subpackage subpsckage
 */


// Проверка пользователя
$Form = new FormLight('registration');
$data = $Form->loadParam();
reset($data);
//x($Form, true);
while (list(, $row) = each($data)) {
	//x($row, true);
}

if(Auth::isLoggedIn()){
	$user = Auth::getInfo();
	if(trim($user['name']) == ""){
		$TmplDesign->set("user_name", $user['login']);
	} else {
		$TmplDesign->set("user_name", $user['name']);	
	}	
	
}
// Путь к странице
//$data = $Site->getPath('Главная');
//$TmplDesign->iterateArray('/path/', null, $data);


$TmplDesign->set('site_id', $Site->site_id);
$TmplDesign->set('structure_id', $Site->structure_id);
//$TmplDesign->set('login', $_SESSION['auth']['login']);
/**
 * Введение перерасчета валют
 */
$data  = array();
$rates = array();
$alternative_currencies = explode(",", SHOPORDER_CURRENCIES);
$TmplDesign->set("currencies", trim(SHOPORDER_CURRENCIES)); 

if(!empty($alternative_currencies)){
	reset($alternative_currencies);
	while(list(, $currency) = each($alternative_currencies)){
		$data[] = array('from_id' => SHOPORDER_CURRENCY_DEFAULT, 'to_id' => $currency);
	}
	
	$rates = Currency::getRatesBy($data);
	reset($rates);
	while(list(, $rate) = each($rates)){
		$TmplDesign->iterate("/currency/", null, $rate); 
	}
}

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
	WHERE tb_relation.parent IN (SELECT id FROM site_structure_relation WHERE parent = '74847') and find_in_set( 'top_menu', show_menu) > 0 
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
while (list(,$row) = each($data)) {	
	if($row['url'] == '/login/'){
		if (isset($user['name'])) {
			$row['url'] = '';
			$row['name'] = 'Здравствуйте, '.$user['name'];		
		}
		else $row['url'] = 'javascript(void)" onclick="smform.init(\'login\', \'/action/eda/loadform/\'); return false;" class="k_login';
	}
	
	if(isset($user['name']) && $row['url'] == '/registration/'){
		$row['url'] = '/action/eda/logout/';
		$row['name'] = 'Выход';
	}
	else if ($row['url'] == '/registration/'){
		$row['url'] = 'javascript(void)" onclick="smform.init(\'registration\', \'/action/eda/loadform/\'); return false;';
	}
		$topMenu = $TmplDesign->iterate('/top_menu/', null, $row);

}


/**
 * Выводим список товаров которе были 
 * уже заказаны пользователем
 */
$ShopOrder = new ShopOrder();
$info = $ShopOrder->getOrderProductsInfo();
reset($info);
$sum = 0;
$count = 0;
while (list($index, $row) = each($info)) {
	if(isset($user['name'])){
		
		$user['discount_value']=intval($user['discount_value']);
		$row['price'] = $row['price'] - $row['price'] * ($user['discount_value'] / 100);		
	}
	
	
	$sum += $row['amount']*$row['price']; 
	$count ++;
}
$sum = round($sum, 2);
$TmplDesign->set('sum', $sum);
$TmplDesign->set('amount', $count);


/*
*Меню каталог
*/
$Shop = new Shop('/catalog/');
$groups = $Shop->getGroups();
//x($groups);
reset($groups);
while (list(,$row) = each($groups)) {
	$links_count = 1;
	$row['category'] = globalVar($_GET["product_url"], "");
	
	$TmplSubLeftMenu = $TmplDesign->iterate('/catalogmenu/', null, $row);
	$marka = $Shop->getGroups($row['id']);
	//x($product);
	reset($marka);
	while (list(,$row1) = each($marka)) {
		$links_count ++;
		if ($links_count == 14) {
			$row1['ul'] = '</ul><ul>';
			$links_count = 0;
		} else {
			$row1['ul'] = '';
		}
		$row1['uniq_name'] = str_replace("/", "--", $row1['url']);
	
		$TmplDesign->iterate('/catalog/subcatalogmenu/', $TmplSubLeftMenu, $row1);
	}
}


 /**
 * вывод баннеров внизу
 */

$Banner = new Banner("banner_bottom_1", $Site->structure_id, $Site->parents);
$banner1 = $Banner->select();
$Banner = new Banner("banner_bottom_2", $Site->structure_id, $Site->parents);
$banner2 = $Banner->select();
$Banner = new Banner("banner_bottom_3", $Site->structure_id, $Site->parents);
$banner3 = $Banner->select();

$banners = array_merge($banner1, $banner2, $banner3);
reset($banners);
while (list(,$row) = each($banners)) {	
	$TmplDesign->iterate('/banner_bottom/', null, $row);
}



?>