<?php
/**
 * Удаление новостей при установке системы
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$news = globalVar($_REQUEST['news'], array());

$query = "select distinct id from news_type_relation where parent in (0".implode(",", $news).")";
$type = $DB->fetch_column($query);

do {
	$query = "select id from news_message where type_id in (0,0".implode(",", $type).") limit 100";
	$data = $DB->fetch_column($query);
	
	delete_news($data);
	
} while(!empty($data));

$query = "delete from news_type where id in (0".implode(",", $type).")";
$DB->delete($query);

$query = "delete from news_type_relation where id in (0".implode(",", $type).")";
$DB->delete($query);

function delete_news($data) {
	global $DB;
	
	$query = "delete from news_message where id in (0".implode(",", $data).")";
	$DB->delete($query);
	
	$dirs = Filesystem::getDirContent(SITE_ROOT.'uploads/news_message/', true, true, false);
	reset($dirs);
	while (list(,$dir) = each($dirs)) {
		reset($data);
		while (list(,$row) = each($data)) {
			Filesystem::delete(SITE_ROOT.'uploads/news_message/'.$dir.'/'.Uploads::getIdFileDir($row).'/');
		}
	}
}


?>