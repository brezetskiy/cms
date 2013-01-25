<?php
/** 
 * Отслеживание неправильного размещения файлов в системе 
 * @package Pilot 
 * @subpackage SDK 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008 
 */ 
 
$query = "select lower(name) from cms_module";
$modules = $DB->fetch_column($query);

$query = "show tables";
$tables = $DB->fetch_column($query);

function check_folder($dir, $name, $check_array) {
	global $modules, $TmplContent;
	$listing = Filesystem::getDirContent($dir, false, true, true);
	
	reset($listing); 
	while (list(,$row) = each($listing)) { 
		if ($row != '.htaccess' && (is_file($dir.$row) || !in_array(substr($row, 0, -1), $check_array))) {
			if (empty($tmpl_folder)) {
				$tmpl_folder = $TmplContent->iterate('/folder/', null, array('name'=>$name));
			}
			
			$TmplContent->iterate('/folder/item/', $tmpl_folder, array('name'=>$row));
		}
	}
}

$check_tables = array(
	'CONTENT_ROOT' => CONTENT_ROOT,
	'UPLOADS_ROOT' => UPLOADS_ROOT
);

reset($check_tables); 
while (list($name,$row) = each($check_tables)) { 
	check_folder($row, $name, $tables); 
}

$check_modules = array(
	'Tools' => SITE_ROOT.'tools/',
	'System Import' => SITE_ROOT.'system/import/',
	'CSS' => SITE_ROOT.'css/',
	'JavaScript' => SITE_ROOT.'js/',
	'Images' => SITE_ROOT.'img/',
	'Templates' => TEMPLATE_ROOT.'module/'
);

reset($check_modules); 
while (list($name,$row) = each($check_modules)) { 
	check_folder($row, $name, $modules); 
}

?>