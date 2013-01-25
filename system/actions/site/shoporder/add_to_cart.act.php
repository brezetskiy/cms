<?php
/** 
 * Добавление товара в корзину
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$product_id = globalVar($_REQUEST['product_id'], 0);
$amount = globalVar($_REQUEST['amount'], 1);

$Order = new ShopOrder();	
if(!empty($product_id)){	
	$Order->addProduct($product_id, $amount);
}

/**
 * Выводим список товаров которе были 
 * уже заказаны пользователем
 */
$ShopOrder = new ShopOrder();
$info = $ShopOrder->getOrderProductsInfo();
reset($info);
$sum = 0;$count = 0;
while (list($index, $row) = each($info)) {
	if(Auth::isLoggedIn()){
		$user = Auth::getInfo(); 
		$user['discount_value']=intval($user['discount_value']);
		$row['price'] = $row['price'] - $row['price'] * ($user['discount_value'] / 100);		
	}
	
	$sum += $row['amount']*$row['price']; 
	$count ++;
}
$sum = round($sum, 2);

echo 'count: '.$count.', sum: '.$sum;

?>  