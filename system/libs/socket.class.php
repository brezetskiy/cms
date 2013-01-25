<?php
/**
 * ����� ��� ������ � ��������
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class Socket {
	
	/**
	 * ������ �����������
	 * @var resource
	 */
	protected $socket = null;
	
	/**
	 * ��������� ��������� ������
	 * @var string
	 */
	protected $error = '';
	
	/**
	 * ��������� ������
	 * @var array
	 */
	protected $options = array(
		/* �������� ���������� ���������� */
		'debug' => false,
		/* ������������� ��������� ����� ��� ������ ������ write() */
		'auto_append' => '',
	);
	
	/**
	 * ��������� ���������� � ��������� ������
	 * @param string $host
	 * @param int $port
	 */
	public function connect($host, $port) {
		/**
		 * ������� �����
		 */
		$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false) {
			return $this->socketError();
		}
		
		$this->debug("Socket created");
		
		/**
		 * ������ ���������
		 */
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>5,"usec"=>0));
//		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1,"usec"=>0));
		
		/**
		 * ������������� �����������
		 */
		$result = @socket_connect($this->socket, $host, $port);
		if ($result === false) {
			return $this->socketError();
		}
		
		$this->debug("Connected to $host:$port");
		
		return true;
	}
	
	/**
	 * ��������� ������� ���������� � ��������
	 * @return null
	 */
	public function disconnect() {
		@socket_close($this->socket);
	}
	
	/**
	 * ����� ������ � ����� � ������ ����� �������
	 * ����������� ������ write � read ��� ����� ������� ������ � ����
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
	 * ����� ������ � �����
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
	 * ������ ������ �� ������. ������ ������������ ������� �� $chunk_size ����,
	 * ������ ������������ ��� ���������� $delimiter ��� � ����� ������. Delimiter ����� ���� 
	 * ���������� ���������� pcre
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
			 * ���� ����� �� ������������ - ���������� ������
			 */
			if (preg_match("/".$delimiter."/ims", $response)) {
				$this->debug("response matched delimiter $delimiter");
				break;
			}
		}
		
		/**
		 * ���� �� ����� �� ������� ����� ������ - ������ ���
		 */
//		if (preg_match("/".$delimiter."/ims", $response)) {
//			$response .= $this->read($delimiter, $chunk_size);
//		}
		
		$this->debug("< $response");
		return $response;
	}
	
	/**
	 * ������ ��������� ������
	 * ������� ����, �������� �� �������� ����������� (Socket::connect)
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
	 * ���������� ����� ��������� ������������ ������
	 * @return string
	 */
	public function getLastError() {
		return $this->error;
	}
	
	/**
	 * ������������� ������, ������������ ��� ������ ������
	 * @return false
	 */
	protected function socketError() {
		return $this->error("Socket Error: ".socket_last_error($this->socket).', '.socket_strerror(socket_last_error($this->socket)));
	}
	
	/**
	 * ������������� �� ������
	 * @param string $message
	 * @return false
	 */
	protected function error($message) {
		$this->error = $message;
		return false;
	}
	
	/**
	 * Debug ���������
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
	 * �����������
	 * @return Socket
	 */
	public function __construct() {}
	
	
}

?>