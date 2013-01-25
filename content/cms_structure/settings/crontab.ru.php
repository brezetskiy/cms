<?php
/**
 * Формирование розписания задач
 * @package Pilot
 * @subpackage CMS
 * @author Markovskiy Dima<dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
	$cron_str = '';

$cron_time = array();
$cron_time_list = $DB->query("
	SELECT 
		url, 
		DATE_FORMAT(start_dtime, '%d.%m.%Y %H:%i:%s') as dtime,
		DATE_FORMAT(end_dtime, '%d.%m.%Y %H:%i:%s') as end_dtime,
		TIMESTAMPDIFF(SECOND, start_dtime, end_dtime) as time,
		case
			when status = 'failed' then '<span style=\'color:red;font-size:10px;\'>неудачно</span>'
			when status = 'blocked' then '<span style=\'color:#777;font-size:10px;\'>заблокирован</span>' 
			else '<span style=\'color:green;font-size:10px;\'>успешно</span>'
		end as status
	FROM cms_crontab  
");
reset($cron_time_list);
while (list(,$row) = each($cron_time_list)) {
	$cron_time[$row['url']]['dtime']  = $row['dtime'];
	$cron_time[$row['url']]['time']   = $row['time'];
	$cron_time[$row['url']]['status'] = $row['status'];
}



$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'system/crontab/', true);
reset($files);
while (list(, $file) = each($files)) {
	if (substr($file, -4) != '.php') {
		continue;
	}
	$info = array(
		'path' => Uploads::getURL($file),
		'description' => '', 
		'package' => '',
		'subpackage' => '',
		'author' => '',
		'copyright' => '',
		'crontab' => '' 
	);
	$subpack = 1;
	$content = file_get_contents($file);
	preg_match('/^<\?php(.+)\*\//ismU', $content, $matches);
	if (empty($matches[1])) {
		continue;
	}
	
	preg_match('/(?:@subpackage)\s+(.+)$/imsU', $matches[1], $subpackage);
	$path = trim(substr($info['path'], strlen('/system/crontab/'), strpos($info['path'], '/', strlen('/system/crontab/')) - strlen('/system/crontab/')));
	if (empty($subpackage[1]) || (strcmp(strtolower($path), strtolower(trim($subpackage[1]))) <> 0)) {
		$subpack = 0;
		preg_match('/^\/\*\*(.+)@/imsU', $matches[1], $desc);
		$name = "#".trim(preg_replace(array('/\*/', '/\n+/', '/\s+/'), array('', '', ' '), $desc[1]))."<br />";
		$name .= ((empty($subpackage[1]))?"' '":$subpackage[1])." != ".$info['path']." /usr/local/bin/php&nbsp;&nbsp;".$file."<br /><br />";
		$TmplContent->iterate('/bedsubpack/', null, array('name' => $name));
	}
				
	preg_match('/(?:@crontab|@cron)\s+(.+)$/imsU', $matches[1], $cron);
	if (!empty($cron[1])) {
		$info['cron'] = preg_replace('/\~/', '*', $cron[1]);
		$list = preg_split("/\s+/", trim($info['cron']), -1, PREG_SPLIT_NO_EMPTY);
		if ($list[0] == 'none') {
			// скрипт не выполняется по расписанию
			continue;
		} elseif (count($list) != 5) {
			// неправильное кол-во параметров
			$TmplContent->iterate('/bad_param/', null, array('name' => $name));
			continue;
		}
		preg_match('/^\/\*\*(.+)@/imsU', $matches[1], $desc);
		if (!empty($desc[1]) && $subpack) {
			$name = "#".trim(preg_replace(array('/\*/', '/\n+/', '/\s+/'), array('', '', ' '), $desc[1]))."<br />";
			$name .= $info['cron']." /usr/local/bin/php&nbsp;&nbsp;".$file;
			$cron_str .= $info['cron']." /usr/local/bin/php ".$file.'|<br>';
			$file_cutted = str_replace(SITE_ROOT, '', $file);
			
			$dtime 	= (!empty($cron_time[$file_cutted])) ? $cron_time[$file_cutted]['dtime'] : '';
			$time  	= (!empty($cron_time[$file_cutted])) ? $cron_time[$file_cutted]['time'] : '';
			$status = (!empty($cron_time[$file_cutted])) ? $cron_time[$file_cutted]['status'] : '';
			$TmplContent->iterate('/goodscript/', null, array('name' => $name, 'dtime' => $dtime, 'time' => $time, 'status' => $status));
		}
		
	} elseif (IS_DEVELOPER && preg_match('/^\/\*\*(.+)@/imsU', $matches[1], $desc)) {
			$name = "#".trim(preg_replace(array('/\*/', '/\n+/', '/\s+/'), array('', '', ' '), $desc[1]))."<br />";
			$name .= substr($file, strlen(SITE_ROOT))."<br /><br />";
			$TmplContent->iterate('/bedscript/', null, array('name' => $name));
	}
}
$TmplContent->setGlobal('cron_str',$cron_str);
?>