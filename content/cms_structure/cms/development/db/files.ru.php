<?php
/** 
 * Проверка существования файлов, привязанных к таблицам БД 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$start = microtime(true);

/**
 * 1. Проверка существования файлов, привязанных к таблицам БД 
 */
$query = "
	SELECT
		tb_table.name AS table_name,
		tb_table.title_".LANGUAGE_CURRENT." AS table_title,
		tb_field.name AS field_name,
		tb_field.title_".LANGUAGE_CURRENT." AS field_title
	FROM cms_table AS tb_table
	INNER JOIN cms_field AS tb_field ON tb_field.table_id = tb_table.id
	WHERE tb_field.field_type = 'file'
	ORDER BY tb_table.name, tb_field.name
";
$fields = $DB->query($query);
$last_table = $last_field = null;
reset($fields); 
while (list(,$row) = each($fields)) { 
	
	// Строки таблицы, к которым привязаны файлы
	$query = "
		SELECT id, `$row[field_name]` AS file_field
		FROM `$row[table_name]`
		WHERE `$row[field_name]` IS NOT NULL AND `$row[field_name]` <> ''
	";
	$data = $DB->query($query);
	reset($data); 
	while (list(,$table_row) = each($data)) { 
		$row['filename'] = Uploads::getFile($row['table_name'], $row['field_name'], $table_row['id'], $table_row['file_field']);
		
		if (file_exists($row['filename'])) {
			continue;
		}
		
		$row['table_title'] = make_subtitle('table_title', $row['table_title']);
		
		$row['filename'] = Uploads::getURL($row['filename']);
		$row['id'] = $table_row['id'];
		
		$TmplContent->iterate('/table/', null, $row);	
	}
}


/**
 * 2. Проверка существования записей в БД, соответствующих файлам контента
 */
$index = 0;
$directory = Filesystem::getDirContent(CONTENT_ROOT, false, true, false);
reset($directory); 
while (list(,$dirname) = each($directory)) { 
	$dirname = substr($dirname, 0, -1);
	$table_name = preg_replace('~\..*$~i', '', $dirname);
	$table_name = preg_replace('~/$~i', '', $table_name);
	if ($table_name == 'cms_table') {
		continue;
	}
	
	$query = "SELECT id FROM cms_table WHERE name='$table_name'";
	$table_id = $DB->result($query);
	if ($DB->rows == 0) {
		$TmplContent->iterate('/no_table/', null, array('dirname' => $dirname));
		continue;
	}
	
	// Определяем тип адресации url или id
	$query = "select if(count(*) > 0, 1, 0) from cms_field where table_id='$table_id' and name='url'";
	$is_url = $DB->result($query);
	
	$files = Filesystem::getAllSubdirsContent(CONTENT_ROOT.$dirname, true);
	reset($files);
	while (list($index, $file) = each($files)) {
		// Убираем язык(+3) и расширение файла(-8) .inc.php
		$filename = substr($file, strlen(CONTENT_ROOT.$dirname)+4, -8);
		$query = ($is_url) ?
			"SELECT id FROM `$table_name` WHERE url = '$filename'":
			"SELECT id FROM `$table_name` WHERE id = '".str_replace(DIRECTORY_SEPARATOR, '', $filename)."'";
		$DB->query($query);
		if ($DB->rows == 0) {
			$TmplContent->iterate('/content/', null, array('title' => make_subtitle('dirname', $dirname), 'filename' => $filename, 'file' => $file, 'dirname'=>$dirname));
		}
	}
}
?>