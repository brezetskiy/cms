<?php
/**
* Подробная статистика переходов по баннеру
*
* @package Pilot
* @subpackage Banner
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$banner_id = globalVar($_GET['banner_id'], 0);
$date = globalVar($_GET['date'], '');
$return_path = globalVar($_GET['_return_path'], '/');

$query = "
	SELECT 
		tb_group.id AS group_id,
		tb_group.name AS group_name,
		tb_banner.title,
		tb_banner.id
	FROM banner_banner AS tb_banner
	INNER JOIN banner_group AS tb_group ON tb_banner.group_id = tb_group.id
	WHERE tb_banner.id = '$banner_id'
";
$banner = $DB->query_row($query);

if ($DB->rows == 0) {
	header('Location: /Admin/BannerGroup/');
	exit;
}

$TmplContent->set('date', date(LANGUAGE_DATE, convert_date("Y-m-d", $date)));
$TmplContent->set('banner', $banner);

$query = "
	SELECT 
		tb_stat.banner_id,
		tb_user.login,
		DATE_FORMAT(tb_stat.tstamp, '".LANGUAGE_DATETIME_SQL.":%s') AS tstamp,
		INET_NTOA(tb_stat.ip) AS ip,
		INET_NTOA(tb_stat.local_ip) AS local_ip
	FROM banner_click_raw as tb_stat
	LEFT JOIN auth_user as tb_user on tb_user.id=tb_stat.user_id
	WHERE tb_stat.banner_id = '$banner_id' AND DATE_FORMAT(tb_stat.tstamp, '%Y-%m-%d') = '$date'
	ORDER BY tb_stat.tstamp DESC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('title', ' ');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', '../?banner_id='.$banner_id);
$cmsTable->addColumn('tstamp', '25%', 'center');
$cmsTable->addColumn('ip', '25%');
$cmsTable->addColumn('local_ip', '25%');
$cmsTable->addColumn('login', '25%', 'left', 'Пользователь');
$TmplContent->set('cms_table', $cmsTable->display());
unset($cmsTable);
?>