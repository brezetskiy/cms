<?php
/** 
 * Вывод 404 ошибок
 * @package Pilot 
 * @subpackage Site 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 


function cms_prefilter($row) {
	$row['del']  = "<a href='/action/admin/sdk/del404/?url=".urlencode($row['url'])."&_return_path=".CURRENT_URL_LINK."'><img src='/design/cms/img/icons/del.gif'  style='border:0px;'></a>";
	$link = $row['url'];
	$row['url'] = "<a href='$link'>".substr($link, 0, 50)."</a>"; 
	
	if($row['url_counter'] > 1){
		$row['url'] = $row['url']."&nbsp; <div style=\"float:right;\" >[ <a href='./extendedview/?url=".urlencode($link)."'>детальнее</a> ]</div>";    
	}
	
	if (strlen($row['referer']) > 50) {
		$row['referer'] = '<a href="'.$row['referer'].'">'.substr($row['referer'], 0, 50).'...</a>';
	}
	return $row;
}

$query = "
	SELECT 
		`id`, 
		`url`, 
		date,
		ip,
		referer,
		user_agent,
		count(`id`) as url_counter, 
		sum(`count`) as `total_count`
	FROM `cms_log_404`
	GROUP BY `url`
	ORDER BY `total_count` DESC  
";
 
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('excel', false);

$cmsTable->filterSkipField('cms_log_404','date');
$cmsTable->filterSkipField('cms_log_404','ip');
$cmsTable->filterSkipField('cms_log_404','referer');
$cmsTable->filterSkipField('cms_log_404','user_agent');

$cmsTable->addColumn('url', '40%', 'left');
$cmsTable->addColumn('referer', '20%', 'left');
$cmsTable->addColumn('user_agent', '30%', 'left');
$cmsTable->addColumn('total_count', '10%', 'right', 'Кол-во');
$cmsTable->addColumn('del', '10%', 'center', 'Удл.');

echo $cmsTable->display();
unset($cmsTable);


?>