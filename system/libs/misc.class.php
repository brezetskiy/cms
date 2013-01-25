<?php
/**
 * Класс, который содержит разные функции, которые в большинстве своем не связаны друг с другом
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2005, Delta-X ltd.
 */

/**
 * Класс, который содержит разные функции, которые в большинстве своем не связаны друг с другом
 * @package Pilot
 * @subpackage CMS
 */
class Misc {
	
	static function cmsFKeyReference($fk_table_id, $selected_id = 0, $filter = array(), $offset = 0) {
		global $DB;

		$select = array();
		$where = array(1);
		$table = cmsTable::getInfoById($fk_table_id);
		$fields = cmsTable::getFields($fk_table_id);
		
		$Template = new Template(SITE_ROOT.'templates/cms/admin/fkey_reference');
		$Template->setGlobal('table_id', $fk_table_id);
		
		reset($fields);
		while (list(,$row) = each($fields)) {
			if ($row['is_reference'] == 0 && $row['name'] != 'id' && $row['name'] != $table['fk_show_name']) {
				continue;
			}
			$select[] = $row['name'];
			if (isset($filter[$row['name']]) && !empty($filter[$row['name']])) {
				$where[] = "`$row[name]` like '%".$filter[$row['name']]."%'";
				$row['filter_value'] = $filter[$row['name']];
			}
			
			if ($row['name'] == 'id') {
				$row['width'] = "10%";
			}
			$Template->iterate('/title/', null, $row);
		}
		
		$order_by_index = (!empty($filter['id'])) ? " if(id={$filter['id']}, 0, 1) ASC, " : "";
		
		$query = "
			select sql_calc_found_rows `".implode("`,`", $select)."`
			from `$table[table_name]` 
			where ".implode(" AND ", $where)."
			order by $order_by_index `$table[fk_order_name]` $table[fk_order_direction]
		".self::limit_mysql(20, 0, $offset);
		$data = $DB->query($query);
		
		reset($data);
		while (list(,$row) = each($data)) {
			$tmpl_row = $Template->iterate('/row/', null, $row);
			$title = str_replace("'", '', implode("; ", $row));
			reset($row);
			while (list($key, $value) = each($row)) {
				$Template->iterate('/row/field/', $tmpl_row, array('value' => $value, 'name' => $key, 'title' => htmlspecialchars($title)));
			}
		}
		
		// Пролистывание страниц
		$query = "select found_rows()";
		$total_rows = $DB->result($query);
		
		$Template->set('page_list', self::pages($total_rows, 20, 10, 0, true, false, null, 'send({$offset});', $offset));
		
		
		return $Template->display();
	}

	/**
	* Выводит список страниц
	* @param int $total_rows
	* @param int $rows_per_pages
	* @param int $show_pages
	* @return void
	*
	static function pages_list($total_rows, $rows_per_page, $show_pages, $anchor_name = null) {
		if ($total_rows <= $rows_per_page) return;
		if (PAGE_START - $rows_per_page < 0) {
			$previous = 0;
		} elseif (PAGE_START - $rows_per_page > $total_rows) {
			$previous = $total_rows - 1;
		} else {
			$previous = PAGE_START - $rows_per_page;
		}
		
		if (!empty($anchor_name)) {
			$anchor_name = "#$anchor_name";
		}
		
		// Определяем параметры, переданные методом GET
		$get = $_GET;
		unset($get['_start']);
		unset($get['_REWRITE_URL']);
		unset($get['_GALLERY_URL']);
		$get = http_build_query($get);
		
		$next = (PAGE_START + $rows_per_page >= $total_rows) ?	$total_rows - 1 : PAGE_START + $rows_per_page;
		$return = '<table border="0" cellpadding="0" cellspacing="5" class="pages_list">';
		// Показываем ссылку на предыдущую страницу
		$return.= (PAGE_START != 0) ?
			'<td onclick="self.location=\'?_start='.$previous.'&'.$get.$anchor_name.'\'" class="go_left"><a href="?_start='.$previous.'&'.$get.$anchor_name.'"><img align="absmiddle" src="/img/shared/button/previous.gif" alt="Предыдущая страница" border="0"></a></td>':
			'<td class="go_left"><img class="disabled" align="absmiddle" src="/img/shared/button/previous.gif" alt="Предыдущая страница" border="0"></td>';
		// Формируем список страниц
		$total_pages = ceil($total_rows / $rows_per_page) - 1;
		$current_page = ceil(PAGE_START / $rows_per_page) + 1;
		$first_page = $current_page - $show_pages - 1;
		$last_page = $current_page + $show_pages - 1;
		if ($first_page < 0) 			$last_page = $show_pages * 2;
		if ($last_page > $total_pages) 	$first_page = $total_pages - $show_pages * 2;
		// Не совмещать!!! if условия
		if ($first_page < 0) 			$first_page = 0;
		if ($last_page > $total_pages) 	$last_page = $total_pages;
		
		for ($i = $first_page; $i <= $last_page; $i++) {
			$return .= ($i * $rows_per_page == PAGE_START) ?
				'<td class="selected_page">'.intval($i + 1).'</a></td>' :
				'<td onclick="self.location=\'?_start='.intval($i * $rows_per_page).'&'.$get.$anchor_name.'\'"><a href="?_start='.intval($i * $rows_per_page).'&'.$get.$anchor_name.'">'.intval($i + 1).'</a></td>';
		}
		// Показываем ссылку на следующую страницу
		$return .= (PAGE_START + $rows_per_page < $total_rows) ?
			'<td onclick="self.location=\'?_start='.$next.'&'.$get.$anchor_name.'\'" class="go_right"><a href="?_start='.$next.'&'.$get.$anchor_name.'"><img align="absmiddle" src="/img/shared/button/next.gif" alt="Следующая страница" border="0"></a></td>':
			'<td class="go_right"><img class="disabled" align="absmiddle" src="/img/shared/button/next.gif" alt="Следующая страница" border="0"></td>';
		return $return.'</table>';
	}
	
	/**
	 * Выводит ссылки на пролистывание страниц
	 *
	 * @param int $total_rows - суммарное количество рядов
	 * @param int $rows_per_page - количество рядов, которое выводится на одной странице
	 * @param int $show_pages - количество ссылок для перехода по страницам
	 * @param mixed $keyword - уникальный код данной листалки. Указывается номер листалки, которая выводится на странице. Если указать вместо этого параметра значение, которое начинается со знака /, например /News/p{$offset}, то пролистывание страниц будет производится через mod_rewrite
	 * @param boolean $show_all_link - вывести в конце списка страниц ссылку "Показать все"
	 * @param boolean $show_text_links - вывести в начале и конце списка ссылки "Первая", "Последняя"
	 * @param string $anchor_name - при переходе по страницам использовать якорь #test
	 * @param string $javascript - для пролистывания страниц использовать указанный JavaScript код. {$offset} подставляется номер страницы (смещение)
	 * @param int $page_start - номер страницы. Используется в тех случаях когда номер страницы содержится не в параметре $_GET[offset][intval($keyword)]
	 * @return string
	 */
	public static function pages($total_rows, $rows_per_page, $show_pages = 10, $keyword = 0, $show_all_link = false, $show_text_links = false, $anchor_name = '', $javascript = '', $page_start = null) {
		if ($total_rows <= $rows_per_page) return '';
		$anchor_name = (empty($anchor_name)) ? '' : "#$anchor_name";
		$anchor_name = (strlen($anchor_name) == 1) ? '': $anchor_name;
		
		if (is_null($page_start)) {
			$page_start = globalVar($_GET['_offset'][intval($keyword)], 0);
		}
						
		// Определяем формат ссылки
		if (!empty($javascript) && !empty($anchor_name)) {
			$link = 'href="'.$anchor_name.'" onclick="'.$javascript.'"';
		} elseif (!empty($javascript) && empty($anchor_name)) {
			$link = 'href="javascript:void(0);" onclick="'.$javascript.'"';
		} else {
			/**
			 * Определяем параметры, переданные методом GET
			 * Удаляем из запроса все, что начинается на 2 подчеркивания
			 * Используется для передачи параметров, установленных mod_rewrite
			 */
			$get = $_GET;
			unset($get['_offset'][$keyword], $get['_REWRITE_URL'], $get['_GALLERY_URL']);
			if (substr($keyword, 0, 1) == '/') {
				reset($get);
				while (list($key,) = each($get)) {
					if (substr($key, 0, 1) == '_') unset($get[$key]);
				}
				$get = http_build_query($get);
				$get = (empty($get)) ? '' : '?'.$get;
				$link = 'href="'.$keyword.$get.$anchor_name.'"';
			} else {
				$link = 'href="?'.urlencode("_offset[$keyword]").'={$offset}&'.http_build_query($get).$anchor_name.'"';
			}
		}
		
		if ($page_start - $rows_per_page < 0) {
			$previous = 0;
		} elseif ($page_start - $rows_per_page > $total_rows) {
			$previous = $total_rows - 1;
		} else {
			$previous = $page_start - $rows_per_page;
		}
		
		$next = ($page_start + $rows_per_page >= $total_rows) ?	$total_rows - 1 : $page_start + $rows_per_page;
		$return = '';
		
		
		/**
		 * Блок ссылок:
		 * 
		 * v.1: 20%        60%         20%          <-- если текущая страница >60% от show_pages и < total_pages - (60% * show_pages)
		 *      первые ... средние ... последние
		 * 
		 * v.2: 80%        20%                      <-- если текущая страница <=60% от show_pages
		 *      первые ... последние
		 * 
		 * v.3: 20%        80%                      <-- если текущая страница >= total_pages - (60% * show_pages)
		 * 	    первые ... последние
		 *
		 */
		$current_page = ceil($page_start / $rows_per_page) + 1;
		$total_pages = ceil($total_rows / $rows_per_page) - 1;
		
		$first_block_start = 0;
		$last_block_end = $total_pages;

		if ($total_pages <= $show_pages) {
			// v.0 - просто список страниц
			$first_block_end = $total_pages;
			$middle_block_start = $middle_block_end = $last_block_end = $last_block_start = 0;
		} elseif ($current_page <= $show_pages*0.6) {
			// v.2
			$first_block_end = ceil($show_pages*0.8)-1;
			$middle_block_start = $middle_block_end = 0;
			$last_block_start = $total_pages - floor($show_pages*0.2) + 1;
		} elseif ($current_page >= $total_pages - $show_pages*0.5) {
			// v.3
			$first_block_end = ceil($show_pages*0.2)-1;
			$middle_block_start = $middle_block_end = 0;
			$last_block_start = $total_pages - floor($show_pages*0.8) + 1;
		} else {
			// v.1
			$first_block_end = ceil($show_pages*0.2)-1;
			$last_block_start = $total_pages - floor($show_pages*0.2) + 1;
			$middle_block_count = $show_pages - ($first_block_end-$first_block_start+1) - ($last_block_end-$last_block_start+1);
			$middle_block_start = $current_page - floor($middle_block_count/2);
			$middle_block_end = $middle_block_end = $middle_block_start + $middle_block_count - 1;
		}
		
//		$debug = array(
//			'total_pages' => $total_pages,
//			'show_pages' => $show_pages,
//			'current_page' => $current_page,
//			
//			'first' => array('start' => $first_block_start, 'end' => $first_block_end),
//			'middle' => array('start' => $middle_block_start, 'end' => $middle_block_end),
//			'last' => array('start' => $last_block_start, 'end' => $last_block_end)
//		);
//		x($debug);
		
		if ($show_text_links) {
			// Показываем ссылку на предыдущую страницу
			$active = ($page_start > 0) ? false : true;
			$return .= self::pageGetLink($link, $previous, cms_message('CMS', 'Предыдущая'), $active);
		}
		
		// Формируем список страниц
		
		for ($i = $first_block_start; $i <= $first_block_end; $i++) {
			$active = ($i * $rows_per_page == $page_start) ? true : false;
			$return .= self::pageGetLink($link, intval($i * $rows_per_page), intval($i + 1), $active);
		}
		
		if ($middle_block_start != 0) {
			$return .= '<span class="page_dots">...</span>';
			for ($i = $middle_block_start; $i <= $middle_block_end; $i++) {
				$active = ($i * $rows_per_page == $page_start) ? true : false;
				$return .= self::pageGetLink($link, intval($i * $rows_per_page), intval($i + 1), $active);
			}
		}
		
		if ($last_block_end != $last_block_start) {
			$return .= '<span class="page_dots">...</span>';
		}
		
		if ($last_block_start != 0) {
			for ($i = $last_block_start; $i <= $last_block_end; $i++) {
				$active = ($i * $rows_per_page == $page_start) ? true : false;
				$return .= self::pageGetLink($link, intval($i * $rows_per_page), intval($i + 1), $active);
			}
		}
		
		
		if ($show_text_links) {
			// Показываем ссылку на следующую страницу
			$active = ($page_start + $rows_per_page < $total_rows  && $page_start != -1) ? false : true;
			$return .= self::pageGetLink($link, $next, cms_message('CMS', 'Следующая'), $active);
		}
		
		if ($show_all_link) {
			// Показываем ссылку "Показать все"
			$active = ($page_start == -1) ? true : false;
			$return .= self::pageGetLink($link, -1, cms_message('CMS', 'Показать всё'), $active);
		}
		
		return $return;
	}
	
	static private function pageGetLink($link, $offset_value, $name, $active) {
		$class = (is_numeric($name)) ? '': 'verbal';
		return ($active) ? '<span class="page_list '.$class.'">'.$name.'</span>': '<a class="page_list '.$class.'" accesskey="37" '.str_replace('{$offset}', $offset_value, $link).'">'.$name.'</a>';
	}
	
	
	/**
	 * Функция формирует ограничение LIMIT для запросов к MySQL
	 * Используется совместно с Misc::pages()
	 * 
	 * @param int $rows_per_page
	 * @param string $keyword
	 */
	public static function limit_mysql($rows_per_page, $keyword = 0, $page_start = null) {
		$page_start = (empty($page_start)) ? globalVar($_GET['_offset'][$keyword], 0): $page_start;
		if ($page_start < 0) return '';
		return " LIMIT ".intval($page_start).", ".intval($rows_per_page)." ";
	}
	
	public static function pagedContent($content, $keyword = 0, $page_start = null) {
		if (stripos($content, '<hr') === false) return $content;
		
		$content = preg_split("/<hr[^>]*>/i", $content, -1, PREG_SPLIT_NO_EMPTY);
		$page_list = Misc::pages(count($content), 1, 10, 0, true, true);
		
		$page_start = (empty($page_start)) ? globalVar($_GET['_offset'][$keyword], 0): $page_start;
		if ($page_start < 0) return implode("<p>", $content).'<br><center>'.$page_list.'</center>';
		return (isset($content[$page_start])) ? $content[$page_start].$page_list: $content[0].$page_list;
	}

	/**
	* Обрезает строку с определенным количеством символов, не разрывая слова
	* @param string $str
	* @param int $len
	* @return string
	*/
	static function word_wrapper($str, $len) {
		$cut_pos = strpos(wordwrap($str, $len, '<stop>', true), '<stop>');
		return ($cut_pos) ? substr($str, 0, $cut_pos).'...': $str;
	}
	
	/**
	* Генерация случайных последовательностей символов
	* @param int $chars
	* @param string $genChars
	* @return string
	*/
	static function randomKey($chars, $genChars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789') {
		$retkey = "";
		for ($i = 1; $i <= $chars; $i++) {
			$rand = rand(1,strlen($genChars));
			$retkey .= substr($genChars,$rand -1,1);
		}
		
		return ($retkey);
	}
	
	/**
	* Генерирует ключ
	* @static 
	* @param int $chars
	* @param int $blocks
	* @param string $separator
	* @return string
	*/
	static function keyBlock($chars, $blocks, $separator = '-') {
		$key = "";
		for($i = 0; $i < $blocks;$i++) {
			//Create an array of keys
			$key[] = self::randomKey($chars);
		}
		$key = implode($separator, $key);
		return($key);
	}
	
	/**
	* Выполняет противополжные действия от bin2hex
	* @static
	* @param string $hexdata
	* @return string
	*/
	static function hex2bin($hexdata) {
		for ($i=0;$i<strlen($hexdata);$i+=2) {
			$bindata.=chr(hexdec(substr($hexdata,$i,2)));
		}
		return $bindata;
	}
	

	/**
	 * Определение колонок, которые надо открывать, для поля cmsEdit - ext_multiple
	 *
	 * @param object $DBServer - соединение с БД, в которой находится редактируемое поле
	 * @param int $master_id - id ряда, который сейчас редактируется
	 * @param array $parent_tables - список таблиц, которые будут выведены начиная со следующего уровня
	 * @param string $relation_table_name
	 * @param string $relation_select_field
	 * @param string $relation_parent_field
	 * @return void
	 */
	static function extMultipleOpen(DB $DBServer, $master_id, $parent_tables, $relation_table_name, $relation_select_field, $relation_parent_field) {
		global $DB;
		
		$query = "
			DROP TEMPORARY TABLE IF EXISTS `tmp_open`;
			CREATE TEMPORARY TABLE `tmp_open` (id INT UNSIGNED NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM;
		";
		$DBServer->multi($query);

		/**
		 * Определение таблиц и названий полей, которые являются родительскими в таблице
		 */
		$open_tables = array();
		reset($parent_tables);
		while(list(,$row) = each($parent_tables)) {
			$query = "
				SELECT
					tb_table.name AS table_name,
					tb_field.name AS parent_field_name
				FROM cms_table AS tb_table
				LEFT JOIN cms_field AS tb_field ON tb_field.id=tb_table.parent_field_id
				WHERE tb_table.id='$row'
			";
			$open_tables[] = $DB->query_row($query);
		}
		
		// таблица, которая выводит значения на данном уровне
		$select_table = array_shift($open_tables);
		$query = "
			INSERT IGNORE INTO tmp_open (id)
			SELECT tb_0.id
			FROM `$select_table[table_name]` AS tb_0 ";
		$where_table = array(); // таблица, с которой выбираются значения
		$where_table_index = 0;
		reset($open_tables);
		while(list($index, $row) = each($open_tables)) {
			$index++;
			$query .= "
			INNER JOIN `$row[table_name]` AS tb_$index ON tb_$index.`$row[parent_field_name]`=tb_".($index-1).".id";
			$where_table = $row;
			$where_table_index = $index;
		}
		$query .= "
			INNER JOIN `$relation_table_name` AS tb_relation ON tb_relation.`$relation_select_field`=tb_$where_table_index.`id`
			WHERE tb_relation.`$relation_parent_field`='".$master_id."'
		";
		$DBServer->insert($query, 'id', 'id');	
		
		return 0;
	}
	

	/**
	 * Копирует ряды в таблице
	 *
	 * @param string $table_name
	 * @param array $where_fields
	 * @param array $update_fields
	 */
	static function copyRows($table_name, $where_condition, $substitute = array()) {
		global $DB;
		$insert = array();
		$field_list = array();
		$last_inserted_id = -1;
		if (empty($where_condition)) {
			$where_condition = 1;
		}
		
		$query = "
			SELECT *
			FROM `$table_name`
			WHERE $where_condition
		";
		$data = $DB->query($query);
		reset($data); 
		while (list(,$row) = each($data)) { 
			$row_insert = array();
			reset($row); 
			while (list($field,$value) = each($row)) { 
				if (isset($substitute[$field])) {
					$row_insert[] = "'".$substitute[$field]."'";
				} elseif ($field == 'id') {
					continue;
				} elseif (is_null($value)) {
					$row_insert[] = "NULL";
				} else {
					$row_insert[] = "'$value'";
				}
			}
			if (empty($field_list)) {
				unset($row['id']);
				$field_list = "`".implode("`,`", array_keys($row))."`";
			}
			$insert[] = "(".implode(",", $row_insert).")";
		}
		
		
		if (!empty($insert)) {
			$query = "INSERT INTO `$table_name` ($field_list) VALUES ".implode(",",$insert);
			$last_inserted_id = $DB->insert($query);
		}
		return  $last_inserted_id;
	}
	
	/**
	 * Фиксирует изменения в таблице
	 *
	 * @param object $DBServer
	 * @param string $table_name
	 * @param int $row_id
	 * @param enum $action_type
	 * @param array $data
	 * @return bool
	 */
	static public function cvsDbDiff($DBServer, $table_name, $row_id, $action_type, $data) {
		global $DB; 
		
		// Определяем id таблицы, которая обновляется
		$query = "
			select tb_table.id
			from cms_db as tb_db
			inner join cms_table as tb_table on tb_table.db_id=tb_db.id
			where
				tb_db.alias='".$DBServer->db_alias."' and
				tb_table.name='$table_name'
		";
		$table_id = $DB->result($query);
		if ($DB->rows != 1) {
			return false;
		}
		
		// Определяем существующие в таблице значения
		$query = "select * from `".$DBServer->db_name."`.`$table_name` where id='$row_id'";
		$old = $DBServer->query_row($query);
		
		// Подгружаем данные из таблицы
		$fields = cmsTable::getFields($table_id);
		
		// Создаём транзакцию
		$query = "
			insert into cvs_db_transaction (admin_id,table_id,event_type,row_id) 
			values ('".$_SESSION['auth']['id']."', '$table_id', '$action_type', '$row_id')
		";
		$transaction_id = $DB->insert($query);

		// Определяем данные, которые поступили на обновление
		reset($data); 
		while (list($field_name, $value) = each($data)) { 
			if (!$fields[$field_name]['is_real']) {
				continue;
			} elseif (!isset($fields[ $field_name ])) {
				continue;
			}
			
			if (is_null($value) && ($value != $old[$field_name] || $action_type == 'insert')) {
				$query = "
					insert into cvs_db_change (transaction_id, field_id, field_language, value_null) 
					values ('$transaction_id', '".$fields[ $field_name ]['id']."', '".$fields[ $field_name ]['field_language']."', 'true')
				";
				$DB->insert($query);
			} elseif ($value != $old[$field_name] || $action_type == 'insert') {
				$query = "
					insert into cvs_db_change (transaction_id, field_id, field_language, value_".$fields[$field_name]['pilot_type'].") 
					values ('$transaction_id', '".$fields[ $field_name ]['id']."', '".$fields[ $field_name ]['field_language']."', '$value')
				";
				$DB->insert($query);
			}
		}
		return true;
	}
	
	/**
	 * Посылает письмо по электронной почте по указанному шаблону
	 *
	 * @param string $email Может содержать имя, например "Admin <admin@email.com>"
	 * @param string $subject
	 * @param string $content - html текст письма
	 * @param array $extra_headers
	 * @param bool $plain_text
	 * @param array $attachments
	 * @param bool $immediatly
	 * @return mixed
	 */	
	public static function sendMail($email, $subject, $content, $extra_headers = array(), $plain_text = false, $attachments = array(), $immediatly = false) {
		$Sendmail = new Sendmail(CMS_MAIL_ID, $subject, $content);
		$Sendmail->send($email, $immediatly);
	}
	
	/**
	 * getCalender - функция для построения календаря в модуле stuff
	 * @param array $date - даты событий 
	 * @param int $month - месяц
	 * @param int $year - год
	 */
	public static function getCalendar($date, $month, $year) {
		global $DB;
		
		//определяем первый день месяца как день недели (пн либо вт ...) 
		$first_date = (date('w', mktime(0, 0, 0, $month, 1, $year)));
		$first_date--;
		if ($first_date == -1) {
			$first_date = 6;
		}
		
		
		$correct = 0;
		//определяем коректируюший коефицыент зависимо от количества
		//днейв месяце
		$num_date = date('t', mktime(0, 0, 0, $month, 1, $year));
		if ($num_date == 30) {
			$correct = 0;
		} elseif ($num_date == 29) {
			$correct = -1;
		} elseif ($num_date == 28) {
			$correct = -2;
		} elseif ($num_date == 31) {
			$correct = 1;
		}
//		формируем шапку месяца
		$name_month = constant('LANGUAGE_MONTH_NOM_'.(int)$month);

		
		$html = '<table class="stuff"><tr><td class="title" colspan="7">'.$name_month.' '.$year.'</td></tr><tr><td>пн</td>
				<td>вт</td><td>ср</td><td>чт</td><td>пт</td><td class="colred">сб</td>
				<td style="color: Red">вс</td></tr><tr>';
//		проходим по всех днях месяца и заполняем их
		$row = array();

		for ($i = 0; $i < $num_date + $first_date ; $i++) {
			if ($i % 7 == 0 && $i != 0) {
				$html .= '</tr><tr>';
			}
			if ($i < $first_date) {
				$html .= '<td>&nbsp;</td>';	
			} else {
				$day = $i - $first_date + 1;
				if (isset($date[$day])) {
					if (date('w', mktime(0, 0, 0, $month, $day, $year)) == 0 || date('w', mktime(0, 0, 0, $month, $day, $year)) == 6){
						if ($day == date('j') && date('m') == $month && $year == date('Y')) {
							$html .= '<td class="bcred" title="'.$date[$day]['descript'].'" style="background-color:'.$date[$day]['color'].';">'.$day.'</td>';
						} else {
							$html.='<td class="colred" title="'.$date[$day]['descript'].'" style="background-color:'.$date[$day]['color'].'">'.$day.'</td>';
						}
					} else {
						if ($day == date('j') && date('m') == $month && $year == date('Y')){
							$html .= '<td class="bordred" title="'.$date[$day]['descript'].'" style="background-color:'.$date[$day]['color'].';">'.$day.'</td>';
						} else {
							$html .= '<td title="'.$date[$day]['descript'].'" style="background-color:'.$date[$day]['color'].'">'.$day.'</td>';
						}
					}
				} else {
					if (date('w', mktime(0, 0, 0, $month, $day, $year)) == 0 || date('w', mktime(0, 0, 0, $month, $day, $year)) == 6) {
						if ($day == date('j') && date('m') == $month && $year == date('Y')) {
							$html .= '<td class="bcred">'.$day.'</td>';
						} else {
							$html .= '<td class="colred">'.$day.'</td>';
						}
					} else {
						if ($day == date('j') && date('m') == $month && $year == date('Y')) {
							$html .= '<td class="bordred">'.$day.'</td>';
						} else {
							$html .= '<td>'.$day.'</td>';
						}
					}
				}
			}
		}
		$html .= '</tr></table>';
		return $html;
	}
	
	/**
	 * Формирует кеш расположения классов системы
	 * @return void
	 */
	public static function refreshLibsCache() {
		
		$listing = Filesystem::getAllSubdirsContent(LIBS_ROOT, true);
		
		$cache_content = "<?php\n\n\$_LIBS_CACHE = array(\n";
		
		reset($listing); 
		while (list(,$row) = each($listing)) { 
			$content = php_strip_whitespace($row); 
			
			if (preg_match('~class\s+([a-z0-9_]+)\s*~i', $content, $match)) {
				$filename = substr($row, strlen(LIBS_ROOT));
				$cache_content .= "	'".strtolower($match[1])."' => '$filename',\n";
			}
		}

		$cache_content .= ");";
		
		file_put_contents(CACHE_ROOT.'libs_cache.php', $cache_content);
	}
	
	/**
	 * Отправка SMS
	 * @param int $recipient 	12 цифр: 380xxxxxxxxx
	 * @param string $message
	 * @param boolean $flash
	 * @return array
	 */
	public static function sendSms($recipient, $message, $flash=false) {   
		global $DB;
		
		$DB->insert("insert into cms_smslog set recipient = '".$recipient."', message = '".$message."', datetime = NOW()");
 
		/**
		 * Мгновенная отправка СМС. 30.05.2011 Барин М. 
		 * Не работает: внешнее устройство не может распарсить отправляемый нами xml, 
		 * 				хотя он соответствует имеющемуся стандарту
		 */  
//		if(IS_DEVELOPER){
//			$SmsSender = new GsmIp2Sms(array('recipient' => $recipient, 'message' => $message));  
//			$response = $SmsSender->sendSms();
//			if($response) {
//				echo "Отправлено";
//				x($response);
//			} else { 
//				echo "ОШИБКА";
//			}
//			exit;
//			  
//			$sms_id = $DB->insert("
//				insert into gsm_sms set
//					client_id = '".GSM_GATEWAY_CLIENT_ID."',
//					queue_dtime = now(),
//					recipient = '$recipient',
//					message = '".$DB->escape($message)."'
//			");
//			return array('error'=> '', 'sms_id' => $sms_id);
//		}
		
		/**
		 * Отправка смс посредством cron скрипта.
		 */
		if($flash){
			$SmsSender = new GsmIp2Sms(array('recipient' => $recipient, 'message' => $message));  
			return $SmsSender->sendSms();
		}
		
		$client = new SoapClient("http://gsm.delta-x.ua/static/gsm/GsmServer.wsdl");
		try { 
			return $client->sendSms(GSM_GATEWAY_CLIENT_ID, GSM_GATEWAY_ACCESS_CODE, $recipient, iconv(CMS_CHARSET, 'UTF-8//IGNORE', $message), false);
		} catch (SoapFault $e) {
			return array(
				'error' => $e->getMessage(),
				'sms_id' => 0
			);
		}
	}
	
	/**
	 * Посылает уведомление пользователю, если это разрешено его настройками
	 * @param int $user_id
	 * @param string $message
	 * @return boolean
	 */
	public static function userSmsNotify($user_id, $message) {
		global $DB;
		
		$send_sms_notify = $DB->result("select send_sms_notify from auth_user where id='".(int)$user_id."'");
		if ($DB->rows == 0) return false;
		if ($send_sms_notify == 'false') return false;
		
		$phone = $DB->result("select phone from auth_user_phone where user_id = '$user_id' LIMIT 1");
		if ($DB->rows == 0) return false;
		
		preg_match('/\+([0-9]{12})/', $phone, $match);
		if(empty($match[1])) return false;
		
		$result = self::sendSms($match[1], $message, false);
		return (isset($result['sms_id']) && $result['sms_id'] > 0);
	}
	
	
	
	
}
?>
