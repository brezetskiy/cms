<?php
/** 
 * Вывод ошибок файла
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

$filename = globalVar($_GET['file'], "");

function cms_prefilter($row) {	
	$row['del']  = "<a href='/action/admin/sdk/delgrouplogerrors/?id_list=$row[id_list]&_return_path=".CURRENT_URL_LINK."'><img src='/design/cms/img/icons/del.gif'  style='border:0px;'></a>";
	$row['message'] = "<a href='$row[url]'>$row[url]</a><br><br>$row[type]: ".nl2br($row['message'])."<br><br><font color=gray>".nl2br($row['process'])."<br>$row[date]</font>";
	return $row;
}

$query = "set session group_concat_max_len=5000";
$DB->query($query);  

$query = "
	SELECT
		id,
		GROUP_CONCAT(id) as id_list,
		date_format(date, '".LANGUAGE_DATETIME_SQL."') as date,
		url,
		type,
		refferer,
		user_agent,
		message,
		process,
		sum(count) as count
	FROM cms_log_error
	WHERE file='$filename'
	GROUP BY line, refferer, user_agent, message
	ORDER BY line ASC  
"; 
$cmsTable = new cmsShowView($DB, $query); 
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('excel', false);
$cmsTable->setParam('parent_link', '../?');
$cmsTable->filterSkipField('cms_log_error','author');
$cmsTable->filterSkipField('cms_log_error','file');

$cmsTable->setParam('show_parent_link', true);
$cmsTable->addColumn('message', '50%');
$cmsTable->addColumn('refferer', '20%');
$cmsTable->addColumn('user_agent', '20%');
$cmsTable->addColumn('count', '5%');
$cmsTable->addColumn('del', '5%', 'center', 'Удл.');
echo $cmsTable->display();
?>

