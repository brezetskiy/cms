<?php
/**
 * Класс для работы с сокетами
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class Socket {
	
	/**
	 * Ресурс подключения
	 * @var resource
	 */
	protected $socket = null;
	
	/**
	 * Последняя возникшая ошибка
	 * @var string
	 */
	protected $error = '';
	
	/**
	 * Параметры класса
	 * @var array
	 */
	protected $options = array(
		/* выводить отладочную информацию */
		'debug' => false,
		/* автоматически добавлять текст при каждом вызове write() */
		'auto_append' => '',
	);
	
	/**
	 * Выполняет соединение с указанным хостом
	 * @param string $host
	 * @param int $port
	 */
	public function connect($host, $port) {
		/**
		 * Создаем сокет
		 */
		$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false) {
			return $this->socketError();
		}
		
		$this->debug("Socket created");
		
		/**
		 * Задаем параметры
		 */
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>5,"usec"=>0));
//		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1,"usec"=>0));
		
		/**
		 * Устанавливаем подключение
		 */
		$result = @socket_connect($this->socket, $host, $port);
		if ($result === false) {
			return $this->socketError();
		}
		
		$this->debug("Connected to $host:$port");
		
		return true;
	}
	
	/**
	 * Разрывает текущее соединение с сервером
	 * @return null
	 */
	public function disconnect() {
		@socket_close($this->socket);
	}
	
	/**
	 * Пишет данные в сокет и читает ответ сервера
	 * Комбинирует вызовы write и read для более удобной работы с ними
	 *
	 * @param string $write_data
	 * @param string $read_delimiter
	 * @param int $read_chunk_size
	 * @return string|false
	 */
	public function getResponse($write_data, $read_delimiter, $read_chunk_size = 100) {
		if (strlen($write_data)>0) {
			if (!$this->write($write_data)) {
				return false;
			}
		}
		
		if (!($response = $this->read($read_delimiter, $read_chunk_size))) {
			return false;
		}
		
		return $response;
	}
	
	/**
	 * Пишет данные в сокет
	 * @param string $data
	 */
	public function write($data) {
		$data = $data.$this->options['auto_append'];
		if (($bytes_written = @socket_write($this->socket, $data, strlen($data))) === false) {
			$this->debug("write('$data') failed");
			return $this->socketError();
		}
		$this->debug("> $data");
		return $bytes_written;
	}
	
	/**
	 * Чтение данных из сокета. Чтение производится кусками по $chunk_size байт,
	 * чтение прекращается при достижении $delimiter или в конце данных. Delimiter может быть 
	 * регулярным выражением pcre
	 * @param string $delimiter
	 * @param int $chunk_size
	 */
	public function read($delimiter, $chunk_size = 100) {
		$response = '';
		while (strlen($read = @socket_read($this->socket, (int)$chunk_size, PHP_NORMAL_READ)) > 0) {
			if ($read === false) {
				return $this->socketError();
			}
			$response .= $read;
			$this->debug("read chunk: '$read'");
			
			/**
			 * Если дошли до ограничителя - прекращаем чтение
			 */
			if (preg_match("/".$delimiter."/ims", $response)) {
				$this->debug("response matched delimiter $delimiter");
				break;
			}
		}
		
		/**
		 * Если не дошли до нужного куска данных - читаем еще
		 */
//		if (preg_match("/".$delimiter."/ims", $response)) {
//			$response .= $this->read($delimiter, $chunk_size);
//		}
		
		$this->debug("< $response");
		return $response;
	}
	
	/**
	 * Задает параметры сокета
	 * Понятно дело, вызывать до создания подключения (Socket::connect)
	 * @param string $option
	 * @param mixed $value
	 */
	public function setOption($option, $value) {
		if (array_key_exists($option, $this->options)) {
			$this->options[$option] = $value;
		} else {
			trigger_error("Unknown parameter: $option", E_USER_WARNING);
		}
	}
	
	/**
	 * Возвращает текст последней произошедшей ошибки
	 * @return string
	 */
	public function getLastError() {
		return $this->error;
	}
	
	/**
	 * Устанавливает ошибку, произошедшую при работе сокета
	 * @return false
	 */
	protected function socketError() {
		return $this->error("Socket Error: ".socket_last_error($this->socket).', '.socket_strerror(socket_last_error($this->socket)));
	}
	
	/**
	 * Сигнализирует об ошибке
	 * @param string $message
	 * @return false
	 */
	protected function error($message) {
		$this->error = $message;
		return false;
	}
	
	/**
	 * Debug сообщение
	 * @param string $message
	 */
	protected function debug($message) {
		if ($this->options['debug']) {
			$message = preg_replace("~\r~", '\r', $message);
			$message = preg_replace("~\n~", '\n', $message);
			$message = preg_replace('~([^a-z0-9\s\|\+\'\"\\\:;/\~!@#\$%^&\*\(\)=\-\?<>\.,])~ie', '"0x".dechex(ord("\\1"))', $message);
			echo iconv('cp1251', 'utf-8//IGNORE', "[DEBUG]: $message\n");
		}
	}
	
	/**
	 * Конструктор
	 * @return Socket
	 */
	public function __construct() {}
	
	
}

?>