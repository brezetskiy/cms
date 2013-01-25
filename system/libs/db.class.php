<?php
/**
 * ����� ����������� � ��������� ����
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2004, Delta-X ltd.
 */

/**
 * ����� ����������� � ����
 * @package Database
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
abstract class db {
	
	/**
	 * ��������� ��������� ��������
	 * @var array
	 */
	private static $instance = array();
	
	/**
	 * ������ ���������� � ��
	 * @var resource
	 */
	public $link;
	
	/**
	 * ��� ��
	 * @var string
	 * @deprecated ���� ���� �������� �� ������������, �� ��� ����� ������
	 */
	public $db_name;
	
	/**
	 * ����� ��
	 * @var string
	 * @deprecated 
	 */
	public $db_alias;
	
	/**
	 * ���������� �����, ������� ������ ��������� ������
	 * @var int 
	 */
	public $rows;
	
	/**
	 * ���������� ����� ���������� � �������� UPDATE, INSERT, DELETE
	 * @var int 
	 */
	public $affected_rows;
	
	/**
	 * ��������� ������, ������� ���������
	 * @var string
	 */
	public $error = '';
	
	/**
	 * ����� ����������� ������ � ������� � ������������ SQL �������.
	 * ���� ������������ true, �� ��� ����� ����� ���������� � ������ �������.
	 * @var bool
	 */
	public $lower_case = true;
	
	/**
	 * ���������� �� ���������� SQL ��������. ������������ �� �� �������, � ��
	 * ��������� ��� ��������� ������� ���������.
	 * @var array
	 */
	public $statistic = array(
		'insert' => 0,
		'select' => 0,
		'update' => 0,
		'delete' => 0,
		'multi' => 0,
		'other' => 0
	);
	
	/**
	 * ���� ��������� DEBUG == 1, �� � ��� �������� ����� �������� ������ SQL ������,
	 * ������� ������ ����� �����
	 * @var array
	 */
	protected $debug = array();
	protected $debug_fp;
	private $debug_counter = 0;
	
	/**
	 * ����������� � ������� ����
	 * @param string $alias
	 * @param bool $singelton
	 * @return object
	 */
	public static function factory($alias, $singelton = true) {
		
		$alias = strtoupper($alias);
		if (empty($alias) || !defined('DB_'.$alias.'_HOST')) {
			// ����������� ������ ����� #%d ���� ������, � ������� ���������� ���������� ����������.
			trigger_error(cms_message('CMS', '����������� ������ ����� #%d ���� ������, � ������� ���������� ���������� ����������.', $alias), E_USER_ERROR);
		}
		
		/**
		 * ���������, ��� �� ��� ��������� ����������� � ��������,
		 * ���� ��� ����, �� ���������� ������ �� ����
		 */
		if (isset(self::$instance[$alias]) && $singelton) {
			return self::$instance[$alias];
			
		} else {
			
			/**
			* ��������� ����� ������ � ����
			*/
			require_once(LIBS_ROOT .'db/'.strtolower(constant('DB_'.$alias.'_TYPE')).'.class.php');
			
			$class_name = 'db'.constant('DB_'.$alias.'_TYPE');
			self::$instance[$alias] = new $class_name(
				constant('DB_'.$alias.'_HOST'), 
				constant('DB_'.$alias.'_LOGIN'), 
				constant('DB_'.$alias.'_PASSWORD'), 
				constant('DB_'.$alias.'_NAME')
			);
			
			self::$instance[$alias]->db_alias = strtolower($alias);
			return self::$instance[$alias];
		}		
	}
	
	
	protected function debug_start() {
		mysqli_query($this->link, "SET profiling_history_size=100");
		mysqli_query($this->link, "SET profiling=1");
		
		// ������ ����, � ������� ����� �������� �������
		$this->debug_fp = tmpfile();
		
		// ������ ��������� ������� ��� �������� ����������, ��� ���������� ��-�� ����, ��� MySQL
		// ������ ������ ��������� 100 ��������
		$query = "
			CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_db_profile` (
			  `query_id` int(10) NOT NULL,
			  `duration` float(15,8) NOT NULL,
			  `query` varchar(255) collate cp1251_ukrainian_ci NOT NULL,
			  PRIMARY KEY (`query_id`),
			  KEY `query` (`query`)
			) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COLLATE=cp1251_ukrainian_ci
		";
		$this->query($query);
		
		$query = "
			CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_db_stat` (
			  `query_id` int(10) NOT NULL,
			  `seq` int(10) NOT NULL,
			  `state` varchar(255) collate cp1251_ukrainian_ci NOT NULL,
			  `duration` float(15,8) NOT NULL,
			  PRIMARY KEY (`query_id`, `seq`),
			  KEY `state` (`state`)
			) ENGINE=MyISAM DEFAULT CHARSET=cp1251 COLLATE=cp1251_ukrainian_ci
		";
		$this->query($query);
		
		$this->debug = true;
	}
	protected function debug_log($query) {
		if ($this->debug == false || !is_resource($this->debug_fp)) return;
		
		$debug_backtrace = debug_backtrace();
		if ($this->debug_counter == 0) {
			fwrite($this->debug_fp, "/* ".$debug_backtrace[1]['file']." (".$debug_backtrace[1]['line'].") */\n$query;\n\n");
		} else {
			fwrite($this->debug_fp, "/* # ".$this->debug_counter." */\n/* ".$debug_backtrace[1]['file']." (".$debug_backtrace[1]['line'].") */\n$query;\n\n");
		}
		
		// ��������� ���������� �� ������ 90 ��������
		if ($this->debug_counter % 90 == 0) {
			$this->debug_save_stat();
		}
		
		$this->debug_counter++;
	}
	private function debug_save_stat() {
		return false; // ������������� �� ��������� 5.1
		$debug_state = $this->debug;
		$row_state = $this->rows;
		$affected_state = $this->affected_rows;
		
		$this->debug = false;
		$query = "SHOW PROFILES";
		$data = $this->query($query);
		
		$insert = array();
		reset($data); 
		while (list(,$row) = each($data)) {
			$row['query'] = substr(preg_replace("/[^a-zA-Z0-9=]/", '', $row['query']), 0, 250);
			$insert[] = "'$row[query_id]', '$row[duration]', '$row[query]'";
		}
		
		$query = "
			INSERT IGNORE INTO tmp_db_profile (`query_id`, `duration`, `query`)
			VALUES (".implode("),(", $insert).")
		"; 
		$this->insert($query);
		
		$query = "
			INSERT IGNORE INTO `$this->db_name`.tmp_db_stat (query_id, seq, state, duration)
			SELECT
				query_id,
				seq,
				state,
				duration
			FROM cms_schema.profiling
		";
		$this->insert($query);
		
		$this->debug = $debug_state;
		$this->rows = $row_state;
		$this->affected_rows = $affected_state;

	}
	public function debug_show() {
		static $last_query_id = 0;
		if (!is_resource($this->debug_fp)) {
			return false;
		}
		
		if ($this->debug == true) {
			$this->debug_stop();
		}
		
		// ��������� ������ �� �����
		$min_indent = 9999;
		$return = $backtrace = '';
		while (is_resource($this->debug_fp) && !feof($this->debug_fp)) {
			$line = fgets($this->debug_fp);
			if (preg_match("~/\* # [0-9]+ \*/~", trim($line))) {
				break;
			} elseif (empty($return) && empty($backtrace)) {
				$backtrace = $line;
			} elseif (trim($line)!='' && trim($line)!=';') {
				$line = str_replace("\t", "    ", $line);
				$indent = strlen($line) - strlen(ltrim($line));
				if ($min_indent > $indent && $indent!=0) {
					$min_indent = $indent;
				}
				$return .= $line;
			}
		}
		
		if (feof($this->debug_fp)) {
			return false;
		}


		$return = preg_replace("/^[\s]{0,$min_indent}/m", "", trim($return));
				
		// ���������� ����� ������� � ��������
		$query = "
			SELECT
				query_id,
				CAST(duration AS decimal(15,8)) AS duration
			FROM tmp_db_profile
			WHERE 
				query=SUBSTRING('".preg_replace("/[^a-zA-Z0-9=]/", '', $return)."' FROM 1 FOR LENGTH(query))
				AND query_id > '$last_query_id'
			ORDER BY query_id ASC
			LIMIT 1
		";
		$query_id = $this->query_row($query);
		if (empty($query_id)) {
			return $return;
		}
		$last_query_id = $query_id['query_id'];
		
		$return = " /* Query id: $query_id[query_id]; duration: $query_id[duration] seconds */\n".trim($backtrace)."\n$return";
		if ($query_id['duration'] > 0.01) {
			
			// ���������� ���������� �� �������
			$query = "
				SELECT 
					MIN(seq) AS seq,
					state,
					COUNT(*) AS `repeat`,
					SUM(duration) AS duration
				FROM tmp_db_stat
				WHERE query_id=".$query_id['query_id']."
				GROUP BY state
				ORDER BY seq ASC
			";
			$data = $this->query($query);
			$return .= "\n/*\n";
			reset($data); 
			while (list(,$row) = each($data)) { 
				$return .= sprintf('%02d', $row['seq']).". $row[duration]: $row[state] ($row[repeat] times)\n"; 
			}
			$return .="*/";
		}
		
		
		
		return $return;
	}
	
	private function debug_stop() {
		return false; // ������������� �� ��������� 5.1

		fseek($this->debug_fp, 0);
		$this->debug = false;
		
		$query = "SET profiling=0";
		$this->query($query);
		
		$this->debug_save_stat();
	}

	/**
	 * ������, ������� ���������� ����� �����
	 * @param string $query
	 * @param string $key ��� ���� ������ ������������ � �������� �����
	 * @return array
	 */
	abstract public function query($query, $key = null);
	
	/**
	 * ������, ������� ���������� ������ ���� ������
	 * @param string $query
	 * @return array
	 */
	abstract public function query_row($query);
	
	/**
	 * ��������� ������ � ����������� �������� ����� ���� �������
	 * @param string $query
	 * @param string $key ��� ���� ������ ������������ � �������� �����
	 * @param string $val ��� ���� ������ ������������ � �������� ��������
	 * @return array
	 */
	abstract public function fetch_column($query, $key = null, $val = null);
	
	/**
	 * ��������� ������ ������� ���������� ���� ��������
	 * @param string $query
	 * @param mixed $fail_return
	 * @return string
	 */
	abstract public function result($query, $fail_return = '');
	
	/**
	 * ��������� ������ � ���������� �� id ��� false
	 * @param string $query
	 * @return mixed
	 */
	abstract public function insert($query);
	
	/**
	 * ������ �� ��������� ���������� UPDATE
	 * @param string $query
	 * @return void
	 */
	abstract public function update($query);
	
	/**
	 * ��������
	 * @param string $query
	 * @return void
	 */
	abstract public function delete($query);

	/**
	 * ������, ������� ������ ������
	 * @param string $query
	 * @return string
	 */
	abstract protected function error($query);
	
	/**
	 * ������������ ������
	 * @param string
	 * @return string
	 */
	abstract public function escape($string);
}
?>