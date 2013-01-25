<?php
/**
 * ����� �� ������ � POP3 ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

/**
 * ����� �� ������ � POP3 ��������
 * @package Maillist
 * @subpackage Libraries
 */
class POP3 {
	
	/**
	* ������ ����������
	* @var resource
	*/
	private $connection;
	
	/**
	* ������ ���������
	* @var array
	*/
	public $stat = array();
	
	/**
	 * ������ �������� ������, ��� M$ Exchange ������������ \r\n 
	 *
	 * @var string
	 */
	private $newline = "\n";
		
	/**
	* ����������� ������
	* @param string $host
	* @param int $port
	* @return object
	*/
	public function __construct($host, $port, $user, $pass) {
		$this->connection = fsockopen($host, $port, $errno, $errstr, 30);
		if(!$this->connection) {
			trigger_error(cms_message('CMS', '���������� ���������� ���������� � POP3 �������� %s (%d)', $errstr, $errno), E_USER_ERROR);
		}
		$greeting = fgets($this->connection, 128);
		$this->newline = (stristr($greeting, "microsoft")) ? "\r\n" : "\n";
		fputs($this->connection, "user $user".$this->newline);
		fgets($this->connection, 128);
		fputs($this->connection, "pass $pass".$this->newline);
		$pass_check = fgets($this->connection, 128);
		
		/**
		 * ���� ������� ����� �� ������� - ������� ������
		 */
		if (!preg_match('/^\+OK/i', $pass_check)) {
			trigger_error(cms_message('CMS', '���������� ���������� ���������� � POP3 �������� %s (%d)', $pass_check, 0), E_USER_ERROR);
		}
		
		/**
		* ���������� ���������� � ������ �����
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
	* ������ ���������
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
	* ������ ��������� + ������� n ����� ���������
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
	* ������ ���� ���������
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
	* ������� ���������
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
	* ���������� ������
	* @param void
	* @return void
	*/
	public function __destruct() {
		fputs($this->connection, "quit".$this->newline);
		fclose($this->connection);
	}
	
}
?>