<?php
/**
 * Формирование статистики регистрации 
 * на сайтах системи за год
 */
$site_id = globalVar($_GET['site_id'], 0);

/**
 * Сайты
 */
$query = "select id, url from site_structure_site order by priority";
$sites = $DB->fetch_column($query);
$TmplContent->set('sites', $sites);

$TmplContent->set('site_id', $site_id);

$query = "select id, url from site_structure_site where id = '$site_id'";
$site = $DB->query_row($query);

$result = array();
$result2 = array();


function cms_filter($row) {
	global $result, $result2, $site_id;
	$result[$row['year']."-".$row['month']] = $row['sumname'];
	if (is_module('Billing') == 1) {
		$result2[$row['year']."-".$row['month']] = $row['sumpayed'];
	}
	$row['year_month'] = "<a href='/Admin/User/Yearstat/Userstat/?month=$row[month]&year=$row[year]&site_id=".$site_id."'>".$row['year']." ".constant('LANGUAGE_MONTH_NOM_'.(int)$row['month'])."</a>";
	
	if (is_module('Billing') == 1) {
		$row['reject'] = number_format(100 - ($row['sumpayed'] / $row['sumname'] * 100), 2, '.', '');
	}
	 
	return $row;
}

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
		    tb_user.id,
		    tb_user.login,
		    tb_bilout.`status`
		from auth_user as tb_user
		LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
		left join site_structure_site as tb_site on tb_site.id=tb_user.site_id
		left join billing_contragent_user as tb_biluser on tb_biluser.user_id = tb_user.id
		left join billing_invoice_out as tb_bilout on tb_bilout.contragent_id = tb_biluser.contragent_id
		left join billing_invoice_out_item as tb_item on tb_bilout.id = tb_item.invoice_id
		where tb_bilout.`status`='payed' 
		 ".where_clause('tb_site.id', $site_id)."   
		group by tb_user.id 
	";
	$DB->query($query);
}

$query = "
	select
	    tb_user.id,
	    tb_user.site_id as site_url,
	    count(tb_user.name) as sumname,";
if (is_module('Billing') == 1) {
	$query .= " ifnull(sum(tb_log.status = 'payed'), 0) as sumpayed,";
}
$query .= "	    
	    year(tb_user.registration_dtime) as year,
	    MONTH(tb_user.registration_dtime) as month,
	    DATE(tb_user.registration_dtime) as date,
	    tb_user.registration_dtime,
	    tb_site.url as site_id,
	    tb_site.id as id
	FROM auth_user AS tb_user
	LEFT JOIN auth_group AS tb_group ON tb_user.group_id = tb_group.id
	left join site_structure_site as tb_site on tb_site.id=tb_user.site_id";
if (is_module('Billing') == 1) {
	$query .= " left join tmp_log as tb_log on tb_log.user_id = tb_user.id";
}

$query .= "	
	where 
			tb_user.registration_dtime != '0000-00-00'
			".where_clause('tb_site.id', $site_id)." 
	GROUP BY date_format(tb_user.registration_dtime, '%Y-%m')
	ORDER BY tb_user.registration_dtime desc
";
$caption = ($site_id == 0) ? 'Статистика регистрации на всех сайтах системы ': 'Количество регистраций на сайте '.$site['url'].'';

$cmsTable = new cmsShowView($DB, $query, 31);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('class_field', 'class');
$cmsTable->setParam('title', $caption);
$cmsTable->addColumn('year_month', '20%', 'left', 'Месяц');
$cmsTable->addColumn('sumname', '20%', 'left', 'Количество регистраций ');
if (is_module('Billing') == 1) {
	$cmsTable->addColumn('sumpayed', '20%', 'left', 'Количество регистраций с оплатой');
	$cmsTable->addColumn('reject', '20%', 'left', 'Отказ');
}

$TmplContent->set('cms_table', $cmsTable->display());

if(isset($result) && !empty($result)) {
	$Chart = new FusionChart(array('labelDisplay'=>'ROTATE', 'slantLabels'=>'1'));
	$Chart->setCaption($caption);
	$Chart->setLabels(array_keys(array_reverse($result)));
	$Chart->addDataSet($result, array('showValues'=>0, 'seriesName'=>'Регистрации', 'color'=>'d7e3f6'));
	if (is_module('Billing') == 1) {
		$TmplContent->set('type', 'MS');
		$Chart->addDataSet($result2, array('showValues'=>0, 'seriesName'=>'Регистрации с оплачеными счетами', 'color'=>'e51313'));
	}
	$TmplContent->set('chart_xml', $Chart->renderXml(CMS_CHARSET));
} 

?>