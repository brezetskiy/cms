<?php
/** 
 * Содержимое заказа
 * @package Pilot 
 * @subpackage Shop 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$order_id = globalVar($_GET['order_id'], 0);

if (is_module('referral')) {
	$referral_columns_addon = ",IFNULL(tb_user.referral_percent, 0) as referral_percent";
} else {
	$referral_columns_addon = '';
}

$query = "
	SELECT 
		tb_order.name,
		tb_order.id
		$referral_columns_addon
	FROM shop_order as tb_order
	LEFT JOIN auth_user as tb_user ON tb_order.user_id = tb_user.id
	WHERE tb_order.id = '$order_id'
";
$order = $DB->query_row($query);
if ($DB->rows == 0) {
	header('Location: ../');
	exit;
}

if (is_module('referral')) {
	$referral_columns_addon = ",ROUND(tb_order.price*tb_order.amount*$order[referral_percent]/100) as commission";
} else {
	$referral_columns_addon = '';
}

$query = "
	SELECT 
		tb_order.id,
		tb_order.name, 
		tb_order.price, 
		tb_order.amount,
		ROUND(tb_order.price*tb_order.amount) as total_amount
		$referral_columns_addon
	FROM shop_order_product as tb_order
	WHERE tb_order.order_id = '$order_id'
	ORDER BY tb_order.name asc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam("show_parent_link", true);
$cmsTable->setParam("parent_link", "../?");
$cmsTable->setParam("title", "Содержимое заказа №$order[id], клиент $order[name]");
$cmsTable->addColumn('name', '40%', 'left', 'Товар');
$cmsTable->addColumn('price', '10%');
$cmsTable->addColumn('amount', '10%');
$cmsTable->addColumn('total_amount', '10%', 'right', 'Сумма');
if (is_module('referral')) {
	$cmsTable->addColumn('commission', '10%', 'right', 'Комиссия');
}
echo $cmsTable->display();
unset($cmsTable);


?>