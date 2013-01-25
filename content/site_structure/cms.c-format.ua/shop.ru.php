<?php
/**
 * страница товаров
 * @package Pilot
 * @subpackage subpsckage
 * @author Yaschenko Yuriy <xyhtep@ukr.net>
 * @copyright Delta-X, ltd. 2010
 */

 
global $DB;
$product_id = globalVar($_GET["product_url"], "");
$type = globalVar($_GET["type"], "");


$rate = Currency::getRatesBy(array(0 =>array('from_id' => 840, 'to_id' => 980)));
$rate = $rate[0]['rate'];
if (!empty($product_id)) {
	$newShop = new Shop('catalog');
	$TmplContent->set('show', 'product');
	
	$TmplDesign->set('headline', '');
	
	$query = $newShop->getProducts(0, 1, 0, array('tb_product.url' => $product_id));
	reset($query);
	while (list(,$row) = @each($query)) {
		//$photos = $newShop->getProductPhotos($row['id']);
		$row['notice'] = nl2br($row['notice']);
		$row['info'] = nl2br($row['info']);
		$id = $row['id'];
		$TmplContent->iterate('/item/', null, $row);
		
		if(empty($row['title']))
			$TmplDesign->set('title', $row['name']);
		else $TmplDesign->set('title', $row['title']);
		$TmplDesign->set('keywords', $row['keywords']);
		$TmplDesign->set('description', $row['description']);	
		
	}
	
	$newShop = new Shop('catalog/'.$type);
	$products = $newShop->getProducts(0, 4, $newShop->group_id);
	reset($products);
	if(count($products) > 0)
		$TmplContent->set('is_related', true);	
		
	while (list(,$row) = each($products)) {
		
		if ($row['id'] != $id){
			$TmplContent->iterate("/related/", null, $row);					
		}
	}
	
	
} else if(!empty($type)){
		
	$newShop = new Shop('catalog/'.$type);
	$TmplContent->set('show', 'catalog');	
	$query = $DB->query_row('SELECT * FROM shop_group WHERE id = '.$newShop->group['id']);

	
	$TmplDesign->set('headline', $newShop->group['name']);
	$TmplDesign->set('title', $newShop->group['title']);
	$TmplDesign->set('keywords', $newShop->group['keywords']);
	$TmplDesign->set('description', $newShop->group['description']);	
	$TmplContent->set('content_ru', $newShop->group['preview']);	
	$TmplContent->set('collection', $newShop->group['name']);
	

	$products = $newShop->getProducts(0, 1000, $newShop->group_id);
	reset($products);
	while (list(,$row) = each($products)) {		
		
		$row['collection'] = $newShop->group['name'];
		$row['price_uah'] = str_replace(',','.',round($row['price']*$rate, 2));
		
		$TmplProducts = $TmplContent->iterate("/model/", null, $row);		
	}			
} 

?>