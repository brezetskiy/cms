<?php
/** 
 * Изменение порядка сортировки пунктов меню в структуре сайта 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$group_id = globalVar($_REQUEST['group_id'], -1);
$language_current = globalEnum($_REQUEST['language'], preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY));
$order = globalEnum($_REQUEST['order'], array('name', 'uniq_name'));
$direction = globalEnum($_REQUEST['direction'], array('asc', 'desc'));

$query = "
	select id
	from shop_group
	where group_id='$group_id'
	order by `$order` $direction
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) {
	$query = "update shop_group set priority=$index+1 where id='$row[id]'"; 
	$DB->update($query);
}

?>