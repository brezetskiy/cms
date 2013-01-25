<?php
/** 
 * Расширенный вывод 404 ошибок
 * @package Pilot 
 * @subpackage Site 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$url = globalVar($_GET['url'], "");
$url = str_replace("[AND]", "&", $url);

function cms_prefilter($row) {
	$row['referer'] = (strlen($row['referer']) > 100) ?
		'<a href="'.$row['referer'].'">'.substr($row['referer'], 0, 100).'...</a>':
		'<a href="'.$row['referer'].'">'.$row['referer'].'</a>';
	return $row;
}

$query = "
	SELECT
		id,
		DATE_FORMAT(date, '%d.%m.%Y') as date,
		url,
		ip,
		referer,
		user_agent,
		count
	FROM cms_log_404
	WHERE url='$url'
	ORDER BY count DESC  
"; 
$cmsTable = new cmsShowView($DB, $query); 
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('title', $url);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('excel', false);
$cmsTable->setParam('parent_link', '../?');
$cmsTable->setParam('show_parent_link', true);

$cmsTable->addColumn('date', '5%', 'center');
$cmsTable->addColumn('ip', '5%', 'center');
$cmsTable->addColumn('referer', '40%', 'left');
$cmsTable->addColumn('user_agent', '30%', 'left');
$cmsTable->addColumn('count', '10%', 'right', 'Кол-во');
echo $cmsTable->display();
unset($cmsTable);
?>

