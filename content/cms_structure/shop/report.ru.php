<?php
/** 
 * ����� �� �������� � ������.
 * @package Pilot 
 * @subpackage Shop 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

/**
 * ����� ����������� �����, ��������� � �������, �� ��� �����
 */
$credit 			= array();

/**
 * ��� ���������� �������
 */
$request 			= array();

/**
 * ���� �� ����������� ��������
 */
$request_debt    	= array();

/**
 * ����������� ����������� �������
 */
$request_payment 	= array();  

/**
 * ������� �� �����, ��������� � �������
 */
$available 			= array();

$query = "
	create temporary table tmp_payment (
		user_id int unsigned not null default 0,
		credit int not null default 0,
		request int not null default 0,
		request_debt int not null default 0,
		request_payment int not null default 0,
		available int not null default 0,
		primary key (user_id)
	)
";
$DB->query($query);

// ����� ����������� ����� �� ��� �����
$query = "
	insert into tmp_payment (user_id, credit, request, request_debt, request_payment)
	SELECT 
		tb_commission.user_id,
		sum(if(tb_commission.dtime < current_date() - INTERVAL 14 DAY AND tb_commission.amount > 0, amount, 0)),
		sum(if(tb_commission.amount < 0, tb_commission.amount, 0)),
		sum(if(tb_commission.amount < 0 AND tb_request.status = 0, tb_commission.amount, 0)),
		sum(if(tb_commission.amount < 0 AND tb_request.status = 1, tb_commission.amount, 0))
	FROM shop_order_commission AS tb_commission
	LEFT JOIN shop_order_payment_request as tb_request ON tb_request.commission_id = tb_commission.id
	GROUP BY tb_commission.user_id
";
$DB->insert($query);

$query = "
	SELECT 
		concat('<a href=\"/Admin/Shop/Report/PaymentsHistory/?user_id=', tb_user.id, '\">', tb_user.login, '</a>') as login,
		tb_payment.credit,
		abs(tb_payment.request) as request,
		tb_payment.request_debt,
		abs(tb_payment.request_payment) as request_payment,
		tb_payment.credit + tb_payment.request_payment as available
	FROM auth_user as tb_user
	INNER JOIN tmp_payment as tb_payment on tb_payment.user_id=tb_user.id
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('title', "�����");
$cmsTable->addColumn('login', '20%', 'left', '�������');
$cmsTable->addColumn('credit', '10%', 'right', '����� ����������� �����, ��������� � �������');
$cmsTable->addColumn('request', '10%', 'right', '����� ����� ���� ���������� ������');
$cmsTable->addColumn('request_debt', '10%', 'right', '���� �� ����������� ��������');
$cmsTable->addColumn('request_payment', '10%', 'right', '����� ����� ���� ������������� ������');
$cmsTable->addColumn('available', '10%', 'right', '������� �� �����, ��������� � �������');
echo $cmsTable->display();
unset($cmsTable);


?>