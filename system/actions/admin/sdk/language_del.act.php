<?php
/**
* Удаляет колонки для языков, которые удалены
* @package Pilot
* @subpackage Actions_Admin
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

$interface = globalVar($_GET['interface'], 0);

/**
* Определяем название интерфейса
*/
$query = "SELECT name FROM cms_interface WHERE id='".$interface."'";
$interface_name = $DB->result($query);
if ($DB->rows != 1) {
	Action::setError(cms_message('CMS', 'Указанный интерфейс отсутствует в системе'));
	Action::onError();
}


/**
* Выбираем все БД и все таблицы в них, которые будут обработаны
*/
$query = "
	SELECT
		tb_table.id AS table_id,
		tb_table.name AS table_name,
		tb_db.alias AS db_alias,
		tb_db.id AS db_id
	FROM cms_table AS tb_table
	INNER JOIN cms_db AS tb_db ON tb_db.id = tb_table.db_id
	WHERE tb_table.interface_id = '".$interface."'
";
$tables = $DB->query($query);


$DBServer = array();
$counter = 0;

$available_languages = preg_split('/[^a-z]+/', constant('LANGUAGE_'.$interface_name.'_AVAILABLE'), -1, PREG_SPLIT_NO_EMPTY);

$updated_fields = array();

reset($tables);
while (list(, $table_data) = each($tables)) {
	$table_data['db_name'] = db_config_constant("name", $table_data['db_alias']); 
	
	/**
	* Соединение с БД, в которой будут проводится изменения
	*/
	$currentDB = DB::factory($table_data['db_alias']);
	
	/**
	* Игнорируем таблицы, которые не существуют
	*/
	$query = "SHOW TABLES FROM `".$table_data['db_name']."` LIKE '".$table_data['table_name']."'";
	$currentDB->query($query);
	if ($currentDB->rows == 0) {
		continue;
	}
	
	/**
	* Обрабатываем каждую колонку на наличие многоязычности
	*/
	$query = "SHOW COLUMNS FROM `".$table_data['db_name']."`.`".$table_data['table_name']."`";
	$fields = $currentDB->query($query, 'field');
	
	// Колонки, которые будут удалены
	$delete = array();
	
	/**
	* Определяем названия многоязычных колонок
	*/
	$multilanguage = array();
	reset($fields);
	while (list($field, ) = each($fields)) {
		if (!preg_match('/(.+)_('.constant('LANGUAGE_REGEXP').')$/', $field, $matches)) {
			continue;
		}
		
		$multilanguage[$matches[1]] = $matches[1];
	}
	
	/**
	* Определяем названия колонок, которые необходимо удалить
	*/
	reset($fields);
	while (list($field, ) = each($fields)) {
		if (!preg_match('/(.+)_([a-z]{2})$/', $field, $matches)) {
			continue;
		}
		
		if (!isset($multilanguage[$matches[1]]) || in_array($matches[2], $available_languages)) {
			continue;
		}
		
		// Удаляем колонку
		$query = "ALTER TABLE `".$table_data['table_name']."` DROP COLUMN ".$field;
		$DB->update($query);

		$updated_fields[] = array(
			'field_name' => $field,
			'table_id' => $table_data['table_id']
		);
		
		$counter++;
	}
}

Action::setLog(cms_message('CMS', 'Из таблиц удалено %d колонок', $counter));
?>