<?php
/**
 * ����� ������ � ������������ ��������
 * @package Pilot
 * @subpackage Shop
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

/**
 * ������� ����� ������ ���������� � ������ ��� ������ �������
 *
 */
class Shop {
	
	/**
	 * id ������
	 *
	 * @var int
	 */
	public $group_id = 0;
	
	/**
	 * ���������� � ������
	 *
	 * @var array
	 */
	public $group = array();
	
	/**
	 * �������, ������� �������� ���������� � �������
	 *
	 * @var array
	 */
	public $table_name = '';
	
	/**
	 * ������, ������� �������� ���������
	 *
	 * @var int
	 */
	public $param_group_id = 0;
	
	/**
	 * ���������� � ������ ������ ���������
	 *
	 * @var int
	 */
	public $total_products = 0;
	
	/**
	 * id ������, ������� ����������� � URL
	 *
	 * @var int
	 */
	public $product_id = 0;
	
	/**
	 * ����, ������� ������� ���������� ����������, ����� ���������� ���������� - ����� �������
	 *
	 * @var bool
	 */
	public $debug = false;
	
	/**
	 * ����, ������� ������������ ��� �������
	 *
	 * @var array
	 */
	public $filter_fields = array();
	
	/**
	 * �����������
	 *
	 * @param mixed $group_id_or_url
	 */
	public function __construct($group_id_or_url, $debug = false) {
		global $DB;
		
		$this->debug = $debug;
		
		if (empty($group_id_or_url)) {
			trigger_error(cms_message('Shop', '� ����������� ������ Shop ������� ������ �������� group_id'), E_USER_ERROR);
			exit;
		}
		
		if (!is_numeric($group_id_or_url)) {
			// � URL ����� ������������� �� ������ ����� ������ �� � � �������� ���������� ��������� ����� ���� ������ URL ������, ������� �� ������ ��������
			$group_id_or_url = trim($group_id_or_url, '/');
			$query = "select id, url from shop_group where url in ('$group_id_or_url', '".substr($group_id_or_url, 0, strrpos($group_id_or_url, '/'))."') order by length(url) desc limit 1";
			$info = $DB->query_row($query);
			if ($DB->rows == 0) {
				trigger_error(cms_message('Shop', '���������� ��������� ������ �������������� ������ (url:%s)', $group_id_or_url), E_USER_ERROR);
				exit;
			}
			$this->group_id = $info['id'];
			if (strtolower($info['url']) != strtolower($group_id_or_url)) {
				// � URL ������ ������������ �����
				$query = "select id from shop_product where group_id='$this->group_id' and _url='".substr($group_id_or_url, strrpos($group_id_or_url, '/') + 1)."'";
				$this->product_id = $DB->result($query);
			}
		} else {
			$this->group_id = $group_id_or_url;
		}
		
		// ���������� � ������ �������
		$query = "
			select *,
				content_".LANGUAGE_CURRENT." as content,
				preview_".LANGUAGE_CURRENT." as preview, 
				title_".LANGUAGE_CURRENT." as title, 
				keywords_".LANGUAGE_CURRENT." as keywords, 
				description_".LANGUAGE_CURRENT." as description,
				_param_group_id as param_group_id
			from shop_group
			where id='$this->group_id'
		";
		$this->group = $DB->query_row($query);
		$this->param_group_id = $this->group['param_group_id'];
		$this->table_name = 'shop_x_'.$this->group['param_group_id'];
		$image = Uploads::getStorage('shop_group', 'image', $this->group_id).'.'.$this->group['image'];
		$this->group['image'] = (is_file(SITE_ROOT.'uploads/'.$image)) ? '/uploads/'.$image: '';
		if (empty($this->group['title'])) $this->group['title'] = $this->group['name'];
		if (empty($this->group['description'])) $this->group['description'] = $this->group['title'];
	}
	
	/**
	 * ���������� ��������� ������ �������
	 *
	 * @param int $group_id
	 * @return array
	 */
	public function getGroupParams($group_id = 0) {
		global $DB;
		if (empty($group_id)) {
			$group_id = $this->param_group_id;
		}
		
		$query = "
			select
				tb_param.id,
				tb_param.id as param_id,
				tb_param.uniq_name,
				tb_param.required,
				tb_param.name,
				tb_param.data_type,
				tb_param.description,
				tb_param.info_id,
				tb_param.fkey_table_id,
				case tb_param.data_type
					when 'char' then 'value_char'
					when 'file' then 'value_char'
					when 'image' then 'value_char'
					when 'decimal' then 'value_decimal'
					when 'bool' then 'value_int'
					when 'fkey' then 'value_int'
					when 'fkey_table' then 'value_int'
					when 'date' then 'value_date'
					else 'value_text'
				end as field_type
			from shop_group_param as tb_param
			inner join shop_group_relation as tb_relation on tb_relation.parent=tb_param.group_id
			where tb_relation.id='$group_id'
			order by tb_relation.priority asc, tb_param.priority asc
		";
		return $DB->query($query, 'uniq_name');
	}
	
	/**
	 * ���������� ���������� � ������
	 *
	 * @param int $product_id
	 * @param bool $skip_static_table - ������ ������� �� �������� �������, ������������ ��� ������ ������ � �������, ��� ��� ����������� ������� ����� ���� �������
	 * @return array
	 */
	public function getProductInfo($product_id, $skip_static_table = false) {
		global $DB;
		
		// ���� ������ � ����������� �������
		if (!$skip_static_table) {
			$query = "select * from `$this->table_name` where id='$product_id'";
			$info = $DB->query_row($query);
			if (!empty($info)) {
				return $info;
			}
		}
		
		// ���� ������ �� ������� � ����������� �������, �� ������ �� �� ���������
		// ������ �������� �������
		$query = "select * from shop_product where id='$product_id'";
		$info = $DB->query_row($query);
		if (empty($info)) {
			$info = array(
				'id' => 0,
				'name' => '',
				'priority' => 99999
			);
		}
		
		$query = "
			select 
				tb_param.uniq_name,
				case tb_value.data_type
					when 'char' then value_char
					when 'decimal' then value_decimal
					when 'file' then value_text
					when 'image' then value_text
					when 'bool' then value_int
					when 'fkey' then value_int
					when 'fkey_table' then value_int
					when 'multiple' then value_text
					when 'text' then value_text
					when 'html' then value_text
					when 'date' then value_date
				end as value
			from shop_product_value as tb_value
			inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
			where tb_value.product_id='$product_id'
		";
		$data = $DB->fetch_column($query);
		return array_merge($info, $data);
	}
	
	/**
	 * ���������� ������
	 *
	 * @param int $product_id
	 */
	public function getProductPhotos($product_id) {
		global $DB;
		$query = "
			select id, photo, description_".LANGUAGE_CURRENT." as description
			from gallery_photo
			where 
				group_table_name = 'shop_product'
				and group_id='$product_id'
				and active=1
			order by priority asc
		";
		$data = $DB->query($query);
		reset($data);
		while (list($index,$row) = each($data)) {
			$row['file'] = Uploads::getFile('gallery_photo', 'photo', $row['id'], $row['photo']);
			$data[$index]['file'] = Uploads::getURL($row['file']);
			$data[$index]['image'] = Uploads::htmlImage($row['file']);
		}
		
		return $data;
	}
	
	
	/**
	 * ��������� ����� ������ ������������� � ����������� ����  
	 *
	 * @param int $product_id
	 * @param string $fields
	 * @return array
	 */
	static function getFieldsFileList($product_id, $fields) {
		$result = array();
		$dir = SITE_ROOT.'uploads/shop_product/'.$fields.'/'.Uploads::getIdFileDir($product_id);

		if(!is_dir($dir)) return array();
		
		$file = Filesystem::getDirContent($dir, true, false, true);
		reset($file);
		while (list(,$filename) = each($file)) {
			$result[] = Uploads::getURL($filename);
		}
		return $result;	
	}
	
	
	/**
	 * ��������� ������ �����������
	 *
	 * @param int $group_id
	 * @return array
	 */
	public function getGroups($group_id = -1) {
		global $DB;
		
		if ($group_id == -1) {
			$group_id = $this->group_id;
		}
		
		$query = "
			select 
				tb_group.id, 
				tb_group.uniq_name, 
				tb_group.image, 
				tb_group.url as url, 
				tb_group.content_".LANGUAGE_CURRENT." as content,
				tb_group.name,
				count(tb_child.id) as childs
			from shop_group as tb_group
			left join shop_group as tb_child on tb_child.group_id = tb_group.id
			where tb_group.group_id='$group_id' and tb_group.active=1
			group by tb_group.id
			order by tb_group.priority
		";
		$data = $DB->query($query, 'id');
		reset($data);
		while (list($index, $row) = each($data)) {
			$file = Uploads::getStorage('shop_group', 'image', $row['id']).'.'.$row['image'];
			$data[$index]['image'] = (is_file(SITE_ROOT.'uploads/'.$file)) ? '/uploads/'.$file : '';
		}
		return $data;
	}
	
		
	/**
	 * ���������� ���� � ������� ��������
	 *
	 * @return array
	 */
	public function getPath() {
		global $DB;
		
		$query = "
			select
				tb_group.id,
				tb_group.name,
				tb_group.uniq_name,
				tb_group.url as url
			from shop_group as tb_group
			inner join shop_group_relation as tb_relation on tb_relation.parent=tb_group.id
			where tb_relation.id='$this->group_id'
			order by tb_relation.priority
		";
		return $DB->query($query);
	}
	
	/**
	 * ���������� ������ ������ ��� ���������� ��� �� hotliine.ua
	 *
	 * @param array $selected
	 * @return array
	 */
	public function getFilter($selected, $local=true) {
		global $DB;
		
		$query = "
			select uniq_name, name
			from shop_group_param
			where 
				group_id IN (SELECT parent FROM shop_group_relation WHERE id = '$this->param_group_id')
				and is_filter=1
			order by priority
		";
		$this->filter_fields = $DB->query($query, 'uniq_name');
		$result = array();
		
		$where = ($local) ? 'where 1 '.where_clause('group_id', $this->group_id) : '';
		$query = "
			select 
				`".implode("`,`", array_keys($this->filter_fields))."`,
				count(*) as _amount,
				0 as _selected
			from `$this->table_name`
			$where
			group by `".implode("`,`", array_keys($this->filter_fields))."`
		";
		$data = $DB->query($query);
		
		// �������� ����, ������� ��� ���� � �������
		reset($data);
		while (list($index,$row) = each($data)) {
			reset($selected);
			while (list($field_name, $options) = each($selected)) {
				if (in_array($row[$field_name], $options)) {
					$data[$index]['_selected'] = 1;
				}
			}
		}
		
		// ��������� ������ � ���������� �������
		reset($data);
		while (list(,$row) = each($data)) {
			reset($row);
			while (list($field_name, $val) = each($row)) {
				
				if(empty($val)){
					$val = '������';  
				}
				
				// ���������� ��������� ����
				if ($field_name == '_amount' || $field_name == '_selected') {
					continue;
				}
				 
				if (empty($selected)) {
					if (!isset($result[$field_name][$val])) {
						$result[$field_name][$val] = 0;
					}
					$result[$field_name][$val] += $row['_amount'];
				} elseif (isset($selected[$field_name]) && !in_array($val, $selected[$field_name])) {
					// ������ ���� ����������� � �������, �� �������� �� ������ ��� ����������
					if (!isset($result[$field_name][$val])) {
						$result[$field_name][$val] = 0;
					}
					$result[$field_name][$val] += $row['_amount'];
				} elseif (!isset($selected[$field_name]) && $row['_selected'] == 1) {
					// ������ ��� ����������� � �������
					if (!isset($result[$field_name][$val])) {
						$result[$field_name][$val] = 0;
					}
					$result[$field_name][$val] += $row['_amount'];
				}
			}
		}
		
		return $result;
	}
	
	
	/**
	 * �������� ��������� �������� ��� �������
	 *
	 * @param string $field_name
	 * @return array
	 */
	public function getFilterValues($field_name) {
		global $DB;
		
		$query = "select id, data_type from shop_group_param where uniq_name='$field_name' and group_id='$this->param_group_id'";
		$param = $DB->fetch_column($query);
		
		$query = "select * from shop_product_value where param_id in (0,".implode(",", array_keys($param)).")";
		
		$data = $DB->query($query);
		$result = array();
		reset($data);
		while (list(,$row) = each($data)) {
			$data_type = $param[$row['param_id']];
			if($data_type == 'fkey') $data_type = 'char';
			$result[] = $row['value_'.$data_type];
		}
		$result = array_unique($result);
		asort($result);
		return $result;
	}
	
	/**
	 * ���������� ������ �����, � ������� ���� ��������
	 *
	 * @param string $search - ��������� ������
	 * @return array
	 */
	public function searchGroups($search) {
		global $DB;
				
		$query = "
			select group_id, count(*) as amount
			from shop_product
			where _search like '%$search%'
			group by group_id
			order by amount desc
		";
		$groups = $DB->fetch_column($query);
		
		// ���������� ������, ������� ��������� �� ��������� ������
		$query = "
			select id, id as id2
			from shop_group_relation 
			where id in (0".implode(",", array_keys($groups)).") and parent='$this->group_id'
		";
		$filter = $DB->fetch_column($query);
		reset($groups);
		while (list($group_id,) = each($groups)) {
			if (!isset($filter[$group_id])) {
				unset($groups[$group_id]);
			}
		}
		
		$query = "
			select 
				tb_group.id as real_id,
				tb_group.id,
				tb_group.group_id as parent,
				tb_group.name,
				tb_group.url
			from shop_group_relation as tb_relation
			inner join shop_group as tb_group on tb_group.id=tb_relation.parent
			where tb_relation.id in (0".implode(",", array_keys($groups)).")
		";
		$data = $DB->query($query, 'id');
		reset($data);
		while (list($index, $row) = each($data)) {
			if (!isset($groups[$row['id']])) {
				continue;
			}
			$data[$index]['name'] = '<a href="/'.$row['url'].'/?search='.urlencode($search).'">'.$row['name'].'</a> ('.$groups[$row['id']].')';
		}
		return $data;
	}
	
	/**
	 * ��������� ������ �������
	 *
	 * @param int $page_start
	 * @param int $rows_per_page
	 * @param int $group_id ���� ������ �� ������� �� ��������� ��� ������, ������� ���� � �������
	 * @param array $filter - ������, � ������� ����������� ��������� ���������� �������
	 * @param string $search - ����� ������ �� �����
	 * @return array
	 */
	public function getProducts($page_start, $rows_per_page, $group_id = 0, $filter = array(), $search = '', $order = 'price', $order_direction = 'asc') {
		global $DB;
		
		$join  = '';
		$where = '';
		
		// ���������� �������� �������, � ������� ��������� ������
		if (!empty($group_id) && $group_id != $this->group_id) {
			$query = "select _param_group_id from shop_group where id='$group_id'";
			$table_name = 'shop_x_'.$DB->result($query);
		} else {
			$table_name = $this->table_name;
		}
		 
		// ������� ������� ���������� ������
		reset($filter);
		while (list($field, $value) = each($filter)) {
			$where .= where_clause($field, $value);
		}
		
		// ������� ������� ������
		if (!empty($search)) {
			$join .= "inner join shop_product as tb_search on tb_search.id=tb_product.id";
			$where .= "and tb_search._search like '%".$DB->escape($search)."%'";
		}
		
		$query = "
			select sql_calc_found_rows
				tb_product.*,
				concat(tb_group.url, '/', tb_product.url) as url,
				tb_group.url as group_url,
				tb_product.url as product_url
			from `$table_name` as tb_product
			inner join shop_group as tb_group on tb_group.id=tb_product.group_id
			$join
			where 1 
				$where 
				".where_clause('tb_product.group_id', $group_id)."
			order by tb_product.$order $order_direction
			".Misc::limit_mysql($rows_per_page, 0, $page_start)."
		";
		$data = $DB->query($query, 'id');
		$this->total_products = $DB->result("select found_rows()");
		return $data;
	}
	
	/**
	 * ���� ��� ������, � ������� ���� ��������� � ������� �������� � ��������
	 *
	 * @param int $page_start
	 * @param int $rows_per_page
	 * @param array $filter
	 */
	public function getAllProducts($page_start, $rows_per_page, $filter, $order = 'price', $order_direction = 'asc') {
		global $DB;
		$return = array();
		
		// ������ id ����������, ������� ���� � �������
		$query = "select id, uniq_name from shop_group_param where uniq_name in ('".implode("', '", array_keys($filter))."')";
		$param = $DB->fetch_column($query);
		
		$where = array();
		reset($param);
		while (list($param_id, $param_name) = each($param)) {
			$where[] = "(param_id='$param_id' and value_int='".$filter[$param_name]."')";
		}
		
		// ���� ������ � ���������� ����������
		$query = "
			select product_id
			from shop_product_value
			where ".implode(" AND ", $where)."
			group by product_id
			having count(*)=".count($filter)."
			".Misc::limit_mysql($rows_per_page, 0, $page_start)."
		";
		$products = $DB->fetch_column($query);
		reset($products);
		while (list(,$product_id) = each($products)) {
			$return[] = $this->getProductInfo($product_id, true);
		}
		
		return $return;
	}
	
		
	/**
	 * ���������� ��� ���������� ��� ��������� ������
	 * @param array $products
	 */
	public function getDirectProductInfo($list) {
		global $DB;
		
		$products = array();
		if(empty($list)){
			return $products;
		}
		
		$query  = "
			SELECT id, group_id, price, name 
			FROM shop_product 
			WHERE id IN ('".implode("', '", $list)."') 
			ORDER BY priority ASC
		";
		$products = $DB->query($query, "id");
	
		// ������ id ����������, ������� ���� � �������
		$query  = "
			select 
				tb_value.product_id,
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
				end as value
			from shop_product_value as tb_value
			inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
			where tb_value.product_id IN ('".implode("', '", $list)."')
		";
		$values = $DB->query($query);
		
		reset($values);
		while(list(, $value) = each($values)){
			if(!isset($products[$value['product_id']])){
				continue;
			}
			$products[$value['product_id']][$value['uniq_name']] = $value['value'];
		}
		return $products;
	}

}

