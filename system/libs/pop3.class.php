<?php
/**
 * Класс по работе с POP3 сервером
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

/**
 * Класс по работе с POP3 сервером
 * @package Maillist
 * @subpackage Libraries
 */
class POP3 {
	
	/**
	* Ресурс соединения
	* @var resource
	*/
	private $connection;
	
	/**
	* Индекс сообщений
	* @var array
	*/
	public $stat = array();
	
	/**
	 * Формат перевода строки, для M$ Exchange используется \r\n 
	 *
	 * @var string
	 */
	private $newline = "\n";
		
	/**
	* Конструктор класса
	* @param string $host
	* @param int $port
	* @return object
	*/
	public function __construct($host, $port, $user, $pass) {
		$this->connection = fsockopen($host, $port, $errno, $errstr, 30);
		if(!$this->connection) {
			trigger_error(cms_message('CMS', 'Невозможно установить соединение с POP3 сервером %s (%d)', $errstr, $errno), E_USER_ERROR);
		}
		$greeting = fgets($this->connection, 128);
		$this->newline = (stristr($greeting, "microsoft")) ? "\r\n" : "\n";
		fputs($this->connection, "user $user".$this->newline);
		fgets($this->connection, 128);
		fputs($this->connection, "pass $pass".$this->newline);
		$pass_check = fgets($this->connection, 128);
		
		/**
		 * Если попыька входа не удалась - выводим ошибку
		 */
		if (!preg_match('/^\+OK/i', $pass_check)) {
			trigger_error(cms_message('CMS', 'Невозможно установить соединение с POP3 сервером %s (%d)', $pass_check, 0), E_USER_ERROR);
		}
		
		/**
		* Определяем количество и размер писем
		*/
		fputs($this->connection, "list".$this->newline);
		$stat = '';
		while (trim($stat) != ".") {
			$stat = fgets($this->connection, 128);
			if (preg_match("/^([0-9]+) ([0-9]+)/", $stat, $matches)) {
				$this->stat[$matches[1]] = $matches[2];
			}
		}
	}
	
	/**
	* Читает сообщение
	* @param int $id
	* @return mixed
	*/
	public function retr($id) {
		if (!isset($this->stat[$id])) {
			return false;
		}
		
		fputs($this->connection, "retr $id".$this->newline);
		
		return $this->read();
	}
	
	/**
	* Читает заголовок + верхние n строк сообщения
	* @param int $id
	* @param int $lines
	* @return mixed
	*/
	public function top($id, $lines) {
		if (!isset($this->stat[$id])) {
			return false;
		}
		fputs($this->connection, "top $id $lines".$this->newline);
		
		return $this->read();
	}
	
	/**
	* Читает тело сообщения
	* @param void
	* @return string
	*/
	private function read() {
		do {
			$message[] = $line = fgets($this->connection);
//			echo "READLINE: $line\n";
		} while ((trim($line) != '.'));
		
		array_shift($message);
		array_pop($message);
		
		return implode("", $message);
	}
	
	
	/**
	* Удаляет сообщение
	* @param int $id
	* @return string
	*/
	public function dele($id) {
		if (!isset($this->stat[$id])) {
			return false;
		}
		unset($this->stat[$id]);
		fputs($this->connection, "dele $id".$this->newline);
		return fgets($this->connection, 128);
	}		
	
	/**
	* Деструктор класса
	* @param void
	* @return void
	*/
	public function __destruct() {
		fputs($this->connection, "quit".$this->newline);
		fclose($this->connection);
	}
	
}
?>