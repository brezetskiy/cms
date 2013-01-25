<?php
/**
 * Статистика по поиску
 * @package Pilot
 * @subpackage Search
 * @author Markovskiy Dima<dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$month = globalVar($_GET['month'], (int)date('m'));
$year = globalVar($_GET['year'], date('Y'));
$site_id = globalVar($_GET['site_id'], 0);

$time = @mktime(0, 0, 0, $month, 1, $year);
if ($time === false) {
	$time = time();
}
$TmplContent->set('month', (int)date('m', $time));
$TmplContent->set('year', date('Y', $time));

/**
 * Сайты
 */
$query = "
	select id, url from site_structure_site
	order by priority
";
$sites = $DB->fetch_column($query);
$TmplContent->set('sites', $sites);
$TmplContent->set('site_id', $site_id);

$monthes = array();
for ($i=1; $i<=12; $i++) {
	$monthes[$i] = constant('LANGUAGE_MONTH_NOM_'.$i);
}
$TmplContent->set('monthes', $monthes);

$query = "select count(*) from search_content where 1 ".where_clause('site_id', $site_id);
$TmplContent->set('index_count', $DB->result($query));

$query = "select count(*) from search_log where 1 ".where_clause('site_id', $site_id);
$TmplContent->set('number_request', $DB->result($query));

$query = "
	select
		keyword,
		count(*) as count	
	from search_log
	where amount <> 0 and date_format(tstamp, '%Y-%c') = '".$year."-".$month."' ".where_clause('site_id', $site_id)." 
	group by keyword
	order by count desc	
	limit 20	
";
$data = $DB->query($query);
$TmplContent->set('countsp', $DB->rows);
$TmplContent->iterateArray('/searchphraze/', null, $data);

$query = "
	select
	    keyword,
	    count(*) as count
	from search_log
	where amount = 0 and date_format(tstamp, '%Y-%c') = '".$year."-".$month."' ".where_clause('site_id', $site_id)."
	group by 
	    keyword
   	order by 
		count desc
 
";
$data = $DB->query($query);
$TmplContent->set('countzp', $DB->rows);
$TmplContent->iterateArray('/nullresult/', null, $data);

$query = "
	select
		keyword,
		count(*) as count
	from search_log
	where date_format(tstamp, '%Y-%c') = '".$year."-".$month."' ".where_clause('site_id', $site_id)."
	group by 
		keyword	
	order by 
		count desc
	limit 200	
";
$data = $DB->query($query);
$TmplContent->set('countp', $DB->rows);
$TmplContent->iterateArray('lastresult', null, $data)

?>