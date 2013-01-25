<?php
/** 
 * Установка параметров системы 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@id.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */
$module_id = globalVar($_REQUEST['module_id'], 0);
$TmplContent->set('module_id', $module_id);

$query = "select name, description_".LANGUAGE_CURRENT." as description from cms_module where id='$module_id'";
$info = $DB->query_row($query);
$TmplContent->set($info);

$time_unit = array(
	1 => 'секунд',
	60 => 'минут',
	3600 => 'часов',
	86400 => 'дней'
);
$byte_unit = array(
	1 => 'байт',
	1000 => 'Кбайт',
	1000000 => 'Мбайт',
	1000000000 => 'Гбайт'
);
$TmplContent->setGlobal('time_unit', $time_unit);
$TmplContent->setGlobal('byte_unit', $byte_unit);

$query = "
	SELECT
		tb_settings.id,
		tb_settings.name as name,
		tb_settings.value,
		tb_settings.unit,
		tb_settings.enum_values,
		tb_settings.description_".LANGUAGE_CURRENT." AS description,
		tb_settings.type,
		tb_table.name AS `table`,
		tb_field.name AS `field`,
		tb_module.description_ru AS module
	FROM cms_settings AS tb_settings
	INNER JOIN cms_module AS tb_module ON tb_module.id=tb_settings.module_id
	LEFT JOIN cms_table AS tb_table ON tb_table.id=tb_settings.fk_table_id
	LEFT JOIN cms_field AS tb_field ON tb_table.fk_show_id=tb_field.id
	where tb_settings.module_id='$module_id'
	order by tb_settings.priority, tb_module.name, tb_settings.name
";
$data = $DB->query($query);

$prev_module = '';
$prevtabs = '';
reset($data);
while (list($index,$row) = each($data)) {
	if($row['type'] == 'devider') {
		$prev_module = '';
		$row['divname'] = $row['name'];
		$tmpl = $TmplContent->iterate('/capture/', null, $row);
	} else {

		if(($index == 0) && ($row['type'] != 'devider')) {
			$prev_module = '';
			$row['divname'] = 'Главные';
			$tmpl = $TmplContent->iterate('/capture/', null, $row);	
		}	
		
		$row['class'] = ($index % 2) ? 'odd' : 'even';
				
		if ($prev_module != $row['module']) {
			$prev_module = $row['module'];
		} else {
			$row['module'] = '';
		}
		
		if ($row['type'] == 'enum') {
			$tmp = preg_split("/\s*,\s*/", $row['enum_values'], -1, PREG_SPLIT_NO_EMPTY);
			$row['enum_values'] = array();
			reset($tmp);
			while (list($key,$val) = each($tmp)) {
				$row['enum_values'][$val] = $val;
			}
		} elseif ($row['type'] == 'bool') {
			$row['checked'] = ($row['value'] == 1) ? 'checked' : '';
		} elseif ($row['type'] == 'file' && !empty($row['value'])) {
			$file = SITE_ROOT.$row['value'];
			if (is_file($file)) {
				$row['download_file'] = '<a target="_blank" href="/'.$row['value'].'">Посмотреть файл</a><br>';
			}
		} elseif ($row['type'] == 'time') {
			if ($row['value'] % 86400 == 0) {
				$row['time_unit'] = 86400;
				$row['value'] = $row['value'] / 86400;
				
			} elseif ($row['value'] % 3600 == 0) {
				$row['time_unit'] = 3600;
				$row['value'] = $row['value'] / 3600;
				
			} elseif ($row['value'] % 60 == 0) {
				$row['time_unit'] = 60;
				$row['value'] = $row['value'] / 60;
		
			}
			$row['unit'] = '';
		} elseif ($row['type'] == 'byte') {
			if ($row['value'] % 1000000000 == 0) {
				$row['byte_unit'] = 1000000000;
				$row['value'] = $row['value'] / 1000000000;
				
			} elseif ($row['value'] % 1000000 == 0) {
				$row['byte_unit'] = 1000000;
				$row['value'] = $row['value'] / 1000000;
				
			} elseif ($row['value'] % 1000 == 0) {
				$row['byte_unit'] = 1000;
				$row['value'] = $row['value'] / 1000;
				
			}
			$row['unit'] = '';
		} elseif ($row['type'] == 'fkey') {
			$query = "select id, `$row[field]` from `$row[table]` order by `$row[field]` asc";
			$row['fkey'] = $DB->fetch_column($query, 'id', $row['field']);
		} else {
			$row['value'] = htmlspecialchars($row['value']);
		}
		$TmplContent->iterate('/capture/row/', $tmpl, $row);
	}	
}




?>