<?php
/** 
 * Модуль электронного магазина 
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

/**
 * Класс, который отвечает за изменение свойств группы
 *
 */
class ShopEdit extends Shop {
	
	/**
	 * Системные поля, которые нельзя использовать в названиях параметров
	 *
	 * @var array
	 */
	private $system_fields = array('id', 'name', 'group_id', 'price', 'priority', 'url', 'available');
	
	/**
	 * Конструктор класса
	 *
	 * @param int $group_id
	 */
	public function __construct($group_id) {
		parent::__construct($group_id);
	}
	
	
	
	/**
	 * Создаёт временную таблицу
	 *
	 */
	public function createTable() {
		global $DB;
		
		// Определяем поля, которые есть в группе
		$query = "
			select
				lower(tb_param.uniq_name) as field_name, 
				case tb_param.data_type
					when 'char' then concat('`', lower(tb_param.uniq_name), '` varchar(2000)')
					when 'decimal' then concat('`', lower(tb_param.uniq_name), '` decimal(13,2)')
					when 'file' then concat('`', lower(tb_param.uniq_name), '` varchar(500)')
					when 'image' then concat('`', lower(tb_param.uniq_name), '` varchar(500)')
					when 'bool' then concat('`', lower(tb_param.uniq_name), '` boolean')
					when 'fkey' then concat('
						`', lower(tb_param.uniq_name), '` varchar(500),
						`', lower(tb_param.uniq_name), '_id` int(10) unsigned
					')
					when 'fkey_table' then concat('`', lower(tb_param.uniq_name), '` int(10) unsigned')
					when 'text' then concat('`', lower(tb_param.uniq_name), '` text')
					when 'html' then concat('`', lower(tb_param.uniq_name), '` text')
					when 'multiple' then concat('`', lower(tb_param.uniq_name), '` text')
					else ''
				end as field_type
			from shop_group_param as tb_param
			inner join shop_group_relation as tb_relation on tb_relation.parent=tb_param.group_id
			where 
				tb_relation.id='".$this->group_id."'
				and tb_param.uniq_name not in ('".implode("','", $this->system_fields)."')
			having field_type!=''
			order by 
				tb_relation.priority desc,
				tb_param.priority asc
		";
		$field = $DB->fetch_column($query);
		
		// Определяем поля с индексом
		$query = "
			select
				lower(tb_param.uniq_name) as field_name,
				concat('key (',tb_param.uniq_name,'_id),') as idx
			from shop_group_param as tb_param
			inner join shop_group_relation as tb_relation on tb_relation.parent=tb_param.group_id
			where tb_relation.id='".$this->group_id."' and tb_param.data_type='fkey'
			order by 
				tb_relation.priority desc,
				tb_param.priority asc
		";
		$index = $DB->fetch_column($query);
		
		// Удаляем старую временную таблицу
		$query = "
			select table_name
			from information_schema.tables
			where
				table_schema='$DB->db_name'
				and table_name='tmp_$this->table_name'
		";
		$DB->query($query);
		if ($DB->rows > 0) {
			$query = "drop table `tmp_$this->table_name`";
			$DB->delete($query);
		}
		
		if (!empty($field)) {
			// Создаём таблицу, если для нее назначено хоть одно поле
			$query = "
				create table `tmp_$this->table_name` (
					`id` int unsigned not null auto_increment,
					`name` varchar(255) not null,
					`group_id` int(10) unsigned not null default 0,
					`price` decimal(10,2) unsigned not null default 0,
					`available` tinyint(1) unsigned not null default 0,
					`url` varchar(255) default null,
					".implode(",\n", $field).",
					`priority` smallint(5) unsigned not null default 0,
					".implode("\n", $index)."
					primary key (`id`),
					key url (`url`)
				);
			";
			$DB->insert($query);
			return true;
		} else {
			// Проверяем, есть ли запрошенная таблица
			$query = "show tables like '$this->table_name'";
			$DB->query($query);
			if ($DB->rows > 0) {
				// Удаляем таблицу в которой нет полей
				$query = "drop table if exists `$this->table_name`";
				$DB->delete($query);
			}
			return false;
		}
	}
	
	
	/**
	 * Преобразование временной таблицы в постоянную
	 *
	 */
    public function commitCreate() {
	global $DB;
	
	$DB->query("select table_name from information_schema.tables where table_schema='$DB->db_name' and table_name='$this->table_name'");
	if ($DB->rows > 0) {		
	    $DB->delete("drop table if exists `$this->table_name`");
	}
	
	$DB->query("select table_name from information_schema.tables where table_schema='$DB->db_name' and table_name='tmp_$this->table_name'");
	if ($DB->rows > 0) {		
	    $DB->query("alter table `tmp_$this->table_name` RENAME TO `$this->table_name`");
	}
    }
	
	/**
	 * Загружает информацию со статической таблицы во временную
	 * Используется при добавлении нового параметра товара
	 *
	 */
	public function loadOldData() {
		global $DB;
		
		$query = "
			select column_name
			from information_schema.columns
			where 
				table_schema='$DB->db_name'
				and table_name='$this->table_name'
			order by ordinal_position asc
		";
		$old_columns = $DB->fetch_column($query);
		if ($DB->rows == 0) {
			// реальная таблица еще не существует, это первая колонка,
			// которая добавляется в таблицу
			return false;
		}
		
		$query = "
			select column_name
			from information_schema.columns
			where 
				table_schema='$DB->db_name'
				and table_name='tmp_$this->table_name'
			order by ordinal_position asc
		";
		$new_columns = $DB->fetch_column($query);
		
		$columns = array_intersect($old_columns, $new_columns);
		
		$query = "
			insert ignore into `tmp_$this->table_name` (`".implode("`,`", $columns)."`)
			select `".implode("`,`", $columns)."`
			from `$this->table_name`
		";
		$DB->insert($query);
	}
	
	/**
	 * Загружает всю группу товаров с линейной таблицы во временную
	 *
	 * @param int $group_id группа, которую необходимо закачивать
	 * @param bool $recursive - закачивать так же и дочерние группы
	 * @return array массив товаров, которые были добавлены
	 */
	public function loadData($group_id = null, $recursive = false) {
		global $DB;
		$return = array();
		
		if (is_null($group_id)) {
			$group_id = $this->group_id;
		}
		
		if ($recursive) {
			$query = "select id from shop_group where _param_group_id='$group_id'";
			$group_id = $DB->fetch_column($query);
		}
		
		// Добавляем значения в таблицу
		$query = "
			select 
				tb_product.id,
				tb_product.name,
				tb_product.group_id,
				tb_product.price,
				tb_product.available,
				tb_product._url as url,
				tb_product.priority,
				tb_param.uniq_name,
				tb_value.data_type,
				tb_value.value_int,
				case tb_value.data_type
					when 'char' then value_char
					when 'decimal' then value_decimal
					when 'file' then value_text
					when 'image' then value_text
					when 'bool' then value_int
					when 'fkey' then value_char
					when 'fkey_table' then value_int
					when 'multiple' then value_text
					when 'text' then value_text
					when 'html' then value_text
				end as value
			from shop_product as tb_product
			inner join shop_product_value as tb_value on tb_product.id=tb_value.product_id
			inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
			where 
				tb_param.uniq_name not in ('".implode("','", $this->system_fields)."')
				".where_clause('tb_product.group_id', $group_id)."
		";
		$data = $DB->query($query);
		$insert = array();
		$count = 0;
		reset($data); 
		while (list(,$row) = each($data)) {
			$return[$row['id']] = $row['id'];
			$count++;
			if ($row['data_type'] == 'fkey' || $row['data_type'] == 'fkey_table') {
				$insert[$row['uniq_name'].'_id'][] = "('$row[id]', '$row[group_id]', '".$DB->escape($row['name'])."', '$row[url]', '$row[price]','$row[available]', '$row[value_int]', '$row[priority]')";
			}
			
			$insert[$row['uniq_name']][] = "('$row[id]', '$row[group_id]', '".$DB->escape(addslashes($row['name']))."', '$row[url]', '$row[price]','$row[available]', '".$DB->escape(addslashes($row['value']))."', '$row[priority]')";
			if ($count > 200) {
				$this->multipleInsert($insert);
				$insert = array();
				$count = 0;
			}
		}
		$this->multipleInsert($insert);
		
		return $return;
	}
	
	/**
	 * Функция служит для добавления большого количества записей во временную таблицу
	 *
	 * @param array $insert
	 */
	private function multipleInsert($insert) {
		global $DB;
		
		reset($insert); 
		while (list($field_name,) = each($insert)) {
			 $query = "
			 	insert into `tmp_$this->table_name` (id, group_id, name, url, price, available, `$field_name`, `priority`) 
			 	values ".implode(", ", $insert[$field_name])."
			 	on duplicate key update `$field_name` = values(`$field_name`)
			 ";
			 $DB->insert($query);
		}
	}
	
	/**
	 * Добавлет указанный товар с линейной таблицы в нормальную
	 *
	 * @param int $product_id
	 * @return int - id добавленного товара
	 */
	public function insertProduct($product_id) {
		global $DB;
		$insert = array();
		
		// Определяем название товара
		$query = "select id, name, _url as url, price,available, group_id, priority from shop_product where id='$product_id'";
		$data = $DB->query_row($query);
		
		// Определяем свойства товара
		$query = "
			select 
				tb_param.uniq_name,
				case tb_value.data_type
					when 'char' then value_char
					when 'decimal' then value_decimal
					when 'file' then value_text
					when 'image' then value_text
					when 'bool' then value_int
					when 'fkey' then value_char
					when 'fkey_table' then value_int
					when 'multiple' then value_text
					when 'text' then value_text
					when 'html' then value_text
					when 'date' then value_date
				end as value
			from shop_product as tb_product
			inner join shop_product_value as tb_value on tb_product.id=tb_value.product_id
			inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
			where tb_product.id='$product_id'
		";
		$data += $DB->fetch_column($query);
		
		// Определяем внешние ключи для товара
		$query = "
			select 
				concat(tb_param.uniq_name, '_id') as uniq_name,
				tb_value.value_int
			from shop_product as tb_product
			inner join shop_product_value as tb_value on tb_product.id=tb_value.product_id
			inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
			where tb_product.id='$product_id' and tb_value.data_type='fkey'
		";
		$data += $DB->fetch_column($query);
		
		$insert = array();
		reset($data); 
		while (list($field, $value) = each($data)) {
			$insert[] = "`$field`='".addslashes($value)."'"; 
		}
		
		$query = "insert into `$this->table_name` set ".implode(",\n", $insert)."";
		return $DB->insert($query);
	}
	
	/**
	 * Удаление товара
	 *
	 * @param mixed $product_id
	 */
	public function deleteProduct($product_id) {
		global $DB;
		
		// проверяем не удалена ли таблица с товаром
		if (empty($this->table_name)) return true;
		$query = "delete from `$this->table_name` where 1 ".where_clause('id', $product_id);
		$DB->delete($query);
	}
	
	/**
	 * Обновляет данные товарного каталога, удаляет товары которые не связаны с группами.
	 *
	 *
	static function renew() {
		global $DB;
		
		$query = "
			select tb_group.id
			from shop_group as tb_group
			left join shop_group as tb_parent on tb_parent.id=tb_group.group_id
			where tb_parent.id is null and tb_group.group_id!=0
		";
		$groups = $DB->fetch_column($query);
		reset($groups);
		while (list(,$group_id) = each($groups)) {
			$query = "select id from shop_product where group_id='$group_id'";
			$products = $DB->fetch_column($query);
			
			$query = "delete from shop_product_value where product_id in (0".implode(",", $products).")";
			$DB->delete($query);
			
			$query = "delete from shop_product where  group_id='$group_id'";
			$DB->delete($query);
			
			$query = "delete from shop_group where id='$group_id'";
			$DB->delete($query);
		}
		
		$query = "
			select *
			from shop_product_value as tb_value
			left join shop_product as tb_product on tb_product.id=tb_value.product_id
			where tb_product.id is null
		";
		$data = $DB->query($query);
		
		$query = "
			select tb_product.id
			from shop_product as tb_product
			left join shop_group as tb_group on tb_group.id=tb_product.group_id
			where tb_group.id is null
		";
		$data = $DB->fetch_column($query);
		reset($data);
		while (list(,$product_id) = each($data)) {
			$query = "delete from shop_product where id='$product_id'";
			$DB->delete($query);
		}
	}
	*/
	
	/**
	 * Обновляет описание товара
	 * 
	 * @param int $product_id
	 */
	static public function updateDescription($product_id) {
		global $DB;
		
		$query = "
			SELECT 
				concat('<b>', tb_param.name, ':</b> ', 
				case tb_value.data_type
					when 'char' then value_char
					when 'decimal' then value_decimal
					when 'file' then value_text
					when 'image' then value_text
					when 'bool' then value_int
					when 'fkey' then value_char
					when 'fkey_table' then value_int
					when 'multiple' then value_text
					when 'text' then value_text
					when 'html' then value_text
				end) as value
			FROM shop_group_param tb_param
			INNER JOIN shop_product_value tb_value ON (tb_param.id = tb_value.param_id)
			WHERE 
				tb_value.product_id='$product_id' AND 
				tb_param.data_type not in ('bool', 'html', 'file', 'image') AND
				tb_param.is_description=1
			ORDER BY tb_param.priority ASC
		";
		$description = $DB->fetch_column($query);
		
		$query = "
			SELECT 
				case tb_value.data_type
					when 'char' then value_char
					when 'decimal' then value_decimal
					when 'file' then value_text
					when 'image' then value_text
					when 'bool' then value_int
					when 'fkey' then value_char
					when 'fkey_table' then value_int
					when 'multiple' then value_text
					when 'text' then value_text
					when 'html' then value_text
				end as value
			FROM shop_group_param tb_param
			INNER JOIN shop_product_value tb_value ON (tb_param.id = tb_value.param_id)
			WHERE 
				tb_value.product_id='$product_id' AND 
				tb_param.data_type not in ('bool', 'html', 'file', 'image') AND
				tb_param.is_search=1
			ORDER BY tb_param.priority ASC
		";
		$search = $DB->fetch_column($query);
		
		
		// Создаем URL для товара
		$query = "select group_id, name from shop_product where id='$product_id'";
		$product = $DB->query_row($query);
		
		$url = self::getURL('shop_product', '_url', $product_id, $product['group_id'], $product['name']);
		$query = "
			update shop_product set 
				_url='$url',
				_description='".$DB->escape(implode("; ", $description))."',
				_search='".$DB->escape(implode("; ", $search))."'
			where id='$product_id'";
		$DB->update($query);
	}
	
	/**
	 * Определяет URL для товара или группы
	 *
	 * @param string $table_name
	 * @param string $field_name
	 * @param int $id
	 * @param int $group_id
	 * @param string $name
	 * @return string
	 */
	static public function getURL($table_name, $field_name, $id, $group_id, $name) {
		global $DB;
		
		$url = substr(trim(preg_replace("/[^0-9A-Za-z]+/", "-", Charset::translit($name)), '-'), 0, 100);
		
		$query = "
			select `$field_name`
			from `$table_name`
			where 
				$field_name like '$url%'
				and group_id='$group_id'
		";
		if (!empty($id)) {
			$query .= " and id!='$id' ";
		}
		$data = $DB->fetch_column($query);
		
		// Запись с таким URL уже существует
		if (false !== in_array($url, $data)) {
			$counter = 0;
			do {
				if (false === in_array($url.'-'.$counter, $data)) {
					break;
				} else {
					$counter++;
				}
			} while (1);
			$url .= "-$counter";
		}
		return $url;
	}
	
	
	/**
	 * Обновляет значение поля shop_group._param_group_id для всех групп
	 *
	 */
	public static function reloadParamGroupId() {
		global $DB;
		
		// Перечень групп, в которых есть хоть один параметр
		$query = "select group_id from shop_group_param group by group_id";
		$param_groups = $DB->fetch_column($query);
		reset($param_groups);
		while (list(,$param_group_id) = each($param_groups)) {
			$query = "select id from shop_group_relation where parent='$param_group_id'";
			$groups = $DB->fetch_column($query);
			$query = "update shop_group set _param_group_id='$param_group_id' where id in (0".implode(",", $groups).")";
			$DB->update($query);
		}
	}
	
}


?>