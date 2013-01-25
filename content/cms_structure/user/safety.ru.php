<?php

/** 
 * Статистика входов администратора в систему управления 
 * @package Pilot
 * @subpackage Auth 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

$query = "
	SELECT
		IF(tb_log.local_ip!='', CONCAT(tb_log.ip, '/', tb_log.local_ip), ip) AS ip,
		if(
			tb_user.id is null,
			concat('<span style=\"color:red;\">', tb_log.login, '</span>'),
			concat('<span style=\"color:green;\">', tb_user.login, '</span>')
		) as login,
		DATE_FORMAT(tb_log.login_dtime, '".LANGUAGE_DATETIME_SQL."') AS login_dtime,
		if(
			tb_log.login_dtime=tb_log.logout_dtime,
			'-',
			DATE_FORMAT(tb_log.logout_dtime, '".LANGUAGE_DATETIME_SQL."')
		) AS logout_dtime,
		tb_log.hit_count
	FROM auth_log as tb_log
	LEFT JOIN auth_user as tb_user on tb_user.id=tb_log.user_id
	ORDER BY tb_log.login_dtime DESC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->filterSkipTable('auth_user');

$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('add', false);

$cmsTable->addColumn('ip', '20%', 'left', 'IP');
$cmsTable->addColumn('login', '20%', 'left', 'Логин');
$cmsTable->addColumn('login_dtime', '15%', 'center', 'Вход');
$cmsTable->addColumn('logout_dtime', '15%', 'center', 'Выход');
echo $cmsTable->display();

unset($cmsTable);

?>