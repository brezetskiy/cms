<?php
/**
* Отправка сообщений по протоколу SMTP
* @package Pilot
* @subpackage CMS
* @version 1.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/


/**
* Определение конца строки
*/
define('CRLF', "\r\n");

/**
* Отправка сообщений по протоколу SMTP
* @package Maillist
* @subpackage CMS
*/
class SMTP {
	
	/**
	* Соединение с сервером
	* @var resource
	*/
	private $connection;
	
	/**
	 * Включение режима отладки для класса
	 * @var bool
	 */
	public $debug = false;
	
	/**
	* Таймаут соединения
	* @var int
	*/
	private $timeout = 5;
	
	/**
	* Код последнего ответа сервера
	* @var string
	*/
	private $last_code;
	
	/**
	* Текст сообщения последнего ответа сервера
	* @var string
	*/
	private $last_message;
	
	/**
	* Последний ответ сервера
	* @var string
	*/
	private $last_response;
	
	/**
	* Строка приветствия для SMTP
	* @var string
	*/
	private $helo = '';
	
	/**
	* Конструктор
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
		* Чтобы вытащить из буфера начальное приглашение сервера
		*/
		$this->readResponse();
		
		//return $this->need_auth ?  $this->ehlo() : $this->helo();
		
		if (!$this->ehlo()) {
			trigger_error(cms_message('Maillist', 'Не удаётся послать приветствие почтовому серверу.'), E_USER_ERROR);
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
			case '': // так как указание none в ini файле без кавычек равнозначно пустому значению
				$auth_result = true;
				break;
			default:
				trigger_error(cms_message('CMS', 'Неподдерживаемый тип SMTP авторизации: %s', $auth_type), E_USER_ERROR);
		}
		
		if (!$auth_result) {
			trigger_error(cms_message('CMS', 'Ошибка SMTP авторизации'), E_USER_ERROR);
		}
	}

	/**
	* Login авторизация
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
	* Простейшая Plain Text авторизация
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
	* Посылает почтовое сообщение
	* @param string $from Адрес отправителя
	* @param string $to Адрес получателя
	* @param array $headers MIME-заголовки
	* @param string $body Тело сообщения
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
		
		// Устанавливаем заголовки для прохождения через SPAM фильтры
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
		
		// Разбор заголовков
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
	* SMTP-Приветствие БЕЗ авторизации
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
	* SMTP-Приветствие с авторизацией
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
	* Возвращает true, если последняя операция завершилась НЕ с кодом $code
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
	* Чтение ответа сервера
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
	* Посылает данные серверу
	* @param string $string Строка, посылаемая серверу
	* @param boolean $wait_response После отсылки строки прочитать ответ сервера
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
	 * Возвращает последний ответ SMTP сервера
	 *
	 * @return string
	 */
	public function getLastMessage() {
		return $this->last_response;
	}
	
	/**
	* Деструктор, завершение соединения
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