<?php
/**
 * Удаление сайтов при установке системы
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$site = globalVar($_REQUEST['site'], array());
$languages = preg_split("/,/", LANGUAGE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);

// Определяем файлы, которые необходимо удалить
$query = "select url from site_structure where id in (0".implode(",", $site).")";
$data = $DB->fetch_column($query);
reset($languages);
while (list(,$language) = each($languages)) {
	reset($data);
	while (list(,$row) = each($data)) {
		// Удаляем контент
		Filesystem::delete(SITE_ROOT.'content/site_structure/'.$row.'.'.$language.'.php');
		Filesystem::delete(SITE_ROOT.'content/site_structure/'.$row.'.'.$language.'.tmpl');
		Filesystem::delete(SITE_ROOT.'content/site_structure/'.$row.'/');
	}
}

reset($data);
while (list(,$url) = each($data)) {
	// Удаляем картинки
	$query = "select id from site_structure where url like '$url/%' or url='$url'";
	$structure = $DB->fetch_column($query);
	reset($structure);
	while (list(,$id) = each($structure)) {
		Filesystem::delete(SITE_ROOT.'uploads/content_ru/'.Uploads::getIdFileDir($id).'/');
	}
	
	$query = "delete from site_structure where url like '$url/%' or url='$url'";
	$DB->delete($query);
	
	$query = "truncate table site_structure_relation";
	$DB->delete($query);
}

$query = "delete from site_structure_site where id in (0".implode(",", $site).")";
$DB->delete($query);

$query = "delete from site_structure_site_alias where site_id in (0".implode(",", $site).")";
$DB->delete($query);

$query = "delete from site_structure_site_template where site_id in (0".implode(",", $site).")";
$DB->delete($query);

?>