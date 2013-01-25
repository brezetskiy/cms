<?php

$active 		= globalVar($_POST['active'], array());
$day_id 		= globalVar($_POST['day_id'], 0);

if($day_id ){
	$query = "DELETE FROM `shop_order_timeorder` WHERE day_id=$day_id";
	$DB->delete($query);
	
	$insert = '';
	foreach($active as $key=>$value){
		$insert .= (empty($insert)) ? '(NULL, '.$day_id.', '.$value.')' : ', (NULL, '.$day_id.', '.$value.')';
	}
	
	if(!empty($insert)){
		$query = "INSERT INTO `shop_order_timeorder` (`id`, `day_id`, `time_id`) VALUES $insert";
		$DB->insert($query);
	}
}
?>