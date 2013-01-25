<?php

/**
 * Скачивает все файлы модуля
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2008
 */
$module_id = globalVar($_REQUEST['module_id'], 0);


// Информация о модуле
$query = "SELECT LOWER(name) FROM cms_module WHERE id='$module_id'";
$module = $DB->result($query);

$tmp_root = TMP_ROOT."$module/";
Filesystem::delete($tmp_root);
if (is_file(TMP_ROOT.$module.'.tar.gz')) {
	unlink(TMP_ROOT.$module.'.tar.gz');
}


// Информация об интерфейсах
$query = "SELECT id, LOWER(name) AS name FROM cms_interface";
$interfaces = $DB->fetch_column($query, 'id', 'name');


// Информация обо всех языках в системе
$languages = preg_split("/,/", LANGUAGE_AVAILABLE);


// Таблицы, которые пренадлежат модулю
$query = "
	SELECT tb_table.name, tb_table._table_type as table_type
	FROM cms_table AS tb_table
	INNER JOIN cms_db AS tb_db ON tb_db.id=tb_table.db_id
	WHERE tb_table.module_id='$module_id'
";
$tables = $DB->fetch_column($query);
$sql = '';


reset($tables);
while (list($table, $table_type) = each($tables)) {
	if (strtolower($table_type) == 'view') {
		$query = "show create view `$table`";
		$info = $DB->query_row($query);
		$sql .= "\n\nDROP VIEW IF EXISTS `$table`;\n".$info['create view'].";\n";
		continue;
	}
	
	$query = "show create table `$table`";
	$info = $DB->query_row($query);
	$sql .= "\n\nDROP TABLE IF EXISTS `$table`;\n".$info['create table'].";\n";
	
	// определяем, есть ли у таблицы тригеры
	$query = "select * from information_schema.triggers where trigger_schema='".$DB->db_name."' and event_object_table='$table'";
	$triggers = $DB->query($query);
	
	reset($triggers);
	while (list(,$row) = each($triggers)) {
		$sql .= "
			create trigger $row[trigger_name] $row[action_timing] $row[event_manipulation] ON $row[event_object_table]
			for each $row[action_orientation]
			$row[action_statement];
		";
	}
}


if (!is_dir($tmp_root)) {
	mkdir($tmp_root, 0777, true);
}

file_put_contents($tmp_root.'schema.sql', $sql);


$query = "
	SELECT tb_table.id
	FROM cms_table AS tb_table
	INNER JOIN cms_db AS tb_db ON tb_db.id=tb_table.db_id
	WHERE tb_table.module_id='$module_id'
";
$tables = $DB->fetch_column($query);
$sql = '';


reset($tables);
while (list(,$table_id) = each($tables)) {
	$sql .= "\n";
	$sql .= export_data('cms_table', $table_id, array('db_id'));
	$sql .= "\n";
	// Поля, которые есть в таблице
	$query = "
		select tb_field.id
		from cms_field as tb_field 
		where tb_field.table_id='$table_id' and left(tb_field.name, 1)<>'_'
	";
	$fields = $DB->fetch_column($query);
	reset($fields);
	while (list(,$field_id) = each($fields)) {
		$sql .= export_data('cms_field', $field_id, array());
	}
}


file_put_contents($tmp_root.'beta.sql', $sql);


function export_data($table_name, $id, $skip_fields = array()) {
	global $DB;
	$result = '';
	
	// информация
	$query = "select * from `$table_name` where id='$id'";
	$info = $DB->query_row($query);
	
	// структура таблицы
	$query = "select id from cms_table where name='$table_name'";
	$table_id = $DB->result($query);
	$fields = cmsTable::getFields($table_id);
	
	$where = export_constrain($table_id, $id, false);
	$update = array();
	
	reset($fields);
	while (list($field,$row) = each($fields)) {
		if ($field == 'id' || in_array($field, $skip_fields)) {
			unset($info[$field]);
		} elseif (!empty($row['fk_table_name'])) {
			$set_where = export_constrain($row['fk_table_id'], $info[$field], $field);
			
			if (!empty($set_where)) {
				$result .= "update `$table_name` set $set_where where $where;\n";
			}
			unset($info[$field]);
		} elseif (empty($info[$field])) {
			unset($info[$field]);
		}
	}
	$result = "insert into `$table_name` (`".implode("`,`", array_keys($info))."`) values ('".implode("','", $info)."');\n".$result;
	return $result;
}


function export_get_short_constrain($db_name, $table_name) {
	global $DB;
	
	// находим в таблице уникальный ключ с наименьшим кол-вом столбцов
	$query = "
		SELECT group_concat(column_name order by ordinal_position asc separator ','), count(*) as sort
		from information_schema.key_column_usage
		where
			table_schema='$db_name' and
			table_name='$table_name' and
			constraint_name<>'primary'
		group by constraint_name
		order by sort asc
		limit 1
	";
	return $DB->result($query);
}


// определяем уникальный ключ для таблицы, на которую ссылаемся
function export_constrain($table_id, $parent_id, $parent_field) {
	global $DB;
	
	if (empty($parent_id)) {
		// пропускаем ссылки на внешние ключи = NULL и 0
		return '';
	}
	
	$where = array();
	
	$info = $DB->query_row("select table_name, db_alias from cms_table_static where id='$table_id'");
	$info['db_name'] = db_config_constant("name", $info['db_alias']); 
	
	
	$constrain = export_get_short_constrain($info['db_name'], $info['table_name']);
	if (empty($constrain)) {
//		echo "[w] У таблицы `$info['table_name']` нет уникальных полей\n";
		return '';
	}
	
	// Определяем значения внешних ключей
	$query = "select $constrain from `$info[table_name]` where id='$parent_id'";
	$parent = $DB->query_row($query);
	
	// определяем не является ли хоть одино из полей внешним ключём
	$data = cmsTable::getFields($table_id);
	
	reset($parent);
	while (list($field,$value) = each($parent)) {
		if (!empty($data[$field]['fk_table_id'])) {
			$where[] = export_constrain($data[$field]['fk_table_id'], $value, $field);
		} else {
			$where[] = "`$field`='$value'";
		}
	}
	
	if (empty($where)) {
		return '';
	}
	
	if (!empty($parent_field)) {
		return "`$parent_field`=(select id from `$info[table_name]` where ".implode(" and ", $where).")";
	} else {
		return implode(" and ", $where);
	}
}


// События
reset($interfaces);
while(list(,$interface) = each($interfaces)) {
	if (!is_dir(ACTIONS_ROOT.$interface.'/'.$module)) {
		continue;
	}
	$files = Filesystem::getAllSubdirsContent(ACTIONS_ROOT.$interface.'/'.$module, true);
	download_files($files);
}

// Crontab
if (is_dir(SITE_ROOT.'system/crontab/'.$module)) {
	$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'system/crontab/'.$module, true);
	download_files($files);
}

// Шаблоны
if (is_dir(TEMPLATE_ROOT.$module)) {
	$files = Filesystem::getAllSubdirsContent(TEMPLATE_ROOT.$module, true);
	download_files($files);
}

// Include
if (is_dir(INC_ROOT.$module)) {
	$files = Filesystem::getAllSubdirsContent(INC_ROOT.$module, true);
	download_files($files);
}

// Tools
if (is_dir(SITE_ROOT.'tools/'.$module)) {
	$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'tools/'.$module, true);
	download_files($files);
}

// Страницы сайта и шаблоны
$query = "
	select lower(tb_structure.url) as url
	from site_structure as tb_structure
	inner join cms_module_site_structure as tb_relation on tb_relation.structure_id=tb_structure.id
	where tb_relation.module_id='$module_id'
";
$data = $DB->fetch_column($query);
$files = array();
reset($languages);
while (list(,$language) = each($languages)) {
	reset($data);
	while (list(,$row) = each($data)) {
		$file = CONTENT_ROOT.'site_structure/'.$row.'.'.$language.'.php';
		$file_template = CONTENT_ROOT.'site_structure/'.$row.'.'.$language.'.tmpl';
		if (is_file($file)) {
			$files[] = $file;
		}
		if (is_file($file_template)) {
			$files[] = $file_template;
		}
	}
}
download_files($files);

// Страницы и шаблоны администртивного интерфейса
$query = "
	select lower(url) as url
	from cms_structure
	where module_id='$module_id'
";
$data = $DB->query($query);
$files = array();
reset($languages);
while (list(,$language) = each($languages)) {
	reset($data);
	while (list(,$row) = each($data)) {
		$file = CONTENT_ROOT.'cms_structure/'.$row.'.'.$language.'.php';
		$file_template = CONTENT_ROOT.'cms_structure/'.$row.'.'.$language.'.tmpl';
		if (is_file($file)) {
			$files[] = $file;
		}
		if (is_file($file_template)) {
			$files[] = $file_template;
		}
	}
}
download_files($files);

// Таблицы стилей
if (is_dir(SITE_ROOT.'css/'.$module)) {
	$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'css/'.$module, true);
	download_files($files);
}

// Картинки
if (is_dir(SITE_ROOT.'img/'.$module)) {
	$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'img/'.$module, true);
	download_files($files);
}


function download_files($files) {
	global $tmp_root;
	reset($files);
	while (list(,$file) = each($files)) {
		$new_filename = $tmp_root.substr($file, strlen(SITE_ROOT));
		if (!is_dir(dirname($new_filename))) {
			mkdir(dirname($new_filename), 0777, true);
		}
		copy($file, $new_filename);
	}
}

$zip_file = TMP_ROOT.$module.'.tar.gz';
$site_root = substr(TMP_ROOT, 0, -1);
$tmp_root = substr($tmp_root, strlen($site_root) + 1);
`tar -c -z -f $zip_file -C $site_root ./$tmp_root`;

header('Content-Type: application/x-zip-compressed');
header("Content-Disposition: attachment; filename=\"$module.tar.gz\"");
echo file_get_contents($zip_file);

?>