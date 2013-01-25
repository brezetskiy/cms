<?php
/** 
 * Класс работы с MSSQL 
 * @package Pilot
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
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
class dbMSSQL extends db {
	
	/**
	* Конструктор класса, создает соединение с СУБД
	* @param string $host
	* @param string $login
	* @param string $password
	* @param string $db_name
	* @return void
	*/ 
	public function __construct($host, $login, $password, $db_name) {
		$this->db_name = $db_name;
		
		$this->link = mssql_connect($host, $login, $password);
		
		if(false === $this->link) {
			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Status: 503 Service Temporarily Unavailable");
			header("Retry-After: 120");
			header("Connection: Close");
			
			trigger_error(cms_message('CMS', 'Невозможно подключиться к серверу %s', $host), E_USER_ERROR);
		}
		
		if (!mssql_select_db($this->db_name, $this->link)) {
			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Status: 503 Service Temporarily Unavailable");
			header("Retry-After: 120");
			header("Connection: Close");
			
			trigger_error(cms_message('CMS', 'Невозможно выбрать базу данных %s на сервере %s', $this->db_name, $host), E_USER_ERROR);
		}
		
	}

	/**
	* Определяет id последнего вставленного столбца,
	* правильно работает только в случае, если в таблице нет триггеров
	* @param void
	* @return mixed
	*/
	private function mssql_insert_id() {
		return mssql_result(mssql_query("SELECT @@IDENTITY", $this->link), 0, 0);
	}
	
	/**
	* Определяет количество затронутых запросом столбцов
	* @param void
	* @return int
	*/
	private function mssql_affected_rows() {
		return mssql_result(mssql_query("SELECT @@ROWCOUNT", $this->link), 0, 0);
	}

	/**
	* Запрос, который возвращает много строк
	* @param string $query
	* @param string $key имя поля котрое использовать в качестве ключа
	* @return array
	*/
	public function query($query, $key = null) {
		$this->statistic['select']++;
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->rows = 0;
		$return = array();
		$result = mssql_query($query, $this->link) or $this->error($query);
		if (!is_resource($result)) {
			return array();
		}
		$this->rows = mssql_num_rows($result);
		while($row = mssql_fetch_assoc($result)) {
			if ($this->lower_case === true) $row = array_change_key_case($row, CASE_LOWER);
			array_walk($row, 'array_trim');
			if (isset($row[$key])) {
				$return[$row[$key]] = $row;
			} else {
				$return[] = $row;
			}
		}
		mssql_free_result($result);
		return $return;
	}
	
	/**
	* Запрос, который возвращает только одну строку
	* @param string $query
	* @return array
	*/
	public function query_row($query) {
		$this->statistic['select']++;
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->rows = 0;
		$result = mssql_query($query, $this->link) or $this->error($query);
		$this->rows = mssql_num_rows($result);
		if ($this->rows > 0) {
			$row = mssql_fetch_assoc($result);
			array_walk($row, 'array_trim');
			if ($this->lower_case === true) $data = array_change_key_case($row, CASE_LOWER);
		} else {
			$row = array();
		}
		mssql_free_result($result);
		return $row;
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
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->rows = 0;
		$return = array();
		$result = mssql_query($query, $this->link) or $this->error($query);
		$this->rows = mssql_num_rows($result);
		while($row = mssql_fetch_array($result)) {
			if ($this->lower_case === true) $row = array_change_key_case($row, CASE_LOWER);
			if (isset($row[$key])) {
				$return[$row[$key]] = trim($row[$val]);
			} else {
				$return[] = $row[0];
			}
		}
		mssql_free_result($result);
		return $return;
	}
	
	/**
	 * Выполняет запрос который возвращает одно значение
	 * @param string $query
	 * @param mixed $fail_return
	 * @return string
	 */
	public function result($query, $fail_return = '') {
		$this->statistic['select']++;
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->rows = 0;
		$result = mssql_query($query, $this->link) or $this->error($query);
		$this->rows = mssql_num_rows($result);
		if($this->rows > 0) {
			$return = mssql_result($result, 0, 0);
			mssql_free_result($result);
			return trim($return);
		} else {
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
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->rows = 0;
		if (mssql_query($query, $this->link)) {
			$this->affected_rows = $this->rows = $this->mssql_affected_rows($this->link);
			return $this->mssql_insert_id();
		} else {
			$this->error($query);
		}
	}
	
	/**
	* запрос на изменение информации UPDATE
	* @param string $query
	* @return void
	*/
	public function update($query) {
		$this->statistic['update']++;
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->affected_rows = $this->rows = 0;
		if (mssql_query($query, $this->link)) {
			$this->affected_rows = $this->rows = $this->mssql_affected_rows();
		} else {
			$this->error($query);
		}

	}
	
	/**
	* Удаление
	* @param string $query
	* @return void
	*/
	public function delete($query) {
		$this->statistic['delete']++;
		if (IS_DEVELOPER && DEBUG) $this->debug[] = $query;
		$this->affected_rows = $this->rows = 0;
		if (mssql_query($query, $this->link)) {
			$this->affected_rows = $this->rows = $this->mssql_affected_rows();
		} else {
			$this->error($query);
		}
	}
	
	/**
	* Ошибка, которую вернул запрос
	* @param string $query
	* @return string
	*/
	protected function error($query) {
		x($query);
		trigger_error(mssql_get_last_message(), E_USER_WARNING);
	}
	
	/**
	* Квотирование строки
	* @param string $string
	* @return string
	*/
	public function escape($string) {
		return htmlspecialchars(str_replace("'", "''", stripslashes($str)));
	}
	
}
?>