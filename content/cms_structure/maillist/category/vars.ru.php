<?php
/**
 * Перечень переменных, которые можно использовать в рассылке
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$category_id = globalVar($_REQUEST['category_id'], 0);

$query = "
	select name_".LANGUAGE_SITE_DEFAULT." as name, sql_query
	from maillist_category
	where id='$category_id'
";
$info = $DB->query_row($query);
$TmplContent->set('category', $info['name']);
if (!empty($info['sql_query'])) {
	preg_match("/select(.+)from/ismU", $info['sql_query'], $matches);
	$fields = preg_split("/,/", $matches[1], -1, PREG_SPLIT_NO_EMPTY);
	reset($fields);
	while (list(,$field) = each($fields)) {
		preg_match("/[\s\n\r\t\.]([a-z0-9_]+)$/", trim($field), $matches);
		$TmplContent->iterate('/row/', null, array('name' => $matches[1]));
	}
} else {
	$query = "select uniq_name, name from auth_user_group_param where data_type!='devider'";
	$info = $DB->fetch_column($query);
	reset($info);
	while (list($key, $val) = each($info)) {
		$TmplContent->iterate('/row/', null, array('name' => "$key ($val)"));
	}
}

?>