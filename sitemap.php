<?php
/**
 * Обработчик sitemaps для всех сайтов системы
 * @package Pilot
 * @subpackage Site
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

define('CMS_INTERFACE', 'SITE');
require_once('system/config.inc.php');
$DB = DB::factory('default');

// Получаем информацию о текущем сайте
$Site = new Site(HTTP_URL, 'site_structure');

$site_sitemap_file = SITE_ROOT."static/sitemap/".$Site->filename.".xml";

if (file_exists($site_sitemap_file)) {
	header('Content-Type: text/xml; charset=utf-8');
	header('Content-Length: '.filesize($site_sitemap_file));
	
	$f = fopen($site_sitemap_file, 'r');
	fpassthru($f);
	exit;
}

header('HTTP/1.0 404 Not found');
exit;