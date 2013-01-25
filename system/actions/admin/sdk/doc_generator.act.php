<?php
/**
 * создание документации по программе
 * @package Pilot
 * @subpackage SDK
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$query = "truncate table sdk_doc_class";
$DB->delete($query);
$query = "truncate table sdk_doc_function";
$DB->delete($query);
$query = "truncate table sdk_doc_argument";
$DB->delete($query);

$files = Filesystem::getAllSubdirsContent(SITE_ROOT.'system/libs/', true);
reset($files);
while (list(,$file) = each($files)) {
	$php = file_get_contents($file);
	preg_match("~/\*\*(.+)\*/[\s\n\r\t]+class\s+([\w\d]+)[\s{]~ismU", $php, $matches);
	if (empty($matches)) {
		continue;
	}
	
	$class = parse_comment($matches[1]);
	if (!isset($class['copyright']) || stristr($class['copyright'], 'Delta-X') === false) {
//		echo "<font color=red><b>$matches[2]</b> ($file)</font><br>";
		continue;
	}
//	echo "<b>$matches[2]</b> ($file)<br>";
	if (empty($class['version'])){
		$class['version'] = '';
	}
	$query = "
		insert into sdk_doc_class set
			name='$matches[2]',
			package='$class[package]',
			subpackage='$class[subpackage]',
			version='$class[version]',
			author='$class[author]',
			copyright='$class[copyright]',
			description='".addslashes($class['description'])."'
	";
	$class_id = $DB->insert($query);
	
	// Методы класса
	preg_match_all("~/\*\*([^/]+)\*/[\s\n\r\t]+([\w\s\t]+)function\s+([\d\w_\s]+)\(([^\)]+)\)\s*\{~isU", $php, $matches);
	reset($matches[1]);
	while (list($index,$row) = each($matches[1])) {
		$param = parse_comment($row);
		$param['function'] = trim($matches[3][$index]);
		$param['scope'] = implode(",", preg_split("/[\s\n\r\t]+/", $matches[2][$index], -1, PREG_SPLIT_NO_EMPTY));
		
		$argv = preg_split("/,/", $matches[4][$index], -1, PREG_SPLIT_NO_EMPTY);
		reset($argv);
		while (list($index, $row) = each($argv)) {
			$row = trim($row);
			$link = (strpos($row, '&')) ? 1 : 0;
			if (preg_match("/^\W+([\w\d_]+)\s*=\s*(.+)$/", $row, $mvar)) {
				$name = $mvar[1];
				$default = "'".str_replace(array('"', "'"), '', $mvar[2])."'";
			} else {
				$default = 'NULL';
				$name = str_replace(array('$', '&'), '', $row);
			}
			
			$param['param'][$name]['priority'] = $index;
			$param['param'][$name]['error'] = 1;
			if (isset($param['param'][$name])) {
				$param['param'][$name]['default'] = $default;
				$param['param'][$name]['link'] = $link;
				$param['param'][$name]['error'] = 0;
			}
		}
		if (empty($param['return'])) {
			$param['return'] = '';
		}
		$query = "
			insert into sdk_doc_function set
				class_id='$class_id',
				name='$param[function]',
				description='".addslashes($param['description'])."',
				`return`='$param[return]',
				scope='$param[scope]'
		";
		$function_id = $DB->insert($query);
		
		reset($param['param']);
		while (list($name, $row) = each($param['param'])) {
			if (!isset($row['type'])) {
				$row['type'] = 'undefined';
			}
			if (!isset($row['description'])) {
				$row['description'] = '';
			}
			if (!isset($row['default'])) {
				$row['default'] = 'NULL';
			}
			if (!isset($row['link'])) {
				$row['link'] = 0;
			}
			if (!isset($row['error'])) {
				$row['error'] = 0;
			}
			$query = "
				insert into sdk_doc_argument set
					function_id='$function_id',
					name='$name',
					type='$row[type]',
					description='".addcslashes($row['description'], "'")." ',
					`default`=$row[default],
					link='$row[link]',
					error='$row[error]'
			";
			$DB->insert($query);
		}
	}
}


function parse_comment($comment) {
	$comment = str_replace('*', '', $comment);
	
	// Описание
	preg_match("/^(.+)@/ismU", $comment, $matches);
	$description = (isset($matches[1])) ? trim($matches[1]): trim($comment);
	
	// Параметры
	if(preg_match_all("/@(\w+)\s+([^\n\r]+)[\n\r]+/ismU", $comment, $matches)) {
		$return = array_combine($matches[1], $matches[2]);
		$return['param'] = array();
		reset($matches[1]);
		while (list($index,$value) = each($matches[1])) {
			if ($value == 'param') {
				preg_match("/^(\w+)\s+([a-z0-9_\$]+)(.*)$/i", $matches[2][$index], $m);
				if (empty($m)) {
					continue;
				}
				$var = str_replace('$', '', $m[2]);
				$return['param'][$var]['type'] = $m[1];
				$return['param'][$var]['name'] = $var;
				$return['param'][$var]['description'] = trim($m[3]);
			}
		}
		if (isset($return['param']) && empty($return['param'])) {
			unset($return['param']);
		}
	}	
	$return['description'] = preg_replace("/\s+/", ' ', $description);
	return $return;
}