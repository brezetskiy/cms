<?php
/** 
 * RSS
 * @package Pilot 
 * @subpackage News 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

/**
 * Определяем интерфейс для поддержки интернационализации
 * @ignore 
 */
define('CMS_INTERFACE', 'SITE');

/**
 * Конфигурационный файл
 */
require_once('../../system/config.inc.php');

$DB = DB::factory('default');
$category = array('main');


$category_separator = '#@#';  

/**
 * Список новостей
 */
$query = "
	select
		tb_message.id, 
		tb_message.headline_".LANGUAGE_CURRENT." AS title,
		tb_message.announcement_".LANGUAGE_CURRENT." AS description,
		tb_message.content_".LANGUAGE_CURRENT." AS content,
		UNIX_TIMESTAMP(tb_message.date) AS date_tstamp,
		tb_message.image,
		tb_message.url,
		tb_type.name_".LANGUAGE_CURRENT." as category
	from news_message as tb_message
	inner join news_type as tb_type on tb_type.id=tb_message.type_id
	where tb_message.active=1 ".where_clause("tb_type.uniq_name", $category)."
	order by tb_message.date desc, id desc
	limit 0, ".NEWS_RSS_PAGE_COUNT."
";
$news = $DB->query($query);

$RssExport = new RssExport();

$channel_image = SITE_ROOT.NEWS_RSS_CHANNEL_IMAGE; 
if (file_exists($channel_image) && is_file($channel_image) && is_readable($channel_image)) {
	$channel_image = CMS_URL.NEWS_RSS_CHANNEL_IMAGE;
} else {
	$channel_image = null;
}
$RssExport->setChannelInfo("Новости ".CMS_HOST, '', CMS_URL, time(), null, LANGUAGE_CURRENT, $channel_image, "Новости ".CMS_HOST, CMS_URL);

reset($news); 
while (list(,$row) = each($news)) { 
	
	/**
	 * Оставляем только первую категорию
	 */
	$pos = strpos($row['category'], $category_separator);
	if ($pos !== false) {
		$row['category'] = substr($row['category'], 0, $pos);
	}
	
	$enclosure = Uploads::getFile('news_message', 'image', $row['id'], $row['image']);
	if (file_exists($enclosure) && is_readable($enclosure)) {
		$enclosure = CMS_URI.Uploads::getURL($enclosure);
	} else {
		$enclosure = null;
	}
	
	if (NEWS_RSS_FULLTEXT) {
		$row['description'] = id2url($row['content']);
	}
	
	$RssExport->addItem($row['title'], $row['description'], CMS_URL."News/$row[url].html", date('r', $row['date_tstamp']), $row['category'], $enclosure);
}

$user_agent = globalVar($_SERVER['HTTP_USER_AGENT'], '');
header('Content-Type: '.$RssExport->getContentTypeFor($user_agent));
echo $RssExport->export();
exit;

?>