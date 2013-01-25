<?php
/**
* Класс экспорта данных из таблиц
* @package Pilot
* @subpackage SDK
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X ltd, 2005
*/

/**
* Класс экспорта данных из таблиц
* @package Database
* @subpackage SDK
*/
class SQLExport {
	
	/**
	 * Экспорт данных
	 *
	 * @param int $table_id
	 * @param int $parent_id
	 * @param array $export_id
	 */
	static function export2xml($table_id, $parent_id, $export_tables = array(), $export_id = 0) {
		global $DB;
		
		// Обрабатываются только те таблицы, которые есть в export_tables,
		// если этот массив не пустой
		if (!empty($export_tables) && !in_array($table_id, $export_tables)) {
			return;
		}
		
		// Определяем имя таблицы по ее id
		$query = "
			SELECT 
				tb_table.name AS table_name,
				(SELECT name FROM cms_field WHERE id=parent_field_id) AS parent_field
			FROM cms_table AS tb_table
			WHERE tb_table.id='".$table_id."'
		";
		$table_info = $DB->query_row($query);
		if ($DB->rows == 0) {
			Action::onError(cms_message('CMS', 'Таблица #%d - не существует.', $table_id));
		}
		
		// Зпрашиваем данные
		$query = "
			SELECT * 
			FROM ".$table_info['table_name'];
		
		// В таблице есть родительское поле
		if (!empty($table_info['parent_field']) && empty($export_id)) {
			$query .= " WHERE ".$table_info['parent_field']."='".$parent_id."'";
		
		// Указаны id, которые необходимо экспортировать (только для первой итерации)
		} elseif (!empty($export_id)) {
			$query .= " WHERE id IN (0".implode(",", $export_id).")";
		}
		
		$data = $DB->query($query);
		
		// В данном разделе нет подразделов
		if ($DB->rows == 0) return;
		
		// Обрабатываем подразделы
		reset($data);
		while(list(,$row) = each($data)) {
			echo "\n<".$table_info['table_name']." ";
			
			reset($row);
			while(list($key, $val) = each($row)) {
				if ($key == $table_info['parent_field'] || empty($val)) {
					continue;
				} else {
					echo $key.'="'.iconv(CMS_CHARSET, 'UTF-8', htmlspecialchars($val, ENT_COMPAT, CMS_CHARSET)).'" ';
				}
			}

			echo '>';
			
			// Определяем дочерние таблицы
			$query = "
				SELECT tb_table.id, tb_table.name AS table_name
				FROM cms_table AS tb_table
				WHERE tb_table.parent_field_id IN (
					SELECT id
					FROM cms_field
					WHERE fk_table_id='".$table_id."'
				)
			";
			$parent_tables = $DB->query($query);
			
			// Экспортируем данные из дочерних таблиц
			reset($parent_tables);
			while(list(, $table) = each($parent_tables)) {
				self::export2xml($table['id'], $row['id'], $export_tables);
			}
			
			echo '</'.$table_info['table_name'].'>';
		}
	}
}