<?php
/**
 * Копирование разделов структуры сайта
 * @package Pilot
 * @subpackage Site
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
$to_id = globalVar($_REQUEST['to_id'], 0);
$from = globalVar($_REQUEST['id'], array());
$template_id = globalVar($_REQUEST['template_id'], 0);

reset($from);
while (list(,$from_id) = each($from)) {
	copy_structure($from_id, $to_id);
}


function copy_structure($from_id, $to_id) {
	global $DB, $template_id;
	// Определяем url, куда будет копироваться контент
	$query = "select url from site_structure where id='$to_id'";
	$destination_url = $DB->result($query);
	
	$query = "select * from site_structure where id='$from_id'";
	$data = $DB->query($query);
	reset($data);
	while (list(,$row) = each($data)) {
		// Экранируем все значения
		reset($row);
		while (list($key, $val) = each($row)) {
			$row[$key] = $DB->escape($val);
		}
		
		// Изменяем значения парметров на новые
		$src_id = $row['id'];
		$src_url = $row['url'];
		unset($row['id']);
		$row['structure_id'] = $to_id;
		$row['template_id'] = (!empty($template_id)) ? $template_id: $row['template_id'];
		$dst_url = $row['url'] = $destination_url.'/'.$row['uniq_name'];
		// Копирование текущего раздела
		$query = "
			replace into site_structure (`".implode("`,`", array_keys($row))."`) 
			values ('".implode("', '", $row)."')
		";
		$new_id = $DB->insert($query);
				
		$languages = preg_split("/[^a-z]+/", LANGUAGE_ADMIN_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
		reset($languages);
		while (list(,$language_current) = each($languages)) {
			// Копируем шаблон
			$template_src = CONTENT_ROOT.'site_structure/'.strtolower($src_url).'.'.$language_current.'.tmpl';
			$template_dst = CONTENT_ROOT.'site_structure/'.strtolower($dst_url).'.'.$language_current.'.tmpl';
			Filesystem::copy($template_src, $template_dst, true);
			
			// Копируем файл с контентом
			$content_src = CONTENT_ROOT.'site_structure/'.strtolower($src_url).'.'.$language_current.'.php';
			$content_dst = CONTENT_ROOT.'site_structure/'.strtolower($dst_url).'.'.$language_current.'.php';
			Filesystem::copy($content_src, $content_dst, true);
			
			// Копируем картинки
			$uploads_src = UPLOADS_ROOT.'site_structure/'.$language_current.'/'.strtolower($src_url).'/';
			$uploads_dst = UPLOADS_ROOT.'site_structure/'.$language_current.'/'.strtolower($dst_url).'/';
			$uploads = Filesystem::getDirContent($uploads_src, true, false, true);
			reset($uploads);
			while (list(,$src) = each($uploads)) {
				Filesystem::copy($src, $uploads_dst.basename($src));
			}
			
			// Меняем путь к картинкам
			if (is_file($content_dst)) {
				$content = file_get_contents($content_dst);
				$content = str_replace(substr($uploads_src, strlen(SITE_ROOT) -1), substr($uploads_dst, strlen(SITE_ROOT)-1), $content);
				file_put_contents($content_dst, $content);
			}
		}
		
		// Перемещаем дочерние разделы
		$query = "select id from site_structure where structure_id='$src_id'";
		$sub_from = $DB->fetch_column($query);
		reset($sub_from);
		while (list(,$sub_from_id) = each($sub_from)) {
			copy_structure($sub_from_id, $new_id);
		}
	}
} 

//echo "Выбранные разделы - скопированы";
$_RESULT['javascript'] = "$('#structure_copy').jqmHide();";
$_RESULT['action_success'] = "Структура сайта успешно скопирована";
exit;
?>