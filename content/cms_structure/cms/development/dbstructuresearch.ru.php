<?php
/** 
 * Поиск по регулярному выражению в структуре таблиц, триггеров, процедур и функций 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */

define('DB_ALIAS', strtoupper(globalVar($_REQUEST['db_alias'], '')));
$search_text = globalVar($_REQUEST['search_text'], '/test/i');

$last = strrpos($search_text, substr($search_text, 0, 1)) - 1;
$higliht_regexp = substr($search_text, 0, 1).'('.substr($search_text, 1, $last).')'.substr($search_text, $last + 1);
$go = globalVar($_REQUEST['go'], 'false');

$default_checkbox = ($go == 'true' ? 'false' : 'true'); 

$search_table = globalVar($_REQUEST['search_table'], $default_checkbox);
$search_trigger = globalVar($_REQUEST['search_trigger'], $default_checkbox);
$search_routine = globalVar($_REQUEST['search_routine'], $default_checkbox);

$TmplContent->set('search_table_checked', ($search_table == 'true' ? 'checked' : ''));
$TmplContent->set('search_trigger_checked', ($search_trigger == 'true' ? 'checked' : ''));
$TmplContent->set('search_routine_checked', ($search_routine == 'true' ? 'checked' : ''));
$TmplContent->set('search_text', htmlentities($search_text, ENT_COMPAT, CMS_CHARSET));

// Определяем все существующие соединения
$connections = $DB->fetch_column("select alias from cms_db", 'alias', 'alias');
reset($connections);
while(list($alias, ) = each($connections)){
	$connections[$alias] = db_config_constant("type", $alias)."->".db_config_constant("host", $alias)."->".db_config_constant("name", $alias);
}


$TmplContent->set('connections', $connections);

if ($go == 'true') {
	
	$DBServer = DB::factory(DB_ALIAS);
	
	$found['table'] = $found['trigger'] = $found['routine'] = false;
	
	/**
	 * 1. Структура таблиц
	 */
	if ($search_table == 'true') {
		$query = "SHOW TABLES FROM ".constant('DB_'.DB_ALIAS.'_NAME');
		$tables = $DBServer->fetch_column($query);
		
		reset($tables); 
		while (list(,$table_name) = each($tables)) { 
			$query = "SHOW CREATE TABLE `$table_name`";
			$table_def = $DBServer->query_row($query);
			
			$haystack = (isset($table_def['create view']) ? $table_def['create view'] : $table_def['create table']); 
			
			if (preg_match($search_text, $haystack)) {
				$found['table'] = 'true';
				$geshi = new GeSHi($haystack, 'SQL');
				$def = preg_replace($higliht_regexp, "<span style='background-color:red; color:white;font-weight:bold;'>$1</span>", $geshi->parse_code());
				// Для View делаем Word_Wrap
				$def = (isset($table_def['create view']) ? wordwrap($def, 50) : $def);
				$TmplContent->iterate('/result_table/', null, array('name' => $table_name, 'def' => $def));
			}
		}
	}
	
	/**
	 * 2. Триггеры
	 */
	if ($search_trigger == 'true') {
		$query = "SHOW TRIGGERS FROM ".constant('DB_'.DB_ALIAS.'_NAME');
		$triggers = $DBServer->query($query);
		
		reset($triggers); 
		while (list(,$trigger) = each($triggers)) { 
			if (preg_match($search_text, $trigger['statement'])) {
				$found['trigger'] = 'true';
				$geshi = new GeSHi($trigger['statement'], 'SQL');
				$trigger['def'] = preg_replace($higliht_regexp, "<span style='background-color:red; color:white;font-weight:bold;'>$1</span>", $geshi->parse_code()); 
				$TmplContent->iterate('/result_trigger/', null, $trigger);
			}
		}
	}

	/**
	 * 3. Процедуры и функции
	 */
	if ($search_routine == 'true') {
		$query = "SELECT * FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA = '".constant('DB_'.DB_ALIAS.'_NAME')."'";
		$routines = $DBServer->query($query, 'routine_name');
		
		reset($routines); 
		while (list(,$routine) = each($routines)) { 
			$routine['routine_data'] = $DBServer->query_row("SHOW CREATE $routine[routine_type] `".constant('DB_'.DB_ALIAS.'_NAME')."`.$routine[routine_name]");
			$routine['routine_definition'] = $routine['routine_data'][strtolower("create $routine[routine_type]")];
			
			if (preg_match($search_text, $routine['routine_definition'])) {
				$found['routine'] = 'true';
				$geshi = new GeSHi($routine['routine_definition'], 'SQL');
				$routine['def'] = preg_replace($higliht_regexp, "<span style='background-color:red; color:white;font-weight:bold;'>$1</span>", $geshi->parse_code()); 
				$TmplContent->iterate('/result_routine/', null, $routine);
			}
			
		}
	}
	
	$TmplContent->set('found', $found);
	
}

?>