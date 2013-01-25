<?php
/**
 * Формирование Sitemap для новостей
 * @package Pilot
 * @subpackage News
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron 0 6 * * *
 */

define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
chdir(dirname(__FILE__));
require_once('../../config.inc.php');

$DB = DB::factory('default');

define('NEWSSITEMAP_ROOT', SITE_ROOT.'static/news-sitemap/');
if (!is_dir(NEWSSITEMAP_ROOT)) {
	mkdir(NEWSSITEMAP_ROOT, 0750, true);
}

$query = "
	select tb_site.*
	from site_structure_site as tb_site
	inner join news_type as tb_type on tb_type.site_id = tb_site.id
	group by tb_site.id
";
$sites = $DB->query($query);

$sitemaps = array();

reset($sites);
while (list(,$site) = each($sites)) {
	echo "[i] $site[url] ";
	$query = "
		select
			tb_message.id, 
			tb_message.dtime, 
			tb_message.keywords_ru,
			tb_type.rss_url as type_url
		from news_message as tb_message
		inner join news_type as tb_type on tb_message.type_id = tb_type.id
		where tb_type.site_id = '$site[id]'
	";
	$data = $DB->query($query);
	$Sitemap = new SitemapNews();
	reset($data);
	while (list(,$row) = each($data)) {
		echo '.';
		$Sitemap->addUrl("http://$site[url]".$row['type_url'].$row['id'].'/', $row['dtime'], $row['keywords_ru']);
	}
	
	$Sitemap->build(NEWSSITEMAP_ROOT, "$site[url].xml", "http://$site[url]/static/news-sitemap/", false);
	$sitemaps = array_merge($sitemaps, $Sitemap->getSitemaps());
	echo "\n";
}

/**
 * Удаляем старые файлы sitemap
 */
$listing = Filesystem::getDirContent(NEWSSITEMAP_ROOT, false, false, true);

reset($listing);
while (list(,$row) = each($listing)) {
	if (!in_array($row, $sitemaps)) {
		echo "[i] Remove obsolete $row\n";
		unlink(NEWSSITEMAP_ROOT.$row);
	}
}


?>