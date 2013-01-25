<?php
/**
 * Поиск по исходникам
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$regexp = stripslashes(globalVar($_REQUEST['regexp'], ''));
$extension = stripslashes(globalVar($_REQUEST['extension'], ''));
$ignore_comments = stripslashes(globalVar($_REQUEST['ignore_comments'], 0));
$dir = globalVar($_REQUEST['dir'], '');

if (empty($dir)) {
	$dirs = Filesys
}


function search_in_file($file, $regexp, ) {
	global $search_text, $ignore_comments, $TmplContent, $counter, $total_matches;
	
	// Загружаем файл
	if (is_file($file) && is_readable($file)) {
		$content = ($ignore_comments == 'true' && substr($file, strlen($file) - 4) == '.php') ?
			php_strip_whitespace($file) :
			file_get_contents($file);
	} else {
		// Файл был удалён
		$content = '';
	}
	
	// Ищем текст
	if ($count = preg_match_all($search_text, $content, $matches)) {
		$TmplContent->iterate('/result/', null, array(
			'name' => str_replace(SITE_ROOT, '', $file),
			'real_file' => str_replace(SITE_ROOT, '', $file),
			'file' => urlencode(str_replace(SITE_ROOT, '', $file)),
			'search_text' => urlencode($search_text),
			'count' => $count
		));
		$counter++;
		$total_matches += $count;
	}
}


?>