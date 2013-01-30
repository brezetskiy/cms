<?php

/**
 * Класс, который отвечает за построение схемы таблицы
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class cmsDB {
	/**
	 * Информация о БД
	 *
	 * @var array
	 */
	private $db = array();
	
	/**
	 * Соединение с БД, с которой получаем структуру таблиц
	 *
	 * @var object
	 */
	private $DBServer;
	
	
	/**
	 * Конструктор класса
	 *
	 * @param mixed $db_id - БД, структуру которой обновляем
	 */
	public function __construct($db_id) {
		global $DB;
		 
		if (is_array($db_id)) {
			$this->db = $db_id;
		} else {
			$this->db = $DB->query_row("select id, alias from cms_db where id='$db_id'");
			$this->db['name'] 	= db_config_constant("name", $this->db['alias']);
			$this->db['host'] 	= db_config_constant("host", $this->db['alias']);
			$this->db['login'] 	= db_config_constant("login", $this->db['alias']);
			$this->db['passwd'] = db_config_constant("password", $this->db['alias']);
			$this->db['type'] 	= db_config_constant("type", $this->db['alias']);
		}
		 
		$this->DBServer = DB::factory($this->db['alias']);
		
		// Правильность структуры БД
		$this->checkDuplicates();
	}
	
	public function checkDuplicates() {
		// Проверяем, существуют ли одноимённые объекты
		$query = "
			create temporary table tmp_check 
			(
				select table_name
				from information_schema.tables
				where table_schema='".$this->db['name']."'
			) union all (
				select routine_name
				from information_schema.routines
				where routine_schema='".$this->db['name']."'
			)
		";
		$this->DBServer->insert($query);
		$query = "select table_name, count(table_name) as x from tmp_check group by table_name having x > 1";
		$duplicates = $this->DBServer->fetch_column($query, 'table_name');
		if ($this->DBServer->rows > 0) {
			trigger_error(cms_message('CMS', 'Не должно быть объектов в БД с одинаковым именем. Дублирующиеся названия `%s`', implode("`, `", $duplicates)), E_USER_ERROR);
		}
		$query = "drop temporary table tmp_check";
		$this->DBServer->delete($query);	
	}
	
	/**
	 * Обновление структуру всей БД
	 *
	 */
	public function updateDB() {
		global $DB;
		
		// Обновляем таблицы  и процедуры
		$query = "
			(
				select table_name, 'table' as type
				from information_schema.tables
				where 
					table_schema='".$this->db['name']."'
					and table_name not like 'shop\_x\_%'
			) union all (
				select routine_name, routine_type as type
				from information_schema.routines
				where 
					routine_schema='".$this->db['name']."'
			)
		";
		$data = $this->DBServer->fetch_column($query);
		reset($data);
		while (list($name, $type) = each($data)) {
			$this->updateTable($name, $type);
		}
		
		// Устанавливаем флаг для таблиц, которые не существуют
		$query = "select name from cms_table where db_id='".$this->db['id']."'";
		$all_tables = $DB->fetch_column($query);
		$deleted = array_intersect(array_keys($data), $all_tables);
		$query = "update cms_table set _is_real=0 where db_id='".$this->db['id']."'";
		$DB->update($query);
		if (!empty($deleted)) {
			$query = "update cms_table set _is_real=1 where db_id='".$this->db['id']."' and name in ('".implode("','", $deleted)."')";
			$DB->update($query);
		}
		
		
	}
	
	/**
	 * Обновление структуры одной таблицы
	 *
	 * @param string $table_name
	 * @param string $type
	 * @param bool $obligatory_update
	 * @return bool
	 */
	public function updateTable($table_name, $type='table', $obligatory_update = 0) {
		global $DB;
		
		// Проверяем, была ли изменена таблица с момента последнего обновления
		$query = "select _create_table_md5 as checksum from cms_table where db_id='".$this->db['id']."' and name='$table_name'";
		$md5_old = $DB->result($query);
		
		$query = "show create $type `$table_name`";
		$create_object = $this->DBServer->query_row($query);
		if (isset($create_object['create table'])) {
			$create = $create_object['create table'];
		} elseif (isset($create_object['create view'])) {
			$create = $create_object['create view'];
		} elseif (isset($create_object['create procedure'])) {
			$create = $create_object['create procedure'];
		} elseif (isset($create_object['create function'])) {
			$create = $create_object['create function'];
		} else {
			x($create_object);
		}
		$md5_new = md5(preg_replace("/AUTO_INCREMENT=\d+/", '', $create));
		if (!$obligatory_update && $md5_new == $md5_old) {
			return false;
		}
		
		// Запрашиваем список модулей
		$module = substr($table_name, 0, strpos($table_name, '_'));
		$query = "select id from cms_module where lower(name)='$module'";
		$module_id = $DB->result($query);
		
		// Импорт данных о таблице
		$query = "
			(
				select 
					table_schema as db_name,
					table_name,
					table_type,
					create_time
				from `INFORMATION_SCHEMA`.`TABLES`
				where 
					table_schema='".$this->db['name']."' and
					table_name='$table_name'
			) union all (
				select 
					routine_schema as db_name,
					routine_name as table_name,
					routine_type as table_type,
					created as create_time
				from `INFORMATION_SCHEMA`.`ROUTINES`
				where 
					routine_schema='".$this->db['name']."' and
					routine_name='$table_name'
			)
		";
		$data = $this->DBServer->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$create_dtime = (is_null($row['create_time'])) ? 'NULL': "'$row[create_time]'";
			$query = "
				insert into cms_table (db_id, name, module_id, _table_type, _create_dtime, _is_real, _create_table_md5)
				values ('".$this->db['id']."', '$row[table_name]', '".intval($module_id)."', '$row[table_type]', $create_dtime, 1, '$md5_new')
				on duplicate key update _table_type=values(_table_type), _create_dtime=values(_create_dtime), _is_real=values(_is_real), _create_table_md5=values(_create_table_md5)
			";
			$DB->insert($query);
		}
		
		if (!isset($data[0]['table_type']) || in_array($data[0]['table_type'], array('PROCEDURE', 'FUNCION'))) {
			return;
		}
		
		// Определяем id таблцы
		$query = "select id from cms_table where name='$table_name' and db_id='".$this->db['id']."'";
		$table_id = $DB->result($query);
		if ($DB->rows == 0) {
			$table_id = 0;
		}
		
		// Устанавливаем флаг существования таблиц и полей
		$query = "update cms_field set _is_real=0 where table_id='$table_id'";
		$DB->update($query);
		
		// Определяем max priority для данной таблицы
		$query = "select max(priority) from cms_field where table_id='$table_id'";
		$priority = $DB->result($query);
		
		$query = "select name from cms_field where table_id='$table_id'";
		$exists = $DB->fetch_column($query);
		
		// Обнуляем существование enum полей
		$query = "select id from cms_field where table_id='$table_id'";
		$field_list = $DB->fetch_column($query);
		
		$query = "update cms_field_enum set _is_real=0 where field_id in (0".implode(",", $field_list).")";
		$DB->update($query);

		
		// Импорт данных о колонках
		$query = "
			SELECT
				IF(LEFT(RIGHT(column_name,3),1)='_' AND FIND_IN_SET(RIGHT(LOWER(column_name), 2), '".LANGUAGE_ALL_AVAILABLE."')>0, LEFT(LOWER(column_name), LENGTH(column_name) - 3), column_name) AS name,
				column_default,
				IF(is_nullable='YES', 1, 0) is_nullable,
				MIN(ordinal_position) as ordinal_position,
				CASE
					WHEN column_type='enum(\'true\',\'false\')' THEN 'boolean'
					WHEN column_type='enum(\'false\',\'true\')' THEN 'boolean'
					WHEN column_type='tinyint(1)' THEN 'boolean'
					ELSE data_type
				END AS data_type,
				column_type,
				CASE
					WHEN character_maximum_length IS NOT NULL THEN character_maximum_length
					WHEN numeric_scale=0 THEN numeric_precision
					WHEN numeric_scale>0 THEN numeric_precision+1
					WHEN data_type='year' THEN 4
					ELSE 0
				END AS max_length,
				IF(LEFT(RIGHT(column_name, 3), 1)='_' AND FIND_IN_SET(RIGHT(LOWER(column_name), 2), '".LANGUAGE_ALL_AVAILABLE."')>0, 1, 0) as is_multilanguage
			FROM INFORMATION_SCHEMA.columns
			WHERE 
				table_schema='".$this->db['name']."' and
				table_name='$table_name'
			GROUP BY name
			ORDER BY ordinal_position ASC
		";
		$data = $this->DBServer->query($query);
		$insert = array();
		reset($data);
		while (list(,$row) = each($data)) {
			$priority++;
			$field_type = 'auto';
			if ($row['name'] == 'id' || $row['name'] == 'priority') {
				$field_type = 'hidden';
			} elseif (substr($row['name'], 0, 1) == '_') {
				$field_type = 'fixed_hidden';
			}
			$row['column_default'] = (is_null($row['column_default'])) ? "NULL" : "'$row[column_default]'";
			
			$fk_table_id = 0;
			$title_ru = '';
			if (!in_array($row['name'], $exists)) {
				// Только для полей, которых нет в БД
				if (substr($row['name'], -3) == '_id') {
					// определяем таблицу, с которой связана колонка
					$query = "select id from cms_table where name like '".$module."\_%".substr($row['name'], 0, -3)."' and module_id='$module_id'";
					$fk_table_id = $DB->result($query);
					if ($DB->rows == 0) {
						// поиск за пределами модуля
						$query = "select id from cms_table where name like '".$module."\_%".substr($row['name'], 0, -3)."'";
						$fk_table_id = $DB->result($query);
					}
					
					// определяем название новой колонки с внешним ключом
					$query = "
						select tb_field.title_ru
						from cms_table as tb_table
						inner join cms_field as tb_field on tb_field.id=tb_table.parent_field_id
						where tb_table.id='$fk_table_id'
					";
					$title_ru = $DB->result($query);
				} else {
					// определяем название колонки
					$query = "select title_ru from cms_field where module_id='$module_id' and name='$row[name]' limit 1";
					$title_ru = $DB->result($query);
					if ($DB->rows == 0) {
						$query = "select title_ru from cms_field where name='$row[name]' limit 1";
						$title_ru = $DB->result($query);
					}
				}
				if ($row['name'] == 'name') {
					$title_ru = 'Название';
				}
			}
			$insert[] = "($table_id, '$row[name]', '$title_ru', '".intval($fk_table_id)."', $row[column_default], '$row[is_nullable]', '$row[ordinal_position]', '$row[data_type]', '".addcslashes($row['column_type'], "'")."', '$row[max_length]', 1, $priority, '$field_type', '$row[is_multilanguage]')";
		}
		if (!empty($insert)) {
			$query = "
				INSERT INTO cms_field (table_id, name, title_ru, fk_table_id, _column_default, _is_nullable, _ordinal_position, _data_type, _column_type, _max_length, _is_real, priority, field_type, _is_multilanguage)
				VALUES ".implode(",\n", $insert)."
				ON DUPLICATE KEY UPDATE 
					_column_default=values(_column_default),
					_is_nullable=values(_is_nullable),
					_ordinal_position=values(_ordinal_position),
					_data_type=values(_data_type),
					_column_type=values(_column_type),
					_max_length=values(_max_length),
					_is_real=1,
					_is_multilanguage=values(_is_multilanguage)
			";
			$DB->insert($query);
		}
		
		// Импорт enum и set
		$query = "
			select 
				tb_field.id, 
				tb_field._column_type,
				ifnull((select max(priority) from cms_field_enum where field_id=tb_field.id), 0) as priority
			from cms_field as tb_field
			where 
				tb_field.table_id='$table_id' and 
				tb_field._data_type in ('enum','set')";
		$data = $DB->query($query);
		$insert = array();
		reset($data);
		while (list(, $row) = each($data)) {
			$values = preg_split("/[',]+/", substr($row['_column_type'], strpos($row['_column_type'], '(') + 1, -1), -1, PREG_SPLIT_NO_EMPTY);
			reset($values);
			while (list(,$enum) = each($values)) {
				$row['priority']++;
				$insert[] = "('$row[id]', '$enum', 1, $row[priority])";
			}
		}
		unset($data);
		if (!empty($insert)) {
			$query = "insert cms_field_enum (field_id, name, _is_real, priority) VALUES ".implode(",", $insert)." on duplicate key update _is_real=1";
			$DB->insert($query);
		}
		
		// Импорт индексов
		$query = "delete from cms_table_index where table_id='$table_id'";
		$DB->delete($query);
		
		$query = "
			SELECT
				$table_id,
				index_name,
				LOWER(column_name) as column_name,
				IF(nullable='YES', 1, 0) as is_nullable,
				seq_in_index
			FROM `INFORMATION_SCHEMA`.`STATISTICS`
			WHERE 
				table_schema='".$this->db['name']."'
				and table_name='$table_name'
				and non_unique='NO'
				and column_name!='id'
		";
		$data = $this->DBServer->query($query);
		$insert = array();
		reset($data);
		while (list(,$row) = each($data)) {
			$insert[] = "($table_id, '$row[index_name]', '$row[column_name]', '$row[is_nullable]', '$row[seq_in_index]')";
		}
		if (!empty($insert)) {
			$query = "
				insert into cms_table_index (table_id, name, column_name, is_nullable, priority)
				values ".implode(",", $insert)."
			";
			$DB->insert($query);
		}
		
		return true;
	}
	

	/**
	 * Строит статистику по таблице
	 */
	public function buildTableStatic() {
		global $DB;
		
		$DB->delete("delete from cms_table_static");
		$DB->insert("
			insert into cms_table_static (
				id, name, db_id, db_alias, table_id,
				table_name, table_type, use_cvs, fk_show_id, fk_show_name,
				fk_order_direction, fk_order_name, default_language, parent_field_id,
				parent_field_name, parent_table_id, parent_table_name, relation_table_id,
				relation_table_name, triggers_dir, languages, cms_type
			)
			SELECT
				tb_table.id, 
				tb_table.name,
				tb_table.db_id,
				tb_db.alias AS db_alias,
				tb_table.id AS table_id,
				tb_table.name AS table_name,
				tb_table._table_type,
				tb_table.use_cvs,
				tb_table.fk_show_id,
				(
					SELECT IF(_is_multilanguage, CONCAT(name, '_', tb_language.code), name)
					FROM cms_field
					WHERE id=tb_table.fk_show_id
				) AS fk_show_name,
				tb_table.fk_order_direction,
				IF(tb_table.fk_order_id!=0, 
					(SELECT IF(_is_multilanguage, CONCAT(name, '_', tb_language.code), name) FROM cms_field WHERE id=tb_table.fk_order_id),
					(SELECT IF(_is_multilanguage, CONCAT(name, '_', tb_language.code), name) FROM cms_field WHERE id=tb_table.fk_show_id)
				) AS fk_order_name,
				tb_language.code AS default_language,
				tb_table.parent_field_id,
				IFNULL(tb_field_1.name, 0) AS parent_field_name,
				tb_field_1.fk_table_id as parent_table_id,
				(SELECT name FROM cms_table WHERE id=tb_field_1.fk_table_id) AS parent_table,
				tb_table.relation_table_id,
				IF(
					tb_table.relation_table_id<>0,
					(SELECT name FROM cms_table WHERE id=tb_table.relation_table_id),
					0
				) AS relation_table_name,
				CONCAT(tb_db.alias, '/', tb_table.name, '/') AS triggers_dir,
				case
					when (select count(*) from cms_field where table_id=tb_table.id and _is_multilanguage=1)=0 then ''
					else 
						ifnull((
							select group_concat(distinct code)
							from cms_language as tb_language
							inner join cms_language_usage as tb_relation on tb_relation.language_id=tb_language.id
							where tb_relation.interface_id=tb_table.interface_id
						), '')
				end as languages,
				CASE
					WHEN tb_field_1.id IS NULL THEN 'list'
					WHEN tb_field_1.fk_table_id=tb_table.id THEN 'tree'
					ELSE 'cascade'
				END as cms_type
			FROM cms_table AS tb_table
			INNER JOIN cms_db AS tb_db ON tb_table.db_id=tb_db.id
			LEFT JOIN cms_interface AS tb_interface ON tb_interface.id=tb_table.interface_id
			LEFT JOIN cms_language AS tb_language ON tb_language.id=tb_interface.default_language
			LEFT JOIN cms_field AS tb_field_1 ON tb_table.parent_field_id=tb_field_1.id
		");
		
		$relation_table = $DB->fetch_column("select distinct fk_link_table_id from cms_field");
		$DB->update("update cms_table_static set cms_type='relation' where id in (0".implode(",", $relation_table).")");
	}
	

	
	/**
	 * Строит статистику по полям
	 */
	public function buildFieldStatic() {
		global $DB;
		
		$query = "delete from cms_field_static";
		$DB->delete($query);
		
		$query = "
			insert into cms_field_static (
				id, name, db_id, db_alias, table_id, table_name,
				field_id, field_name, module_id, field_type,
				data_type, pilot_type, no_default_value, is_obligatory, fk_table_id, fk_link_table_id,
				stick, show_in_filter, regexp_id, field_language,
				group_edit, priority, is_nullable, column_default, is_real, cms_type, currency_field_name, is_multilanguage,
				full_name, is_reference
			)
			SELECT 
				tb_field.id,
				tb_field.name,
				tb_table.db_id,
				tb_db.alias as db_alias,
				tb_field.table_id,
				tb_table.name as table_name,
				tb_field.id as field_id,
				tb_field.name as field_name,
				if(tb_field.module_id=0, tb_table.module_id, tb_field.module_id) as module_id,
				tb_field.field_type,
				tb_field._data_type as data_type,
				tb_type.pilot_type,
				IF((tb_field._column_default is null and tb_field._is_nullable=0) or tb_type.no_default_value=1, 1, 0) as no_default_value,
				tb_field.is_obligatory,
				tb_field.fk_table_id,
				tb_field.fk_link_table_id,
				tb_field.stick,    
				tb_field.show_in_filter,    
				tb_field.regexp_id,
				ifnull(tb_language.code, '') AS field_language,
				tb_field.group_edit,
				tb_field.priority,
				tb_field._is_nullable as is_nullable,
				tb_field._column_default as column_default,
				tb_field._is_real,
				case
					when tb_field._is_real=0 and tb_field.field_type='auto' and tb_field.fk_link_table_id!=0 and tb_fk.cms_type='list' then 'fk_nn_list'
					when tb_field._is_real=0 and tb_field.field_type='auto' and tb_field.fk_link_table_id!=0 and tb_fk.cms_type='tree' then 'fk_nn_tree'
					when tb_field._is_real=0 and tb_field.field_type='auto' and tb_field.fk_link_table_id!=0 and tb_fk.cms_type='cascade' then 'fk_nn_cascade'
					when tb_field._is_real=0 and tb_field.field_type='ext_multiple' then 'ext_multiple'
					when tb_field._is_real=0 and tb_field.field_type='swf_upload' then 'swf_upload'
					when tb_field._is_real=0 and tb_field.field_type='devider' then 'devider'
					when tb_field._is_real=0 then 'error'
					when tb_field.field_type='hidden' then 'hidden'
					when tb_field.field_type='money' then 'money'
					when tb_field.field_type='file' then 'file'
					when tb_field.field_type='ajax_select' then 'ajax_select'
					when tb_field.field_type='fixed_hidden' then 'fixed_hidden'
					when tb_field.field_type='fixed_open' then 'fixed_open'
					when tb_field.field_type='html' then 'html'
					when tb_field.field_type in ('passwd_open', 'passwd_md5') then 'password'
					when tb_field.field_type='ext_select' and tb_fk.cms_type='tree' then 'fk_ext_tree'
					when tb_field.field_type='ext_select' and tb_fk.cms_type='cascade' then 'fk_ext_cascade'
					when tb_field.field_type='ext_select' then 'fk_ext_list'
					when tb_field.field_type='auto' and tb_type.pilot_type='char' then 'text'
					when tb_field.field_type='auto' and tb_type.pilot_type='text' then 'textarea'
					when tb_field.field_type='auto' and tb_field._data_type='set' then 'checkbox_set'
					when tb_field.field_type='auto' and tb_type.pilot_type='boolean' then 'checkbox'
					when tb_field.field_type='auto' and tb_field._data_type='enum' then 'radio'
					when tb_field.field_type='auto' and tb_field._data_type='datetime' then 'datetime'
					when tb_field.field_type='auto' and tb_field._data_type='date' then 'date'
					when tb_field.field_type='auto' and tb_field._data_type='time' then 'time'
					when tb_field.field_type='auto' and tb_fk.cms_type='list' then 'fk_list'
					when tb_field.field_type='auto' and tb_fk.cms_type='tree' then 'fk_tree'
					when tb_field.field_type='auto' and tb_fk.cms_type='cascade' then 'fk_cascade'
					when tb_field.field_type='auto' and tb_type.pilot_type in ('int', 'decimal') then 'decimal'
					else 'error'
				end as cms_type,
				case
					when tb_field.field_type='money' then (
						select t_field.name 
						from cms_field as t_field 
						where 
							t_field.id!=tb_field.id and 
							t_field.title_ru=tb_field.title_ru and 
							t_field.table_id=tb_field.table_id
						limit 1)
					else null
				end as currency_field_name,
				tb_field._is_multilanguage,
				case 
					when tb_language.code is not null and tb_language.code!='' then concat(tb_field.name, '_', tb_language.code)
					else tb_field.name
				end as full_name,
				tb_field.is_reference
			FROM cms_field AS tb_field
			INNER JOIN cms_table as tb_table on tb_table.id=tb_field.table_id
			INNER JOIN cms_db as tb_db ON tb_db.id=tb_table.db_id
			LEFT JOIN cms_table_static as tb_fk ON tb_fk.id=tb_field.fk_table_id
			LEFT JOIN cms_datatype as tb_type ON tb_type.db_type=tb_field._data_type
            LEFT JOIN cms_language_usage as tb_relation on tb_relation.interface_id=tb_table.interface_id AND tb_field._is_multilanguage=1
            LEFT JOIN cms_language as tb_language on tb_language.id=tb_relation.language_id
		";
		$DB->insert($query);
	}
	
	
	public function checkAllTables() {
		global $DB;
		
		$query = "select id from cms_table where db_id='".$this->db['id']."'";
		$data = $DB->fetch_column($query);
		reset($data);
		while (list(,$table_id) = each($data)) {
			$this->checkTable($table_id);
		}

	}
	
	/**
	 * Проверка правильности заполнения полей
	 *
	 * @param int $table_id
	 * @return array
	 */
	public function checkTable($table_id) {
		global $DB;
		
		$error = array();
		
		$query = "select * from cms_table where id='$table_id'";
		$table = $DB->query_row($query);
		
		// Не указан интерфейс, хотя в таблице есть многоязычные колонки
		if (empty($table['interface_id'])) {
			$query = "select name from cms_field where table_id='".$table['id']."' and _is_multilanguage=1";
			$data = $DB->fetch_column($query, 'name');
			if ($DB->rows > 0) {
				$error[] = 'Необходимо указать интерфейс для таблицы, так как в ней есть многоязычные поля.';
			}
		}
		
		// Модуль для данной таблицы не существует
		$query = "select name from cms_module where id='".$table['module_id']."'";
		$DB->query($query);
		if ($DB->rows == 0) {
			$error[] = 'Не указан модуль для таблицы или модуль был удалён.';
		}
		
		// Наличие таблицы, которая хранит связи и правильность её структуры
		if (!empty($table['relation_table_id'])) {
			$query = "select name from cms_table where id='".$table['relation_table_id']."'";
			$DB->result($query);
			if ($DB->rows == 0) {
				$error[] = 'Таблица связей - не существует.';
			}
			
			$query = "select name from cms_field where table_id='$table[relation_table_id]'";
			$data = $DB->fetch_column($query);
			if ($DB->rows != 3 || !in_array('id', $data) || !in_array('parent', $data) || !in_array('priority', $data)) {
				$error[] = 'Таблица связей должна содержать три коронки (id int, parent int, priority int).';
			}
		}
		
		// Если таблица ссылается сама на себя, то необходлимо для неё создать relation таблицу
		if (empty($table['parent_field_id'])) {
			$query = "select fk_table_id from cms_field where id='".$table['parent_field_id']."'";
			$data = $DB->result($query);
			if ($data == $table['id']) {
				$error[] = 'Необходимо создать relation таблицу для хранения связей';
			}
		}
			
		// Если данная таблица указывается как внешний ключ к другой таблице, то необходимо
		// что б в ней было указано поле parent_field_id
		if (empty($table['fk_show_id'])) {
			$query = "select * from cms_field where fk_table_id='".$table['id']."'";
			$data = $DB->query($query);
			if ($DB->rows > 0) {
				$error[] = 'Необходимо указать поле для внешенго отображения.';
			}
		}

		// Определяем, есть ли колонки, которые ссылаются на несуществующие таблицы
		$query = "
			select tb_field.name, tb_table.id
			from cms_field as tb_field
			left join cms_table as tb_table on tb_table.id=tb_field.fk_table_id
			where 
				tb_field.table_id='$table[id]' and
				tb_field.fk_table_id is null
			having tb_table.id is null
			order by tb_field.priority
		";
		$data = $DB->fetch_column($query, 'name');
		if ($DB->rows > 0) {
			$error[] = 'Поля `'.implode("`, `", $data).'` ссылаются на несуществующие таблицы, проверьте значение поля "Внешний ключ".';
		}
		
		// Проверяет совпадение название таблицы с названием модуля,
		// необходимо для того, что б небыло ошибочной связи таблица-модуль
		$query = "select name from cms_module where id='".$table['module_id']."'";
		$module = $DB->result($query);
		if (strtolower($module) !== substr($table['name'], 0, strlen($module))) {
			$error[] = 'Начало названия таблицы должно совпадать с названием модуля, которому принадлежит таблица.';
		}
		
		// Отслеживать несоответствие типов данных для внешних ключей
		$query = "
			select 
				tb_field.name as src_field,
				tb_field._column_type as src_type,
				tb_table.name as dst_table,
				tb_fk_field.name as dst_field,
				tb_fk_field._column_type as dst_type
			from cms_field as tb_field 
			inner join cms_table as tb_table on tb_table.id=tb_field.fk_table_id
			inner join cms_field as tb_fk_field on tb_fk_field.table_id=tb_table.id and tb_fk_field.name='id'
			where 
				tb_field.fk_table_id!=0
				and tb_field.fk_link_table_id=0
				and tb_field.table_id='$table[id]'
			order by tb_field.priority
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$row['src_type'] = preg_replace("/\(\d+\)/", '', $row['src_type']);
			$row['dst_type'] = preg_replace("/\(\d+\)/", '', $row['dst_type']);
			if ($row['src_type'] != $row['dst_type']) {
				$error[] = "Поле $table[name].$row[src_field] ($row[src_type]) имеет тип данных, который не соответствует внешнему ключу $row[dst_table].$row[dst_field] ($row[dst_type])";
			}
		}
		
		// Проверка того, что text и blob поля должны иметь возможность быть NULL
		$query = "
			select tb_field.name
			from cms_field as tb_field
			inner join cms_datatype as tb_type on tb_type.db_type=tb_field._data_type
			where 
				tb_field.table_id='$table[id]'
				and tb_field._is_nullable=0
				and tb_type.pilot_type in ('text', 'blob')
				and tb_type.no_default_value=1
				and tb_field.is_obligatory=0
			order by tb_field.priority
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Поля типа text и blob должны поддерживать значение NULL либо быть обязательными для заполнения. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
		}

		// Нет значения по умолчанию для полей
		$query = "
			select tb_field.name
			from cms_field as tb_field
			inner join cms_datatype as tb_type on tb_type.db_type=tb_field._data_type
			where
					tb_type.no_default_value=0
				and tb_type.pilot_type in ('int', 'char', 'decimal')
				and tb_field._is_nullable=0
				and tb_field.is_obligatory=0
				and tb_field._column_default is null
				and tb_field.table_id='$table[id]'
				and tb_field.name != 'id'
			order by tb_field.priority
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Необходимо установить значение по умолчанию для полей `'.implode("`, `", $data).'`.';
		}
		
		if (!empty($error)) {
			$query = "update cms_table set _check_failed=1 where id='$table[id]'";
		} else {
			$query = "update cms_table set _check_failed=0 where id='$table[id]'";
		}
		$DB->update($query);
		
		// Внешний ключ должен оканчиваться на _id
		$query = "
			select name
			from cms_field
			where
				table_id='$table[id]' 
				and fk_table_id is not null 
				and fk_table_id!=0
				and right(name, 3) != '_id'
				and _is_real=1
			order by priority
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Названия полей `'.implode("`, `", $data).'` должны иметь окончание _id.';
		}
		
		// Проверяем, правильно ли определён cms_field
		$query = "
			select name
			from cms_field_static
			where 
				table_id='$table[id]'
				and cms_type='error'
			order by priority
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Не удалось определить тип поля `'.implode("`, `", $data).'`, проверьте правильность указания свойств.';
		}
		
		// поле файл не может быть обязательным для заполнения, то же со спрятанными полями
		$query = "
			select name
			from cms_field
			where 
				table_id='$table[id]'
				and field_type in ('file', 'hidden')
				and is_obligatory=1
			order by priority
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			//  fixed_hidden, fixed_open - могут быть, пример cms_field.table_id
			$error[] = 'Поля типа file, hidden не должны быть обязательными для заполнения. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
		}
		
 		// Колонка priority должна быть not null с default = 0
		$query = "
			select name
			from cms_field
			where 
				table_id='$table[id]'
				and name='priority'
				and (_is_nullable=1 or _column_default!=0)
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Поле priority должно быть NOT NULL DEFAULT 0.';
		}
		
// 		 поле с поддержкой null не может быть обязательным для заполнения (не пустым)
//		$query = "
//			select name
//			from cms_field
//			where 
//				table_id='$table[id]'
//				and _is_nullable=1
//				and is_obligatory=1
//			order by priority
//		";
//		$data = $DB->fetch_column($query);
//		if ($DB->rows > 0) {
//			$error[] = 'Поля, поддерживающие NULL не могут быть обязательными для заполнения. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
//		}
		
 		// поле, которое связано с list таблицей и имеет тип ext_multiple - выдавать ошибку
 		$query = "
 			select tb_field.name
 			from cms_field as tb_field
 			inner join cms_table_static as tb_table on tb_table.id=tb_field.fk_table_id
 			where
 				tb_field.table_id='$table[id]'
 				and tb_table.cms_type='list'
 				and tb_field.field_type='ext_multiple'
 		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Поля, которые связаны с list таблицей не могут иметь тип ext_multiple. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
		}
		
 		// не указан внешний ключ для колонки, которая заканчивается на _id
 		$query = "
 			select tb_field.name
 			from cms_field as tb_field
 			left join cms_table as tb_table on tb_table.id=tb_field.fk_table_id
 			where
 				tb_field.table_id='$table[id]'
 				and right(tb_field.name, 3)='_id'
 				and tb_table.id is null
 		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Не указан внешний ключ для колонки, которая заканчивается на _id. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
		}
 		
		// Уникальное поле не может быть пустым
		$query = "select column_name from cms_table_index where table_id='$table[id]'";
		$index = $DB->fetch_column($query);
		
		$query = "
			select name
			from cms_field
			where
				table_id='$table[id]'
				and is_obligatory=0
				and name in ('".implode("','", $index)."')
		";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$error[] = 'Уникальный ключ должен быть обязательным для заполнения. Проверьте правильность указания свойств для полей `'.implode("`, `", $data).'`.';
		}
		
		// Таблица, в которой хранятся связи n:n должна сосстоять из 2-х колонок, если она ссылается на одну и ту же таблицу
		// то в ней должен быть определён ключ, который указывает на id
 		
 		
		return $error;
	}
	
	
	public function getAlias(){
		return $this->db['alias'];
	}
	
}

?>