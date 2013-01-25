<?php
/**
 * Статистика реестрации пользователей 
 * на сайтах системы за месяц
 */

$site_id = globalVar($_GET['site_id'], 0);
$month = globalVar($_GET['month'], date('m'));
$year = globalVar($_GET['year'], date('Y'));

$time = @mktime(0, 0, 0, $month, 1, $year);
if ($time === false) {
	$time = time();
}
$TmplContent->set('month', (int)date('m', $time));
$TmplContent->set('year', date('Y', $time));


/**
 * Сайты
 */
$query = "select id, url from site_structure_site order by priority";
$sites = $DB->fetch_column($query);
$TmplContent->set('sites', $sites);
//if (!isset($sites[$site_id])) {
//	$rev_sites = array_keys($sites);
//	$site_id = $rev_sites[0];
//}

$TmplContent->set('site_id', $site_id);


// Формируем месяцы

$monthes = array();
for ($i=1; $i<=12; $i++) {
	$monthes[$i] = constant('LANGUAGE_MONTH_NOM_'.$i);
}
$TmplContent->set('monthes', $monthes);

$query = "select id, url from site_structure_site where id = '$site_id'";
$site = $DB->query_row($query);

function cms_filter($row) {
	global $day, $chart_data_hits, $chart_data_hosts;
	$site_id = $GLOBALS['site_id'];
	$row['date'] = "<a href='/Admin/User/Yearstat/Userstat/Day/?day=$row[day]&month=$row[month]&year=$row[year]&site_id=$site_id'>$row[date]</a>";
	return $row;
}

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
		where month(tb_user.registration_dtime) = '".date('m', $time)."' and year(tb_user.registration_dtime) = '".date('Y', $time)."' and tb_bilout.`status`='payed' 
		and  ".$where."    
	";
	$DB->query($query);

}

$query = "
	select
	    tb_user.id,
	    tb_user.site_id as site_url,
	     count(DISTINCT(tb_user.name)) as sumname, ";
if (is_module('Billing') == 1) {
	$query .= " ifnull(sum(tb_log.status = 'payed'), 0) as sumpayed,";
}
$query .= "		
		DAY(tb_user.registration_dtime) as day,
	    year(tb_user.registration_dtime) as year,
	    MONTH(tb_user.registration_dtime) as month,
		DATE_FORMAT(tb_user.registration_dtime, '".LANGUAGE_DATE_SQL."') as date,
		date_format(tb_user.registration_dtime, '".LANGUAGE_DATE_SQL."') as date_chart,
	    tb_user.registration_dtime,
	    tb_site.url as site_id
	FROM auth_user AS tb_user
	LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
	left join site_structure_site as tb_site on tb_site.id=tb_user.site_id";
if (is_module('Billing') == 1) {
	$query .= " left join tmp_log as tb_log on tb_log.user_id = tb_user.id";
}
$query .= " 
	WHERE
		month(tb_user.registration_dtime)='".date('m', $time)."'
		and year(tb_user.registration_dtime) = '".date('Y', $time)."' 
		and  ".$where." 
	GROUP BY date_format(tb_user.registration_dtime, '".LANGUAGE_DATE_SQL."')
	ORDER BY tb_user.registration_dtime     
";

$caption = ($site_id == 0) ? 'Количество регистраций на всех сайтах системы': 'Количество регистраций на сайте '.$site['url'];

$cmsTable = new cmsShowView($DB, $query, 50);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', "/Admin/User/Yearstat/?&site_id=$site_id");
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('class_field', 'class');
$cmsTable->setParam('title', $caption);
$cmsTable->addColumn('date', '50%', 'left', 'Дата');
$cmsTable->addColumn('sumname', '25%', 'left', 'Количество регистраций за день');
if (is_module('Billing') == 1) {
	$cmsTable->addColumn('sumpayed', '25%', 'left', 'Количество регистраций с оплатой');
}
$TmplContent->set('cms_table', $cmsTable->display());

$chart_data = $chart_data2 = array();
$data = $cmsTable->getData();
reset($data);
while (list(,$row) = each($data)) {
	if(is_module('Billing') == 1) {
		$chart_data[$row['date_chart']] = $row['sumname'] - $row['sumpayed'];
		$chart_data2[$row['date_chart']] = $row['sumpayed'];
	} else {
		$chart_data[$row['date_chart']] = $row['sumname'];
	}
}

//x($chart_data);
$Chart = new FusionChart(array('labelDisplay'=>'ROTATE'));
$Chart->setCaption($caption);
$Chart->createDaysLabels($year, $month, LANGUAGE_CURRENT);
if (is_module('Billing') == 1) {
	$TmplContent->set('type', 'MS');
	$Chart->addDataSet($chart_data2, array('showValues'=>0, 'seriesName'=>'Регистрации с оплатой', 'color'=>'e51313'));
	$Chart->addDataSet($chart_data, array('showValues'=>0, 'seriesName'=>'Регистрации без оплаты', 'color'=>'d7e3f6'));

} else {
	$Chart->addDataSet(array(), array('showValues'=>0, 'seriesName'=>'Регистрации с оплачеными счетами', 'color'=>'e51313'));
	$Chart->addDataSet($chart_data, array('showValues'=>0, 'seriesName'=>'Регистрации', 'color'=>'d7e3f6'));

}
$TmplContent->set('chart_xml', $Chart->renderXml(CMS_CHARSET));

?>