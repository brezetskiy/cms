<?php
/**
* Класс работы с СУБД MySQL
* @package Pilot
* @subpackage CMS
* @version 5.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

/**
* В начале каждой функции, которая выполняет SQL, должно стоять аннулирование параметра $this->rows
* это влиеяет на повышение безопасности. По состоянию этого парааметра проверяются некоторые из прав
* доступа.
*/

/**
* Выбор и подключение к БД
* @package Database
* @subpackage Libraries
*/
class dbMySQLi extends db {
	/**
	* Конструктор класса, создает соединение с СУБД
	* @param string $host
	* @param string $login
	* @param string $password
	* @param string $db_name
	* @param int $port
	* @return void
	*/
	public function __construct($host, $login, $password, $db_name, $port = 3306) {
		$this->db_name = $db_name;
		 
		$this->link = @mysqli_connect($host, $login, $password, $this->db_name, $port);
		if(false === $this->link) {
			@header("HTTP/1.1 503 Service Temporarily Unavailable");
			@header("Status: 503 Service Temporarily Unavailable");
			@header("Retry-After: 120");
			@header("Connection: Close");
			
			if (is_file(SITE_ROOT.'design/cms/db_connection.'.LANGUAGE_CURRENT.'.tmpl')) {
				$Template = new Template(SITE_ROOT.'design/cms/db_connection');
				$Template->set('host', $host);
				$Template->set('login', $login);
				$Template->set('error', mysqli_connect_error());
				echo $Template->display();
			} else {
				trigger_error(mysqli_connect_error(), E_USER_ERROR);
			}
			exit;
		}
		
		mysqli_query($this->link, "SET NAMES '".CMS_CHARSET."' COLLATE '".CMS_COLLATION."'");
		mysqli_query($this->link, "SET CHARACTER SET '".CMS_CHARSET."'");
		if (IS_DEVELOPER) {
			mysqli_query($this->link, "SET SQL_MODE='strict_all_tables'");
		}
		if (DEBUG && IS_DEVELOPER) {
			$this->debug_start();
		}
	}

	/**
	* Запрос, который возвращает много строк
	* @param string $query
	* @param string $key имя поля котрое использовать в качестве ключа
	* @return array
	*/
	public function query($query, $key = null) {
		$this->statistic['select']++;
		$this->rows = 0;
		$return = array();
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if ($result = mysqli_query($this->link, $query)) {
			if ($result === true) {
				return array();
			}
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			
			$this->rows = (is_bool($result)) ? 0: mysqli_num_rows($result);
			while($row = mysqli_fetch_assoc($result)) {
				if ($this->lower_case === true) $row = array_change_key_case($row, CASE_LOWER);
				if (isset($row[$key])) {
					$return[$row[$key]] = $row;
				} else {
					$return[] = $row;
				}
			}
			mysqli_free_result($result);
			if ($this->debug) $this->debug_log($query);
			return $return;
		} else {
			$this->error($query);
			return array();
		}
	}
	
	
	/**
	* Запрос, который выполняет несколько запросов к БД
	* @param string $query
	* @return void
	*/
	public function multi($query) {
		$this->statistic['multi']++;
		$this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		mysqli_query($this->link, "SET SQL_MODE=''");

		// обрабатываем ошибки в запросе 1. Остальные будут отработаны ниже.
		if ($this->debug) $this->debug_log($query);
		$result = mysqli_multi_query($this->link, $query) or $this->error($query);
		if ($result) {
			do {
				if ($result = mysqli_store_result($this->link)) {
					mysqli_free_result($result);
				}
			} while (mysqli_next_result($this->link));
		}
		// Обрабатываем сообщение об ошибках которые произошли в запросе №2 и более
		if (mysqli_warning_count($this->link) != 0) {
			$this->error($query);
			mysqli_query($this->link, "SET SQL_MODE='strict_all_tables'");
			return array();
		}
		mysqli_query($this->link, "SET SQL_MODE='strict_all_tables'");
	}
	
	/**
	* Запрос, который возвращает только одну строку
	* @param string $query
	* @return array
	*/
	public function query_row($query) {
		$this->statistic['select']++;
		$this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if ($result = mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			$this->rows = (is_bool($result)) ? 0: mysqli_num_rows($result);
			if ($this->rows > 0) {
				$data = mysqli_fetch_assoc($result);
				if ($this->lower_case === true) $data = array_change_key_case($data, CASE_LOWER);
			} else {
				$data = array();
			}
			mysqli_free_result($result);
			if ($this->debug) $this->debug_log($query);
			return $data;
		} else {
			$this->error($query);
			return array();
		}
	}
	
	
	/**
	* Выполняет запрос в результатах которого всего одна колонка
	* @param string $query
	* @param string $key имя поля котрое использовать в качестве ключа
	* @param string $val имя поля котрое использовать в качестве значения
	* @return array
	*/
	public function fetch_column($query, $key = null, $val = null) {
		$this->statistic['select']++;
		$this->rows = 0;
		$return = array();
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if ($result = mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			// Количество колонок, которые есть в результате запроса
			$columns = 0;
			$this->rows = (is_bool($result)) ? 0: mysqli_num_rows($result);
			while($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
				if (empty($columns)) {
					$columns = count($row);
				}
				
				if ($this->lower_case === true) {
					$row = array_change_key_case($row, CASE_LOWER);
				}
				
				if (is_null($key) && !isset($key) && $columns == 4) {
					$return[ $row[0] ] = $row[1];
				} elseif (isset($key) && isset($row[$key]) && !isset($val)) {
					$return[] = $row[$key];
				} elseif (isset($key) && isset($row[$key]) && isset($val) && isset($row[$val])) {
					$return[$row[$key]] = $row[$val];
				} else {
					$return[] = $row[0];
				}
			}
			mysqli_free_result($result);
			if ($this->debug) $this->debug_log($query);
			return $return;
		} else {
			$this->error($query);
			return array();
		}
	}
	
	/**
	* Выполняет запрос который возвращает одно значение
	* @param string $query
	* @param mixed $fail_return
	* @return string
	*/
	public function result($query, $fail_return = '') {
		$this->statistic['select']++;
		$this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if ($result = mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			$this->rows = (is_bool($result)) ? 0: mysqli_num_rows($result);
			if($this->rows > 0) {
				$return = mysqli_fetch_array($result);
				mysqli_free_result($result);
				if ($this->debug) $this->debug_log($query);
				return $return[0];
			} else {
				return $fail_return;
			}
		} else {
			$this->error($query);
			return $fail_return;
		}
	}
	
	/**
	* Вставляет строку и возвращает ее id или false
	* @param string $query
	* @return mixed
	*/
	public function insert($query) {
		$this->statistic['insert']++;
		$this->affected_rows = $this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if (mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			$this->affected_rows = $this->rows = mysqli_affected_rows($this->link);
			$inserted_id = mysqli_insert_id($this->link);
			if ($this->debug) $this->debug_log($query);
			return $inserted_id;
		} else {
			$this->error($query);
			return 0;
		}
		
	}
	
	/**
	* запрос на изменение информации UPDATE
	* @param string $query
	* @return void
	*/
	public function update($query) {
		$this->statistic['update']++;
		$this->affected_rows = $this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if (mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			$this->affected_rows = $this->rows = mysqli_affected_rows($this->link);
			if ($this->debug) $this->debug_log($query);
		} else {
			$this->error($query);
			return 0;
		}
	}
	
	/**
	* Удаление
	* @param string $query
	* @return void
	*/
	public function delete($query) {
		$this->statistic['delete']++;
		$this->affected_rows = $this->rows = 0;
		if (defined('CMS_URL') && defined('CURRENT_URL_FORM')) $query .= "\n/* http://".CMS_URL.CURRENT_URL_FORM." ".HTTP_IP." */";
		if (mysqli_query($this->link, $query)) {
			if (IS_DEVELOPER && mysqli_warning_count($this->link) != 0) {
				$this->error($query, 'warning');
			}
			$this->affected_rows = $this->rows = mysqli_affected_rows($this->link);
			if ($this->debug) $this->debug_log($query);
		} else {
			$this->error($query);
			return 0;
		}
	}
	
	/**
	* Ошибка, которую вернул запрос
	* @param string $query
	* @return string
	*/
	protected function error($query, $type = 'error') {
	
		$report = $warnings = array();
		$result = mysqli_query($this->link, "SHOW WARNINGS") or die(mysqli_error($this->link));
		while($row = mysqli_fetch_assoc($result)) {
			$warnings[] = $row;
			$report[] = "\nLevel: $row[Level]\nCode: $row[Code]\nMessage: $row[Message]";
		}
		
		z($warnings);
		if ($type=='error') {
			trigger_error("Query:\n".$query.implode("\n", $report), E_USER_ERROR);
		} else {
			trigger_error("Query:\n".$query.implode("\n", $report), E_USER_WARNING);
		}
	}
	
	/**
	* Квотирование строки
	* @param string $string
	* @return string
	*/
	public function escape($string) {
		return mysqli_real_escape_string($this->link, $string);
	}
	
	/**
	 * Close mysql connectionss
	 *
	 */
	public function close() {
		mysqli_close($this->link);
	}
}
?>