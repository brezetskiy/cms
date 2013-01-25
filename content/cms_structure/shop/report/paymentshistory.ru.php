<?php
/** 
 * История заказов и выплат пользователя
 * @package Pilot 
 * @subpackage Shop 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$user_id = globalVar($_REQUEST['user_id'], 0);

$query = "SELECT login FROM auth_user WHERE id = '$user_id'";
$login = $DB->result($query);

function cms_filter($row) {
	if($row['status'] == 1 && !is_null($row['commission_id'])){
		$row['status'] = "<span style='color:grey;'>выплачено</span>";
	} elseif(is_null($row['status']) && is_null($row['commission_id'])){
		$row['status'] = "<span style='color:green;'>начислено</span>";
	} elseif($row['status'] == 0 && !is_null($row['commission_id'])){
		$row['status'] = "<span style='color:red;'>не выплачено</span>";
	}
	
	$row['content']  = (!empty($row['order_id'])) ? "<a href='/Admin/Shop/Orders/Products/?order_id=$row[order_id]'><img src='/design/cms/img/box.png' border='0'></a>" : "-";
	$row['order_id'] = (!empty($row['order_id'])) ? $row['order_id'] : "-";
	return $row;
}


$query = "
	SELECT 
		tb_commission.id,
		DATE_FORMAT(tb_commission.dtime, '%d.%m.%Y %H:%i:%s') as date,
		tb_commission.amount,
		tb_commission.order_id,
		tb_request.status,
		tb_request.commission_id
	FROM shop_order_commission as tb_commission
	LEFT JOIN shop_order_payment_request as tb_request ON tb_request.commission_id = tb_commission.id
	WHERE tb_commission.user_id = '$user_id'
	ORDER BY tb_commission.dtime desc 
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('title', "История заказов и выплат пользователя $login");
$cmsTable->addColumn('order_id', '20%', 'center', 'ID заказа');
$cmsTable->addColumn('date', '20%', 'center', 'Дата');
$cmsTable->addColumn('amount', '20%', 'right', 'Сумма');
$cmsTable->addColumn('status', '20%', 'center', 'Статус');
$cmsTable->addColumn('content', '10%', 'center', 'Состав заказа');
echo $cmsTable->display();
unset($cmsTable);


?>