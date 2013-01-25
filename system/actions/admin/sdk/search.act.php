<?php

$pattern = globalVar($_REQUEST['pattern'], '');
$ignore_comments = globalVar($_REQUEST['ignore_comments'], 0);
$counter = 0;

reset($_SESSION['search_index']);
while (list($index, $file) = each($_SESSION['search_index'])) {
	$counter++;
	
	if (!is_file($file) || !is_readable($file)) continue;
	$content = ($ignore_comments && substr($file, strlen($file) - 4) == '.php') ? php_strip_whitespace($file) : file_get_contents($file);
	
	if ($matches_count = preg_match_all($pattern, $content, $matches)) {
		$_SESSION['search_result'][] = array(
			'file' => $file,
			'matches_count' => $matches_count
		);
	}
	
	unset($_SESSION['search_index'][$index]);
	
	if ($counter > 500) {
		$current_index = addslashes(dirname(substr(reset($_SESSION['search_index']), strlen(SITE_ROOT))));
		$_RESULT['javascript'] = "search('$current_index');";
		exit;
	}
}

$TmplContent = new Template(SITE_ROOT.'templates/sdk/search');
$TmplContent->set('files_found', count($_SESSION['search_result']));
$TmplContent->setGlobal('pattern', urlencode($pattern));


// Выводим результат
reset($_SESSION['search_result']);
while (list(,$row) = each($_SESSION['search_result'])) {
	$row['filename'] = substr($row['file'], strlen(SITE_ROOT));
	$row['file_url'] = urlencode($row['filename']);
	$TmplContent->iterate('/result/', null, $row);
}

$_RESULT['result'] = $TmplContent->display();
exit;

?>