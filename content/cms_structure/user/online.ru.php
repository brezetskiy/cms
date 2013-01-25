<?php
/** 
 * Список пользователей, котороые находятся онлайн
 * @package Pilot
 * @subpackage Auth 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 
$query = "
	SELECT
		tb_user.login as user_id,
		tb_online.ip,
		tb_online.local_ip,
		tb_auth_group.name as auth_group_id,
		date_format(tb_online.tstamp, '".LANGUAGE_DATE_SQL." %H:%i') as tstamp,
		concat('<a href=\"/".LANGUAGE_URL."action/admin/auth/user_shutdown/?user_id=', tb_online.user_id, '&cookie_code=', tb_online.cookie_code, '&_return_path=".CURRENT_URL_LINK."\">Отключить</a>') as logout
	from auth_online as tb_online
	left join auth_user as tb_user on tb_user.id=tb_online.user_id
	left join site_auth_group as tb_auth_group on tb_auth_group.id=tb_online.auth_group_id
	order by tb_online.tstamp DESC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('add', false);
$cmsTable->addColumn('user_id', '16%', 'left');
$cmsTable->addColumn('auth_group_id', '16%', 'left', 'Сайты');
$cmsTable->addColumn('ip', '16%', 'left');
$cmsTable->addColumn('local_ip', '16%', 'left');
$cmsTable->addColumn('tstamp', '16%', 'left');
$cmsTable->addColumn('logout', '16%', 'left', 'Выход');
echo $cmsTable->display();

unset($cmsTable);

?>