<?php
/** 
 * Удаление файлов и папок с контентом, для которых нет записи в БД 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$files = globalVar($_POST['file'], array());
$dirs = globalVar($_POST['dir'], array());

$deleted_files = $deleted_dirs = 0;

reset($files); 
while (list($table,) = each($files)) { 
	reset($files[$table]);
	while (list(,$row) = each($files[$table])) {
		Filesystem::delete($row);
	}
}

Action::setSuccess("Удалено $deleted_files файлов и $deleted_dirs каталогов");

?>