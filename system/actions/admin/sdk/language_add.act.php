<?php
/**
* Устанавливает поддержку многоязычности для таблиц
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
$query = "SELECT name FROM cms_interface WHERE id='$interface'";
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

// Вновь созданные поля
$new_fields = array();

reset($tables);
while (list(, $table_data) = each($tables)) {
	$table_data['db_name'] = db_config_constant("name", $table_data['db_alias']);  
	
	/**
	* Хранит поля, без названия языка, для того, чтоб по 
	* несколько раз не добавлять языковые колонки
	*/
	$updated_fields = array();
	
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
	$query = "SHOW COLUMNS FROM `$table_data[db_name]`.`$table_data[table_name]`";
	$fields = $currentDB->query($query, 'field');
	
	reset($fields);
	while (list($field, $field_type) = each($fields)) {
		
		/**
		* Работаем только с многоязычными столбцами
		*
		* Результаты выполнения REGEXP'а:
		* $matches = Array (
		*	[0] => name_ru
		*	[1] => name
		*	[2] => ru
		* )
		*/
		if (!preg_match('/(.+)_('.constant('LANGUAGE_REGEXP').')$/', $field, $matches)) {
			continue;
		}
		
		/**
		* Игнорируем поля, которые уже обновлены,
		* так к примеру, когда у нас есть уже 2 языка с колонками name_ru, name_en,
		* то программа попробует два раза вставить новый язык, что создаст ошибу
		*/
		if (isset($updated_fields[$matches[1]])) {
			continue;
		}
		
		$field_type['null'] = ($field_type['null'] == 'YES') ? 'NULL' : 'NOT NULL';
		$field_type['default'] = (empty($field_type['default'])) ? '' : "DEFAULT '".addcslashes($field_type['default'], "'")."'";
		
		
		reset($available_languages);
		while (list(, $language_current) = each($available_languages)) {
			
			/**
			* Если колонки с данным языком - не существует, добавляем ее
			*/
			if (!isset($fields[$matches[1].'_'.$language_current])) {
				$query = "
					ALTER TABLE `".$table_data['table_name']."` 
					ADD COLUMN ".$matches[1]."_".$language_current." ".$field_type['type']."
					".$field_type['null']."
					".$field_type['default']."
					AFTER ".$matches[0];
				$DB->update($query);
				
				$updated_fields[$matches[1]] = '';
				
				$new_fields[] = array(
					'field_name' => $matches[1],
					'language' => $language_current,
					'table_id' => $table_data['table_id']
				);
				
				$counter++;
			}
		}
	}
}


Action::setLog(cms_message('CMS', 'В таблицы было успешно добавлено %d колонок.', $counter));
?>