<?php
/**
 * Список зарегистрированных на сайте пользователей
 * @package User
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */


function row_filter($row) {
	global $DB;
	
	if (is_module('Billing')) {
		
		if ($row['balance'] == 0) {
			$row['balance'] = "<span style='color:gray'>$row[balance]</span>";
		} elseif ($row['balance'] < 0) {
			$row['balance'] = "<span style='color:red'>$row[balance]</span>";
		} else {
			$row['balance'] .= "<br><span class=comment>[<a href='javascript:void(0);' onclick=\"AjaxRequest.send(null, '/action/admin/billing/balance_refund_form/', '', true, { user_id: $row[id], _return_path: '".CURRENT_URL_FORM."' });return false;\">возврат</a>]</span>";
		}
		
		if ($row['credit_limit'] == 0) {
			$row['credit_limit'] = "<span style='color:gray'>$row[credit_limit]</span>";
		} elseif ($row['credit_limit'] < 0) {
			$row['credit_limit'] = "<span style='color:red'>$row[credit_limit]</span>";
		}
		
		$contragent_id = $DB->fetch_column("select contragent_id from billing_contragent_user where user_id='$row[id]'");
		
		$query = "
			select round(sum(tb_item.amount * tb_item.quantity), 2) as `sum`
			from billing_invoice_out as tb_invoice
			inner join billing_invoice_out_item as tb_item on tb_item.invoice_id = tb_invoice.id
			inner join billing_payment_handler as tb_handler on tb_item.handler_id = tb_handler.id
			where 
				tb_invoice.contragent_id in (0".implode(",", $contragent_id).") 
				and tb_invoice.status = 'payed'
				and tb_handler.uniq_name != 'balance'
		";
		$row['valuedservices'] = $DB->result($query);
		
		$row['valuedservices'] = number_format($row['valuedservices'], 2, '.', '');
		if ($row['valuedservices'] == 0) {
			$row['valuedservices'] = "<span style='color:gray'>$row[valuedservices]</span>";
		}
		
		$row['name'] .= "<br><span class=comment>Контрагенты: ".implode(",", $contragent_id)."</span>";
		
	}
	
	if(is_module('PM')) {
		$row['message'] = "<a href=\"./Message/?user_id=".$row['id']."\">Сообщения</a>";
	}
	
	return $row;
}



$query = "
	SELECT 
		tb_user.*,
		concat('<a href=\"./Info/?user_id=', tb_user.id, '\">', tb_user.login, '</a>') as login,
		".((is_module('Billing')) ? " tb_user.balance, tb_user.credit_limit, " : "")." 
		CONCAT(
			tb_user.name, ' (', tb_user.email, ')',
			'<br><span class=\"comment\">Группа: ', ifnull(tb_group.name, '<span style=\"color: #0E5FD8;\">не назначены</span>'), '<br>', tb_phone.phone_original, '</span>'
		) AS name,
		tb_site.url as site_id,
		CONCAT('<a title=\"Перейти в аккаунт этого пользователя\" href=\"/".LANGUAGE_URL."action/admin/auth/switch_user/?switch_id=', tb_user.id, '&_return_path=".urlencode('/')."\"><img src=\"/design/cms/img/icons/lock.gif\" width=\"15px\" height=\"15px\" border=0></a>') AS switch
	FROM auth_user AS tb_user
	LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
	LEFT JOIN site_structure_site AS tb_site ON tb_site.id=tb_user.site_id
	LEFT JOIN auth_user_phone AS tb_phone ON tb_phone.user_id=tb_user.id
	GROUP BY tb_user.id
	ORDER BY login
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'row_filter');

$cmsTable->addColumn('login', '20%');
$cmsTable->setColumnParam('login', 'order', 'login');
$cmsTable->addColumn('name', '50%', 'left', 'ФИО');
$cmsTable->setColumnParam('name', 'order', 'name');
$cmsTable->addColumn('site_id', '5%');
$cmsTable->addColumn('checked', '5%');
$cmsTable->setColumnParam('checked', 'editable', true);

if (is_module('Billing')) {
	$cmsTable->addColumn('balance', '5%');
	$cmsTable->addColumn('credit_limit', '5%');
	$cmsTable->addColumn('valuedservices', '5%', 'right', 'Оборот');
}

if (is_module('referral')) {
	$cmsTable->addColumn('referral_balance', '5%', 'right', 'Реф. баланс');
}

if(is_module('PM')) {
	$cmsTable->addColumn('message', '5%', 'center', 'Сообщения');
}

$cmsTable->addColumn('switch', '5%', 'center', 'Вход');

echo $cmsTable->display();
unset($cmsTable);


?>