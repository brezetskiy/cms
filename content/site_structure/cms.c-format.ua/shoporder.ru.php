<?php
/**
 * Оформление заказа
 * @package watch
 * @subpackage subpsckage
 * @author Yaschenko Yuriy <xyhtep@ukr.net>
 * @copyright Delta-X, ltd. 2010
 */
if(Auth::isLoggedIn()){
	$user = Auth::getInfo();
	$TmplContent->set("user_id", $user['id']);
	if(trim($user['name']) == ""){
		$TmplContent->set("user_name", $user['login']);
	} else {
		$TmplContent->set("user_name", $user['name']);	
	}
	
	$phone = substr($user['phone'], 0, 3) .' '. substr($user['phone'], 3, 3) .'-'.substr($user['phone'], 6, 2). '-'. substr($user['phone'], 8, 2); // возвращает "bcd"
	$TmplContent->set("user_phone", $phone);
	$TmplContent->set("user_email", $user['email']);
}

if (isset($_SESSION['ActionError']) && isset($_SESSION['ActionReturn']['error'])){
	list(, $error) = each($_SESSION['ActionReturn']['error']);
	$TmplContent->set('error', $error);
}
$Order 	   = new ShopOrder();
$products  = $Order->getOrderProductsInfo(); 
$TmplContent->set("rows_count", $Order->total_order_products);

$total_all = 0;
$class     = 0;
$rate = Currency::getRatesBy(array(0 =>array('from_id' => 840, 'to_id' => 980)));
$rate = $rate[0]['rate'];
reset($products);
$measure_l=$measure_g = 0.0;
while(list(, $row) = each($products)){ 
	$group_id = $DB->query_row("SELECT group_id,_url as uniq_name from shop_product where id = {$row['product_id']}");
	$query = "
	SELECT tb_group.name, tb_group.uniq_name
	FROM shop_group tb_group
	INNER JOIN shop_group_relation tb_relation ON (tb_group.id = tb_relation.parent)
	WHERE tb_relation.id = {$group_id['group_id']}
 	order by tb_relation.priority";
	$group_info = $DB->query($query);

	//$row['price'] = round($row['price']*$rate);
	
	$newShop = new Shop('catalog');
	$info_product = $newShop->getProductInfo($row['product_id']);
	$row['img'] = $info_product['img'];
	$row['measure'] = $info_product['measure'];
	
	$row['url']='';
	while(list(,$ul)=each($group_info)){
		$row['url'] .= $ul['uniq_name'].'/';
	}
	
	$row['url'] .= $info_product['url'];
	$row['total'] = $row['price']*$row['amount'];
	if (!empty($user)){
		
		$row['discount_price'] =  intval($user['discount_value']);
		$stock = $row['total'] * ($row['discount_price'] / 100);
		$row['total'] = round(($row['total'] - $stock), 2);
	}
	else $row['discount_price'] =  -1;
	$class = $class+1;
	
	$total_all = $total_all + $row['total'];
	
	$row['amount1'] = floor($row['amount']);
	$rw = (string)$row['amount'];
	$rw=explode('.',$rw);
	if (isset($rw[1]))
		$row['amount2'] = $rw[1];
	else
		$row['amount2'] = '000';
		
	if($row['measure'] == 'мл')
		$measure_l +=$row['amount'];
	elseif($row['measure']=='грамм')
		$measure_g +=$row['amount'];
		
	$TmplProducts = $TmplContent->iterate("/products/", null, $row);
}
$TmplContent->set("total_all", $total_all);

$TmplContent->set("measure_l", $measure_l);
$TmplContent->set("measure_r", $measure_g);


if(isset($_SESSION['ActionError'])){
	reset($_SESSION['ActionReturn']['error']);
	while(list(, $message) = each($_SESSION['ActionReturn']['error'])){
		$row['message'] = $message;
		$TmplContent->iterate("error", null, $row);
	}
	$TmplContent->set($_SESSION['ActionError']);
}

$TmplDesign->set('headline', "Ваш заказ");
	
$query = "SELECT id, day_in_week FROM shop_order_day";
$data = $DB->query($query); 
$str_time ='{';
while(list(, $row) = each($data)){ 
	if($str_time != '{') $str_time .=', ';
	$day = $row['day_in_week'];	
	
	$query = "SELECT t1.id, t2.hour, t2.minute FROM shop_order_timeorder AS t1
				LEFT JOIN shop_order_time AS t2 ON t2.id=t1.time_id
				WHERE t1.day_id = $day
				ORDER BY t2.hour ASC, t2.minute ASC				
			";
	$time = $DB->query($query);
	
	$begin = '"'.$day.'": [';
	$end = ']';
	$cm = '';
	while(list(,$newrow) = each($time)){
		$cm .=(empty($cm)) ? '': ', ';
		$cm .= '{"h":'.$newrow['hour'].', "m":'.$newrow['minute'].'}';
	}
	if(!empty($cm)) $str_time .= $begin.$cm.$end;
	
}
$str_time .='}';
$TmplContent->set('str_time', $str_time); 
?>