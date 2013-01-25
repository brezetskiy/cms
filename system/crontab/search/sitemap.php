<?php 
/**
 * ��������� ����� ����� �� ��������� ������ �� ���������� �������
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron 43 8 * * *
 */

/**
 * ���������� ���������
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');

// ������������� ���������� ������� ����������
chdir(dirname(__FILE__));

/**
* ���������������� ����
*/
require_once('../../config.inc.php');

$DB = DB::factory('default');

// ���������� ������������ ������� �������
Shell::collision_catcher();

// ��������� ����� �����
$sitemaps = array();

$query = "select * from site_structure_site";
$sites = $DB->query($query);
reset($sites);
while (list(,$site) = each($sites)) {
	echo "[i] $site[url] ";
	$Sitemap = new Sitemap();
	
	
	// ��������� ����� ����� �� ��������� ��������� �������
	$data = $DB->query("
		select 
			url,
			max(change_dtime) as change_dtime,
			change_frequency,
			page_priority
		from search_content
		where site_id='$site[id]'
		group by url
	");
	reset($data);
	while (list(,$row) = each($data)) {
		echo ".";
		$Sitemap->addUrl('http://'.$site['url'].$row['url'], $row['change_dtime'], $row['change_frequency'], $row['page_priority']);
	}
	
	
	// ��������� ����� ����� ��� ����������� ���������
	if (is_module('Shop')) {
		$site_groups = $DB->fetch_column("select id from shop_group");
		$groups = $DB->fetch_column("select id from shop_group_relation where parent in (0".implode(",", $site_groups).") group by id");
		$max_dtime = $DB->result("select max(tstamp) from shop_product where group_id in (0".implode(",", $groups).")");
		
		// ���������
		$data = $DB->query("
			select
				url,
				'$max_dtime' as change_dtime,
				'weekly' as change_frequency,
				0.7 as page_priority
			from shop_group
			where id in (0".implode(",", $groups).")
		");
		reset($data);
		while (list(,$row) = each($data)) {
			echo ".";
			$Sitemap->addUrl('http://'.$site['url'].'/'.substr($row['url'], strpos($row['url'], '/') + 1).'.htm', $row['change_dtime'], $row['change_frequency'], $row['page_priority']);
		}
		
		// ��������
		$data = $DB->query("
			select
				_url as url,
				tstamp as change_dtime,
				'weekly' as change_frequency,
				0.9 as page_priority
			from shop_product
			where group_id in (0".implode(",", $groups).")
		");
		reset($data);
		while (list(,$row) = each($data)) {
			echo ".";
			$Sitemap->addUrl('http://'.$site['url'].'/'.$row['url'].'.html', $row['change_dtime'], $row['change_frequency'], $row['page_priority']);
		}
	}

	
	$Sitemap->build(SITE_ROOT.'static/sitemap/', "$site[url].xml", "http://$site[url]/static/sitemap/", false);
	$sitemaps = array_merge($sitemaps, $Sitemap->getSitemaps());
	echo "\n";
}

/**
 * ������� ������ ����� sitemap
 */
$listing = Filesystem::getDirContent(SITE_ROOT.'static/sitemap/', false, false, true);
reset($listing);
while (list(,$row) = each($listing)) {
	if (!in_array($row, $sitemaps)) {
		echo "[i] Remove obsolete $row\n";
		unlink(SITE_ROOT.'static/sitemap/'.$row);
	}
}


?>