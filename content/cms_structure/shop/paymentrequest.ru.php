<?php
/** 
 * Заявки на выплату
 * @package Pilot 
 * @subpackage Shop 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

function cms_filter($row) {
	if($row['payment_type'] == "card"){
		$row['payment_type'] = "пластиковая карта";
	} elseif($row['payment_type'] == "webmoney"){
		$row['payment_type'] = "webmoney";
	}
	
	if($row['status'] == 1){
		$row['status'] = "<span style=\"color:grey;\">выплачена</span>";   
	} else {
		$row['status'] = "не выплачена";   
	}
	
	$row['amount'] = -$row['amount']; 
	
	return $row;
}

$query = "
	SELECT
		tb_request.*,
		DATE_FORMAT(tb_request.dtime, '%d.%m.%Y %H:%i:%s') as date,
		tb_user.login,
		tb_commission.amount
	FROM shop_order_payment_request as tb_request
	INNER JOIN shop_order_commission as tb_commission ON tb_commission.id = tb_request.commission_id
	INNER JOIN auth_user as tb_user ON tb_user.id = tb_commission.user_id
	ORDER BY tb_request.dtime desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('add', false);

$cmsTable->addColumn('date', '10%', 'center', 'Дата');
$cmsTable->addColumn('login', '10%', 'left', 'Пользователь');
$cmsTable->addColumn('fio', '10%', 'left', 'ФИО');
$cmsTable->addColumn('payment_type', '10%', 'center', 'Форма оплаты');
$cmsTable->addColumn('number', '10%', 'right', 'Реквизиты');
$cmsTable->addColumn('amount', '10%', 'right', 'Сумма');
$cmsTable->addColumn('status', '10%', 'center', 'Статус'); 
echo $cmsTable->display();
unset($cmsTable);


?>