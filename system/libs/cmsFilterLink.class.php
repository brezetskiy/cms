<?php
/**
 * Класс, при помощи которого можно создать ссылку на определённую
 * страницу с указанными условиями фильтрации
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
class cmsFilterLink {
	
	private $structure_id = 0;
	private $instance_number = 1;
	private $admin_id = 0;
	private $filter = array();
	private $db_alias = '';
	private $link = '';
	
	public function __construct($db_alias, $link, $instance_number = 1) {
		global $DB;
		$this->link = $link;
		$this->instance_number = $instance_number;
		$this->admin_id = Auth::getUserId();
		$this->db_alias = $db_alias;
		$link = parse_url($link);
		$link = substr($link['path'], strlen('/Admin/'), -1);
		$query = "select id from cms_structure where url='$link'";
		$this->structure_id = $DB->result($query);
	}
	
	public function addCondition($condition, $table_name, $field_name, $value_1, $value_2 = '', $is_dummie = 0) {
		$this->filter[$table_name][$field_name] = array(
			'condition' => $condition,
			'value_1' => $value_1,
			'value_2' => $value_2,
			'dummie' => $is_dummie
		);
	}
	
	public function getLink() {
		global $DB;
		$param = array();
		reset($this->filter);
		while (list($table_name,) = each($this->filter)) {
			$fields = array_keys($this->filter[$table_name]);
			$query = "
				select full_name, concat(id, '_', field_language)
				from cms_field_static 
				where 
					table_name='$table_name' and 
					full_name in ('".implode("','", $fields)."') and
					db_alias='".$this->db_alias."' 
			";
			$data = $DB->fetch_column($query);
			reset($data);
			while (list($field_name, $field_code) = each($data)) {
				$value_1 = (is_array($this->filter[$table_name][$field_name]['value_1'])) ? implode(',', $this->filter[$table_name][$field_name]['value_1']): $this->filter[$table_name][$field_name]['value_1'];
				$param[] = urlencode("filter[$field_code][condition]")."=".urlencode($this->filter[$table_name][$field_name]['condition']);
				$param[] = urlencode("filter[$field_code][0]")."=".urlencode($value_1);
				$param[] = urlencode("filter[$field_code][1]")."=".urlencode($this->filter[$table_name][$field_name]['value_2']);
			}
		}
		$return = '/action/admin/cms/table_filter/?_return_path='.urlencode($this->link).'&structure_id='.$this->structure_id.'&instance_number='.$this->instance_number.'&';
		return $return.implode("&", $param);
	}
}

?>