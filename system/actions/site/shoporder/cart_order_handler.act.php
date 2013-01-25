<?php
/** 
 * Обработчик событий корзины
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$action = globalVar($_REQUEST['action'], '');

if(empty($action)){
	exit;
}

/**
 * Введение перерасчета валют
 */
$data  = array();
$rates = array();
/*
$alternative_currencies = explode(",", SHOPORDER_CURRENCIES);

if(!empty($alternative_currencies)){
	reset($alternative_currencies);
	while(list(, $currency) = each($alternative_currencies)){
		$data[] = array('from_id' => SHOPORDER_CURRENCY_DEFAULT, 'to_id' => $currency);
	}
	$rates = Currency::getRatesBy($data);
}
*/
$rates = Currency::getRatesBy(array(0 =>array('from_id' => 840, 'to_id' => 980)));
$Order = new ShopOrder();

if($action == "recount"){
	$amounts1 = globalVar($_REQUEST['amount1'], array());
	$amounts2 = globalVar($_REQUEST['amount2'], array());
	$amounts = array();
	
	foreach($amounts1 as $key =>$amount){
		if(isset($amounts2[$key])){
			$amount = (string)$amount;
			$amounts2[$key] = (string)$amounts2[$key];
			$amounts[$key] = $amount.'.'.$amounts2[$key];
			$amounts[$key] = (float)$amounts[$key];
			
		}
		else $amounts[$key] =  $amount;
	}
	
	$Order->recount($amounts);	
	
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
	
}

?>  