<?php
/**
 * Фильтр, который выводится в cmsView
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class cmsFilter {
	
	/**
	 * Шаблон
	 *
	 * @var object
	 */
	private $Template;
	
	/**
	 * Парсер SQL
	 *
	 * @var object
	 */
	private $SQLParser;
	
	/**
	 * Сохранённые значения фильтра
	 *
	 * @var array
	 */
	private $filter = array();
	
	/**
	 * Условие для фильтра
	 *
	 * @var array
	 */
	private $where = array();
	
	/**
	 * Соединение с БД
	 *
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * Номер таблицы на странице
	 *
	 * @var int
	 */
	private $instance_number = 0;

	/**
	 * Возможные условия фильтрации для разных типов данных
	 * @var array
	 */
	private $filter_conditions = array(
			'int' => array(
				'' => 'Игнорировать',
				'=' => 'Равно',
				'!=' => 'Не равно',
				'<' => 'Меньше',
				'<=' => 'Меньше или равно',
				'>' => 'Больше',
				'>=' => 'Больше или равно',
				'between' => 'Внутри интервала',
				'not between' => 'Вне интервала',
				'is null' => 'Равно пустому значению',
				'is not null' => 'Не равно пустому значению',
			),
			'char' => array(
				'' => 'Игнорировать',
				'like' => 'Содержит',
				'not like' => 'Не содержит',
				'=' => 'Равно',
				'!=' => 'Не равно',
				'is null' => 'Равно пустому значению',
				'is not null' => 'Не равно пустому значению',
			),
			'fkey' => array(
				'' => 'Игнорировать',
				'=' => 'Равно',
				'!=' => 'Не равно',
				'in' => 'Одно из значений',
				'not in' => 'Не соответсвует значению',
				'is null' => 'Равно пустому значению',
				'is not null' => 'Не равно пустому значению',
			),
	);
	
	/**
	 * Типы полей, которые не выводятся в фильтре
	 *
	 * @var array
	 */
	private $skip = array(
		'swf_upload',
		'html',
		'time',
		'file'
	);
	
	/**
	 * Конструктор
	 *
	 * @param object $Template
	 * @param object $SQLParser
	 * @param object $DBServer
	 * @param int $instance_number
	 */
	public function __construct(&$Template, &$SQLParser, &$DBServer, $instance_number) {
		global $DB;
				
		$this->Template = $Template;
		$this->SQLParser = $SQLParser;
		$this->DBServer = $DBServer;
		$this->instance_number = $instance_number;
		
		// Загружаем значения из фильтра
		$query = "
			select *
			from cms_filter_active_view 
			where 
					structure_id='".CMS_STRUCTURE_ID."'
				and admin_id='".Auth::getUserId()."'
				and instance_number='$this->instance_number'
		";
		$this->filter = $DB->query($query, 'field_id');
		
		// Формируем where условие для фильтра
		$this->where = $this->whereClause($this->filter);
	}
	
	

	/**
	 * Формирует условие where, на основании данных, которые поступили в фильтр
	 * 
	 * @return array
	 */
	private function whereClause() {
		global $DB;
		
		$where = array();
		$table = array_flip($this->SQLParser->tables);
		// Поля для фильтрации значений n:n
		$query = "
			select 
				tb_field.id,
				tb_field.table_name,
				tb_field.full_name as field_name,
				tb_relation.table_name as relation_table_name,
				tb_fk_field.field_name as relation_fk_field_name
			from cms_field_static as tb_field
			inner join cms_table_static as tb_relation on tb_relation.id=tb_field.fk_link_table_id
			inner join cms_field_static as tb_fk_field on tb_fk_field.table_id=tb_relation.id and tb_fk_field.fk_table_id=tb_field.fk_table_id
			where 
				tb_field.table_name in ('".implode("','", array_keys($table))."') 
				and tb_field.fk_link_table_id!=0
		";
		$tree = $DB->query($query, 'id');
		
		reset($this->filter); 
		while (list($field_id, $row) = each($this->filter)) {
			// Пропускаем таблицы, которых нет в запросе. Такие данные поступили из другого cmsView, который находится на этой же странице
			if (!isset($table[$row['table_name']])) {
				continue;
			}
			$current_table = $table[$row['table_name']];
			
			if (isset($tree[substr($row['field_id'], 0, -1)])) {
				$current_relation = $tree[substr($row['field_id'], 0, -1)];
				$current_relation['alias'] = $table[$current_relation['relation_table_name']];
			} else {
				$current_relation = array();
			}
			
			if ($row['pilot_type'] == 'boolean' && empty($row['value_1'])) {
				// Boolean флаг = 0
				$where[] = "`$current_table`.`$row[field_name]` $row[condition] 0";
				
			} elseif ($row['data_type'] == 'set' && $row['condition'] == '=') {
				// SET =
				$where[] = "find_in_set('$row[value_1]', `$current_table`.`$row[field_name]`) > 0";
				
			} elseif ($row['data_type'] == 'set' && $row['condition'] == '=') {
				// SET !=
				$where[] = "find_in_set('$row[value_1]', `$current_table`.`$row[field_name]`) = 0";
				
			} elseif ($row['condition'] == 'like' || $row['condition'] == 'not like') {
				// Похоже
				$where[] = "`$current_table`.`$row[field_name]` $row[condition] '%$row[value_1]%'";
				
			} elseif (in_array($row['condition'], array('between', 'not between'))) {
				// Числовой интервал или интервал дат
				$where[] = "`$current_table`.`$row[field_name]` $row[condition] '$row[value_1]' and '$row[value_2]'";
				
			} elseif (isset($tree[substr($row['field_id'], 0, -1)])) {
				
				// В запросе должна присутствовать relation таблица
				if (!empty($table[$current_relation['relation_table_name']])) {
					$where[] = "`$current_relation[alias]`.`$current_relation[relation_fk_field_name]` $row[condition] '$row[value_1]'";
				}
				
			} elseif ($row['pilot_type'] == 'int' && $row['condition'] == '=' && strpos($row['value_1'], ',') !== false) {
				// Список значений
				// 29.11.2011 обработка значения фильтрации 25,x
				$value_1 = preg_split("/,/", $row['value_1'], -1, PREG_SPLIT_NO_EMPTY);
				$where[] = "`$current_table`.`$row[field_name]` IN ('".implode("', '", $value_1)."')";
				
			} elseif ($row['pilot_type'] == 'int' && $row['condition'] == '!=' && strpos($row['value_1'], ',') !== false) {
				// Список значений
				$where[] = "`$current_table`.`$row[field_name]` NOT IN ($row[value_1])";
				
			} elseif ($row['condition'] == 'is null' || $row['condition'] == 'is not null') {
				// Пустое / не пустое
				$where[] = "`$current_table`.`$row[field_name]` $row[condition]";
				
			} elseif ($row['data_type'] == 'datetime' || $row['data_type'] == 'timestamp') {
				// Дата и время
				$where[] = "date(`$current_table`.`$row[field_name]`) $row[condition] '$row[value_1]'";
				
			} else {
				// Дата или обычное условие
				$where[] = "`$current_table`.`$row[field_name]` $row[condition] '$row[value_1]'";
			}
		}
		return $where;
	}

	
	/**
	 * Метод, который отображает форму для фильтрации данных
	 *
	 * @param array $skip_tables
	 * @param array $skip_fields
	 * @param int $_show_table_id
	 * @return string
	 */
	public function show($skip_tables, $skip_fields, $_show_table_id=0) {
		$show = false;
		
		/**
		 * Должен ли быть показан пользователю фильтр
		 */
		(empty($this->where)) ? $this->Template->set('show_filter', 'none') : $this->Template->set('show_filter', 'block');
		
		/**
		 * Изменяем условие запроса
		 */
		$this->SQLParser->changeCondition($this->where, false);
		
		/**
		 * Удаляем дубликаты таблиц
		 */
		$this->SQLParser->tables = array_unique($this->SQLParser->tables);
		 
		/**
		 * Параметры подсчета полей в соответствии с их характеристиками
		 */
		$fields_total_all = 0;
		$fields_total_dominant = 0;
		
		$fields_table_all = 0;
		$fields_table_dominant = 0;  
		
		/**
		 * Вывод полей фильтра
		 */
		reset($this->SQLParser->tables);
		while (list($table_alias, $table_name) = each($this->SQLParser->tables)) {
			
			// Таблица отключена вручную
			if (isset($skip_tables[$table_name])) continue;
			
			// Таблица не найдена
			$table = cmsTable::getInfoByAlias($this->DBServer->db_alias, $table_name);
			if (empty($table)) continue;
			
			// Определяем, является ли текущая таблица основной при выводе в cmsShowView
			$is_table_show = ($_show_table_id == $table['id']) ? true : false;
			
			// Поля текущей таблицы
			$fields = cmsTable::getFields($table['id']);
			
			// Определяем поля, что необходимо выводить сразу
			reset($fields);  
			while(list($field_name, $field) = each($fields)){
				
				// Пропускаем поля, что не добавлены в фильтр
				if(empty($field['show_in_filter'])) continue;
				
				$fields_total_all++; 
				
				// Если поле занесено в сессию 
				if(!empty($_SESSION['cms_filter'][CMS_STRUCTURE_ID][$this->instance_number]['checked']) && in_array($field['id'], $_SESSION['cms_filter'][CMS_STRUCTURE_ID][$this->instance_number]['checked'])){
					$fields[$field_name]['show_as_checked'] = true;
					$table['show_as_checked'] = true;
				}
				
				// Если для поля найдено сохраненное в базе значение 
				if(!empty($this->filter[$field['id']."_"])){
					$fields[$field_name]['show_as_checked'] = true;
					$table['show_as_checked'] = true;  
				}
				  
				// Доминантными полями могут быть лишь поля из рассматриваемой таблицы
				if(!$is_table_show) continue;
				$fields_table_all++; 
				
				// Если текущее поле назначено таблице для внешнего отображения, отмечаем его, как доминантное (поле фильтра, что необходимо выводить всегда)
				if ($table['fk_show_id'] == $field['id']) $fields[$field_name]['show_as_dominant'] = true;
				
				// Если это поле соответствует одному из списка, отмечаем его, как доминантное
				if (in_array($field_name, array('id', 'uniq_name'))) $fields[$field_name]['show_as_dominant'] = true;
				
				// Подсчет доминантных полей
				if(!empty($fields[$field_name]['show_as_dominant'])){
					$fields_total_dominant++;  
					$fields_table_dominant++;
				}
			}
			
			// Если ни один доминант так и не был определен, ставим в роль доминанта первое поле из главной таблицы
			if($is_table_show && empty($fields_table_dominant)){
				
				reset($fields);
				while (list($field_name, $field) = each($fields)) { 
					if(empty($field['show_in_filter'])) continue;
					
					$fields[$field_name]['show_as_dominant'] = true; 
					$fields_total_dominant++; 
					$fields_table_dominant++;  
					break;
				}
			}
			
			// Вывод таблиц
			if(!empty($fields_table_dominant)) $table['show_as_dominant'] = true;
			$TmplTable = $this->Template->iterate('/filter_table/', null, $table);
			
			// Вывод полей
			reset($fields);
			while (list($field_name, $field) = each($fields)) { 
				
				// пропускаем некоторые типы полей
				if ($field['show_in_filter'] == 0 || isset($skip_fields[$table_name][$field_name])) {
					continue;
				}
				if(in_array($field['cms_type'], $this->skip)) {
					continue;
				}
				
				if ($field['field_type'] == 'passwd_md5' || $field['name'] == 'priority' || ($field['cms_type'] == 'money' && !empty($field['fk_table_id']))) {
					continue;
				}
				
				$field_code = $field['id'].'_'.$field['field_language'];
				$field['title'] = (empty($field['title'])) ? $field['name'] : $field['title'];
				$field['uniq_id'] = uniqid();
				$field['table_title'] = make_subtitle('table_title', $table['title']);
				$field['name'] = 'filter['.$field_code.']';
				$field['field_code'] = $field_code;
		
				// Определяем выбранное значение фильтра и условие
				if (isset($this->filter[$field_code])) {
					$field['value_1'] = stripslashes($this->filter[$field_code]['value_1']);
					$field['value_2'] = stripslashes($this->filter[$field_code]['value_2']);
					$field['condition'] = $this->filter[$field_code]['condition'];
				} else {
					$field['value_1'] = '';
					$field['value_2'] = '';
					$field['condition'] = '';
				}
				
				/**
				 * Обрабатываем разные типы полей
				 */
				if ($field['cms_type'] == 'checkbox_set' || $field['cms_type'] == 'radio' || $field['pilot_type'] == 'boolean' || $field['pilot_type'] == 'variant') {
					$field = $this->showCheckboxSet($field);
					
				} elseif (in_array($field['cms_type'], array('fk_list', 'fk_tree', 'fk_nn_tree', 'fk_nn_list', 'ext_multiple'))) {
					$field = $this->showFkList($field);
					
				} elseif (in_array($field['cms_type'], array('fk_cascade', 'fk_nn_cascade'))) {
					$field = $this->showFKCascade($field);
					
				} elseif ($field['is_real'] && !empty($field['fk_table_id'])) {
					$field = $this->showAjax($field);
					
				} elseif ($field['pilot_type'] == 'char' || $field['pilot_type'] == 'text') {
					$field = $this->showChar($field);
					
				} elseif ($field['pilot_type'] == 'int' || $field['pilot_type'] == 'decimal') {
					$field = $this->showInt($field);
					
				} elseif ($field['pilot_type'] == 'date') {
					$field = $this->showDate($field);
					
				} else {
					continue;
				}
			
				$field['index'] = $field['id'];
				$field['id'] = 'filter_'.$this->instance_number.'_'.$field_code;
				$field['display'] = (in_array($field['condition'], array('between', 'not between'))) ? 'inline' : 'none';
				$field['width'] = (isset($field['conditions']['between'])) ? 40 : 100;
				
				if (!$field['is_nullable']) {
					unset($field['conditions']['is null']);
					unset($field['conditions']['is not null']);
				}
				
				// Выделяем те разделы, которые выбраны
				$field['class'] = (!empty($field['condition'])) ? 'selected' : '';
				 
				$this->Template->iterate('/filter_table/filter_field/', $TmplTable, $field);
				$show = true; 
			}
		
			// Проверка существования дополнительных условий поиска в главной таблице
			if($is_table_show && $fields_table_all > $fields_table_dominant){ 
				$this->Template->setGlobal('fields_select_box_table_exists', true);
			} 
		} 
		
		// Проверка на существование дополнительных условий поиска 
		if($fields_total_all > $fields_total_dominant){  
			$this->Template->setGlobal('fields_select_box_total_exists', true);
		}
		
		// Проверка на существование доминантных полей
		if(!empty($fields_total_dominant)){     
			$this->Template->setGlobal('fields_dominant_exists', true);
		}
		
		return $show;
	}
	
	
	private function showDate($field) {
		$field['conditions'] = $this->filter_conditions['int'];
		$field['value_1'] = (!empty($field['value_1'])) ? date('d.m.Y', convert_date('y-m-d', $field['value_1'])) : '';
		$field['value_2'] = (!empty($field['value_2'])) ? date('d.m.Y', convert_date('y-m-d', $field['value_2'])) : '';
		return $field;
	}
	
	
	private function showCheckboxSet($field) {
		global $DB;
		$field['conditions'] = $this->filter_conditions['fkey'];
		unset($field['conditions']['in']);
		unset($field['conditions']['not in']);
		$field['checkbox_value'] = ($field['pilot_type'] == 'boolean') ? 1 : 'true';
		$field['checked'] = ($field['checkbox_value'] == $field['value_1']) ? 'checked': '';
		$query = "
			SELECT 
				name,
				if(title_".LANGUAGE_CURRENT."='' OR title_".LANGUAGE_CURRENT." IS NULL, name, title_".LANGUAGE_CURRENT.") as title
			FROM cms_field_enum
			WHERE field_id='$field[id]'
		";
		$field['options'] = $DB->fetch_column($query);
		return $field;
	}
	
	private function showFkList($field) {
		$field['conditions'] = $this->filter_conditions['fkey'];
		unset($field['conditions']['in']);
		unset($field['conditions']['not in']);
		$field['value_1'] = preg_split("/,/", $field['value_1'], -1, PREG_SPLIT_NO_EMPTY);
		$field['options'] = cmsTable::loadInfoList($field['fk_table_id']);
		if (count($field['value_1']) > 1) {
			$field['multiple_1'] = '[]';
			$field['multiple_2'] = 'multiple size="5" ';
		}
		return $field;
	}
	
	private function showFkTree($field) {
		$field['conditions'] = $this->filter_conditions['fkey'];
		unset($field['conditions']['in']);
		unset($field['conditions']['not in']);
		$data = cmsTable::loadInfoTree($field['fk_table_id']);
		$Tree = new Tree($data, $field['value_1']);
		$field['tree'] = $Tree->build();
		return $field;
	}
	
	private function showFKCascade($field) {
		$field['conditions'] = $this->filter_conditions['fkey'];
		$data = cmsTable::loadInfoCascade($field['fk_table_id']);
		$Tree = new Tree($data, $field['value_1']);
		$field['tree'] = $Tree->build();
		return $field;
	}
	
	private function showAjax($field) {
		$field['conditions'] = $this->filter_conditions['fkey'];
		$field['text_value'] = (!empty($field['value_1'])) ? cmsTable::showFK($field['fk_table_id'], $field['value_1']) : '';
		return $field;
	}
	
	private function showChar($field) {
		$field['conditions'] = $this->filter_conditions['char'];
		return $field;
	}
	
	private function showInt($field) {
		$field['conditions'] = $this->filter_conditions['int'];
		return $field;
	}
}


?>