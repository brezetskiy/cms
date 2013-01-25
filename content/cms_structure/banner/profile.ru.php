<?php
/** 
 * Профайлы баннеров 
 * @package Pilot 
 * @subpackage Banner 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$query = "
	SELECT
		id,
		name,
		DATE_FORMAT(date_from, '".LANGUAGE_DATE_SQL."') AS date_from,
		DATE_FORMAT(date_to, '".LANGUAGE_DATE_SQL."') AS date_to,
		show_hours,
		(SELECT COUNT(*) FROM banner_banner WHERE profile_id = banner_profile.id) AS banner_count
	FROM banner_profile
	ORDER BY name
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('name', '40%');
$cmsTable->addColumn('date_from', '15%');
$cmsTable->addColumn('date_to', '15%');
$cmsTable->addColumn('show_hours', '15%');
$cmsTable->addColumn('banner_count', '5%', 'center', 'Баннеры');
echo $cmsTable->display();
unset($cmsTable);
