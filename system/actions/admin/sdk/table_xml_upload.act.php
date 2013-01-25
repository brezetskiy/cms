<?php
/**
 * Создание структуры таблиц из xml файла
 * @package Pilot
 * @subpackage Site
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * Расшифровка строки
 * @param string $content
 * @return string
 */
function deformat($content) {
	$content = str_replace("![CDATA[", "", $content);
	$content = str_replace("]]", "", $content);
	$content = base64_decode($content);
	
	return $content;
}

/**
 * Сведение массива структуры в удобный формат
 * @param array $structure
 * @return void
 */
function set_keys($structure){
	$format = array();
	
	reset($structure);
	while(list($index, $row) = each($structure)){
		$format[$row['_name']] = $row;
	}
	
	return $format;
}


/**
 * Вспомогаетельная функция. В случае неудачи импорта таблиц, удаляет все промежуточные данные.
 * @return void
 */
function roll_back(){
	global $DB;
	
	$DB->query("DROP TABLE IF EXISTS `tmp_cms_table`");
	$DB->query("DROP TABLE IF EXISTS `tmp_cms_field`");
	$DB->query("DROP TABLE IF EXISTS `tmp_cms_field_enum`");	
}


/**
 * Приводит в соответствие %_id поля в таблицах - tmp_cms_table, tmp_cms_field, tmp_cms_field_enum
 * @param array $tables
 * @return void
 */
function mysterious_adjusting($tables){
	global $DB;
	
	reset($tables);
	while(list($id, $table) = each($tables)){
		
		$relation_table_id = 0;
		$parent_field_id   = 0;
		$fk_show_id  = 0;
		$fk_order_id = 0;
		
		if(trim($table['relation_table_id']['_data']) != '') $relation_table_id = $DB->result("SELECT id FROM tmp_cms_table WHERE name = '".$table['relation_table_id']['_data']."'");
		
		if(trim($table['parent_field_id']['_data']) != ''){
			$field_link = explode(' ', $table['parent_field_id']['_data']);
			if(!empty($field_link[0]) && !empty($field_link[1])) $parent_field_id = $DB->result("
				SELECT tb_field.id
				FROM tmp_cms_field as tb_field
				INNER JOIN tmp_cms_table as tb_table ON tb_table.id = tb_field.table_id
				WHERE tb_table.name = '{$field_link[0]}' AND tb_field.name = '{$field_link[1]}'
			");
		}
		
		if(trim($table['fk_show_id']['_data']) != ''){
			$field_link = explode(' ', $table['fk_show_id']['_data']);
			if(!empty($field_link[0]) && !empty($field_link[1])) $fk_show_id = $DB->result("
				SELECT tb_field.id
				FROM tmp_cms_field as tb_field
				INNER JOIN tmp_cms_table as tb_table ON tb_table.id = tb_field.table_id
				WHERE tb_table.name = '{$field_link[0]}' AND tb_field.name = '{$field_link[1]}'
			");
		}
		
		if(trim($table['fk_order_id']['_data']) != ''){
			$field_link = explode(' ', $table['fk_order_id']['_data']);
			if(!empty($field_link[0]) && !empty($field_link[1])) $fk_order_id = $DB->result("
				SELECT tb_field.id
				FROM tmp_cms_field as tb_field
				INNER JOIN tmp_cms_table as tb_table ON tb_table.id = tb_field.table_id
				WHERE tb_table.name = '{$field_link[0]}' AND tb_field.name = '{$field_link[1]}'
			");
		}
		
		$DB->update("
			UPDATE tmp_cms_table 
			SET relation_table_id = '$relation_table_id', 
				parent_field_id = '$parent_field_id', 
				fk_show_id 		= '$fk_show_id', 
				fk_order_id 	= '$fk_order_id' 
			WHERE id = '$id'
		");
		
		/**
		 * Ставим соответсвующие значения и для добавленных записей в таблице tmp_cms_field
		 */
		$fields = $DB->query("SELECT id, tmp_fk_table_id, tmp_fk_link_table_id FROM tmp_cms_field WHERE table_id = '$id'");
		
		reset($fields);  
		while(list(, $field) = each($fields)){
			$fk_table_id = 0;
			$fk_link_table_id = 0;
			
			if(trim($field['tmp_fk_table_id']) != '') $fk_table_id = $DB->result("SELECT id FROM tmp_cms_table WHERE name = '".$field['tmp_fk_table_id']."'");	
			if(trim($field['tmp_fk_link_table_id']) != '') $fk_link_table_id = $DB->result("SELECT id FROM tmp_cms_table WHERE name = '".$field['tmp_fk_link_table_id']."'");	
			$DB->update("UPDATE tmp_cms_field SET fk_table_id = '$fk_table_id', fk_link_table_id = '$fk_link_table_id' WHERE id = '{$field['id']}'");	
		}	
	}
}


/**
 * Сохраняет таблицу
 * @param array $table
 * @return int
 */
function save($table){
	global $DB, $languages; 
	
   	/**
   	 * Заносим данные в базу данных
   	 */
   	$query = "
		INSERT IGNORE INTO tmp_cms_table 
		SET db_id = '".addslashes($table['db_id']['_data'])."',
			name  = '".addslashes($table['name']['_data'])."',"; 
   	 
	reset($languages);
   	while(list(, $lang) = each($languages)){
   		if(isset($table['title_'.$lang])) $query .= "title_$lang = '".addslashes(iconv("utf-8", "windows-1251", $table['title_'.$lang]['_data']))."',";
   	}
   	
//  if(trim($table['_create_dtime']['_data']) == '') $table['_create_dtime']['_data'] = NULL;
	$query .= "		
			interface_id 	 	  = '".addslashes($table['interface_id']['_data'])."',
			module_id 		 	  = '".addslashes($table['module_id']['_data'])."', 
			fk_order_direction 	  = '".addslashes($table['fk_order_direction']['_data'])."',
			use_cvs 	 		  = '".addslashes($table['use_cvs']['_data'])."',	
			is_disabled 	 	  = '".addslashes($table['is_disabled']['_data'])."',
			orm_class_name 	 	  = '".addslashes($table['orm_class_name']['_data'])."',
			_check_failed 	 	  = '".addslashes($table['_check_failed']['_data'])."',
			_table_type 		  = '".addslashes($table['_table_type']['_data'])."',
			_create_dtime 		  = '".addslashes($table['_create_dtime']['_data'])."', 
			_is_real 			  = '".addslashes($table['_is_real']['_data'])."',
			tmp_relation_table_id = '".addslashes($table['relation_table_id']['_data'])."',
			tmp_parent_field_id   = '".addslashes($table['parent_field_id']['_data'])."',
			tmp_fk_show_id 		  = '".addslashes($table['fk_show_id']['_data'])."',
			tmp_fk_order_id 	  = '".addslashes($table['fk_order_id']['_data'])."'
	";
	$table_id = $DB->insert($query);
	
	if(!$table_id){
		roll_back();
		Action::onError("Критическая ошибка: попытка внести дублирующую запись в таблицу tmp_cms_table.");	
	}
	
	if(count($table['cms_fields']['_elements']) == 0) return $table_id;
	
	reset($table['cms_fields']['_elements']);
	while(list($index, $field) = each($table['cms_fields']['_elements'])){ 
		$f_info = set_keys($field['_elements']);	
		
		// Заносим в базу данные о регулярном выражении, что привязано к даному полю
		$regexp_info = set_keys($f_info['regexp']['_elements']);
		$regexp_id   = 0;
		
		if(count($regexp_info) > 0){ 
			$regexp_id = $DB->result("SELECT id FROM cms_regexp WHERE regular_expression = '".deformat($regexp_info['regular_expression']['_data'])."'");
			
			if(empty($regexp_id)){
				$query = "
					INSERT INTO cms_regexp 
					SET regular_expression = '".deformat($regexp_info['regular_expression']['_data'])."',
						uniq_name = '".$regexp_info['uniq_name']['_data']."',";
				
				reset($languages);
			   	while(list(, $lang) = each($languages)){
			   		if(isset($regexp_info['name_'.$lang])) $query .= "name_$lang = '".addslashes(iconv("utf-8", "windows-1251", $regexp_info['name_'.$lang]['_data']))."',";
			   		if(isset($regexp_info['error_message_'.$lang])) $query .= "error_message_$lang = '".addslashes(iconv("utf-8", "windows-1251", $regexp_info['error_message_'.$lang]['_data']))."'";
			   	}
				
			   	$regexp_id = $DB->insert($query);
			} 	
		}
		
		// Заносим поле в базу данных 
		$query = "
			INSERT IGNORE INTO tmp_cms_field 
			SET table_id = '".addslashes($table_id)."',	
				module_id 	 = '".addslashes($f_info['module_id']['_data'])."',
				name 	 	 = '".addslashes($f_info['name']['_data'])."',";
			
		reset($languages);
	   	while(list(, $lang) = each($languages)){
	   		if(isset($f_info['title_'.$lang])) $query .= "title_$lang = '".addslashes(iconv("utf-8", "windows-1251", $f_info['title_'.$lang]['_data']))."',";
	   		if(isset($f_info['comment_'.$lang])) $query .= "comment_$lang = '".addslashes(iconv("utf-8", "windows-1251", $f_info['comment_'.$lang]['_data']))."',";
	   	}

	   	$query .= "
	   			field_type 		 	 = '".addslashes($f_info['field_type']['_data'])."',
				tmp_fk_table_id 	 = '".addslashes($f_info['fk_table_id']['_data'])."',
				tmp_fk_link_table_id = '".addslashes($f_info['fk_link_table_id']['_data'])."',
				regexp_id 			 = '".$regexp_id."',
				priority 	 	 	 = '".addslashes($f_info['priority']['_data'])."',
				stick 	 	 		 = '".addslashes($f_info['stick']['_data'])."',
				group_edit 	 	 	 = '".addslashes($f_info['group_edit']['_data'])."',
				show_in_filter 	 	 = '".addslashes($f_info['show_in_filter']['_data'])."',
				is_reference 	 	 = '".addslashes($f_info['is_reference']['_data'])."',
				is_obligatory 	 	 = '".addslashes($f_info['is_obligatory']['_data'])."',
				_column_default 	 = '".addslashes($f_info['_column_default']['_data'])."',
				_is_nullable 	 	 = '".addslashes($f_info['_is_nullable']['_data'])."',
				_ordinal_position 	 = '".addslashes($f_info['_ordinal_position']['_data'])."',
				_data_type 	 	 	 = '".addslashes($f_info['_data_type']['_data'])."',
				_column_type 	 	 = '".addslashes($f_info['_column_type']['_data'])."',
				_max_length 	 	 = '".addslashes($f_info['_max_length']['_data'])."',
				_is_multilanguage 	 = '".addslashes($f_info['_is_multilanguage']['_data'])."',
				_is_real 	 	 	 = '".addslashes($f_info['_is_real']['_data'])."'
		";

		$field_id = $DB->insert($query);

		if(!$field_id){
			roll_back();
			Action::onError("Критическая ошибка: попытка внести дублирующую запись в таблицу tmp_cms_field.");	
		}	
		
		if(count($f_info['cms_field_enums']['_elements']) == 0) continue;
		
		reset($f_info['cms_field_enums']['_elements']);
		while(list($index, $enum) = each($f_info['cms_field_enums']['_elements'])){ 
			$e_info = set_keys($enum['_elements']);	
			
			// Заносим значения enum поля, если оно таковым является в базу данных
			$query = "
				INSERT IGNORE INTO tmp_cms_field_enum 
				SET field_id = '".addslashes($field_id)."',	
					name 	 	 = '".addslashes($e_info['name']['_data'])."',
					priority 	 = '".addslashes($e_info['priority']['_data'])."',
					_is_real  	 = '".addslashes($e_info['_is_real']['_data'])."',
			";
				    
			reset($languages); 
		   	while(list(, $lang) = each($languages)){
		   		if(isset($e_info['title_'.$lang])) $query .= "title_$lang = '".addslashes(iconv("utf-8", "windows-1251", $e_info['title_'.$lang]['_data']))."'";
		   	}
		   	
			$enum_id = $DB->insert($query);	
			
			if(!$enum_id){
				roll_back();
				Action::onError("Критическая ошибка: попытка внести дублирующую запись в таблицу tmp_cms_field_enum.");	
			}	
		}	
	}
	
	return $table_id;
}


/*-------------------------------------------------------------------------------------*/


$languages = explode(",", LANGUAGE_AVAILABLE);
$file 	= $_FILES['table']['tmp_name'];
$tables = array();

if (!file_exists($file)) Action::onError("Ошибка: файл '$_FILES[table][tmp_name]' не существует.");


$parser = new XMLToArray();
$xml = $parser->parseXML(file_get_contents($file));
$root = $xml[0];


/**
 * Удаляем старые временные таблицы, если они все еще не удалены
 */
roll_back();


/**
 * Создаем временную копию таблицы cms_table
 */
$DB->query("CREATE TABLE tmp_cms_table LIKE cms_table");
$DB->query("INSERT INTO tmp_cms_table SELECT * FROM cms_table");


/**
 * Добавляем временные поля
 */
$DB->query("
	ALTER TABLE tmp_cms_table
	ADD `tmp_relation_table_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL,
	ADD `tmp_parent_field_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL,
	ADD `tmp_fk_show_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL,
	ADD `tmp_fk_order_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL
");


/**
 * Создаем временную копию таблицы cms_field
 */
$DB->query("CREATE TABLE tmp_cms_field LIKE cms_field");
$DB->query("INSERT INTO tmp_cms_field SELECT * FROM cms_field");


/**
 * Добавляем временные поля
 */
$DB->query("
	ALTER TABLE `tmp_cms_field` 
	ADD `tmp_fk_table_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL,
	ADD `tmp_fk_link_table_id` VARCHAR( 250 ) CHARACTER SET cp1251 COLLATE cp1251_ukrainian_ci NULL
");


/**
 * Создаем временную копию таблицы cms_field_enum 
 */
$DB->query("CREATE TABLE tmp_cms_field_enum LIKE cms_field_enum");
$DB->query("INSERT INTO tmp_cms_field_enum SELECT * FROM cms_field_enum");


/**
 * Сохраняем таблицы и изменяем соответствующие поля
 */
reset($root);
while(list($index, $table) = each($root['_elements'])){ 
	$t_info   = set_keys($table['_elements']);
	$table_id = save($t_info);
	$tables[$table_id] = $t_info;
}


/**
 * Обновление связей
 */
mysterious_adjusting($tables);
 

/**
 * Удаляем временные поля
 */
$DB->query("ALTER TABLE `tmp_cms_table` DROP COLUMN `tmp_relation_table_id`, DROP COLUMN `tmp_parent_field_id`, DROP COLUMN `tmp_fk_show_id`, DROP COLUMN `tmp_fk_order_id`");
$DB->query("ALTER TABLE `tmp_cms_field` DROP COLUMN `tmp_fk_table_id`, DROP COLUMN `tmp_fk_link_table_id`");
$DB->query("ALTER TABLE `cms_table` RENAME TO _cms_table");
$DB->query("ALTER TABLE `tmp_cms_table` RENAME TO cms_table");
$DB->query("ALTER TABLE `cms_field` RENAME TO _cms_field");
$DB->query("ALTER TABLE `tmp_cms_field` RENAME TO cms_field");
$DB->query("ALTER TABLE `cms_field_enum` RENAME TO _cms_field_enum");
$DB->query("ALTER TABLE `tmp_cms_field_enum` RENAME TO cms_field_enum"); 
 
Action::setSuccess("Таблицы успешно загружены. Пожалуйста, назначьте таблицам соответствующие модули и скопируйте с сайта-источника скрипты-триггеры."); 

?>