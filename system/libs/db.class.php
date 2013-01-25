<?php
/**
 * Класс подключения к различным СУБД
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2004, Delta-X ltd.
 */

/**
 * Класс подключения к СУБД
 * @package Database
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
abstract class db {
	
	/**
	 * Хранилище созданных объектов
	 * @var array
	 */
	private static $instance = array();
	
	/**
	 * Ресурс соединения с БД
	 * @var resource
	 */
	public $link;
	
	/**
	 * Имя БД
	 * @var string
	 * @deprecated если этот параметр не используется, то его нужно убрать
	 */
	public $db_name;
	
	/**
	 * Алиас БД
	 * @var string
	 * @deprecated 
	 */
	public $db_alias;
	
	/**
	 * Количество строк, которое вернул последний запрос
	 * @var int 
	 */
	public $rows;
	
	/**
	 * Количество строк затронутых в запросах UPDATE, INSERT, DELETE
	 * @var int 
	 */
	public $affected_rows;
	
	/**
	 * Последняя ошибка, которая произошла
	 * @var string
	 */
	public $error = '';
	
	/**
	 * Режим отображения ключей в массиве с результатами SQL запроса.
	 * Если используется true, то все ключи будут переведены в нижний регистр.
	 * @var bool
	 */
	public $lower_case = true;
	
	/**
	 * Статистика по количеству SQL запросов. Определяется не по запросу, а по
	 * вызванной для обработки запроса процедуре.
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
	 * Если константа DEBUG == 1, то в это свойство будет добавлен каждый SQL запрос,
	 * который пройдёт через класс
	 * @var array
	 */
	protected $debug = array();
	protected $debug_fp;
	private $debug_counter = 0;
	
	/**
	 * Подключение к серверу СУБД
	 * @param string $alias
	 * @param bool $singelton
	 * @return object
	 */
	public static function factory($alias, $singelton = true) {
		
		$alias = strtoupper($alias);
		if (empty($alias) || !defined('DB_'.$alias.'_HOST')) {
			// Неправильно указан номер #%d базы данных, с которой необходимо установить соединение.
			trigger_error(cms_message('CMS', 'Неправильно указан номер #%d базы данных, с которой необходимо установить соединение.', $alias), E_USER_ERROR);
		}
		
		/**
		 * Проверяем, нет ли уже активного соединнения с сервером,
		 * если оно есть, то возвращаем ссылку на него
		 */
		if (isset(self::$instance[$alias]) && $singelton) {
			return self::$instance[$alias];
			
		} else {
			
			/**
			* Загружаем класс работы с СУБД
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
		
		// Создаём файл, в котором будут хранится запросы
		$this->debug_fp = tmpfile();
		
		// Создаём временные таблицы для хранения статистики, они необходимы из-за того, что MySQL
		// хранит только последние 100 запросов
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
		
		// Сохраняем статистику по каждым 90 запросам
		if ($this->debug_counter % 90 == 0) {
			$this->debug_save_stat();
		}
		
		$this->debug_counter++;
	}
	private function debug_save_stat() {
		return false; // Заблокировано до появления 5.1
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
		
		// Считываем запрос из файла
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
				
		// Определяем номер запроса в профайле
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
			
			// Определяем информацию по запросу
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
		return false; // Заблокировано до появления 5.1

		fseek($this->debug_fp, 0);
		$this->debug = false;
		
		$query = "SET profiling=0";
		$this->query($query);
		
		$this->debug_save_stat();
	}

	/**
	 * Запрос, который возвращает много строк
	 * @param string $query
	 * @param string $key имя поля котрое использовать в качестве ключа
	 * @return array
	 */
	abstract public function query($query, $key = null);
	
	/**
	 * Запрос, который возвращает только одну строку
	 * @param string $query
	 * @return array
	 */
	abstract public function query_row($query);
	
	/**
	 * Выполняет запрос в результатах которого всего одна колонка
	 * @param string $query
	 * @param string $key имя поля котрое использовать в качестве ключа
	 * @param string $val имя поля котрое использовать в качестве значения
	 * @return array
	 */
	abstract public function fetch_column($query, $key = null, $val = null);
	
	/**
	 * Выполняет запрос который возвращает одно значение
	 * @param string $query
	 * @param mixed $fail_return
	 * @return string
	 */
	abstract public function result($query, $fail_return = '');
	
	/**
	 * Вставляет строку и возвращает ее id или false
	 * @param string $query
	 * @return mixed
	 */
	abstract public function insert($query);
	
	/**
	 * запрос на изменение информации UPDATE
	 * @param string $query
	 * @return void
	 */
	abstract public function update($query);
	
	/**
	 * Удаление
	 * @param string $query
	 * @return void
	 */
	abstract public function delete($query);

	/**
	 * Ошибка, которую вернул запрос
	 * @param string $query
	 * @return string
	 */
	abstract protected function error($query);
	
	/**
	 * Квотирование строки
	 * @param string
	 * @return string
	 */
	abstract public function escape($string);
}
?>