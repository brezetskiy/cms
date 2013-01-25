<?php
/**
 * Обновление данных о сайтах
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

/**
 * Добавить сайты, которых еще нет
 */
$query = "
	select * from site_structure
	where structure_id = 0
";
$sites = $DB->query($query);

reset($sites);
while (list(,$row) = each($sites)) {
	
	$query = "
		select * from site_structure_site
		where id = '$row[id]'
	";
	$DB->query_row($query);
	
	if ($DB->rows > 0) {
		Action::setLog("Updated $row[uniq_name]");
		Structure::updateSite($row['id'], $row['uniq_name']);
	} else {
		Action::setLog("Created $row[uniq_name]");
		Structure::createSite($row['id'], $row['uniq_name'], $row['template_id']);
	}
	
}

/**
 * Удалить сайты, которых нет в структуре сайта
 */
$query = "
	select * from site_structure_site
	where id not in (
		select id from site_structure
		where structure_id = 0
	)
";
$drop_sites = $DB->query($query);

reset($drop_sites);
while (list(,$row) = each($drop_sites)) {
	Action::setLog("Deleted $row[url]");
	Structure::deleteSite($row['id']);
}