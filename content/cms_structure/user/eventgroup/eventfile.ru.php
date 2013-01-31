<?php
/**
 * Список файлов-событий
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

/**
 * Типизируем переменные
 */
$module_id = globalVar($_GET['module_id'], 0);
$TmplContent->setGlobal('module_id', $module_id);

/**
 * Определяем имя директории, в которой находятся файлы
 */
$query = "SELECT LOWER(name) FROM cms_module WHERE id='$module_id'";
$group_name = $DB->result($query);

/**
 * Фильтр предварительной обработки
 * @ignore 
 * @param array $row
 * @return array
 */
function cms_prefilter($row) {
	global $group_name;
	if (!is_file(ACTIONS_ROOT."admin/$group_name/$row[name].act.php")) {
		$row['name'] = '<font color="gray">'.$row['name'].'</font>';
	}
	return $row;
}

/**
* Вывод таблицы
*/
$query = "
	SELECT 
		tb_event.id, 
		tb_event.name, 
		tb_event.description_".LANGUAGE_CURRENT." AS description
	FROM cms_event AS tb_event
	WHERE tb_event.module_id='$module_id' 
	ORDER BY tb_event.name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('description', '60%');
$table_info = $cmsTable->getTableInfo();
$cms_view = $cmsTable->display();

$TmplContent->setGlobal('table_id', $table_info['id']);
$TmplContent->set('cms_view', $cms_view);

unset($table_info);
unset($cmsTable);

$TmplContent->set('cms_view', $cms_view);
unset($cms_view);

if (is_dir(ACTIONS_ROOT.'admin/'.$group_name.'/')) {
	
	$query = "SELECT name FROM cms_event WHERE module_id='$module_id'";
	$action_events = $DB->fetch_column($query, 'name', 'name');
	
	/**
	 * Вывод списка файлов, которые небыли добавлены в систему
	 */
	$path = ACTIONS_ROOT.'admin/'.$group_name.'/';
	$action_files = Filesystem::getAllSubdirsContent($path, true);
	#x($action_files, true);
	reset($action_files);
	while (list($index, $file) = each($action_files)) {
		$file = substr($file, strlen($path), -8);
		
		if (strpos(PHP_OS, "WIN") !== FALSE) {
			$file = substr(str_replace('\\', '/', $file), 1); 
		}
				
		if (isset($action_events[$file]) || substr($file, -8) == '.inc.php') {
			unset($action_files[$index]);
		} else {
			$action_files[$index] = $file;
		}
	}
	
	$TmplContent->set('action_files', $action_files);
	
	reset($action_files);
	while (list($index, $file) = each($action_files)) {
		$content = file_get_contents(ACTIONS_ROOT."admin/$group_name/$file.act.php");
		$description = '';
		if (preg_match("~\s\*\s(.+)\n~ismU", $content, $matches)) {
			$description = $matches[1];
		}
		$TmplContent->iterate('/file/', null, array('file'=>$file, 'description' => $description));
	}
	
}
?>