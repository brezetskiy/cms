<?php
/** 
 * Экспорт структуры базы данных 
 * @package Pilot 
 * @subpackage SDK 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$db_id = globalVar($_POST['db_id'], 0);
$export_tables = globalVar($_POST['export_tables'], '');
$export_triggers = globalVar($_POST['export_triggers'], '');
$export_procedures = globalVar($_POST['export_procedures'], '');
$export_functions = globalVar($_POST['export_functions'], '');
$drop_table = globalVar($_POST['drop_table'], '');
$drop_table = globalVar($_POST['drop_table'], '');
$if_not_exists = globalVar($_POST['if_not_exists'], '');
$replace_view = globalVar($_POST['replace_view'], '');
$result = globalVar($_POST['result'], '');

$query = "select * from cms_db where id = '$db_id' and type = 'mysqli'";
$db_row = $DB->query_row($query);

if ($DB->rows == 0) {
	Action::onError('Выберите базу данных для экспорта');
}

$sql = '';
$sql_delayed = '';

function add_query($query, $delayed = false) {
	global $sql, $sql_delayed, $result;
	
	if ($delayed) {
		$holder = &$sql_delayed;
	} else {
		$holder = &$sql;
	}
	
	if ($result == 'save') {
		$holder .= $query."\n\n";
	} else {
		$geshi = new GeSHi($query, 'SQL');
		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS); 
		$geshi->set_keyword_group_style(1, 'color: blue;', true); 
		$geshi->set_overall_style('color: blue;', true); 
		$holder .= $geshi->parse_code()."<br><br>"; 
	}
}

$DBServer = DB::factory($db_row['alias']);

/**
 * 1. Таблицы
 */
if (!empty($export_tables)) {
	$tables = $DBServer->query("show full tables");
	
	reset($tables); 
	while (list(,$row) = each($tables)) { 
		$table_name = $row['tables_in_'.$db_row['name']];
		
		if ($row['table_type']=='BASE TABLE') {
			
			if (!empty($drop_table)) {
				add_query("DROP TABLE IF EXISTS `$table_name`;");
			}
			
		 	$create_stmt = $DB->query_row("show create table `$table_name`");
		 	$create_stmt = $create_stmt['create table'];
		 	
		 	if (!empty($if_not_exists)) {
		 		$create_stmt = preg_replace('~^create table~i', 'CREATE TABLE IF NOT EXISTS ', $create_stmt);
		 	}
		 	
		 	add_query($create_stmt.";");
		} elseif ($row['table_type']=='VIEW') {
			
			if (!empty($drop_table)) {
				add_query("DROP VIEW IF EXISTS `$table_name`;", true);
			}
			
			$create_stmt = $DB->query_row("show create view `$table_name`");
			$create_stmt = $create_stmt['create view'];
			$create_stmt = preg_replace('~definer=[^\s\t\r\n]+~i', '', $create_stmt);
			
			if (!empty($replace_view)) {
				$create_stmt = preg_replace('~^create~i', 'CREATE OR REPLACE ', $create_stmt);
			}
			
		 	add_query($create_stmt.";", true);
		}
	}
}

/**
 * 2. Триггеры
 */
if (!empty($export_triggers)) {
	$triggers = $DBServer->query("show triggers");
	reset($triggers); 
	while (list(,$row) = each($triggers)) {
		
		if ($drop_table) {
			add_query("DROP TRIGGER IF EXISTS `$row[trigger]`;");
		}
		
$query = "
DELIMITER |
CREATE TRIGGER 
$row[trigger] $row[timing] $row[event] 
ON $row[table] FOR EACH ROW $row[statement];
|
DELIMITER ;
";
		add_query($query);
	}
}

/**
 * 3. Процедуры
 */
if (!empty($export_procedures)) {
	$procedures = $DBServer->query("
		select *
		from mysql.proc 
		where type = 'PROCEDURE' and db=database()
	");
	reset($procedures); 
	while (list(,$row) = each($procedures)) { 
		
		if ($drop_table) {
			add_query("DROP PROCEDURE IF EXISTS `$row[name]`;");
		}
	
$query = "
DELIMITER |
CREATE PROCEDURE $row[name] ($row[param_list])
".($row['is_deterministic']=='NO' ? 'NOT' : '')." DETERMINISTIC
SQL SECURITY $row[security_type]
COMMENT '$row[comment]'
$row[body]
|
DELIMITER ;
";
		add_query($query);
	}
}

/**
 * 4. Функции
 */
if (!empty($export_functions)) {
	$functions = $DBServer->query("
		select *
		from mysql.proc 
		where type = 'FUNCTION' and db=database()
	");
	reset($functions); 
	while (list(,$row) = each($functions)) { 
		
		if ($drop_table) {
			add_query("DROP FUNCTION IF EXISTS `$row[name]`;");
		}
		
		$query = "
DELIMITER |
CREATE FUNCTION $row[name] ($row[param_list]) RETURNS $row[returns]
".($row['is_deterministic']=='NO' ? 'NOT' : '')." DETERMINISTIC
SQL SECURITY $row[security_type]
COMMENT '$row[comment]'
$row[body]
|
DELIMITER ;
		";
		add_query($query);
	}
}

if ($result == 'save') {
	// посылаем заголовки для сохранения файла
	header('Content-Type: text/x-sql');
	header('Content-Disposition: attachment; filename="'.$db_row['name'].'_structure_'.date('Y_m_d').'.sql"');
}

echo $sql.$sql_delayed;
exit;

?>