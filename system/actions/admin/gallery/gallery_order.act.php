<?php
/** 
 * Изменение порядка сортировки альбомов в фотогалерее
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 
$group_id = globalVar($_REQUEST['group_id'], 0);
$language_current = globalEnum($_REQUEST['language'], preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY));
$direction = globalEnum($_REQUEST['direction'], array('asc', 'desc'));

$query = "
	select id
	from gallery_group
	where group_id='$group_id'
	order by `name_$language_current` $direction
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) {
	$query = "update gallery_group set priority=$index+1 where id='$row[id]'"; 
	$DB->update($query);
}

?>