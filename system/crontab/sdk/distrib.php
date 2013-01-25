<?php
/**
 * Создает дистрибутив систеы
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

$modules 		 = globalVar($argv[1], '');
$sites 			 = globalVar($argv[2], '');
$tmp_db_host 	 = globalVar($argv[3], '');
$tmp_db_name 	 = globalVar($argv[4], '');
$tmp_db_login 	 = globalVar($argv[5], '');
$tmp_db_password = globalVar($argv[6], '');

if (empty($modules) || empty($tmp_db_password)) {
	$query = "select name from cms_module where obligatory=0 order by name";
	$modules = $DB->fetch_column($query);
	
	$query = "select uniq_name from site_structure where structure_id=0 order by uniq_name";
	$sites = $DB->fetch_column($query);
	
	echo "
Usage: php ./".basename(__FILE__)." modules sites dst_db_host dst_db_name dst_db_login dst_db_password
Options:
modules		List of modules separated by comma.
sites 		List of sites separated by comma.

Available modules:
".implode(",", $modules)."

Available sites: 
".implode(",", $sites)."

";
	exit;
}

if (trim(strtolower(DB_DEFAULT_NAME)) == trim(strtolower($tmp_db_name))) {
	echo "[e] Please enter destination database name, not source\n";
	exit;
}

// Модули
$modules = preg_split("/,+/", $modules, -1, PREG_SPLIT_NO_EMPTY);
$query = "select * from cms_module where name in ('".implode("','", $modules)."') or obligatory=1";
$modules = $DB->fetch_column($query);
do {
	$query = "select distinct dependency_id from cms_module_dependency where module_id in (0".implode(",", $modules).") and dependency_id not in (0".implode(",", $modules).")";
	$dependent = $DB->fetch_column($query);
	$modules = array_merge($modules, $dependent);
} while($DB->rows > 0);

// Сайты
$sites = preg_split("/,+/", $sites, -1, PREG_SPLIT_NO_EMPTY);
$sites_count = count($sites);
$query = "select id from site_structure where uniq_name in ('".implode("','", $sites)."')";
$sites = $DB->fetch_column($query);
if ($DB->rows != $sites_count || $DB->rows == 0) {
	echo "[e] Wrong sites count or one of the site - not found\n";
	exit;
}


/**
 * Выполняем проверки правильности работы системы
 */
$stop = 0;

// Проверяем таблицы, которые описаны в системе но реально не существуют
$query = "select table_name from information_schema.tables where table_schema='".DB_DEFAULT_NAME."'";
$information_schema = $DB->fetch_column($query);
$query = "select name from cms_table where module_id in (0".implode(",", $modules).") and _table_type not in ('function', 'procedure')";
$data = $DB->fetch_column($query);
reset($data);
while (list(,$row) = each($data)) {
	if (!in_array($row, $information_schema)) {
		$stop = 1;
		echo "[e] Table $row does not exists\n";
	}
}

// Проверяем таблицы, которые не привязаны к модулям или привязаны к модулям, которые не существуют
$query = "
	select tb_table.name 
	from cms_table as tb_table 
	left join cms_module as tb_module on tb_module.id=tb_table.module_id
	where tb_module.id is null and _table_type not in ('view', 'procedure', 'function')
";
$data = $DB->fetch_column($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please set module_id for table $row\n";
}

// Наличие таблиц, для которых не указано поле Название
$query = "
	select id, name
	from cms_table 
	where module_id in (0".implode(",", $modules).") and is_disabled=0 and title_ru='' and _table_type not in ('function', 'procedure')
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please check title for table $row[name] (".CMS_URL."Admin/CMS/DB/Tables/Fields/?table_id=$row[id])\n";
}

// Таблицы к которым нет прав доступа на просмотр
$query = "
	SELECT tb_module.id, tb_module.name as module, GROUP_CONCAT(tb_table.name SEPARATOR ',') as tables
	FROM cms_table tb_table
	INNER JOIN cms_module as tb_module ON tb_module.id=tb_table.module_id
	LEFT JOIN auth_action_table_select tb_relation ON (tb_relation.table_id = tb_table.id)
	LEFT JOIN auth_action tb_action ON (tb_relation.action_id = tb_action.id)
	WHERE tb_table.is_disabled = 0 AND tb_table.module_id in (0".implode(",", $modules).") AND tb_action.id is null and _table_type not in ('function', 'procedure')
	GROUP BY tb_module.id
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please check \"select\" permission in module $row[module] for tables $row[tables] (".CMS_URL."Admin/User/Actions/Module/?module_id=$row[id])\n";
}

// Таблицы к которым нет прав доступа на изменение
$query = "
	SELECT tb_module.id, tb_module.name as module, GROUP_CONCAT(tb_table.name SEPARATOR ',') as tables
	FROM cms_table tb_table
	INNER JOIN cms_module as tb_module ON tb_module.id=tb_table.module_id
	LEFT JOIN auth_action_table_update tb_relation ON (tb_relation.table_id = tb_table.id)
	LEFT JOIN auth_action tb_action ON (tb_relation.action_id = tb_action.id)
	WHERE tb_table.is_disabled = 0 AND tb_table.module_id in (0".implode(",", $modules).") AND tb_action.id is null and _table_type not in ('function', 'procedure')
	GROUP BY tb_module.id
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please check \"update\" permission in module $row[module] for tables $row[tables] (".CMS_URL."Admin/User/Actions/Module/?module_id=$row[id])\n";
}


// события, к которым нет прав доступа
$query = "
	SELECT group_concat(tb_event.name separator ', ') as events, tb_module.id, tb_module.name as module
	FROM cms_event tb_event
	INNER JOIN cms_module as tb_module ON tb_module.id=tb_event.module_id
	LEFT JOIN auth_action_event tb_relation ON (tb_relation.event_id = tb_event.id)
	LEFT JOIN auth_action tb_action ON (tb_relation.action_id = tb_action.id)
	WHERE tb_event.module_id in (0".implode(",", $modules).") AND tb_action.id is null
	GROUP BY tb_module.id
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please check permission in module $row[module] for events $row[events] (".CMS_URL."Admin/User/Actions/Module/?module_id=$row[id])\n";
}

// События
$query = "select id, name from cms_module where id in (0".implode(",", $modules).")";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$query = "select name from cms_event where module_id='$row[id]'";
	$events = $DB->fetch_column($query);
	
	// события, которые описаны, но не существуют
	reset($events);
	while (list(,$row2) = each($events)) {
		$file = SITE_ROOT.'system/actions/admin/'.strtolower($row['name']).'/'.strtolower($row2).'.act.php';
		if (!is_file($file)) {
			$stop = 1;
			echo "[e] Event $file does not exists (".CMS_URL."Admin/User/EventGroup/EventFile/?module_id=$row[id])\n";
		}
	}
	
	// события, котрые не описаны
	$files = Filesystem::getDirContent(SITE_ROOT.'system/actions/admin/'.strtolower($row['name']).'/', false, false, true, false);
	reset($files);
	while (list(,$file) = each($files)) {
		if (!in_array(substr($file, 0, strlen('.act.php') * -1), $events)) {
			$stop = 1;
			echo "[e] Event $file does not described in CMS (".CMS_URL."Admin/User/EventGroup/EventFile/?module_id=$row[id])\n";
		}
	}
}

// Разделы админпанели, к которым нет прав доступа
$query = "
	SELECT tb_structure.url, tb_module.id, tb_module.name as module
	FROM cms_structure tb_structure
	INNER JOIN cms_module as tb_module ON tb_module.id=tb_structure.module_id
	LEFT JOIN auth_action_view tb_relation ON (tb_relation.structure_id = tb_structure.id)
	LEFT JOIN auth_action tb_action ON (tb_relation.action_id = tb_action.id)
	WHERE tb_structure.module_id in (0".implode(",", $modules).") AND tb_action.id is null
";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	$stop = 1;
	echo "[e] Please check permission in module $row[module] for $row[url] (".CMS_URL."Admin/User/Actions/Module/?module_id=$row[id])\n";
}


// Проверяем наличие на сайте модулей, которые не выбраны
$data = $DB->query("
	select tb_module.name as module, tb_structure.url
	from site_structure_relation as tb_relation
	inner join cms_module_site_structure as tb_mod2structure on tb_mod2structure.structure_id=tb_relation.id
	inner join cms_module as tb_module on tb_module.id=tb_mod2structure.module_id
	inner join site_structure as tb_structure on tb_structure.id=tb_relation.id
	where tb_relation.parent in (0".implode(",", $sites).") 
		and tb_mod2structure.module_id not in (0".implode(",", $modules).")
");

reset($data);
while (list(,$row) = each($data)) {
	$stop = 1; 
	echo "[e] Add into distrib.php command line module $row[module] or remove page $row[url] which use this module\n";
}





if ($stop) {
	exit;
}






$tmp_root = TMP_ROOT.'distrib/';
Filesystem::delete($tmp_root);
mkdir($tmp_root, 0777, true);
$tables = array();

$languages = preg_split("/,/", LANGUAGE_ALL_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);

$query = "select name from cms_table where clean_on_install=1";
$skip_data_from_tables = $DB->fetch_column($query);
$skip_data_from_tables[] = "sdk_doc_argument";
$skip_data_from_tables[] = "sdk_doc_class";
$skip_data_from_tables[] = "sdk_doc_function";

$DBtmp = new dbMySQLi($tmp_db_host, $tmp_db_login, $tmp_db_password, $tmp_db_name);    

/**
 * Проверяем базу данных
 */
$db_found = $DBtmp->result("SELECT SCHEMA_NAME as found FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$tmp_db_name'");
if(empty($db_found)) {
	echo "[e] DB $tmp_db_name is not found. Please, create destination database \n";
	exit;
}

$db_empty = $DBtmp->result("select count(*) from information_schema.tables where table_type = 'BASE TABLE' and table_schema = '$tmp_db_name'");
if(!empty($db_empty)){
	echo "[e] DB $tmp_db_name is not empty. $db_empty tables found. Please, drop all tables or create other database \n";
	exit;   
}


/**
 * Копирование файлов
 */
$query = "select id, name from cms_module where id in (0".implode(",", $modules).")";
$data = $DB->fetch_column($query);
reset($data);
while (list($module_id, $name) = each($data)) {
	echo "[i] Build module $name...";
	$Module = new Module($module_id);
	echo 'get files, '; 
	$files = $Module->getAllFiles();
	echo 'copy files, ';
	reset($files);
	while (list(,$file) = each($files)) {
		$dst = $tmp_root.substr($file, strlen(SITE_ROOT));
		Filesystem::copy($file, $dst);
	}
	echo "completed\n";
	
	// SQL
	reset($Module->tables); 
	while (list(,$table) = each($Module->tables)) { 
		if ($table['table_type'] == 'BASE TABLE' || $table['table_type'] == 'VIEW') {
			$tables[] = $table['table_name'];
		} else {
			echo "[i] Ignore table $table[table_name] ($table[table_type])\n";
		}
	}
}
mkdir($tmp_root.'tmp/');
mkdir($tmp_root.'cvs/');
mkdir($tmp_root.'system/logs/');
mkdir($tmp_root.'system/run/');
unset($DB);


/**
 * Копирование БД
 */
$dump_file = $tmp_root.'db.sql';
mysql_dump(DB_DEFAULT_HOST, DB_DEFAULT_LOGIN, DB_DEFAULT_PASSWORD, DB_DEFAULT_NAME, $dump_file, $tables, $skip_data_from_tables);

// Закачиваем дамп во временную базу
$query = "drop database $tmp_db_name"; 
$DBtmp->delete($query);
$query = "create database $tmp_db_name DEFAULT CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci";
$DBtmp->delete($query);
$query = "use $tmp_db_name";
$DBtmp->delete($query);
echo "[i] Restore data in destination database...";
$error = exec("/usr/local/mysql/bin/mysql -h $tmp_db_host -u $tmp_db_login --password=$tmp_db_password $tmp_db_name < $dump_file 2>&1");
echo " completed\n";
if (!empty($error)) {
	echo $error;
	echo "\n$dump_file\n";
	exit;
}
unlink($dump_file);
unlink($dump_file.'_triggers');  

/**
 * Обработка новой БД
 */
$query = "select structure_id from cms_module_site_structure where module_id not in (0".implode(",", $modules).")";
$module2structure = $DBtmp->fetch_column($query);
$query = "delete from site_structure where id in (0".implode(",", $module2structure).")";
$DBtmp->delete($query);

// Список таблиц, которые есть в новой БД
$query = "select table_name as a, table_name as b from information_schema.tables where table_schema='$tmp_db_name'";
$real_tables = $DBtmp->fetch_column($query);

// Удаляем данные из таблицы site_structure (Делать это надо до того, как будут удалены все строки c module_id not in ($installed_modules_list))
$query = "delete from site_structure where id in (select structure_id from cms_module_site_structure where module_id not in (0".implode(",", $modules)."))";
$DBtmp->delete($query);

// Удаляем информацию о модулях, которых нет
$query = "
	SELECT tb_table.name
	FROM cms_table tb_module
	INNER JOIN cms_field tb_field ON (tb_module.id = tb_field.fk_table_id)
	INNER JOIN cms_table tb_table ON (tb_field.table_id = tb_table.id)
	WHERE
		tb_module.name = 'cms_module' AND 
		tb_field.name = 'module_id'
";
$tables = $DBtmp->fetch_column($query);
reset($tables); 
while (list(,$table_name) = each($tables)) { 
	if (!isset($real_tables[$table_name])) continue;
	 $query = "delete from `$table_name` where module_id not in (0, 0".implode(",", $modules).")";
	 $DBtmp->delete($query);
}

$query = "delete from cms_module where id not in (0".implode(',', $modules).")";
$DBtmp->delete($query);




/**
 * Удаляем колонки из таблиц, которые привязаны к модулям, которые не установлены
 */
$query = "select concat(table_name, '.', column_name) from information_schema.columns where table_schema='$tmp_db_name'";
$real_columns = $DBtmp->fetch_column($query);

$query = "
	select tb_table.name as table_name, tb_field.name as column_name, tb_db.alias as db_alias
	from cms_field as tb_field
	inner join cms_table as tb_table on tb_table.id=tb_field.table_id
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	where 
		tb_field.module_id not in (0, 0".implode(",", $modules).") and
		tb_field.module_id is not null
";
$data = $DBtmp->query($query);
reset($data);
while (list(,$row) = each($data)) {
	if (!isset($real_columns[$row['table_name'].'.'.$row['column_name']])) continue;
	$query = "alter table `$row[table_name]` drop column `$row[column_name]`";
	echo "[i] $query\n";
	$DBtmp->delete($query);
}

/**
 * Удаляем записи в cms_db в которых нет таблиц
 */
$query = "select db_id from cms_table";
$db_id = $DBtmp->fetch_column($query);
$query = "delete from  cms_db where id not in (0".implode(",", $db_id).")";
$DBtmp->delete($query);

/**
 * Удаляем данные из cms_field
 */
$query = "select id from cms_table";
$tables = $DBtmp->fetch_column($query);
$query = "delete from cms_field where table_id not in (0".implode(",", $tables).")";
$DBtmp->delete($query);
unset($tables);

/**
 * Удаляем данные из cms_field_enum
 */
$query = "select id from cms_field";
$fields = $DBtmp->fetch_column($query);
$query = "delete from cms_field_enum where field_id not in (0".implode(",", $fields).")";
$DBtmp->delete($query);
unset($fields);













/**
 * Удаляем пользователей, которые не являются администраторами (auth_user)
 */

/**
 * Удаляем группы пользователей, в которых нет пользователей (auth_group)
 */

/**
 * Очищаем таблицу sdk_project
 */

// /Admin/User/Yearstat/

// /Admin/CMS/Language/Templates/

// /Admin/CMS/Development/SQL/

// Проверить работу модуля орфографии

// Удалить страницу /Admin/User/Flag/

// Удалить страницу /Admin/Forum/Forbiddenwords/

// Очищать при переносе maillist_message

// Меняем пароли для ящиков cms_mail_account











/**
 * Копируем обработчки таблиц
 */
$query = "
	select concat(tb_db.alias, '/', tb_table.name)
	from cms_table as tb_table
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
";
$tables =  $DBtmp->fetch_column($query);
reset($tables); 
while (list(,$row) = each($tables)) { 
	if (is_file(SITE_ROOT.'content/cms_table/'.$row.'.inc.php')) {
		Filesystem::copy(SITE_ROOT.'content/cms_table/'.$row.'.inc.php', $tmp_root.'content/cms_table/'.$row.'.inc.php');
	}
}



/**
 * Удаляем лишние записи из таблицы site_structure
 */
$structure_id = $sites;
$query = "create temporary table tmp_structure like site_structure";
$DBtmp->query($query);
$query = "insert into tmp_structure select * from site_structure where id in (0".implode(",", $sites).")";
$DBtmp->insert($query);
do {
	$query = "insert ignore into tmp_structure select * from site_structure where structure_id in (0".implode(",", $structure_id).")";
	$DBtmp->insert($query);
	
	$query = "select id from site_structure where structure_id in (0".implode(",", $structure_id).")";
	$structure_id = $DBtmp->fetch_column($query);

} while(!empty($DBtmp->rows));
$query = "truncate table site_structure";
$DBtmp->delete($query);
$query = "insert into site_structure select * from tmp_structure";
$DBtmp->insert($query);
$query = "delete from site_structure_site where id not in (0".implode(",", $sites).")";
$DBtmp->delete($query);


/**
 * Удаляем фотографии в таблице gallery_photo для которых нет родительских таблиц и рядов
 */
$query = "delete from gallery_photo where group_table_name not in ('".implode("','", $real_tables)."')";
$DBtmp->delete($query);
$query = "select distinct group_table_name from gallery_photo";
$group_table = $DBtmp->fetch_column($query);
reset($group_table);
while (list(,$table_name) = each($group_table)) {
	$query = "select id from `$table_name`";
	$id_list = $DBtmp->fetch_column($query);
	
	$query = "delete from gallery_photo where group_table_name='$table_name' and group_id not in (0, 0".implode(",", $id_list).")";
	$DBtmp->delete($query);
}




/**
 * Копируем файлы структуры сайта (по умолчанию Modules->files модержит только те файлы структуры сайта, которые привязаны к модулю)
 */
$query = "select lower(url) from site_structure";
$data = $DBtmp->fetch_column($query);
reset($data); 
while (list(,$row) = each($data)) { 
	reset($languages); 
	while (list(,$lang) = each($languages)) { 
		Filesystem::copy(SITE_ROOT.'content/site_structure/'.$row.'.'.$lang.'.php', $tmp_root.'content/site_structure/'.$row.'.'.$lang.'.php');
		Filesystem::copy(SITE_ROOT.'content/site_structure/'.$row.'.'.$lang.'.tmpl', $tmp_root.'content/site_structure/'.$row.'.'.$lang.'.tmpl');
	}
}


/**
 * Импорт дизайнов
 */
$query = "select distinct template_id from site_structure";
$design_id = $DBtmp->fetch_column($query);
$query = "select distinct group_id from site_template where id in (0".implode(",", $design_id).")";
$design_groups_id = $DBtmp->fetch_column($query);
$query = "select lower(name) from site_template_group where id in (0".implode(",", $design_groups_id).")";
$design_groups = $DBtmp->fetch_column($query);
$design_groups[] = '_default';
$design_groups[] = 'cms';
reset($design_groups); 
while (list(,$row) = each($design_groups)) { 
	Filesystem::copy(SITE_ROOT.'design/'.$row, $tmp_root.'design/'.$row);
}
$query = "delete from site_template where group_id not in (1, 0".implode(",", $design_groups_id).")";
$DBtmp->delete($query);
$query = "delete from site_template_group where id not in (1, 0".implode(",", $design_groups_id).")";
$DBtmp->delete($query);

/**
 * Импорт групп пользователей
 */
$query = "delete from auth_user where site_id not in (0".implode(",", $sites).") and group_id!=5";
$DBtmp->delete($query);

/**
 * Импорт новостей
 */
$query = "select id from news_type where site_id in (0".implode(",", $sites).")";
$types = $DBtmp->fetch_column($query);
$query = "delete from news_message where type_id not in (0".implode(",", $types).")";
$DBtmp->delete($query);
$query = "delete from news_type where site_id not in (0".implode(",", $sites).")";
$DBtmp->delete($query);

/**
 * Удаляем Uploads файлы для таблиц, данные с которых не импортируются
 */
reset($skip_data_from_tables); 
while (list(,$row) = each($skip_data_from_tables)) { 
	 Filesystem::delete($tmp_root.'uploads/'.$row.'/');
}

/**
 * Удаляем uploads файлы, которые не привязаны к строкам
 */
$tables = Filesystem::getDirContent($tmp_root.'uploads/', false, true, false, false);
reset($tables); 
while (list(,$table_name) = each($tables)) { 
	$fields = Filesystem::getDirContent($tmp_root.'uploads/'.$table_name.'/', false, true, false, false);
	reset($fields); 
	while (list(,$field_name) = each($fields)) { 
		 if (is_numeric($field_name)) {
		 	echo "[i] Row number $table_name.$field_name\n";
		 	delete_upload($table_name, '', $field_name);
		 } else {
		 	echo "[i] Field name $table_name.$field_name\n";
		 	$number = Filesystem::getDirContent($tmp_root.'uploads/'.$table_name.'/'.$field_name.'/', false, true, false, false);
		 	reset($number); 
		 	while (list(,$thousand) = each($number)) { 
		 		 delete_upload($table_name, $field_name.'/', $thousand);
		 	}
		 }
	}
}
Filesystem::deleteEmptyDirs($tmp_root.'uploads/');
Filesystem::delete($tmp_root.'uploads/auth_user_data/');

/**
 * Формируем конфигурационные файлы
 */
$DB = $DBtmp; // Необходимо для построения конфига

/**
 * Восстанавливаем relation таблицы
 */
$query  = "
	select tb_table.name, tb_relation.name as relation, tb_field.name as parent_field_name
	from cms_table as tb_table
	inner join cms_field as tb_field on tb_field.id=tb_table.parent_field_id
	inner join cms_table as tb_relation on tb_relation.id=tb_table.relation_table_id
";
$data = $DBtmp->query($query);
reset($data);
while (list(,$row) = each($data)) {
	// Для каждого вызова build_relation создаем новое соединение так как бага MySQL не позволят в одном соединении использовать разные PREPARED STATEMENT (28.07.2010)
	$DB2 = new dbMySQLi($tmp_db_host, $tmp_db_login, $tmp_db_password, $tmp_db_name);
	do {
		$query = "CALL build_relation('$row[name]', '$row[parent_field_name]', '$row[relation]', @total_rows)";
		echo "[i] $query ... ";
		$DB2->query($query);
		$query = "SELECT @total_rows";
		$total_rows = $DB2->result($query);
		echo "$total_rows\n";
	} while ($total_rows > 0);
	unset($DB2);
}


echo "[i] Update config\n";

// Меняем настройки БД
$is_ok = Install::changeDB($tmp_db_host, $tmp_db_login, $tmp_db_password, $tmp_db_name, $error_message);
if (!$is_ok) {
	echo "[e] Fatal error $error_message\n";
	exit;
}

// Удаляем секретные данные в настройках системы
Install::cleanConfig();

// cache/config.inc.pph
$text = Install::buildConfig();
file_put_contents($tmp_root.'cache/config.inc.php', $text);

// cache/language.xx.php
$query = "SELECT code FROM cms_language";
$data = $DBtmp->fetch_column($query);
reset($data); 
while (list(,$language) = each($data)) {
	$text = Install::buildLanguageConfig($language);
	file_put_contents($tmp_root.'cache/language.'.$language.'.php', $text);
}


// Прописываем константы в системном конфигурационном файле
$system_config_file = file_get_contents($tmp_root.'system/config.inc.php');
$system_config_file = preg_replace("/define[\s]*\([^)]*DB_DEFAULT_NAME[^)]*\)/ismU", "define('DB_DEFAULT_NAME', '$tmp_db_name')", $system_config_file);
$system_config_file = preg_replace("/define[\s]*\([^)]*DB_DEFAULT_HOST[^)]*\)/ismU", "define('DB_DEFAULT_HOST', '$tmp_db_host')", $system_config_file);
$system_config_file = preg_replace("/define[\s]*\([^)]*DB_DEFAULT_LOGIN[^)]*\)/ismU", "define('DB_DEFAULT_LOGIN', '$tmp_db_login')", $system_config_file);
$system_config_file = preg_replace("/define[\s]*\([^)]*DB_DEFAULT_PASSWORD[^)]*\)/ismU", "define('DB_DEFAULT_PASSWORD', '$tmp_db_password')", $system_config_file);
file_put_contents($tmp_root.'system/config.inc.php', $system_config_file);  


/**
 * Оптимизируем таблицы
 */
echo "[i] Optimize tables\n";
$DBtmp->query("optimize table `".implode("`,`", $real_tables)."`");

// Создаем дистрибутив базы данных
// mysql_dump($tmp_db_host, $tmp_db_login, $tmp_db_password, $tmp_db_name, $dump_file);


/**
 * Присваиваем пользователям права администраторов с одинаковым паролем
 */
$DBtmp->update("UPDATE auth_user SET group_id = 5, user_group_id = 2, passwd = '".md5('12345')."', checked = 1, confirmed = 1");   
  
 
echo "
Build succesfully completed!
You can find source by executing command:
1. cd ".SITE_ROOT."tmp/distrib/
2. copy sources to new ftp account
3. chown files
4. set right login and passwords for SMTP /Admin/Settings/SMTP/
";







function mysql_dump($host, $login, $password, $db_name, $dump_file, $tables = array(), $skip_data_from_tables = array()) {
	global $tmp_root;
	
	// Структура
	echo "[i] Dumping table definitions...";
	exec("/usr/local/mysql/bin/mysqldump --disable-keys --skip-comments --skip-triggers --quote-names --no-data --routines -h $host -u $login --password=$password $db_name ".implode(" ", $tables)." > $dump_file");
	$data = preg_replace("/DEFINER=`[^`]+`@`[^`]+`/ismU", '', file_get_contents($dump_file));
	file_put_contents($dump_file, $data);
	echo " completed\n";
	
	// Данные
	echo "[i] Dumpung data of ".count($tables)." tables...";
	$tables = array_diff($tables, $skip_data_from_tables);
	exec("/usr/local/mysql/bin/mysqldump --disable-keys --skip-comments --skip-triggers --quote-names --no-create-info --quick --extended-insert -h $host -u $login --password=$password $db_name ".implode(" ", $tables)." >> $dump_file");
	echo " completed\n";	
	
	// Триггеры
	echo "[i] Dumpung triggers of ".count($tables)." tables...";
	exec("/usr/local/mysql/bin/mysqldump --disable-keys --skip-comments --triggers --no-create-info --no-data --quick --quote-names  -h $host -u $login --password=$password $db_name ".implode(" ", $tables)." > {$dump_file}_triggers");
	$data = preg_replace("/DEFINER=`[^`]+`@`[^`]+`/ismU", '', file_get_contents($dump_file.'_triggers'));
	$fp = fopen($dump_file, 'a');
	fwrite($fp, $data);
	fclose($fp);
	echo " completed\n";
}


function build_relation($table_name, $parent_field) {
	global $DBtmp;
	$query = "truncate table {$table_name}_relation";
	$DBtmp->delete($query);
	do {
		$query = "CALL build_relation('$table_name', '$parent_field', '{$table_name}_relation', @total_rows)";
		$DBtmp->query($query);
		
		$query = "SELECT @total_rows";
		$total_rows = $DBtmp->result($query);
	} while ($total_rows > 0);
}

function structure($structure, $structure_id, $path) {
	global $languages, $tmp_root;
	
	$src = SITE_ROOT.'content/site_structure';
	$dst = $tmp_root.'content/site_structure';
	
	if (!isset($structure[$structure_id])) {
		return false;
	}
	
	reset($structure[$structure_id]); 
	while (list($id, $uniq_name) = each($structure[$structure_id])) { 
		structure($structure, $id, $path.'/'.$uniq_name);
	}
	
	$files = array();
	reset($languages); 
	while (list(,$lang) = each($languages)) { 
		 $files[] = $path.'.'.$lang.'.php';
		 $files[] = $path.'.'.$lang.'.tmpl';
	}
	
	reset($files); 
	while (list(,$file) = each($files)) { 
		if (is_file($src.$file)) {
			if (!is_dir(dirname($dst.$file))) {
				mkdir(dirname($dst.$file), 0750, true);
			}
			copy($src.$file, $dst.$file);
		} else {
			echo "[w] $src{$file}\n";
		}
	}
}


function delete_upload($table_name, $field_name, $thousand) {
	global $DBtmp, $tmp_root, $real_columns;
	
	if (!in_array($table_name.'.id', $real_columns)	) {
		echo "[w] There is no such column $table_name.id\n";
		return false;
	}
	
	$files = Filesystem::getDirContent($tmp_root.'uploads/'.$table_name.'/'.$field_name.$thousand.'/', false, true, true, false);
	if (empty($files)) {
		echo "[w] There is no files in directory (".$tmp_root.'uploads/'.$table_name.'/'.$field_name.$thousand.'/'.")\n";
		return false;
	}
	
	$query = "select id, id as v from `$table_name` where id >= $thousand*100 and id < $thousand*100+100";
	$id_list = $DBtmp->fetch_column($query);
	
	reset($files); 
	while (list(,$row) = each($files)) {
		if (is_file($tmp_root.'uploads/'.$table_name.'/'.$field_name.$thousand.'/'.$row)) {
			// Файл /table_name/field_name/0003/03.jpg
			$id = substr($row, 0, strpos($row, '.')) + $thousand * 100;
		} else {
			// Директория /table_name/0003/03/
			$id = $row + $thousand * 100;
			$row .= '/';
		}
		if (!isset($id_list[$id])) {
			Filesystem::delete($tmp_root.'uploads/'.$table_name.'/'.$field_name.$thousand.'/'.$row);
		}
	}
}

?>