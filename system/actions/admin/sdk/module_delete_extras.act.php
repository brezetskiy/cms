<?php
/**
 * Удаление данных, которые не связаны с шаблонами
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$delete = globalVar($_POST['delete'], array());
$tables = globalVar($_POST['tables'], array());


reset($delete);
while (list(,$row) = each($delete)) {
	
	/**
	 * Удаляем директорию с закешированными файлами
	 */
	if ($row == 'cache') {
		$files = Filesystem::getDirContent(CACHE_ROOT, true, true, true);
		reset($files); 
		while (list(,$row) = each($files)) { 
			$file = basename($row);
			if (substr($file, 0, 8) == 'language') {
				continue;
			}
			if (in_array($file, array('config.inc.php', '.htaccess'))) {
				continue;
			}
			if (is_dir($row)) {
				Filesystem::delete($row);
			} else {
				unlink($row);
			}
		}
	}
	
	/**
	 * Удаляем директорию CVS_ROOT
	 */
	if ($row == 'cvs') {
		Filesystem::delete(CVS_ROOT);
		mkdir(CVS_ROOT, 0777, true);
		$query = "truncate table cvs_log";
		$DB->delete($query);
	}
	
	/**
	 * Удаляем директорию LOGS_ROOT
	 */
	if ($row == 'logs') {
		Filesystem::delete(LOGS_ROOT);
		mkdir(LOGS_ROOT, 0777, true);
	}
	
	/**
	 * Удаляем директорию TMP_ROOT
	 */
	if ($row == 'tmp') {
		Filesystem::delete(TMP_ROOT);
		mkdir(TMP_ROOT, 0777, true);
	}
	
	/**
	 * Удаляем статистику авторизации в системе и напоминания паролей
	 */
	if ($row == 'auth') {
		$query = "
			truncate table auth_online;
			truncate table auth_log;
		";
		$DB->multi($query);
	}
	
	/**
	 * Удаляем очередь почтовых сообщений
	 */
	if ($row == 'mailq') {
		$DB->query("truncate cms_mail_queue");
	}
	
	if ($row == 'static') {
		Filesystem::delete("static/");
	}
	
	if ($row == 'import') {
		Filesystem::delete("system/import/");
	}
}

reset($tables);
while (list(,$row) = each($tables)) {
	$row = preg_split("/\./", $row, -1, PREG_SPLIT_NO_EMPTY);
	cmsTable::delete('default', $row[0], $row[1]);
}
 



?>