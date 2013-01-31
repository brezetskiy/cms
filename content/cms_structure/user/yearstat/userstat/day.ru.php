<?php
/**
 * Статистика регистрации на сайте за день
 */
//x($_GET);
//exit();
$site_id = globalVar($_GET['site_id'], 0);
$month = globalVar($_GET['month'], date('m'));
$year = globalVar($_GET['year'], date('Y'));
$day = globalVar($_GET['day'], date('d'));

$time = @mktime(0, 0, 0, $month, $day, $year);
if ($time === false) {
	$time = time();
}
$TmplContent->set('month', (int)date('m', $time));
$TmplContent->set('year', date('Y', $time));

$query = "
	select id, url from site_structure_site
	where id = '$site_id'
";
$site = $DB->query_row($query);

$where = ($site_id == 0)? '1 = 1': 'tb_site.id = '.$site_id; 

if (is_module('Billing') == 1) {
$query ="
	CREATE TEMPORARY TABLE `tmp_log` (
	  `user_id` smallint(6) unsigned DEFAULT '0',
	  `user_login` varchar(200) COLLATE cp1251_ukrainian_ci DEFAULT NULL,
	  `status` varchar(200) COLLATE cp1251_ukrainian_ci DEFAULT NULL,
	  KEY `user_id` (`user_id`, `status`)
	) ENGINE=MyISAM;
";	
	
$DB->query($query);

$query = "
	insert into tmp_log (user_id, user_login, `status`)
	select 
	    DISTINCT(tb_user.id),
	    tb_user.login,
	    tb_bilout.`status`
	from auth_user as tb_user
	LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
	left join site_structure_site as tb_site on tb_site.id=tb_user.site_id
	left join billing_contragent_user as tb_biluser on tb_biluser.user_id = tb_user.id
	left join billing_invoice_out as tb_bilout on tb_bilout.contragent_id = tb_biluser.contragent_id
	where month(tb_user.registration_dtime) = '".date('m', $time)."' and year(tb_user.registration_dtime) = '".date('Y', $time)."' and  day(tb_user.registration_dtime) = '".date('d', $time)."' and tb_bilout.`status`='payed' 
	and  ".$where."    
";
$DB->query($query);
}

$query = "
	select
	    CONCAT(date_format(tb_user.registration_dtime, '%k'), ':00 - ', date_format(tb_user.registration_dtime, '%k'), ':59') AS hour,
    	count(DISTINCT(tb_user.name)) as sumname,";
if (is_module('Billing') == 1) {
	$query .= " ifnull(sum(tb_log.status = 'payed'), 0) as sumpayed,";
}
$query .= "
		GROUP_CONCAT(DISTINCT(tb_user.login) SEPARATOR '<br>') as login,
		date_format(tb_user.registration_dtime, '%k') as chart_hour,
	   	date_format(tb_user.registration_dtime, '".LANGUAGE_DATE_SQL."') as date,
	    tb_user.registration_dtime,
	    tb_site.url as site_id
	FROM auth_user AS tb_user
	LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
	left join site_structure_site as tb_site on tb_site.id=tb_user.site_id";
if (is_module('Billing') == 1) {
	$query .= " left join tmp_log as tb_log on tb_log.user_id = tb_user.id";
}
$query .= " where month(tb_user.registration_dtime) = '".date('m', $time)."' and year(tb_user.registration_dtime) = '".date('Y', $time)."'
		and  day(tb_user.registration_dtime) = '".date('d', $time)."' and $where	
		GROUP BY chart_hour
		ORDER BY tb_user.registration_dtime 
		    
";


$caption = ($site_id == 0) ? 'Количество регистраций на всех сайтах системы за '.date('y-m-d', $time): "Статистика регистрации на сайте ".$site['url']." за ".date('y-m-d', $time);


$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', "/Admin/User/Yearstat/Userstat/?month=$month&year=$year&site_id=$site_id");
$cmsTable->setParam('class_field', 'class');
$cmsTable->setParam('title', $caption);
$cmsTable->addColumn('hour', '25%', 'left', 'Время');
if (is_module('Billing') == 1) {
	$cmsTable->addColumn('sumname', '25%', 'left', 'Количество регистраций за час');
	$cmsTable->addColumn('sumpayed', '25%', 'left', 'Количество регистраций с оплатой');
	$cmsTable->addColumn('login', '25%', 'left', 'Логин');
} else {
	$cmsTable->addColumn('sumname', '25%', 'left', 'Количество регистраций за час');
	$cmsTable->addColumn('login', '25%', 'left', 'Логин');
}
$TmplContent->set('cms_table', $cmsTable->display());



$chart_data = $DB->fetch_column($query, 'chart_hour', 'sumname');
//x($chart_data);
$Chart = new FusionChart();
$Chart->setCaption($caption);
$Chart->createHoursLabels();
if (is_module('Billing') == 1) {
	$TmplContent->set('type', 'MS');
	$chart_data2 = $DB->fetch_column($query, 'chart_hour', 'sumpayed');
	$Chart->addDataSet($chart_data2, array('showValues'=>0, 'seriesName'=>'Регистрации с оплачеными счетами', 'color'=>'e51313'));
	$Chart->addDataSet($chart_data, array('showValues'=>0, 'seriesName'=>'Зарегистрированные пользователи', 'color'=>'d7e3f6'));
	
}
else {
	$Chart->addDataSet(array(), array('showValues'=>0, 'seriesName'=>'Регистрации с оплачеными счетами', 'color'=>'e51313'));
	$Chart->addDataSet($chart_data, array('showValues'=>0, 'seriesName'=>'Зарегистрированные пользователи', 'color'=>'d7e3f6'));
}
$TmplContent->set('chart_xml', $Chart->renderXml(CMS_CHARSET));


?>