<?php
/**
 * Удаление модулей
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$query = "
	select
		id,
		name,
		description_".LANGUAGE_CURRENT." as description
	from cms_module as tb_module 
	where obligatory=0
	order by name asc
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	if ($DB->rows / 2 > $index) {
		$TmplContent->iterate('/delete_1/', null, $row);
	} else {
		$TmplContent->iterate('/delete_2/', null, $row);
	}
}

if (file_exists(SITE_ROOT.'static') || file_exists(SITE_ROOT.'system/import')) {
	$TmplContent->set('show_dirs', 1);
}
$TmplContent->set('static_size', ceil(Filesystem::getSize(SITE_ROOT.'static') / 1000));
$TmplContent->set('import_size', ceil(Filesystem::getSize(SITE_ROOT.'system/import') / 1000));
$TmplContent->set('tmp_size', ceil(Filesystem::getSize(SITE_ROOT.'tmp') / 1000));
$TmplContent->set('logs_size', ceil(Filesystem::getSize(SITE_ROOT.'system/logs') / 1000));
//$TmplContent->set('cvs_size', ceil(Filesystem::getSize(SITE_ROOT.'cvs') / 1000));

/*
// Определяем названия таблиц, которые не связаны с модулями
$query = "select table_name, table_type, concat(table_name, '.', table_type) as id from information_schema.tables where table_schema='$DB->db_name'";
$database = $DB->query($query, 'id');

$query = "select name from cms_module";
$modules = $DB->fetch_column($query);
$tables = array();
reset($modules);
while (list(,$name) = each($modules)) {
	$Module = new Module($name);
	reset($Module->tables);
	while (list(,$row) = each($Module->tables)) {
		unset($database[$row['table_name'].'.'.$row['table_type']]);
	};
}


reset($database);
while (list(,$row) = each($database)) {
	$TmplContent->set('show_tables', 1);
	$TmplContent->iterate('/table/', null, $row);
}

*/

// Удаление определенных групп пользователей
$query = "
	select 
		tb_user.group_id as id,
		ifnull(tb_group.name, 'Без группы') as group_name,
		count(tb_user.id) as user_count 
	from auth_user as tb_user
	left join auth_group as tb_group on tb_user.group_id=tb_group.id
	group by tb_user.group_id
	order by group_name asc
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	if ($DB->rows / 2 > $index) {
		$TmplContent->iterate('/group_1/', null, $row);
	} else {
		$TmplContent->iterate('/group_2/', null, $row);
	}
}


// Удаление лишних сайтов
$query = "
	select id, url
	from site_structure_site
	order by url asc
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	if ($DB->rows / 2 > $index) {
		$TmplContent->iterate('/site_1/', null, $row);
	} else {
		$TmplContent->iterate('/site_2/', null, $row);
	}
}


// Удаление лишних дизайнов
$query = "
	select id, name, title
	from site_template_group
	where name not in ('_default', 'cms')
	order by name asc
";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	if ($DB->rows / 2 > $index) {
		$TmplContent->iterate('/template_1/', null, $row);
	} else {
		$TmplContent->iterate('/template_2/', null, $row);
	}
}


// Удаление новостей
$query = "select id, name_".LANGUAGE_CURRENT." as name from news_type where type_id=0 order by name asc";
$data = $DB->query($query);
reset($data); 
while (list($index,$row) = each($data)) { 
	if ($DB->rows / 2 > $index) {
		$TmplContent->iterate('/news_1/', null, $row);
	} else {
		$TmplContent->iterate('/news_2/', null, $row);
	}
}



?>