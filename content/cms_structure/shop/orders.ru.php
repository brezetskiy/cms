<?php
/** 
 * Справочники
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

function cms_filter($row) {
	if($row['status'] == "new"){
		$row['status'] = "<span style=\"color:green\">новый</span>";
	} elseif($row['status'] == "in_process"){
		$row['status'] = "в обработке";
	} elseif($row['status'] == "confirmed"){
		$row['status'] = "подтвержден";
	} elseif($row['status'] == "cancelled"){
		$row['status'] = "<span style=\"color:red\">отказ</span>";
	} elseif($row['status'] == "done"){
		$row['status'] = "<span style=\"color:grey\">выполнен</span>";
	} elseif($row['status'] == "returned"){
		$row['status'] = "<span style=\"color:red\">возврат</span>";
	}
	  
	$row['content'] = "<a href='/Admin/Shop/Orders/Products/?order_id=$row[id]'><img src='/design/cms/img/box.png' border='0'></a>";
	return $row;
}

if (is_module('referral')) {
	$referral_columns_addon = ",ROUND(SUM(tb_product.price * tb_product.amount)*tb_user.referral_percent/100) as commission";
} else {
	$referral_columns_addon = '';
}
  
$query = "
	SELECT
		tb_order.id,
		tb_user.login,
		tb_order.name,
		tb_order.phone,
		tb_order.address,
		tb_order.email,
		tb_order.comment, 
		DATE_FORMAT(tb_order.dtime, '%d.%m.%Y %H:%i:%s') as date,
		tb_order.status,
		ROUND(SUM(tb_product.price * tb_product.amount)) as total_price,
		ROUND(SUM(tb_product.price * tb_product.amount*(1-if(tb_order.discount_value != 0, tb_order.discount_value/100,0)))) as total_discount_price
		$referral_columns_addon
	FROM shop_order as tb_order
	LEFT JOIN auth_user as tb_user ON tb_user.id = tb_order.user_id
	INNER JOIN shop_order_product as tb_product ON tb_product.order_id = tb_order.id
	WHERE tb_order.accepted = 1
	GROUP BY tb_order.id
	ORDER BY tb_order.status asc, tb_order.dtime desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('add', false);

$cmsTable->addColumn('id', '3%', 'center', 'ID');
$cmsTable->addColumn('date', '10%', 'center', 'Дата');
$cmsTable->addColumn('login', '10%', 'left', 'Пользователь');
$cmsTable->addColumn('name', '10%', 'left', 'ФИО');
$cmsTable->addColumn('phone', '10%');
$cmsTable->addColumn('address', '10%');
$cmsTable->addColumn('email', '10%');
$cmsTable->addColumn('total_price', '10%', 'right', 'Сумма заказа'); 
$cmsTable->addColumn('total_discount_price', '10%', 'right', 'Скидка'); 
if (is_module('referral')) {
	$cmsTable->addColumn('commission', '10%', 'right', 'Комиссия'); 
}
$cmsTable->addColumn('status', '10%', 'center', 'Статус');
$cmsTable->addColumn('content', '5%', 'center', 'Состав');
echo $cmsTable->display();
unset($cmsTable);


?>