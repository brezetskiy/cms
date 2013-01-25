<?php
/**
* �������� ��������� �� ��������� SMTP
* @package Pilot
* @subpackage CMS
* @version 1.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/


/**
* ����������� ����� ������
*/
define('CRLF', "\r\n");

/**
* �������� ��������� �� ��������� SMTP
* @package Maillist
* @subpackage CMS
*/
class SMTP {
	
	/**
	* ���������� � ��������
	* @var resource
	*/
	private $connection;
	
	/**
	 * ��������� ������ ������� ��� ������
	 * @var bool
	 */
	public $debug = false;
	
	/**
	* ������� ����������
	* @var int
	*/
	private $timeout = 5;
	
	/**
	* ��� ���������� ������ �������
	* @var string
	*/
	private $last_code;
	
	/**
	* ����� ��������� ���������� ������ �������
	* @var string
	*/
	private $last_message;
	
	/**
	* ��������� ����� �������
	* @var string
	*/
	private $last_response;
	
	/**
	* ������ ����������� ��� SMTP
	* @var string
	*/
	private $helo = '';
	
	/**
	* �����������
	* @param string $host
	* @param int $port
	* @param string $login
	* @param string $passwd
	* @param string $auth_type (plain, login, none)
	* @return object
	*/
	public function __construct($host, $port, $login, $passwd, $auth_type) {
		
		$this->helo = CMS_HOST;
		
		$this->connection = fsockopen($host, $port, $this->error_num, $this->error_str, $this->timeout);
		
		/**
		* ����� �������� �� ������ ��������� ����������� �������
		*/
		$this->readResponse();
		
		//return $this->need_auth ?  $this->ehlo() : $this->helo();
		
		if (!$this->ehlo()) {
			trigger_error(cms_message('Maillist', '�� ������ ������� ����������� ��������� �������.'), E_USER_ERROR);
		}
		
//		if (preg_match('~^tls://~', $host)) {
//			$this->sendData("STARTTLS");
//		}
		
		switch (strtolower($auth_type)) {
			case 'plain':
				$auth_result = $this->authPlain($login, $passwd);
				break;
			case 'login':
				$auth_result = $this->authLogin($login, $passwd);
				break;
			case 'none':
				$auth_result = true;
				break;
			case '': // ��� ��� �������� none � ini ����� ��� ������� ����������� ������� ��������
				$auth_result = true;
				break;
			default:
				trigger_error(cms_message('CMS', '���������������� ��� SMTP �����������: %s', $auth_type), E_USER_ERROR);
		}
		
		if (!$auth_result) {
			trigger_error(cms_message('CMS', '������ SMTP �����������'), E_USER_ERROR);
		}
	}

	/**
	* Login �����������
	* @param $login string
	* @param $passwd string
	* @return boolean
	*/
	private function authLogin($login, $passwd) {
		$this->sendData("AUTH LOGIN");
		if ($this->checkError('334')) {
			return false;
		}
		
		$this->sendData(base64_encode($login));
		if ($this->checkError('334')) {
			return false;
		}
		
		$this->sendData(base64_encode($passwd));
		if($this->checkError('235')) {
			return false;
		}
		return true;
	}
	
	/**
	* ���������� Plain Text �����������
	* @param $login string
	* @param $passwd string
	* @return boolean
	*/
	private function authPlain($login, $passwd) {
		$this->sendData("AUTH PLAIN");
		if ($this->checkError('334')) {
			return false;
		}
		$this->sendData(base64_encode(chr(0) . $login . chr(0) . $passwd));
		if($this->checkError('235')) {
			return false;
		}
		return true;
	}
	
	
	/**
	* �������� �������� ���������
	* @param string $from ����� �����������
	* @param string $to ����� ����������
	* @param array $headers MIME-���������
	* @param string $body ���� ���������
	* @return bool
	*/
	public function send($from, $to, $headers, $body) {
		if (!is_resource($this->connection)) {
			return false;
		}
		
		if (strpos($from, '<') !== false) {
			$from = substr($from, strpos($from, '<') + 1);
			$from = substr($from, 0, strpos($from, '>'));
		}
		if (strpos($to, '<') !== false) {
			$to = substr($to, strpos($to, '<') + 1);
			$to = substr($to, 0, strpos($to, '>'));
		}
		
		// ������������� ��������� ��� ����������� ����� SPAM �������
		if (!isset($headers['From'])) {
			$headers['From'] = $from;
		}
		if (!isset($headers['Reply-To'])) {
			$headers['Reply-To'] = $from;
		}
		if (!isset($headers['User-Agent'])) {
			$headers['User-Agent'] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.5b) Gecko/20030827';
		}
		if (!isset($headers['Content-Type'])) {
			$headers['Content-Type'] = 'text/plain; charset="Windows-1251"';
		}
		if (!isset($headers['Content-Transfer-Encoding'])) {
			$headers['Content-Transfer-Encoding'] = '8bit';
		}
		if (!isset($headers['MIME-Version'])) {
			$headers['MIME-Version'] = '1.0';
		}
		if (!isset($headers['Date'])) {
			$headers['Date'] = date('r');
		}
		if (!isset($headers['In-Reply-To'])) {
			$host = isset($_SERVER['HOSTNAME']) ? $_SERVER['HOSTNAME'] : CMS_HOST;
			$headers['In-Reply-To'] = '<'.Misc::randomKey(20).'.'.time().'.mail@'.$host.'>';
		}
		if (!isset($headers['To'])) {
			$headers['To'] = $to;
		}
		
		// ������ ����������
		$tmp_headers = '';
		reset($headers);
		while (list($key,$val) = each($headers)) {
			$tmp_headers .= $key.": ".$val.CRLF;
		}

		$this->sendData("RSET");
		if($this->checkError('250')) {
			return false;
		}
		$this->sendData("MAIL FROM: <$from>");
		if($this->checkError('250')) {
			return false;
		}
		$this->sendData("RCPT TO: <$to>");
		if($this->checkError('250')) {
			return false;
		}
		$this->sendData("DATA");
		if($this->checkError('354')) {
			return false;
		}
		
		$this->sendData($tmp_headers.CRLF.$body.CRLF.".");
		
		if ($this->last_code == '250') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* SMTP-����������� ��� �����������
	* @param void
	* @return boolean
	*/
	private function helo() {
		if ($this->sendData("HELO ".$this->helo) && $this->last_code == '250') {
			return true;
		}
		return false;
	}
	
	/**
	* SMTP-����������� � ������������
	* @param void
	* @return boolean
	*/
	private function ehlo() {
		if ($this->sendData("EHLO ".$this->helo) && $this->last_code == '250') {
			return true;
		}
		return false;
	}
	
	/**
	* ���������� true, ���� ��������� �������� ����������� �� � ����� $code
	* @param $code string
	* @return boolean
	*/
	private function checkError($code) {
		if ($this->last_code == $code) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	* ������ ������ �������
	* @return mixed
	*/
	private function readResponse() {
		$return = '';
		$line   = '';
		$loops  = 0;

		if(is_resource($this->connection)){
			while((strpos($return, CRLF) === FALSE || substr($line,3,1) !== ' ') && $loops < 100){
				$line = fgets($this->connection, 512);
				$return .= $line;
				$loops++;
			}
			if ($this->debug) {
				echo "<< $return\n";
			}
			$this->last_code = substr($return, 0, 3);
			$this->last_message = substr($return, 5);
			return $return;

		} else {
			return false;
		}
	}
	
	/**
	* �������� ������ �������
	* @param string $string ������, ���������� �������
	* @param boolean $wait_response ����� ������� ������ ��������� ����� �������
	* @return boolean
	*/
	private function sendData($string, $wait_response = true) {
		if (is_resource($this->connection)) {
			fwrite($this->connection, $string.CRLF, strlen($string)+2);
			if ($this->debug) {
				if (strlen($string) > 100) {
					echo ">> ".substr($string, 0 , 100)."...\n";
				} else {
					echo ">> $string\n";
				}
			}
			if ($wait_response) {
				$this->last_response = $this->readResponse();
			}
			return true;
		}
		return false;
	}
	
	/**
	 * ���������� ��������� ����� SMTP �������
	 *
	 * @return string
	 */
	public function getLastMessage() {
		return $this->last_response;
	}
	
	/**
	* ����������, ���������� ����������
	* @param void
	* @return void
	*/
	public function __destruct() {
		$this->sendData("QUIT");
		if (is_resource($this->connection)) {
			fclose($this->connection);
		}
	}
}