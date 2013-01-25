<?php
/** 
 * Добавление товара в корзину
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$id = globalVar($_REQUEST['id'], 0);

if(empty($id)){
	exit;
}

/**
 * Введение перерасчета валют
 */
$data  = array();
$rates = array();
$alternative_currencies = explode(",", SHOPORDER_CURRENCIES);
if(!empty($alternative_currencies)){
	reset($alternative_currencies);
	while(list(, $currency) = each($alternative_currencies)){
		$data[] = array('from_id' => SHOPORDER_CURRENCY_DEFAULT, 'to_id' => $currency);
	}
	$rates = Currency::getRatesBy($data);
}


$Order = new ShopOrder();
$Order->deleteProduct($id);

$products  = $Order->getOrderProductsInfo();
$total_all = 0;
		
reset($products);
while(list(, $row) = each($products)){
	$total_all = $total_all + $row['price']*$row['amount']; 
}
 
$_RESULT['order_total_'.SHOPORDER_CURRENCY_DEFAULT] = $total_all; 
$_RESULT['minicart_order_total_'.SHOPORDER_CURRENCY_DEFAULT] = $total_all; 

// перерасчет валют
reset($rates);  
while(list(, $rate) = each($rates)){ 
	$_RESULT['order_total_'.$rate['currency_to_id']] = round($total_all*$rate['rate'], 2);
	$_RESULT['minicart_order_total_'.$rate['currency_to_id']] = round($total_all*$rate['rate'], 2);
}

$_RESULT['javascript'] = "$(\"#product_$id\").remove();";

?>  