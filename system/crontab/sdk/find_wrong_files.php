<?php
/**
 * Определяет перечень файлов, которые не относятся ни к одному из модулей
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 * @cron none
 */


/**
 * Определяем интерфейс
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');

// Устанавливаем правильную рабочую директорию
chdir(dirname(__FILE__));

/**
 * Конфигурационный файл
 */
require_once('../../config.inc.php');

$DB = DB::factory('default');

// Список шаблонов дизайна
$query = "select name from site_template_group";
$data = $DB->fetch_column($query);
$foo = $template_files = array();
reset($data);
while (list(,$row) = each($data)) {
	$foo = Module::getTemplateFiles($row);
	$template_files = array_merge($foo, $template_files);
}

// Определяем файлы, которые не относятся ни к одному из модулей
$query = "select * from cms_module";
$data = $DB->query($query);
$module_files = array();
reset($data);
while (list(,$row) = each($data)) {
	$Module = new Module($row['id']);
	$foo = $Module->getAllFiles();
	$module_files = array_merge($module_files, $foo);
}
$module_files = array_merge($module_files, $template_files);
echo "[i] Total module files ".count($module_files)."\n";


$all = Filesystem::getAllSubdirsContent(SITE_ROOT, true);
echo "[i] Total ".count($all)." files\n";

// Удаляем файлы, которые нет необходимости обрабатывать
reset($all);
while (list($index, $file) = each($all)) {
	if (
		substr($file, 0, strlen(CVS_ROOT)) == CVS_ROOT || 
		substr($file, 0, strlen(LOGS_ROOT)) == LOGS_ROOT || 
		substr($file, 0, strlen(CACHE_ROOT)) == CACHE_ROOT || 
		substr($file, 0, strlen(TMP_ROOT)) == TMP_ROOT || 
		substr($file, 0, strlen(SITE_ROOT.'system/pear/')) == SITE_ROOT.'system/pear/' ||
		substr($file, 0, strlen(UPLOADS_ROOT)) == UPLOADS_ROOT
	) {
		unset($all[$index]);
	}
}
echo "[i] Total ".count($all)." files after cleaning\n";


// Файлы специального назначения, которые не относятся ни к одному из модулей
$module_files[] = SITE_ROOT.'design/_default/';
$module_files[] = '.htaccess';
$module_files[] = LIBS_ROOT.'geshi/';
$module_files[] = SITE_ROOT.'design/cms/';
$module_files[] = SITE_ROOT.'js/shared/';
$module_files[] = SITE_ROOT.'img/shared/';
$module_files[] = SITE_ROOT.'system/import/';
$module_files[] = SITE_ROOT.'system/fonts/';
$module_files[] = SITE_ROOT.'system/run/';
$module_files[] = SITE_ROOT.'install/';
$module_files[] = SITE_ROOT.'.htaccess';
$module_files[] = SITE_ROOT.'actions_admin.php';
$module_files[] = SITE_ROOT.'actions_site.php';
$module_files[] = SITE_ROOT.'crossdomain.xml';
$module_files[] = SITE_ROOT.'favicon.ico';
$module_files[] = SITE_ROOT.'index_admin.php';
$module_files[] = SITE_ROOT.'index_admin_edit.php';
$module_files[] = SITE_ROOT.'index_admin_login.php';
$module_files[] = SITE_ROOT.'index_site.php';
$module_files[] = SITE_ROOT.'robots.txt';
$module_files[] = SITE_ROOT.'sitemap.php';
$module_files[] = SITE_ROOT.'content/.htaccess';
$module_files[] = SITE_ROOT.'img/1x1.gif';
$module_files[] = SITE_ROOT.'extras/';
$module_files[] = SITE_ROOT.'static/';
$module_files[] = SITE_ROOT.'system/tests/';


reset($module_files);
while (list(,$m) = each($module_files)) {
	if (empty($m)) {
		continue;
	}
	reset($all);
	while (list($index, $file) = each($all)) {
		if (strstr($file, $m) !== false) {
			unset($all[$index]);
		}
	}
}

// Удаляем из списка .htaccess файлы
reset($all);
while (list($index,$file) = each($all)) {
	if (!substr($file, -1 * strlen('.htaccess')) == '.htaccess') {
		unset($all[$index]);
	}
}

$all = array_values($all);
asort($all);
reset($all);
$index = 0;
while (list(,$row) = each($all)) {
	$index++;
	echo "[$index] ".substr($row, strlen(SITE_ROOT))."\n";
}

?>