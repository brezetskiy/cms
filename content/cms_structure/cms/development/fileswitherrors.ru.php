<?php
/** 
 * Вывод ошибок
 * @package Pilot 
 * @subpackage CMS 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

// Удаляем устаревшие ошибки
$query = "set group_concat_max_len = 100000";
$DB->query($query);

$query = "select file, unix_timestamp(mtime) as tstamp, group_concat(id) as id from cms_log_error group by file, mtime";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	if (!is_file(SITE_ROOT.$row['file'])) {
		continue;
	}
	
	if (filemtime(SITE_ROOT.$row['file']) > $row['tstamp']) {
		$query = "delete from cms_log_error where id in (0$row[id])";
		$DB->delete($query);
	}
}



function cms_prefilter($row) {
	if(file_exists(SITE_ROOT.$row['file'])){
		$row['file_date'] = date("d.m.Y H:i:s", filemtime(SITE_ROOT.$row['file']));
	} else {
		$row['file_date'] = "файл перемещен или удален";
	}
	$row['del']  = "<a href='/action/admin/sdk/delerrorsbyfile/?file=$row[file]&_return_path=".CURRENT_URL_LINK."'><img src='/design/cms/img/icons/del.gif'  style='border:0px;'></a>";
	$row['filelink'] = "<a href='./errorsview/?file=$row[file]'>$row[file]</a>";
	return $row;
}

$query = "
	SELECT id, file, author, count(file) as amount
	FROM cms_log_error
	GROUP BY file
	ORDER BY amount DESC  
";
 
$cmsTable = new cmsShowView($DB, $query, 500);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('excel', false);
$cmsTable->filterSkipField('cms_log_error','url');
$cmsTable->filterSkipField('cms_log_error','ip');
$cmsTable->filterSkipField('cms_log_error','line');
$cmsTable->filterSkipField('cms_log_error','type');
$cmsTable->filterSkipField('cms_log_error','refferer');
$cmsTable->filterSkipField('cms_log_error','user_agent'); 
$cmsTable->filterSkipField('cms_log_error','message');
$cmsTable->filterSkipField('cms_log_error','process');
$cmsTable->filterSkipField('cms_log_error','count');

$cmsTable->addColumn('filelink', '70%', 'left', 'Файл с ошибкой');
$cmsTable->addColumn('author', '15%', 'left');
$cmsTable->addColumn('amount', '10%', 'right', 'Частота ошибок');
$cmsTable->addColumn('del', '10%', 'center', 'Удл.');

echo $cmsTable->display();
unset($cmsTable);


?>