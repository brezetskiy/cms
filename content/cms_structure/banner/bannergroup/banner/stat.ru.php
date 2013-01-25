<?php
/**
* —татистика перехода по баннеру по дн€м
*
* @package Pilot
* @subpackage Banner
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$banner_id = globalVar($_GET['banner_id'], 0);

function cms_filter($row) {
	
	$row['date'] = "<a href='./Raw/?banner_id=$row[banner_id]&date=$row[date_raw]&_return_path='".CURRENT_URL_LINK."'>$row[date]</a>";
	
	return $row;
}

$query = "
	SELECT 
		banner_id,
		DATE_FORMAT(`date`, '".LANGUAGE_DATE_SQL."') AS `date`,
		view,
		click,
		date AS date_raw
	FROM banner_stat
	WHERE banner_id = '$banner_id'
	ORDER BY banner_stat.date DESC
";

$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('date', '30%', 'center');
$cmsTable->addColumn('view', '30%', 'center');
$cmsTable->addColumn('click', '30%', 'center');
echo $cmsTable->display();
unset($cmsTable);

?>
<div class="context_help">
<b>’ост</b> - уникальный пользователь в течении суток.<br>
<b>’ит</b> - уникальный просмотр в течении 1 минуты.<br>
<b>ѕросмотров</b> - количество загрузок баннера.<br>
<b>ѕереходов</b> - количество щелчков по баннеру. 
</div>