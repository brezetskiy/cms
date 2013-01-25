<?php
/**
* Класс который строит меню
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* Класс который строит меню
* @package Pilot
* @subpackage CMS
*/
Class Tree {
	
	/**
	 * Уровень вложенности текущего элемента
	 * 
	 * @var int 
	 */
	private $level = -1;
	
	/**
	 * Массив данных, которые необходимо обработать
	 * 
	 * @var array
	 */
	private $data = array();
	
	/**
	 * Массив со структурой
	 * 
	 * @var array
	 */
	private $relation = array();
	
	/**
	 * id выбранного раздела или текущего
	 * 
	 * @var array
	 */
	private $selected_id = array();
	
	/**
	 * Перечень эелементов, которые были выведены
	 *
	 * @var array
	 */
	public $used = array();
	
	/**
	 * Формат отображения данных
	 *
	 * @var string
	 */
	private $type = 'select';
	
	/**
	 * Конструктор
	 *
	 * @param array $data массив данных в виде [$id] = array(real_id, id, parent, name)
	 * @param mixed $selected_id выбранный элемент
	 * @param string $type формат отображения данных select - для <select> элементов или ul - для формирования списка <li>
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
		
		
		// Строим связи
		reset($data);
		while(list($key, $val) = each($data)) {
			$this->relation[$val['parent']][] = $val['id'];
			if(!isset($val['real_id'])) {
				$data[$key]['real_id'] = $val['id'];
			}
		}
	}
	
	/**
	* Строит подуровни
	* 
	* @param int $id
	* @return void
	*/
	public function build($id = 0, $return = '') {
		if (!isset($this->relation[$id]) || count($this->relation[$id]) == 0) {
			return;
		}
		$return .= ($this->type == 'list') ? '<ul>': '';
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
		$return .= ($this->type == 'list') ? '</ul>': '';
		$this->level--;
		return $return;
	}

	/**
	 * Дизайн для вывода информации в виде поля select, с расскраской полей
	 * @param int $id
	 * @return void
	 */
	private function designSelect($id) {
		if (!isset($this->data[$id])) {
			// 25.04.2011 rudenko в логах были ошибки, поэтому добавлено это условие
			return '';
		} elseif (in_array($this->data[$id]['real_id'], $this->selected_id)) {
			$index = array_search($this->data[$id]['real_id'], $this->selected_id);
			unset($this->selected_id[$index]);
			$selected = 'selected';
		} else {
			$selected = '';
		}
		$this->used[] = $this->data[$id]['real_id'];
		return '<option class="level_'.$this->level.'" '.$selected.' value="'.$this->data[$id]['real_id'].'">'.str_repeat('|&nbsp;&nbsp;&nbsp;', $this->level).'|--'.substr($this->data[$id]['name'], 0, 50)."</option>\n";
	}

	/**
	 * Дизайн для вывода информации в виде списка ul
	 * @param int $id
	 * @return void
	 */
	private function designList($id) {
		$this->used[] = $this->data[$id]['real_id'];
		return '<li>'.$this->data[$id]['name']."</li>\n";
	}

}
?>