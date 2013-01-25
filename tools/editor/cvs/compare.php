<?php
/**
* Фрейм с контентом, который отображается в SiteWerk
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/


/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

/**
* Типизируем переменные
*/
$field_name = globalVar($_GET['field_name'], '');
$compare = globalVar($_GET['compare'], array());

$query = "
	SELECT 
		tb_log.content, 
		tb_log.table_name, 
		tb_log.field_name,
		DATE_FORMAT(tb_log.dtime, '".LANGUAGE_DATE_SQL." %H:%i:%s') AS dtime,
		tb_log.edit_id,
		tb_log.id
	FROM cvs_log AS tb_log
	LEFT JOIN auth_user AS tb_user ON tb_user.id=tb_log.admin_id
	WHERE tb_log.id IN (0".implode(",", $compare).")
	ORDER BY dtime ASC
";
$info = $DB->query($query);
if ($DB->rows != 2) {
	echo 'Сравнивать можно только две версии документа';
	exit;
}

/**
* Проверка прав редактирования таблицы пользователем
*/
reset($info);
while(list(,$row) = each($info)) {
	if (!Auth::editContent($row['table_name'], $row['edit_id'])) {
		echo 'У Вас нет прав на сравнение этих страниц';
		exit;
	}
}

$source_file = TMP_ROOT.uniqid('cvs_src_');
$destination_file = TMP_ROOT.uniqid('cvs_dst_');
file_put_contents($source_file, $info[0]['content']);
file_put_contents($destination_file, $info[1]['content']);
if (0 && ini_get('safe_mode') == 0) {
	exec('diff '.$source_file.' '.$destination_file, $output);
} else {
	$Download = new Download();
	$output = $Download->post('http://tools.delta-x.com.ua/diff.php', array('file1'=>$info[0]['content'], 'file2' => $info[1]['content']));
	$output = unserialize($output);
}

/**
* Выводим текст
*/
$source_content = file($source_file);
array_unshift($source_content, '');
unlink($source_file);

$destination_content = file($destination_file);
array_unshift($destination_content, '');
unlink($destination_file);


$change = $delete = $add = array();
reset($output);
while(list(,$row) = each($output)) {
	$tmp = array();
	if (preg_match("/([0-9,]+)([acd])([0-9,]+)/", $row, $matches)) {
		
		// Определяем строки
		$tmp = preg_split("/,/", $matches[3], -1, PREG_SPLIT_NO_EMPTY);
		
		if ($matches[2] == 'c') {
			// Change
			if (count($tmp) > 1) {
				$change[] = $tmp;
			} else {
				$change[] = $tmp[0];
			}
		} elseif ($matches[2] == 'd') {
			// Delete
			$delete[$matches[3]] = $matches[1];
			
		} elseif ($matches[2] == 'a') {
			// Add
			if (count($tmp) > 1) {
				$add[] = $tmp;
			} else {
				$add[] = $tmp[0];
			}
		}
	} elseif (preg_match("/^\\\\/", $row)) {
		continue;
	}
}

/**
* Подсвечиваем измененный текст
*/
reset($change);
while(list($index,) = each($change)) {
	if (is_array($change[$index])) {
		// Изменения охватывают несколько строк
		$destination_content[ $change[$index][0] ] = '<FONT class="change">'.$destination_content[ $change[$index][0] ];
		$destination_content[ $change[$index][1] ] .= '</FONT>';
	} else {
		// Изменение касается 1-й строки
		$destination_content[ $change[$index] ] = '<FONT class="change">'.$destination_content[ $change[$index] ].'</FONT>';
	}
}

/**
* Подсвечиваем вставленный текст
*/
reset($add);
while(list($index,) = each($add)) {
	if (is_array($add[$index])) {
		// Изменения охватывают несколько строк
		$destination_content[ $add[$index][0] ] = '<FONT class="add">'.$destination_content[ $add[$index][0] ];
		$destination_content[ $add[$index][1] ] .= '</FONT>';
	} else {
		// Изменение касается 1-й строки
		$destination_content[ $add[$index] ] = '<FONT class="add">'.$destination_content[ $add[$index] ].'</FONT>';
	}
}


/**
* Подсвечиваем удаленный текст
*/
reset($delete);
while(list($line, $deleted_lines) = each($delete)) {
	if (!isset($destination_content[$line])) {
		// не знаю насколько это правильно но иногда если нет последней строки то оно глючит
		continue;
	}
	// Определяем строки
	$tmp = preg_split("/,/", $deleted_lines, -1, PREG_SPLIT_NO_EMPTY);
	if (count($tmp) > 1) {
		// Изменения охватывают несколько строк
		$destination_content[$line] = '<FONT class="delete">'.get_lines($tmp[0], $tmp[1], $source_content).'</FONT>'.$destination_content[$line];
	} else {
		// Изменение касается 1-й строки
		$destination_content[$line] = '<FONT class="delete">'.$source_content[$deleted_lines].'</FONT>'.$destination_content[$line];
	}
}


/**
 * Вывод сравнения
 */
echo '<HTML>
<HEAD>
	<title>Сравнение изменений в документе от '.$info[0]['dtime'].' с документом от '.$info[1]['dtime'].'</title>
	<style>
		BODY {
			font: 12px Verdana, Geneva, Arial;
		}
		DIV {
			border: 3px double gray;
			margin-bottom: 10px;
			padding: 10px 10px 10px 10px;
		}
		FONT.delete, DIV.delete {
			background: #F9D6CE;
		}
		FONT.add, DIV.add {
			background: #CEE3F9;
		}
		FONT.change, DIV.change {
			background: #D8F9CE;
		}
		H2 {
			color: #CCCCCC;
		}
	</style>
<BODY>';
echo '<h2>Изменения в тексте:</h2>';
echo implode($destination_content);
echo '<h2>Список изменений:</h2>';
$counter = 0;
reset($output);
while(list(,$row) = each($output)) {
	if (preg_match("/[0-9,]+([acd])[0-9,]+/", $row, $matches)) {
		$counter++;
		if ($counter != 1) {
			echo '</DIV>';
		}
		switch ($matches[1]) {
			case 'a':
				echo '<DIV class="add">';
				break;
			case 'c':
				echo '<DIV class="change">';
				break;
			case 'd':
				echo '<DIV class="delete">';
				break;
		}
	} elseif (preg_match("/^\\\\/", $row)) {
		continue;
	} elseif (preg_match("/^[\-]+$/", $row)) {
		echo '<HR style="border-top:1px dotted #CCCCCC;border-bottom:1px solid #D8F9CE;">';
	} else {
		echo "\n".htmlspecialchars($row)."<br>";
	}
}
echo '</DIV>';
echo '<h2>DIFF:</h2>';
echo '<DIV><pre>'.htmlspecialchars(implode("\n", $output), ENT_COMPAT, LANGUAGE_CHARSET).'</pre></DIV>';
echo '</BODY>
</HTML>';




function sequence($min, $max) {
	if ($min > $max) {
		$tmp_max = $min;
		$min = $max;
		$max = $tmp_max;
	}
	$return = array();
	for ($i=$min;$i <= $max; $i++) {
		$return[] = $i;
	}
	return $return;
}

function get_lines($min, $max, $array) {
	$sequence = sequence($min, $max);
	$result = '';
	reset($sequence);
	while(list(,$line) = each($sequence)) {
		$result .= $array[$line];
	}
	return $result;
}

?>