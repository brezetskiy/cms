<?php

/**
 * Создаёт индекс файлов, по которым необходимо произвести поиск
 * 
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$extension = globalVar($_REQUEST['extension'], '');
$files = globalVar($_REQUEST['files'], array());

$skip = array(
	CACHE_ROOT, 
	SITE_ROOT.'css/',
	CVS_ROOT,
	SITE_ROOT.'i/',
	SITE_ROOT.'img/',
	SITE_ROOT.'static/',
	TMP_ROOT,
	UPLOADS_ROOT
);


$_SESSION['search_index'] = array();
$_SESSION['search_result'] = array();


if (empty($files)) {
	$dirs = Filesystem::getDirContent(SITE_ROOT, true, true, true);
	
	reset($dirs);
	while (list(,$row) = each($dirs)) {
		if (is_file($row)) {
			array_push($_SESSION['search_index'], $row);
			
		} elseif (!in_array($row, $skip)) { 
			$files = Filesystem::getAllSubdirsContent($row, true, true);
			$_SESSION['search_index'] = array_merge($_SESSION['search_index'], $files);
		}
	}
	
	
	reset($_SESSION['search_index']);
	while (list($index,$row) = each($_SESSION['search_index'])) {
		if (!preg_match($extension, $row) || substr($row, 0, strlen(LOGS_ROOT)) == LOGS_ROOT) {
			unset($_SESSION['search_index'][$index]);
		}
	}
	
} else {
	$_SESSION['search_index'] = $files;
}

$_RESULT['index_size'] = 'Поиск будет производится по '.count($_SESSION['search_index']).' файлам';
$_RESULT['javascript'] = "search('".count($_SESSION['search_index'])." файлам');";
exit;

?>