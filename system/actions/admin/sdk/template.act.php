<?php
/** 
 * Осуществляет парсинг шаблонов для того, что б сформировать для них перевод 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

tree(TEMPLATE_ROOT, 0);

// Формируем дерево связей
do {
	$query = "call build_relation ('cms_language_template', 'parent_id', 'cms_language_template_relation', @rows)";
	$DB->query($query);
	
	$query = "select @rows";
	$rows = $DB->result($query);
} while($rows <> 0);

// Добавляем в базу ключи с переводом
$query = "select id from cms_language_template where type='file'";
$data = $DB->fetch_column($query);
reset($data); 
while (list(,$id) = each($data)) {
	parse_translate($id);
}

// Проставляем флаги для шаблонов, у которых нет перевода
$query = "update cms_language_template set translate_en='true', translate_uk='true'";
$DB->update($query);

$query = "
	select 
		template_id,
		if(
			translate_en='' or translate_en is null,
			'false',
			'true'
		) as translate_en,
		if(
			translate_uk='' or translate_uk is null,
			'false',
			'true'
		) as translate_uk
	from cms_language_template_translate
	where 
		translate_en='' or 
		translate_en is null or 
		translate_uk='' or
		translate_uk is null
";
$data = $DB->query($query);
reset($data); 
while (list(,$row) = each($data)) {
	$query = "
		update cms_language_template set 
			translate_uk='$row[translate_uk]', 
			translate_en='$row[translate_en]'
		where id='$row[template_id]'
	"; 
	$DB->update($query);
}






/**
 * Добавляет директории и файлы с шаблонами
 *
 * @param string $path
 * @param int $parent_id
 */
function tree($path, $parent_id) {
	global $DB;
	$insert = array();	
	
	$files = Filesystem::getDirContent($path, false, true, true);
	reset($files); 
	while (list(,$row) = each($files)) {
		$file = $path.$row;
		if (is_dir($file)) {
			$query = "insert ignore into cms_language_template (parent_id, filename, type) values ('$parent_id', '".substr($row, 0, -1)."', 'dir')";
			$last_inserted_id = $DB->insert($query);
			if (empty($last_inserted_id)) {
				$query = "	
					select id 
					from cms_language_template
					where 
						parent_id='$parent_id' and 
						filename='".substr($row, 0, -1)."' and
						type='dir'
				";
				$last_inserted_id = $DB->result($query);
			}
			
			
			// Обрабатываем вложенные директории и файлы
			tree($file, $last_inserted_id);
			
		} elseif (is_file($file) && substr($file, -5) == '.tmpl') {
			$query = "insert ignore into cms_language_template (parent_id, filename, type) values ('$parent_id', '".substr($row, 0, -8)."', 'file')";
			$DB->insert($query);
		}
	}
}

/**
 * Обрабатывает шаблон
 *
 * @param int $id
 */
function parse_translate($id) {
	global $DB;
	
	$query = "
		select group_concat(tb_template.filename order by tb_relation.priority asc separator '/')
		from cms_language_template_relation as tb_relation
		inner join cms_language_template as tb_template on tb_template.id=tb_relation.parent
		where tb_relation.id='$id'
	";
	$file = $DB->result($query);
	$file = TEMPLATE_ROOT . $file .'.ru.tmpl';
	
	$content = file_get_contents($file);
	preg_match_all("/[А-Яа-яё\s,\.\"\-\!\@\(\)]+/", $content, $matches, PREG_OFFSET_CAPTURE);
	$insert = array();
	reset($matches[0]); 
	while (list(,$row) = each($matches[0])) {
		if (!preg_match("/[А-Яа-яё]+/", $row[0])) {
			continue;
		}
		$row[0] = preg_replace("/[\r\n\s]+/", " ", $row[0]);
		$row[0] = preg_replace("/^[\s\n\r\,\!\-\"\@\(\)\.]+/", "", $row[0]);
		$row[0] = preg_replace("/[\s\n\r\,\!\-\"\@\(\)\.]+$/", "", $row[0]);
		$row[0] = trim($row[0]);
		$insert[] = "('$id', '".md5($row[0])."', '".addslashes($row[0])."', '$row[1]')";
	}
	if (!empty($insert)) {
		$query = "
			insert ignore into cms_language_template_translate (template_id,checksum,translate_ru,priority) 
			values ".implode(", ", $insert)."
			on duplicate key update priority=values(priority)
		";
		$DB->insert($query);
	}
}

?>