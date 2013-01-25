<?php
/** 
 * �����������
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

function cms_filter($row) {
	if($row['status'] == "new"){
		$row['status'] = "<span style=\"color:green\">�����</span>";
	} elseif($row['status'] == "in_process"){
		$row['status'] = "� ���������";
	} elseif($row['status'] == "confirmed"){
		$row['status'] = "�����������";
	} elseif($row['status'] == "cancelled"){
		$row['status'] = "<span style=\"color:red\">�����</span>";
	} elseif($row['status'] == "done"){
		$row['status'] = "<span style=\"color:grey\">��������</span>";
	} elseif($row['status'] == "returned"){
		$row['status'] = "<span style=\"color:red\">�������</span>";
	}
	return $row;
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
		tb_order.commission,
		DATE_FORMAT(tb_order.dtime, '%d.%m.%Y %H:%i:%s') as date,
		tb_order.status
	FROM shop_order as tb_order
	LEFT JOIN auth_user as tb_user ON tb_user.id = tb_order.user_id
	WHERE tb_order.accepted = 1
	ORDER BY status desc, dtime desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('add', false);

$cmsTable->addColumn('date', '10%', 'center', '����');
$cmsTable->addColumn('login', '10%', 'left', '������������');
$cmsTable->addColumn('name', '10%', 'left', '���');
$cmsTable->addColumn('phone', '10%');
$cmsTable->addColumn('address', '10%');
$cmsTable->addColumn('email', '10%');
$cmsTable->addColumn('commission', '10%'); 
$cmsTable->addColumn('status', '10%', 'center', '������');
echo $cmsTable->display();
unset($cmsTable);


?>