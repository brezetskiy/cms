<?php
/**
 * Скритпт формирует контент нового месяца 
 * для календаря ноовостей
 * 
 * @package Pilot
 * @subpackage News
 * @author Markovskiy Dima <dima@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2010
 */
$month = globalVar($_REQUEST['month'], date('n')); 
$year = globalVar($_REQUEST['year'], date('Y'));
$type = globalVar($_REQUEST['type'], '');


/**
 * Получаем список дат в 
 * которые публиковались дати
 */
$query = "
	select
		dayofmonth(tb_message.date),
        concat('/News/?type=$type&show_date=', tb_message.date)
	from news_message as tb_message
	inner join news_type as tb_type on tb_type.id = tb_message.type_id
	where 
		tb_message.date >='".date('Y-m-d', mktime(0,0,0,$month, 1, $year))."' and 
		tb_message.date < '".date('Y-m-d', mktime(0,0,0,$month+1,1,$year))."'
		".where_clause("tb_type.uniq_name", $type)."
	group by dayofmonth(tb_message.date)
";
$data = $DB->fetch_column($query);
$_RESULT['calendar'] = TemplateUDF::calendar($param = array('links' => $data, 'show_date' => 0, 'show_month' => mktime(0,0,0,$month,1,$year), 'type' => $type));

?>