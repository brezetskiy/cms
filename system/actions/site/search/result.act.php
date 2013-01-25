<?php
/**
 * ����� �� �����
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
//x($_REQUEST);
$text = globalVar($_REQUEST['text'], '');
$site_id = globalVar($_REQUEST['site_id'], 0);
$table = globalVar($_REQUEST['table'], '');
$order = globalEnum($_REQUEST['order'], array('rel', 'date'));
$fields = array();

if (!empty($table)) {
	$query = "select field_id from cms_field_static where db_alias='default' and table_name='$table'";
	$fields = $DB->fetch_column($query);
}

$TmplContent = new Template(SITE_ROOT.'templates/search/result');
$query = "
	select 
		url,
		title,
		change_dtime as date,
		date_format(change_dtime, '".LANGUAGE_DATE_SQL." %H:%i') as dtime,
		left(content, 200) as content,
		match(title, content) against ('$text') as rel
	from search_content
	where   
		match(title, content) against ('$text')
		and language='".LANGUAGE_CURRENT."'
		".where_clause('site_id', $site_id)."
		".where_clause('field_id', $fields)."
	order by $order desc
	limit 100
";
//x($query);
$data = $DB->query($query);
$TmplContent->set('rows', $DB->rows);

Search::addToLog($text, $DB->rows, $site_id);

$TmplContent->set('text', $text);
$TmplContent->set('order', $order);
reset($data);
while (list($index, $row) = each($data)) {
	if (empty($row['title'])) {
		$row['title'] = '---';
	}
	$row['index'] = $index+1;
	$TmplContent->iterate('/search_result/', null, $row);
}

$_RESULT['result'] = $TmplContent->display(); 
exit;
?>