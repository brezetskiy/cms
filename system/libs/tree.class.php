<?php
/**
* ����� ������� ������ ����
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* ����� ������� ������ ����
* @package Pilot
* @subpackage CMS
*/
Class Tree { 
	
	/**
	 * ������� ����������� �������� ��������
	 * 
	 * @var int 
	 */
	private $level = -1;
	
	/**
	 * ������ ������, ������� ���������� ����������
	 * 
	 * @var array
	 */
	private $data = array();
	
	/**
	 * ������ �� ����������
	 * 
	 * @var array
	 */
	private $relation = array();
	
	/**
	 * id ���������� ������� ��� ��������
	 * 
	 * @var array
	 */
	private $selected_id = array();
	
	/**
	 * �������� ���������, ������� ���� ��������
	 *
	 * @var array
	 */
	public $used = array();
	
	/**
	 * ������ ����������� ������
	 *
	 * @var string
	 */
	private $type = 'select';
	
	/**
	 * ������� ������� ������
	 *
	 * @var string
	 */
	private $current_id = '';
	
	/**
	 * ������� ������� ������
	 *
	 * @var string
	 */
	private $depth = 0;
	
	
	
	/**
	 * �����������
	 *
	 * @param array $data ������ ������ � ���� [$id] = array(real_id, id, parent, name)
	 * @param mixed $selected_id ��������� �������
	 * @param string $type ������ ����������� ������ select - ��� <select> ��������� ��� ul - ��� ������������ ������ <li>
	 */
	public function __construct($data, $selected_id = array(), $type = 'select') {
		$this->data = $data;
		$this->type = $type;
		
		if (empty($selected_id)) {
			$this->selected_id = array();
		} elseif (is_array($selected_id)) {
			$this->selected_id = $selected_id;
		} else {
			$this->selected_id = array($selected_id);
		}
		
		
		// ������ �����
		reset($data);
		while(list($key, $val) = each($data)) {
			$this->relation[$val['parent']][$val['id']] = $val['id'];
			if(!isset($val['real_id'])) {
				$data[$key]['real_id'] = $val['id'];
			}
		}
		
		ksort($this->relation);
		
		$this->depth = 0; 
		$this->depth = $this->defineRelationDepth($this->relation);
	}
	
	
	/**
	* ������ ���������
	* 
	* @param int $id
	* @return void
	*/
	public function build($id = 0, $return = '') {
		if (!isset($this->relation[$id]) || count($this->relation[$id]) == 0) {
			return;
		}
		
		if($this->type == 'list'){
			$return .= "<ul>";
		} else {
			$return .= "";
		}
		
		$this->level++;
		
		reset($this->relation[$id]);
		while (list(, $val) = each($this->relation[$id])) {
			if ($this->type == 'list') {
				$return .= $this->designList($val);
			} else {
				$return .= $this->designSelect($val);
			}
			$return .= $this->build($val);
		} 
		
		if($this->type == 'list'){
			$return .= "</ul>";  
		} else {
			$return .= "";
		}
		
		$this->level--;
		return $return;
	}

	
	/**
	 * ������ ��� ������ ���������� � ���� ���� select, � ����������� �����
	 * @param int $id
	 * @return void
	 */
	private function designSelect($id) {
		if (!isset($this->data[$id])) {
			// 25.04.2011 rudenko � ����� ���� ������, ������� ��������� ��� �������
			return '';
			
		} elseif (in_array($this->data[$id]['real_id'], $this->selected_id)) {
			$index = array_search($this->data[$id]['real_id'], $this->selected_id);
			unset($this->selected_id[$index]);
			$selected = 'selected';
			
		} else {
			$selected = '';
		}

		$this->used[] = $this->data[$id]['real_id'];
		
		// return '<option class="level_'.$this->level.'" '.$selected.' value="'.$this->data[$id]['real_id'].'">'.str_repeat('|&nbsp;&nbsp;&nbsp;', $this->level).'|--'.substr($this->data[$id]['name'], 0, 50)."</option>\n";
		return '<option class="level_'.$this->level.'" '.$selected.' value="'.$this->data[$id]['real_id'].'" style="padding-left:'.($this->level*10).'px">'.substr($this->data[$id]['name'], 0, 50)."</option>\n";
	}

	
	/**
	 * ������ ��� ������ ���������� � ���� ������ ul
	 * @param int $id
	 * @return void
	 */
	private function designList($id) {
		$this->used[] = $this->data[$id]['real_id'];
		return '<li>'.$this->data[$id]['name']."</li>\n";
	}

	
	/**
	 * ����������� ����������� ������������ ������� ������
	 * 
	 * @param array $array
	 * @return int 
	 */
	private function defineRelationDepth($relation_level) {
		$max_depth = 0;
		 
	    reset($relation_level); 
	    while(list($element_id, ) = each($relation_level)){ 
			if (isset($this->relation[$element_id]) && is_array($this->relation[$element_id])) {
				$depth = $this->defineRelationDepth($this->relation[$element_id]) + 1;
				if ($depth > $max_depth) $max_depth = $depth;
			}
	    }
	    
	    return $max_depth;
	} 
}


?>