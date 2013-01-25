<?php
/** 
 * ”даление модулей из системы 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$modules = globalVar($_POST['modules'], array());

// ”дал€ем модули цклически, так как с первого раза они не всегда удал€ютс€ из-за наличи€
// зависимостей от других модулей
do {
	$start_count = count($modules);
	reset($modules);
	while (list($index,$id) = each($modules)) {
		$Module = new Module($id);
		if ($Module->delete()) {
			unset($modules[$index]);
		}
	}
} while(!empty($modules) && $start_count!=count($modules));


// ”дал€ем пустые директории
Filesystem::deleteEmptyDirs(SITE_ROOT.'img/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'css/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'js/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'uploads/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'content/');
Filesystem::deleteEmptyDirs(TRIGGERS_ROOT);
Filesystem::deleteEmptyDirs(SITE_ROOT.'templates/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'system/');

?>