<?php
/**
 * История выполнения cron-скриптов
 * @package Pilot
 * @subpackage Site
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$crontab_id = globalVar($_REQUEST['crontab_id'], '');


if(empty($crontab_id)) {
	$query = "
		SELECT 
			CONCAT('<a href=\'/Admin/CMS/Crontab/?crontab_id=', id, '\'>', url, '</a>') as url,
			date_format(start_dtime, '".LANGUAGE_DATE_SQL." %H:%i') as last_dtime
		FROM cms_crontab
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('add', false);
	$cmsTable->setParam('delete', false);
	$cmsTable->setParam('edit', false);
	$cmsTable->addColumn('url', '70%', 'left');
	$cmsTable->addColumn('last_dtime', '30%', 'left', 'Последний запуск');
	echo $cmsTable->display();
	unset($cmsTable);
	
} else {
	$name = $DB->result("select url from cms_crontab where id='$crontab_id'");
	$query = " 
		SELECT 
			DATE_FORMAT(start_dtime, '%d.%m.%Y %H:%i:%s') as start_dtime,
			DATE_FORMAT(end_dtime, '%d.%m.%Y %H:%i:%s') as end_dtime,
			TIMESTAMPDIFF(SECOND, start_dtime, end_dtime) as time,
			case
				when status = 'failed' then '<span style=\'color:red;\'>неудачно</span>'
				when status = 'blocked' then '<span style=\'color:#777;\'>заблокирован</span>' 
			else '<span style=\'color:green;\'>успешно</span>'
		end as status
		FROM cms_crontab_history
		WHERE crontab_id = '$crontab_id'
		ORDER BY start_dtime DESC
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('title', 'История выполнения скрипта /'.$name);
	$cmsTable->setParam('show_parent_link', true);
	$cmsTable->setParam('parent_link', './?');
	$cmsTable->setParam('add', false);
	$cmsTable->setParam('delete', false);
	$cmsTable->setParam('edit', false);
	$cmsTable->addColumn('start_dtime', '25%', 'center');
	$cmsTable->addColumn('end_dtime', '25%', 'center');
	$cmsTable->addColumn('time', '25%', 'right', 'Время выполнения, в сек.');
	$cmsTable->addColumn('status', '25%', 'center');
	echo $cmsTable->display();
	unset($cmsTable);
}



?>