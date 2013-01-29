<?php
/**
 * Класс изменения и обработки SQL запросов для MySQL 4.1 и выше
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Класс изменения и обработки SQL запросов для MySQL 4.1 и выше
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class SQLParserMySQLi extends SQLParser {
	/**
	 * Список SQL условий запроса, на которые он должен быть разбит
	 * Этот список должен быть именно в том порядке, в котором следуют эти условия в ANSI SQL
	 *
	 * @var array
	 */
	protected $sql_statements = array(
		'SELECT',
		'FROM',
		'WHERE',
		'GROUP BY',
		'HAVING',
		'ORDER BY',
		'LIMIT'
	);
	
	/**
	 * Возможные комбинации условия JOIN и FROM
	 *
	 * @var array
	 */
	private $join_syntax = array(
		'FROM',
		'JOIN',
		'INNER JOIN',
		'CROSS JOIN',
		'STRAIGHT_JOIN',
		'LEFT JOIN',
		'LEFT OUTER JOIN',
		'NATURAL LEFT OUTER JOIN',
		'NATURAL LEFT JOIN',
		'NATURAL JOIN',
		'RIGHT JOIN',
		'RIGHT OUTER JOIN',
		'NATURAL RIGHT OUTER JOIN',
		'NATURAL RIGHT JOIN',
	);
	
	/**
	 * Конструктор класса
	 *
	 * @param string $query
	 */
	public function __construct(DB $DBServer, $query) {
		// Соединенеие с БД
		$this->DBServer = $DBServer;
		
		// Разбираем SQL по частям
		$this->parsed_sql = $this->parseSQL($query);
		
		// Определяем перечень таблиц, которые присутствуют в SQL запросе
		$this->tables = $this->getTables();
	}
	
	
	/**
	 * Разбор SQL запроса по частям, возвращает массив с типом SQL условия и частью запроса
	 * 
	 * @param string $query
	 * @return array
	 */
	protected function parseSQL($query) {
		$query = ' '.trim($query); // Запрос должен начинаться с пробела
		
		// Определяем мёртвые зоны, в которых условия SELECT, WHERE ... являются вложенными запросами	
		preg_match_all('/\(((?>[^()]+)|(?R))*\)/x', $query, $matches, PREG_OFFSET_CAPTURE);
		$deadline = array();
		reset($matches[0]);
		while (list(,$row) = each($matches[0])) {
			if (strlen($row[0]) < 8) {
				continue;
			}
			$deadline[ $row[1] ] = strlen($row[0]) + $row[1];
		}
		
		// Определяем местоположение SQL условий
		preg_match_all("/(?:'.*(?<!\\\)')|(?:[\s\n\r\t]+(".str_replace(' ', '[\s\n\r\t]+', implode("|", $this->sql_statements)).")[\s\n\r\t]+)/ismU", ' '.$query, $matches, PREG_OFFSET_CAPTURE);
		reset($matches[1]);
		while (list($index, $row) = each($matches[1])) {
			if (!is_array($row)) {
				// это были найдены кавычки
				unset($matches[1][$index]);
				continue;
			}
			
			reset($deadline);
			while (list($start, $end) = each($deadline)) {
				if ($start > $row[1]) {
					// Прекращаем проверку, так как числа отсортированы в массиве deadline по возрастанию
					// и если текущее число больше чем искомое, то значит что дальше такого числа - не будет
					continue(2);
				} elseif ($start < $row[1] && $end > $row[1]) {
					// Текущее значение находится а приделах подзапроса
					unset($matches[1][$index]);
					continue(2);
				}
			}
		}
		
		
		// Делим запрос на части
		$parts = array_values($matches[1]);
		$query_parts = array();
		reset($parts);
		while (list($index, $row) = each($parts)) {
			$key = strtoupper(preg_replace("/[\s\n\r\t]+/", ' ', $row[0]));
			$query_parts[$key] = (isset($parts[$index + 1][1])) ?
				substr($query, $row[1] - 1, $parts[$index + 1][1] - $row[1]):
				substr($query, $row[1] - 1);
		}
		return $query_parts;
	}
	
	
	
	/**
	 * Определяет перечень таблиц, которые есть в SQL запросе
	 * 
	 * @return array
	 */
	protected function getTables() {
		$table_reference = preg_replace("/[\s\n\r\t]+/", " ", $this->parsed_sql['FROM']);
		
		// Делим на части условие FROM
		$regexp = "/(".implode("|", $this->join_syntax).")\s/is";
		preg_match_all($regexp, $table_reference, $matches,  PREG_OFFSET_CAPTURE);
		$tables = array();
		for ($i=count($matches[0]) - 1;$i>=0;$i--) {
			// Убираем все части joiin и from, что б осталось только название таблицы и её alias
			$table = substr($table_reference, $matches[0][$i][1]);
			$on = stripos($table, ' ON ');
			$using = stripos($table, ' USING ');
			if ($on !== false || $using !== false) {
				$table = substr($table, 0, max($on, $using));
			}
			$table = trim(substr($table, strlen($matches[0][$i][0])));
			$table = str_ireplace(array(' as ', '`'), array(' ', ''), $table);
			$table = explode(" ", $table);
			
			if (count($table) == 2) {
				$tables[ $table[1] ] = $table[0];
			} else {
				$tables[ $table[0] ] = $table[0];
			}
			// Удаляем обработанную запись
			$table_reference = substr($table_reference, 0, $matches[0][$i][1]);
		}
		return $tables;
	}
	
	
	/**
	 * Выполняет SQL запрос, возвращает результат выполнения.
	 * @param int $start
	 * @param int $offset
	 * @return array
	 */
	public function execQuery($start = null, $offset = null) {
		// Формируем SQL запрос
		$query = $this->getQuery($start, $offset);
		$this->debug = $query;
		
		/**
		 * Опеределяем общее количество рядов в таблице
		 */
		$query = "SELECT SQL_CALC_FOUND_ROWS ".substr($query, 6);
		$data = $this->DBServer->query($query);
		
		/**
		 * Определяем количество колонок в таблице
		 */
		$query = "SELECT FOUND_ROWS() AS rows";
		$this->total_rows = $this->DBServer->result($query);
		
		return $data;
	}
	
	
	
	/**
	 * Возвращает изменённый по всем параметрам SQL запрос
	 * @param int $start
	 * @param int $offset
	 * @return string
	 */
	public function getQuery($start = null, $offset = null) {
		
		// Добавляем ограничение количеству рядов в таблице
		if (!is_null($start) && !is_null($offset)) {
			$this->parsed_sql['LIMIT'] = "LIMIT $start, $offset";
		} elseif (!is_null($start) && is_null($offset)) {
			$this->parsed_sql['LIMIT'] = "LIMIT $start";
		}
		
		/**
		 * Изменяем порядок сортировки SQL запроса, чтоб WHERE было после FROM, а не в конце
		 * это происходит тогда, когда в начальном SQL запросе нет WHERE условия, а потом добавляются
		 * параметры SQL запроса
		 */
		uksort($this->parsed_sql, array(&$this, 'getQuery_callback'));
		return implode("\n", $this->parsed_sql);
	}
	
	/**
	 * Возращает запрос в виде массива
	 * @since 2006-12-25
	 * @return array
	 */
	public function getQueryArray() {
		uksort($this->parsed_sql, array(&$this, 'getQuery_callback'));
		return $this->parsed_sql;
	}
	
	/**
	 * Изменяет порядок сортировки в SQL
	 * 
	 * Поля сортировки будут идти В том порядке, в котором они идут в параметре $order_fields_new. В качестве ключа 
	 * в передаваемом массиве указывается название поля, в качестве значения для ключа указывается 'ASC','DESC',0.
	 * В случае, если в качестве значения будет указан параметр 0, то сортировка по данному полю будет оставаться такой же
	 * как и была в начальном запросе. Если такого поля нет в начальном запросе, то в условие ORDER BY оно добавлятся не будет.
	 * Значение сортировки поля = 0 используется в таблицах., где есть subtitle
	 * 
	 * @param array $order_fields
	 * @return void
	 */
	public function changeOrder($order_fields) {

		if (isset($this->parsed_sql['ORDER BY']) && !empty($this->parsed_sql['ORDER BY'])) {
			/**
			 * В запросе есть условие ORDER BY
			 */
			
			preg_match("/ORDER[\s\n\r\t]+BY(.+)/ism", $this->parsed_sql['ORDER BY'], $matches);
			
			// Делим условие по полям
			preg_match_all('/`?([^\s\n\r\t,`]+)`?( ASC| DESC)?(?:,|$)/ism', $matches[1], $order_by);
			
			/**
			 * Если в запросе стоит ORDER BY date DESC, date, test
			 * то параметр date будет иметь пустой порядок сортировки, так как последний элемент массива перезапишет его,
			 * в то время как в SQL первый элемент имеет больший вес, чем последующие, поэтому для операции array_combine
			 * меняем сортировку значений. После того, как получится результатирующий массив, мы его опять пересортировываем в
			 * обратном порядке.
			 */
			$order_by = array_reverse(array_combine(array_reverse($order_by[1]), array_reverse($order_by[2])));
			
			/**
			 * Определяем направление сортировки для поля полей значение которых = 0
			 */
			reset($order_fields);
			while (list($field, $direction) = each($order_fields)) {
				/**
				 * Для данного поля указан порядок сортировки, пропускаем его
				 */
				if (in_array(strtoupper($direction), array('ASC', 'DESC'))) {
					// Убираем дублирующиеся поля в SQL запросе
					unset($order_by[$field]);
					
					$order_fields[$field] = $field.' '.$direction;
					continue;
				}
				
				/**
				 * Значение сортировки != ASC или DESC, значит надо оставить старый порядок сортировки или 
				 * удалить это поле
				 */
				if (isset($order_by[$field]) && in_array(strtoupper($order_by[$field]), array('ASC', 'DESC'))) {
					$order_fields[$field] = $order_by[$field];
				} elseif (isset($order_by[$field])) {
					$order_fields[$field] = 'ASC';
				} else {
					unset($order_fields[$field]);
					continue;
				}
				
				// Убираем дублирующиеся поля в сортировке
				unset($order_by[$field]);
				
				$order_fields[$field] = $field.' '.$direction;
			}
			
			/**
			 * Добавляем поля из изначального запроса
			 */
			reset($order_by);
			while (list($field, $direction) = each($order_by)) {
				array_push($order_fields, $field.' '.trim($direction));
			}
			
		} else {
			/**
			 * В запросе нет условия ORDER BY
			 */
			reset($order_fields);
			while (list($field, $direction) = each($order_fields)) {
				if (!in_array(strtoupper($direction), array('ASC', 'DESC'))) {
					unset($order_fields[$field]);
					continue;
				}
				$order_fields[$field] = $field.' '.$direction;
			}
		}
		
		$this->parsed_sql['ORDER BY'] = 'ORDER BY '.implode(', ', $order_fields);
	}
	
	
	
	/**
	 * Добавляет в WHERE или HAVING часть SQL запроса условия фильтрации данных
	 * @param array $where_conditions массив с условиями
	 * @return void
	 */
	public function changeCondition($conditions, $having_condition = false) {
		if (empty($conditions)) return;
		
		$type = ($having_condition == false) ? 'WHERE' : 'HAVING';
		
		if (isset($this->parsed_sql[$type])) {
			// В SQL запросе есть условие WHERE/HAVING
			$this->parsed_sql[$type] = $type.' ('.substr($this->parsed_sql[$type], 5).")\n\tAND (".implode(")\n\tAND (", $conditions).")";
		} else {
			//  В SQL запросе нет условия WHERE/HAVING
			$this->parsed_sql[$type] = $type.' ('.implode(")\n\tAND (", $conditions).")";
		}
	}
	
}
?>