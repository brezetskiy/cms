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
		//$newShop = new Shop('catalog');
		//$info_product = $newShop->getProductInfo($row['product_id']);	
		$user['discount_value']=intval($user['discount_value']);
		$row['price'] = $row['price'] - $row['price'] * ($user['discount_value'] / 100);		
	}
	$row['price'] = $row['price'];
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

//Новости
$News = new News();
$data = $News->getHeadlines(3, 'main');
reset($data);
while (list(,$row) = each($data)) {	
	$TmplDesign->iterate('/news/', null, $row);
}

//Акции
$News = new News();
$data = $News->getHeadlines(2, 'stock');
reset($data);
$i=1;
while (list(,$row) = each($data)) {
	$TmplDesign->set('stock_flag', true);
	if ($i==1) $row['mclass'] = 'shares-left';
	else if($i==2) $row['mclass'] = 'shares-right';
	$i++;
	$TmplDesign->iterate('/stock/', null, $row);
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


/**
 * вывод баннерa слайдер
  */
 
$banners=array();
$query = "SELECT DISTINCT(`banner_group`.`uniq_name`) FROM `banner_group`, `banner_banner`
	WHERE `banner_group`.`id` = `banner_banner`.`group_id` AND `banner_banner`.`profile_id`= 8
	";
$data = $DB->query($query); 
reset($data);
while (list(,$row) = each($data)) {	
	$Banner = new Banner($row['uniq_name'], $Site->structure_id, $Site->parents);
	$banners[] = $Banner->select();
}
	
reset($banners);
$flag_show = false;
while (list(,$rows) = each($banners)) {
			
	$row= &$rows[0];
	if(isset($row['id'])){
		$id = $row['id'];
		
		$query  = "	SELECT link FROM banner_banner WHERE id = $id";
		$url = $DB->result($query); 	
		
		if(preg_match('/((\w|\-)+)\.html$/', $url, $matches)) {
			$url = $matches[1];
		
			
			$newShop = new Shop('catalog');
			$data= $newShop->getProducts(0, 1, 0, array('tb_product.url' => $url));
			reset($data); 
			while (list(,$eat) = each($data)) {
				$row['name'] = $eat['name'];
				$row['price'] = $eat['price'];
				$row['product_id'] = $eat['id'];
				$row['measure'] = $eat['measure'];
				$TmplDesign->iterate('/slider/', null, $row);
			}

		
		}
	}
	
	
}
 $TmplDesign->set('slider_show', $flag_show);
 


?>