<?php
/**
 * Работа с объектом Таблица
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class cmsTable {
	
	/**
	 * Определяет id таблицы по ее названию
	 *
	 * @param string $db_alias
	 * @param mixed $table_name_id
	 * @return int
	 */
	static function getIdByAlias($db_alias, $table_name_id) {
		global $DB;
		
		if (is_numeric($table_name_id)) {
			return $table_name_id;
		}
		 
		$query = "    
			select tb_table.id 
			from cms_table as tb_table
			inner join cms_db as tb_db on tb_db.id=tb_table.db_id
			where tb_table.name='$table_name_id' and tb_db.alias='$db_alias'
		";
		return $DB->result($query);
	}
	
		
	/**
	 * Получить информацию о таблице по её id
	 *
	 * @param int $table_id
	 */
	static function getInfoById($table_id) {
		global $DB;
		
		$info = $DB->query_row("select * from cms_table_static where id='$table_id'");
		if ($DB->rows == 0){
			return array(); 
		}
		
		$info['db_name'] = db_config_constant("name", $info['db_alias']);
		$title = $DB->query_row("select * from cms_table where id='$info[id]'");
		
		// Так как данный метод используют и на сайте, в частности в фотогаллерее, то указывать
		// в SQL запросе title_".LANGUAGE_CURRENT." нельзя, так как параметр LANGUAGE_CURRENT 
		// может принять значение языка, который есть на сайте но его не будет в админке
		$info['title'] = (isset($title['title_'.LANGUAGE_CURRENT])) ? $title['title_'.LANGUAGE_CURRENT] : $title['title_'.LANGUAGE_ADMIN_DEFAULT];
		$info['languages'] = preg_split("/,/", $info['languages'], -1, PREG_SPLIT_NO_EMPTY);
		return $info;
	}
	


	/**
	 * Получить информацию о таблице по её названию в БД
	 *
	 * @param string $db_alias
	 * @param string $table_name
	 */
	static function getInfoByAlias($db_alias, $table_name) {
		global $DB;
		
		$info = $DB->query_row("select * from cms_table_static where db_alias='$db_alias' and table_name='$table_name'");
		if ($DB->rows == 0){
			return array(); 
		}
		 
		$info['db_name'] = db_config_constant("name", $info['db_alias']);
		$title = $DB->query_row("select * from cms_table where id='{$info['id']}'");
		
		// Так как данный метод используют и на сайте, в частности в фотогаллерее, то указывать
		// в SQL запросе title_".LANGUAGE_CURRENT." нельзя, так как параметр LANGUAGE_CURRENT 
		// может принять значение языка, который есть на сайте но его не будет в админке
		$info['title'] = (isset($title['title_'.LANGUAGE_CURRENT])) ? $title['title_'.LANGUAGE_CURRENT] : $title['title_'.LANGUAGE_ADMIN_DEFAULT];
		$info['languages'] = preg_split("/,/", $info['languages'], -1, PREG_SPLIT_NO_EMPTY);
		
		return $info;
	}
	
	/**
	 * Структура полей, заведенная в cms
	 *
	 * @param int $table_id
	 * @return array
	 */
	static function getFields($table_id) {
		global $DB;
		
		$query = "
			select
				tb_static.*,
				tb_field.title_".LANGUAGE_CURRENT." as title,
				tb_field.comment_".LANGUAGE_CURRENT." as comment,
				tb_field._max_length as max_length,
				tb_field._column_type as column_type,
				lower(if(tb_static.field_language != '', concat(tb_static.field_name, '_', tb_static.field_language), tb_static.field_name)) as field_name, 
				lower(if(tb_static.field_language != '', concat(tb_static.field_name, '_', tb_static.field_language), tb_static.field_name)) as name,
				tb_static.field_language as language
			from cms_field_static as tb_static
			inner join cms_field as tb_field on tb_field.id=tb_static.id
			where tb_static.table_id='$table_id'
			order by tb_static.priority asc
		";
		return $DB->query($query, 'name');
	}

	
	/**
	 * Определяет родительские таблицы
	 * 
	 * @param $table_id
	 * @return int
	 */
	static function getParentTable($table_id) {
		global $DB;
		$query = "
			SELECT tb_field.fk_table_id
			FROM cms_table AS tb_table
			INNER JOIN cms_field AS tb_field ON tb_field.id=tb_table.parent_field_id
			WHERE tb_table.id='$table_id'
		";
		$parent_table_id = $DB->result($query);
		return ($DB->rows > 0) ? $parent_table_id : 0;
	}
	
	/**
	 * Возвращает дерево родительских таблиц
	 *
	 * @param int $table_id
	 * @return array
	 */
	static function getParentTables($table_id) {
		$return = array();
		do {
			$prev_table = $table_id;
			$return[] = $table_id;
			$table_id = self::getParentTable($table_id);
			if ($table_id == $prev_table) {
				// защита от рекурсивных таблиц
				break;
			}
		} while($table_id != 0);
		return array_reverse($return);
	}
	
	/**
	 * Возвращает информацию о таблице, которая хранит n:n связи
	 *
	 * @param int $table_id
	 * @param int $fk_table_id
	 * @param int $fk_link_table_id
	 * @return array
	 */
	static public function getFkeyNNInfo($table_id, $fk_table_id, $fk_link_table_id) {
		global $DB;
		
		// Таблица ссылается на саму себя
		if ($fk_table_id == $table_id) {
			// Определяем поле, в таблице со связями, в котором хранятся значения
			$query = "
				select tb_field.name as where_field, tb_table.name as from_table
				from cms_field as tb_field
				inner join cms_table as tb_table on tb_table.id=tb_field.table_id and tb_table.parent_field_id=tb_field.id
				where
					tb_field.table_id='$fk_link_table_id'
					and tb_field.fk_table_id='$table_id'
			";
			$return = $DB->query_row($query);
			
		} else {
			$query = "
				select table_name as from_table, field_name as where_field
				from cms_field_static
				where 
					table_id='$fk_link_table_id'
					and fk_table_id='$table_id'
			";
			$return = $DB->query_row($query);
			
		}
		if (!empty($return)) {
			// Определяем поле, с которого мы должны получить значения
			$query = "
				select field_name
				from cms_field_static
				where
					table_id='$fk_link_table_id'
					and fk_table_id='$fk_table_id'
					and field_name!='$return[where_field]'
			";
			$return['select_field'] = $DB->result($query);
		}
		if (count($return) != 3) {
			$query = "select name from cms_table where id='$fk_link_table_id'";
			$fk_link_table_name = $DB->result($query);
			$query = "select name from cms_table where id='$fk_table_id'";
			$fk_table_name = $DB->result($query);
			$query = "select name from cms_table where id='$table_id'";
			$table_name = $DB->result($query);
			if ($fk_table_id == $table_id) {
				trigger_error(cms_message('CMS', 'Одно поле в таблице `%s`, которая содержит связи между таблицей `%s`  и таблицей `%s`, должно указывать внешним ключом на таблицу `%s`, а второе - на таблицу `%s`. Так же для таблицы %s необходимо указать поле, которое будет основным.', $fk_link_table_name, $table_name, $fk_table_name, $table_name, $fk_table_name, $fk_link_table_name), E_USER_ERROR);
			} else {
				trigger_error(cms_message('CMS', 'Одно поле в таблице `%s`, которая содержит связи между таблицей `%s`  и таблицей `%s`, должно указывать внешним ключом на таблицу `%s`, а второе - на таблицу `%s`', $fk_link_table_name, $table_name, $fk_table_name, $table_name, $fk_table_name), E_USER_ERROR);
			}
		}
		return $return;
	}
	

	/**
	 * Удаляет из дерева элементы, которые не приводят к итоговым значениям
	 * @version 2007-02-15
	 * @param array $data
	 * @return array
	 */
	private static function cleanInfoTree($data) {
		if (empty($data)) {
			return array();
		}
		
		$check = array();
		
		reset($data);
		while(list($index,$row) = each($data)) {
			if (!empty($row['real_id'])) {
				$normal[$index] = $index;
				if (!empty($row['parent'])) {
					$check[ $row['parent'] ] = $row['parent'];
				}
			}
		}
		
		$counter=0;
		while (!empty($check)) {
			$counter++;
			$index = reset($check);
			$normal[$index] = $index;
			if (!empty($data[$index]['parent'])) {
				$check[ $data[$index]['parent'] ] = $data[$index]['parent'];
			}
			unset($check[$index]);
		}
		
		reset($data);
		while(list($index,) = each($data)) {
			if (!isset($normal[$index])) {
				unset($data[$index]);
			}
		}
		
		return $data;
	}
	
	/**
	 * Загружает справочник для таблицы типа list
	 *
	 * @param unknown_type $fk_table_id
	 */
	static public function loadInfoList($fk_table_id) {
		$fk_table = cmsTable::getInfoById($fk_table_id);
		$DB_fk = DB::factory($fk_table['db_alias']);
		$query = "
			SELECT 
				id, 
				`%s` AS name 
			FROM `%s`
			ORDER BY `%s` %s
		";
		$order = (!empty($fk_table['fk_order_name'])) ? $fk_table['fk_order_name'] : $fk_table['fk_show_name'];
		$query = sprintf($query, $fk_table['fk_show_name'], $fk_table['table_name'], $order, $fk_table['fk_order_direction']);
		return $DB_fk->fetch_column($query);
	}

	/**
	 * Загружает справочник для таблицы типа tree
	 * 
	 * @param int $fk_table_id
	 * @return array
	 */
	static public function loadInfoTree($fk_table_id) {
		$fk_table = cmsTable::getInfoById($fk_table_id);
		$DB_fk = DB::factory($fk_table['db_alias']);
		$query = "
			SELECT 
				id, 
				id AS real_id, 
				%s AS parent, 
				`%s` AS name 
			FROM `%s`
			ORDER BY `%s` %s
		";
		$order = (!empty($fk_table['fk_order_name'])) ? $fk_table['fk_order_name'] : $fk_table['fk_show_name'];
		$query = sprintf($query, $fk_table['parent_field_name'], $fk_table['fk_show_name'], $fk_table['table_name'], $order, $fk_table['fk_order_direction']);
		return $DB_fk->query($query, 'id');
	}
	
	/**
	 * Загружает справочник для таблицы типа cascade
	 * 
	 * @param int $fk_table_id
	 * @return array
	 */
	static public function loadInfoCascade($fk_table_id) {
		global $DB;
		
		$union = array();
		$used_tables = array(); // защита от зацикливания
		
		/**
		 * Объеденяет все связанные по полю parent таблицы
		 */
		$counter = 0;
		while(!empty($fk_table_id)) {
			$fk_table = cmsTable::getInfoById($fk_table_id);
			if (empty($fk_table)) {
				break;
			}
			$db_alias = $fk_table['db_alias'];
			
			/**
			 * Защита от зацикливания
			 */
			if (in_array($fk_table['name'], $used_tables)) {
				break;
			}
			
			$used_tables[] = $fk_table['name'];
			
			/**
			 * Только для первой таблицы оставляем id, так как пользователь
			 * может выбирать только parent поля для таблицы, которую он редактирует,
			 * а не родительские поля родительской таблицы
			 */
			$real_id = ($counter == 0) ? "id" : "0";
			
			if (!empty($fk_table['fk_order_name'])) {
				$sort_field = $fk_table['fk_order_name'];
			} elseif (!empty($fk_table['fk_show_name'])) {
				$sort_field = $fk_table['fk_show_name'];
			} else {
				$sort_field = "''";
			}
			
			$fk_table_id = $fk_table['parent_table_id'];
			
			$parent = (!empty($fk_table['parent_table_name']) && !in_array($fk_table['parent_table_name'], $used_tables)) ?
				"CONCAT('$fk_table[parent_table_name]', $fk_table[parent_field_name])":
				"0";
			
			$query = "
				SELECT 
					CONCAT('%s', id) AS id,
					%s AS real_id,
					CAST(%s AS char) AS sort,
					%s AS parent,
					`%s` AS name
				FROM `%s`
			";
			$union[] = sprintf($query, $fk_table['table_name'], $real_id, $sort_field, $parent, $fk_table['fk_show_name'], $fk_table['table_name']);
			$counter++;
			
		}
		unset($used_tables);
		unset($counter);
		
		/**
		 * Пустая таблица используется для того, чтоб определить размер полей
		 * в таблицах, если этого не сделать, то поля будут размера первого 
		 * значения в таблице
		 */
		$DB_fk = DB::factory($db_alias);
		$query = "
			(
				SELECT
					REPEAT(' ', 100) AS id, 
					0 AS real_id, 
					REPEAT(' ', 100) AS sort,
					REPEAT(' ', 100) AS parent, 
					REPEAT(' ', 100) AS name
				) UNION ALL (
					".implode(")\nUNION ALL\n(", $union)."
				) 
				ORDER BY sort ASC
		";
		$return = $DB_fk->query($query, 'id'); 
			
		// Убираем значения, которые не имеют значений которые можно выбирать в выпадающем поле
		return self::cleanInfoTree($return);
	}
	
	/**
	 * Возвращает значение поля, которое является внешним клоючом 
	 *
	 * @param int $fk_table_id
	 * @param int $id
	 * @return string
	 */
	static public function showFK($fk_table_id, $id) {
		$fk_table = self::getInfoById($fk_table_id);
		if (empty($fk_table)) {
			return '';
		}
		$fields = self::getFields($fk_table_id);
		$select = array();
		reset($fields);
		while (list(,$row) = each($fields)) {
			if ($row['name'] != 'id' && !$row['is_reference']) {
				continue;
			}
			$select[] = $row['name'];
		}
		$DBServer = DB::factory($fk_table['db_alias']);
		if (empty($select) || count($select) == 1) {
			$query = "SELECT `$fk_table[fk_show_name]` AS name FROM `$fk_table[name]` WHERE id='$id'";
			$return = $DBServer->result($query);
		} else {
			$query = "SELECT `".implode("`,`", $select)."` AS name FROM `$fk_table[name]` WHERE id='$id'";
			$info = $DBServer->query_row($query);
			$return = implode("; ", $info);
		}
		
		return $return;

	}
	
	/**
	 * Удаляет таблицу
	 *
	 * @param string $db_alias
	 * @param string $table_name
	 */
	static public function delete($db_alias, $table_name, $table_type = 'BASE TABLE') {
		global $DB;
		
		$DBServer = DB::factory($db_alias);
		
		$table_type = strtoupper($table_type);
		
		// Определяем наличие таблицы в БД
		if ($table_type == 'BASE TABLE') {
			$query = "select table_name from information_schema.tables where table_schema='$DBServer->db_name' and table_name='$table_name'";
			$data = $DBServer->query($query);
		} elseif ($table_type == 'VIEW') {
			$query = "select table_name from information_schema.views where table_schema='$DBServer->db_name' and table_name='$table_name'";
			$data = $DBServer->query($query);
		} else {
			$query = "select routine_name from information_schema.routines where routine_schema='$DBServer->db_name' and routine_name='$table_name' and routine_type='$table_type'";
			$data = $DBServer->query($query);
		}
		
		// Не найден объект в БД
		if ($DBServer->rows != 1) {
			return false;
		}
		
		/**
		 * Удаляем объекты из БД, только для default БД
		 */
		if ($db_alias == 'default') 	{
			if ($table_type == 'BASE TABLE') {
				$query = "DROP TABLE IF EXISTS `$DBServer->db_name`.`$table_name`";
				$DBServer->delete($query);
			} else {
				$query = "DROP $table_type IF EXISTS `$DBServer->db_name`.`$table_name`";
				$DBServer->delete($query);
			}
		}
		
		/**
		 * Удаляем информацию из таблиц cms_*
		 */
		$query = "select id from cms_db where alias='$db_alias'";
		$db_id = $DB->result($query);
		
		$query = "select id from cms_table where db_id='$db_id' and name='$table_name'";
		$table_id = $DB->result($query);
		
		$query = "delete from cms_table where id='$table_id'";
		$DB->delete($query);
		
		$query = "delete from cms_field where table_id='$table_id'";
		$DB->delete($query);
		
		$query = "delete from cms_table_static where table_id='$table_id'";
		$DB->delete($query);
		
		$query = "delete from cms_field_static where table_id='$table_id'";
		$DB->delete($query);
		
		
		/**
		 * Удаляем файлы, которые привязаны к таблице
		 */
		$files = array();
		$content_dirs = Filesystem::getDirContent(CONTENT_ROOT, false, true, false);
		$uploads_dirs = Filesystem::getDirContent(UPLOADS_ROOT, false, true, false);
		
		// Триггеры
		Filesystem::delete(TRIGGERS_ROOT.$db_alias.'/'.$table_name);
		
		// Контент
		reset($content_dirs);
		while (list(,$dirname) = each($content_dirs)) {
			if (substr($table_name, 0, strlen($dirname) + 1) == substr($dirname, 0, -1).'.' || substr($dirname, 0, -1) == $table_name) {
				Filesystem::delete(UPLOADS_ROOT.$dirname);
			}
		}
		
		// Uploads
		reset($uploads_dirs);
		while (list(,$dirname) = each($uploads_dirs)) {
			if (substr($dirname, 0, strlen($table_name) + 1) == $table_name.'.' || substr($dirname, 0, -1) == $table_name) {
				Filesystem::delete(UPLOADS_ROOT.$dirname);
			}
		}
		
		
		
	}
}

?>
