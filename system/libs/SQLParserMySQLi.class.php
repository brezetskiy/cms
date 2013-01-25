<?php
/**
 * ����� ��������� � ��������� SQL �������� ��� MySQL 4.1 � ����
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ����� ��������� � ��������� SQL �������� ��� MySQL 4.1 � ����
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class SQLParserMySQLi extends SQLParser {
	/**
	 * ������ SQL ������� �������, �� ������� �� ������ ���� ������
	 * ���� ������ ������ ���� ������ � ��� �������, � ������� ������� ��� ������� � ANSI SQL
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
	 * ��������� ���������� ������� JOIN � FROM
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
	 * ����������� ������
	 *
	 * @param string $query
	 */
	public function __construct(DB $DBServer, $query) {
		// ����������� � ��
		$this->DBServer = $DBServer;
		
		// ��������� SQL �� ������
		$this->parsed_sql = $this->parseSQL($query);
		
		// ���������� �������� ������, ������� ������������ � SQL �������
		$this->tables = $this->getTables();
	}
	
	
	/**
	 * ������ SQL ������� �� ������, ���������� ������ � ����� SQL ������� � ������ �������
	 * 
	 * @param string $query
	 * @return array
	 */
	protected function parseSQL($query) {
		$query = ' '.trim($query); // ������ ������ ���������� � �������
		
		// ���������� ������ ����, � ������� ������� SELECT, WHERE ... �������� ���������� ���������	
		preg_match_all('/\(((?>[^()]+)|(?R))*\)/x', $query, $matches, PREG_OFFSET_CAPTURE);
		$deadline = array();
		reset($matches[0]);
		while (list(,$row) = each($matches[0])) {
			if (strlen($row[0]) < 8) {
				continue;
			}
			$deadline[ $row[1] ] = strlen($row[0]) + $row[1];
		}
		
		// ���������� �������������� SQL �������
		preg_match_all("/(?:'.*(?<!\\\)')|(?:[\s\n\r\t]+(".str_replace(' ', '[\s\n\r\t]+', implode("|", $this->sql_statements)).")[\s\n\r\t]+)/ismU", ' '.$query, $matches, PREG_OFFSET_CAPTURE);
		reset($matches[1]);
		while (list($index, $row) = each($matches[1])) {
			if (!is_array($row)) {
				// ��� ���� ������� �������
				unset($matches[1][$index]);
				continue;
			}
			
			reset($deadline);
			while (list($start, $end) = each($deadline)) {
				if ($start > $row[1]) {
					// ���������� ��������, ��� ��� ����� ������������� � ������� deadline �� �����������
					// � ���� ������� ����� ������ ��� �������, �� ������ ��� ������ ������ ����� - �� �����
					continue(2);
				} elseif ($start < $row[1] && $end > $row[1]) {
					// ������� �������� ��������� � �������� ����������
					unset($matches[1][$index]);
					continue(2);
				}
			}
		}
		
		
		// ����� ������ �� �����
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
	 * ���������� �������� ������, ������� ���� � SQL �������
	 * 
	 * @return array
	 */
	protected function getTables() {
		$table_reference = preg_replace("/[\s\n\r\t]+/", " ", $this->parsed_sql['FROM']);
		
		// ����� �� ����� ������� FROM
		$regexp = "/(".implode("|", $this->join_syntax).")\s/is";
		preg_match_all($regexp, $table_reference, $matches,  PREG_OFFSET_CAPTURE);
		$tables = array();
		for ($i=count($matches[0]) - 1;$i>=0;$i--) {
			// ������� ��� ����� joiin � from, ��� � �������� ������ �������� ������� � � alias
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
			// ������� ������������ ������
			$table_reference = substr($table_reference, 0, $matches[0][$i][1]);
		}
		return $tables;
	}
	
	
	/**
	 * ��������� SQL ������, ���������� ��������� ����������.
	 * @param int $start
	 * @param int $offset
	 * @return array
	 */
	public function execQuery($start = null, $offset = null) {
		// ��������� SQL ������
		$query = $this->getQuery($start, $offset);
		$this->debug = $query;
		
		/**
		 * ����������� ����� ���������� ����� � �������
		 */
		$query = "SELECT SQL_CALC_FOUND_ROWS ".substr($query, 6);
		$data = $this->DBServer->query($query);
		
		/**
		 * ���������� ���������� ������� � �������
		 */
		$query = "SELECT FOUND_ROWS() AS rows";
		$this->total_rows = $this->DBServer->result($query);
		
		return $data;
	}
	
	
	
	/**
	 * ���������� ��������� �� ���� ���������� SQL ������
	 * @param int $start
	 * @param int $offset
	 * @return string
	 */
	public function getQuery($start = null, $offset = null) {
		
		// ��������� ����������� ���������� ����� � �������
		if (!is_null($start) && !is_null($offset)) {
			$this->parsed_sql['LIMIT'] = "LIMIT $start, $offset";
		} elseif (!is_null($start) && is_null($offset)) {
			$this->parsed_sql['LIMIT'] = "LIMIT $start";
		}
		
		/**
		 * �������� ������� ���������� SQL �������, ���� WHERE ���� ����� FROM, � �� � �����
		 * ��� ���������� �����, ����� � ��������� SQL ������� ��� WHERE �������, � ����� �����������
		 * ��������� SQL �������
		 */
		uksort($this->parsed_sql, array(&$this, 'getQuery_callback'));
		return implode("\n", $this->parsed_sql);
	}
	
	/**
	 * ��������� ������ � ���� �������
	 * @since 2006-12-25
	 * @return array
	 */
	public function getQueryArray() {
		uksort($this->parsed_sql, array(&$this, 'getQuery_callback'));
		return $this->parsed_sql;
	}
	
	/**
	 * �������� ������� ���������� � SQL
	 * 
	 * ���� ���������� ����� ���� � ��� �������, � ������� ��� ���� � ��������� $order_fields_new. � �������� ����� 
	 * � ������������ ������� ����������� �������� ����, � �������� �������� ��� ����� ����������� 'ASC','DESC',0.
	 * � ������, ���� � �������� �������� ����� ������ �������� 0, �� ���������� �� ������� ���� ����� ���������� ����� ��
	 * ��� � ���� � ��������� �������. ���� ������ ���� ��� � ��������� �������, �� � ������� ORDER BY ��� ���������� �� �����.
	 * �������� ���������� ���� = 0 ������������ � ��������., ��� ���� subtitle
	 * 
	 * @param array $order_fields
	 * @return void
	 */
	public function changeOrder($order_fields) {

		if (isset($this->parsed_sql['ORDER BY']) && !empty($this->parsed_sql['ORDER BY'])) {
			/**
			 * � ������� ���� ������� ORDER BY
			 */
			
			preg_match("/ORDER[\s\n\r\t]+BY(.+)/ism", $this->parsed_sql['ORDER BY'], $matches);
			
			// ����� ������� �� �����
			preg_match_all('/`?([^\s\n\r\t,`]+)`?( ASC| DESC)?(?:,|$)/ism', $matches[1], $order_by);
			
			/**
			 * ���� � ������� ����� ORDER BY date DESC, date, test
			 * �� �������� date ����� ����� ������ ������� ����������, ��� ��� ��������� ������� ������� ����������� ���,
			 * � �� ����� ��� � SQL ������ ������� ����� ������� ���, ��� �����������, ������� ��� �������� array_combine
			 * ������ ���������� ��������. ����� ����, ��� ��������� ���������������� ������, �� ��� ����� ����������������� �
			 * �������� �������.
			 */
			$order_by = array_reverse(array_combine(array_reverse($order_by[1]), array_reverse($order_by[2])));
			
			/**
			 * ���������� ����������� ���������� ��� ���� ����� �������� ������� = 0
			 */
			reset($order_fields);
			while (list($field, $direction) = each($order_fields)) {
				/**
				 * ��� ������� ���� ������ ������� ����������, ���������� ���
				 */
				if (in_array(strtoupper($direction), array('ASC', 'DESC'))) {
					// ������� ������������� ���� � SQL �������
					unset($order_by[$field]);
					
					$order_fields[$field] = $field.' '.$direction;
					continue;
				}
				
				/**
				 * �������� ���������� != ASC ��� DESC, ������ ���� �������� ������ ������� ���������� ��� 
				 * ������� ��� ����
				 */
				if (isset($order_by[$field]) && in_array(strtoupper($order_by[$field]), array('ASC', 'DESC'))) {
					$order_fields[$field] = $order_by[$field];
				} elseif (isset($order_by[$field])) {
					$order_fields[$field] = 'ASC';
				} else {
					unset($order_fields[$field]);
					continue;
				}
				
				// ������� ������������� ���� � ����������
				unset($order_by[$field]);
				
				$order_fields[$field] = $field.' '.$direction;
			}
			
			/**
			 * ��������� ���� �� ������������ �������
			 */
			reset($order_by);
			while (list($field, $direction) = each($order_by)) {
				array_push($order_fields, $field.' '.trim($direction));
			}
			
		} else {
			/**
			 * � ������� ��� ������� ORDER BY
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
	 * ��������� � WHERE ��� HAVING ����� SQL ������� ������� ���������� ������
	 * @param array $where_conditions ������ � ���������
	 * @return void
	 */
	public function changeCondition($conditions, $having_condition = false) {
		if (empty($conditions)) return;
		
		$type = ($having_condition == false) ? 'WHERE' : 'HAVING';
		
		if (isset($this->parsed_sql[$type])) {
			// � SQL ������� ���� ������� WHERE/HAVING
			$this->parsed_sql[$type] = $type.' ('.substr($this->parsed_sql[$type], 5).")\n\tAND (".implode(")\n\tAND (", $conditions).")";
		} else {
			//  � SQL ������� ��� ������� WHERE/HAVING
			$this->parsed_sql[$type] = $type.' ('.implode(")\n\tAND (", $conditions).")";
		}
	}
	
}
?>