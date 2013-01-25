<?php
/**
 * �����, ������� �������� �� ���������� ������ � �������� ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * �����, ������� �������� �� ���������� ������ � �������� ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class Download {
	
	/**
	 * ��� ����� � ������
	 * 
	 * @var string
	 */
	private $cookie_file = '';
	
	/**
	 * ��������� ������ �������
	 *
	 * @var bool
	 */
	private $debug = false;
	
	/**
	 * �������������� ���������� � ������ CURL
	 * @var array
	 */
	private $info = array();
	
	/**
	 * �������� ���� REFERER
	 * 
	 * @var string
	 */
	private $referer = '';
	
	/**
	 * ���������, ������������ � ��������
	 * ��� ����� ����� ������� ������ ���� � ������ ��������!
	 * @var array
	 */
	private $request_headers = array(
		'accept' => '*/*',
		'accept-charset' => 'Windows-1251, *;q=0.1',
		'accept-language' => 'ru;q=1.0, uk;q=0.9, en;q=0.8',
		'cache-control' => 'no-cache' 
	);
	
	private $options = array(
		'NOBODY' => false, // ��������� �� ���� ������ � ������
		// 'AUTOREFERER' => true, // ������������� ������������� Referer ��� ��������� ��������� Location, �������� � PHP >= 5.1.0
		'RETURNTRANSFER' => true, // ���������� ��������� ���������� �������
		'FOLLOWLOCATION' => true, // ���������� �� ���������� Location
		'UNRESTRICTED_AUTH' => true, // ���������� �������� ������ ��� 401 �������� ���� ��� �������� �� Location
		'MAXREDIRS' => 5, // ���������� ��������� �� CURLOPT_FOLLOWLOCATION
		'FAILONERROR' => true, // ���� ��� ������ ������ 400, �� �� �������� ��������
		'HEADER' => false, // ������ ��������� � ����������
		'ENCODING' => '', // ������ ������ - ��������� ���� ����� ������, ������� ������������ CURL
		'CONNECTTIMEOUT' => 60, // ���������� ������, ������� CURL ����� ����� ��� ��������� ����������, 0 - ����������
		'TIMEOUT' => 300, // ������������ ������������ ���������� CURL �������
		'USERAGENT' => '',
		'CURLOPT_SSL_VERIFYPEER' => 0, // �������� ����������� ���������� ���� ��� ����������� �� SSL
	
		// cookie
		'COOKIEFILE' => '', // ��� �����, � ������� �������� cookie, ��������������� � ������������
		'COOKIEJAR' => '', // ��� �����, � ������� ����� ��������� ���������� ����, ��������������� � ������������
		
		// �������
		'NOPROGRESS' => true, // ����� ������ ������� �����
		'VERBOSE' => false
	);
	
	private $set_cookie = array();
	
	/**
	 * ���������, ���������� ��� ������ �� ������
	 * @var array
	 */
	private $response_headers = array();
	
	/**
	 * ����� �����, ������� ������ CURL
	 * ��� ������ ������ ����� �������� �� ����� http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @var int
	 */
	private $error_number = 0;
	
	/**
	 * ������, ������� ���������, � ��������� ����
	 * ��� ������ ������ ����� �������� �� ����� http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @var string
	 */
	private $error_message = '';
	
	/**
	 * ����������� ������
	 * @return object
	 */
	public function __construct() {
		/**
		 * ��������� �������� Cookie
		 */
		$this->cookie_file = TMP_ROOT.'cookie_'.uniqid().'.txt';
		touch($this->cookie_file);
		$this->options['COOKIEFILE'] = $this->cookie_file;
		$this->options['COOKIEJAR'] = $this->cookie_file; 
		register_shutdown_function(array($this, '__destruct'));
		
		/**
		 * ��������� �������������� ����������
		 */
		$this->options['USERAGENT'] = ini_get('user_agent');
		$this->options['HEADERFUNCTION'] = array(&$this, 'parseHeader');
		
		/**
		 * ��������� ������� CURL
		 */
		if (!extension_loaded('curl')) {
			// ��������� ����� ��������� CURL
			trigger_error(cms_message('CMS', '���������� �������������� PHP � ���������� CURL.'), E_USER_ERROR);
		}
		 
		if (ini_get('safe_mode') || ini_get('open_basedir')) {
			// � ���������� ������ ���� �������� �������� �� �����
			unset($this->options['FOLLOWLOCATION']);
		} 
	}
	
	
	/**
	 * ���������� ��������� �������
	 * @param string $key
	 * @param string $value
	 */
	public function setHeader($key, $value) {
		$this->request_headers[strtolower($key)] = $value;
	}
	
	/**
	 * ���������� ������� ���������� �������
	 * @param array $headers
	 */
	public function addHeaders($headers) {
		reset($headers); 
		while (list($key,$value) = each($headers)) { 
			$this->setHeader($key, $value); 
		}
	}
	
	/**
	 * �������� ��������� �������
	 * @param string $key
	 */
	public function deleteHeader($key) {
		unset($this->request_headers[strtolower($key)]);
	}
	
	/**
	 * ���������� ��������� ������
	 * @return array
	 */
	public function getResponseHeaders() {
		return $this->response_headers;
	}
	
	/**
	 * ����������� ������ CURL ������� �� �������
	 *
	 * @param int $connect - max ����� �� ��������� ����������
	 * @param int $total - max ����� �� ������ ����������
	 */
	public function setTimeLimit($connect, $total) {
		$this->options['CONNECTTIMEOUT'] = (int)$connect;
		$this->options['TIMEOUT'] = (int)$total;
	}
	
	/**
	 * ������������� ����������� �� ��������, ���� ������� ����� ���������
	 * $duration ������ �� �������� $rate ���� � �������, �� ���������� ����� ���������
	 *
	 * @param int $duration
	 * @param int $rate
	 */
	public function setSpeedLimit($duration, $rate) {
		$this->options['LOW_SPEED_LIMIT'] = $rate;
		$this->options['LOW_SPEED_TIME'] = $duration;
	}
	
	/**
	 * ��������� ������ �������
	 *
	 * @param bool $status
	 */
	public function debugMode($status) {
		$this->debug = ($status == true) ? true : false;
		
		// ���������  ����������, ���� ��� ���� �������
		if (isset($this->options['STDERR']) && is_resource($this->options['STDERR'])) {
			fclose($this->options['STDERR']);
		}
		
		// ����������� debug �����
		if ($this->debug === true) {
			$fp = fopen('php://output', 'w');
			$this->options['STDERR'] = $fp;
			$this->options['VERBOSE'] = true;
			$this->options['NOPROGRESS'] = false;
		} else {
			unset($this->options['STDERR']);
			$this->options['VERBOSE'] = false;
			$this->options['NOPROGRESS'] = true;
		}
	}
	
	
	/**
	 * ������������� Cookie
	 * �� �������������, �� � ���� �� ���� �������� �������� �����������
	 * ���������� ��� �� ������ ���������� ������ cookie �� ������� ; � 
	 */
	public function setCookie($name, $value = null) {
		if (is_null($value)) {
			// ������� cookie
			unset($this->set_cookie[$name]);
		} else {
			$this->set_cookie[$name] = "$name=$value";
//			����� �� �������� � COOKIE_FILE ��� ����� �� ��������� � COOKIE_FILE, 
// 			�������� �� ������������� ��������� cookie
//			'CURLOPT_COOKIE' = '';
//			curl_setopt($ch, CURLOPT_COOKIE, "name1=value1; name2=value2"); 
		}
		
		if (!empty($this->set_cookie)) {
			$this->options['COOKIE'] = implode("; ", $this->set_cookie);
		}
	}
	
	
	/**
	 * ������������� �������� ��� ��������� Referer
	 *
	 * @param string $referer
	 */
	public function setReferer($referer) {
		$this->referer = $referer;
	}
	
	/**
	 * ������ ���������� � ���������� ���������� �������
	 * 
	 * @return array
	 */
	public function getInfo() {
		return $this->info;
	}
	
	/**
	 * ��������� �� ������
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->error_message;
	}
	
	
	
	/**
	 * ������ ������� GET
	 *
	 * @param string $url
	 * @param array $data - ����������, ������� ���������� �������� ������� GET
	 */
	public function get($url, $data = array(), $if_modified_since = 0) {
		// ��������� GET ���������
//		$get_data = array();
//		reset($data);
//		while(list($key, $val) = each($data)) {
//			$get_data[$key] = urlencode($key).'='.urlencode($val);
//		}
//		$get_data = implode('&', $get_data);
		$get_data = http_build_query($data);
		$url = $this->parseURL($url);
		if (!empty($get_data)) {
			$request_url = (strpos($url, '?') === false) ? $url.'?'.$get_data : $url.'&'.$get_data;
		} else {
			$request_url = $url;
		}
		
		$options = array(
			'REFERER' => $this->referer,
			'URL' => $request_url,
			'HTTPHEADER' => $this->requestHeaders(),
			'HTTPGET' => true
		);
		
		/**
		 * ������ ��������� IF Modified Since, ���� �������������, ������ ��� ������
		 * IMHO ���� ��� ������� �������� ������ � GET ���������, ���� �� ����
		 */
		if ($if_modified_since > 0) {
			$options['TIMECONDITION'] = 'CURL_TIMECOND_IFMODSINCE'; // ����� �������� ����� � ������, ���� �� ��� ������ � ��������� � ��������� CURLOPT_TIMEVALUE ����
			$options['TIMEVALUE'] = $if_modified_since;	// ���� �������� �� ������ � ��������� ����, �� ������� 304 Not Modified ���������
		}
		
		$this->referer = $url;
		return $this->doRequest($options);
		
	}
	
	/**
	 * ������ ������� POST
	 *
	 * @param string $url
	 * @param array $data - ����������, ������� ���������� �������� ������� POST
	 */
	public function post($url, $data = array()) {
		// ��������� POST ���������
		$post_data = array();
		reset($data);
		while(list($key, $val) = each($data)) {
			if (is_array($val)) {
				
				foreach ($val as $subkey => $subval) {
					$post_data[$key.'['.$subkey.']'] = urlencode($key.'['.$subkey.']').'='.urlencode($subval);
				}
				
			} else {
				$post_data[$key] = urlencode($key).'='.urlencode($val);
			}
		}

		$options = array(
			'REFERER' => $this->referer,
			'URL' => $this->parseURL($url),
			'HTTPHEADER' => $this->requestHeaders(),
			'POST' => true,
			'POSTFIELDS' => implode('&', $post_data)
		);
		
		$this->referer = $url;
		return $this->doRequest($options);
	}
	
	
	private function parseURL($url) {
		$url = str_replace(' ', '%20', $url);
		return $url;
	}
	
	private function doRequest($options) {
		$this->response_headers = array();
		
		// ���������� �������
		$ch = curl_init();
		$this->curl_setopt_array($ch, $this->options);
		$this->curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$this->info = curl_getinfo($ch);
		
		// ��������� ������
		if ($this->error_number = curl_errno($ch)) {
			$this->error_message = curl_error($ch);
		} else {
			$this->error_message = '';
		}
		curl_close($ch);
		
		// ��������������� ��������� � ���������
		// 02.10.2010 bugfix rudenko - ��� ���������� $this->response_headers['content-type'] �������� ������, � ����� ������� ����� ��������� ��������
		if (isset($this->response_headers['content-type']) && is_array($this->response_headers['content-type'])) {
			$this->response_headers['content-type'] = end($this->response_headers['content-type']);
		}
		// 02.10.2010 ��������� ������� ����� �������� ��������� ������ ; ��������: windows-1251;
		if (isset($this->response_headers['content-type']) && preg_match("/charset=([^;]+);?$/", $this->response_headers['content-type'], $matches)) {
			if (strtolower($matches[1]) != strtolower(CMS_CHARSET)) {
				$result = iconv($matches[1], CMS_CHARSET.'//IGNORE', $result);
			}
		}
		
		// � ������ ������������� ������ ���������� false
		if ($this->error_number) {
			return false;
		} else {
			return $result;
		}
	}
	
	/**
	 * ������������� ��������� ���������� � CURL
	 *
	 * @param resource $ch 
	 * @param array $options - �������������������
	 */
	private function curl_setopt_array(&$ch, $options) {
		reset($options);
		while(list($key,$val) = each($options)) {
			// ����������� ��������� ��������� ��� �������� CURLOPT_
			if (substr($key, 0, strlen('CURLOPT_')) != 'CURLOPT_') {
				$key = 'CURLOPT_'.$key;
			}
			
			if (!defined($key)) {
				trigger_error('CURL param '.$key.' does not exists.', E_USER_WARNING);
			}
			curl_setopt($ch, constant($key), $val);
		}
	}
	
	
	/**
	* ������ ����������
	* 
	* @param string $headers
	* @return array
	*/
	private function parseHeader($ch, $header) {
		static $prev_header;
		
		if ($this->debug === true) {
			echo $header;
		}
		
		/**
		 * ���� ���������� ��������� - ������ ������, �� ������ 
		 * ������ ������ ��������� ����������. ������� ���������� ��������
		 * ������ ����������.
		 */
		if (empty($prev_header)) {
			$this->response_headers = array();
		}
		
		// ������� ������ ���������� ������ ���������
		$length = strlen($header);
		
		// ��������� ���������� ����: ��������
		if (!preg_match("/^(.+):(.+)$/iU", $header, $matches)) {
			$prev_header = $header;
			return $length;
		}
		
		$header_key = strtolower(trim($matches[1]));
		$header_val = trim($matches[2]);
		
//		if ($header_key == 'set-cookie') {
//			���� ���������� ��� ����� ����, ���� ��� ������ �����
// 
//			preg_match_all("/(?:([a-z0-9_\-\.]+)\=([^;]*)[;\t]*)+/i", $header_val, $cookie);
//			
//			$parsed_cookie = array();
//			reset($cookie);
//			while(list($index,) = each($cookie[1])) {
//				$parsed_cookie[$cookie[1][$index]] = $cookie[2][$index];
//			}
//			
//			// ���������� �������� ����������, ������� ������ ���������� �������� ����
//			preg_match("/^([^\=]+)(?=\=)/", $header_val, $var_name);
//			
//			$cookie_name = $var_name[1];
//			$cookie_value = $parsed_cookie[$cookie_name];
//			unset($var_name);
//			unset($parsed_cookie[$cookie_name]);
//			
//			$parsed_cookie['key'] = $cookie_name;
//			$parsed_cookie['value'] = $cookie_value;
//			
//			$headers[$header_key][] = $parsed_cookie;
//			unset($parsed_cookie);
			
//		} else
		if (isset($this->response_headers[$header_key]) && is_array($this->response_headers[$header_key])) {
			// ��� ������������� ����������, ������� ��� ����������
			$this->response_headers[$header_key][] = $header_val;
		} elseif (isset($this->response_headers[$header_key])) {
			// ������������� ��������� ��������������� � ������
			$this->response_headers[$header_key] = array($this->response_headers[$header_key], $header_val);
		} else {
			$this->response_headers[$header_key] = $header_val;
		}
		
		$prev_header = $header;
		return $length;
	}
	
	/**
	 * ������� ��������� ������, ���������� ��������� �������
	 * @return array
	 */
	private function requestHeaders() {
		$result = array();
		reset($this->request_headers); 
		while (list($key,$value) = each($this->request_headers)) { 
			$result[] = ucwords($key).": $value";
		}
		return $result;
	}
	
	/**
	 * ���������� ������
	 * @param void
	 * @return void
	 */
	public function __destruct() {
		if(file_exists($this->cookie_file)) {
			unlink($this->cookie_file);
		}
	}
	
	public function setOption($option, $value) {
		$this->options[$option] = $value;
	}
	
	public function getOptions(){
		return (IS_DEVELOPER) ? $this->options : array();
	}
	
	public function getCookieContent(){  
		return (IS_DEVELOPER) ? file_get_contents($this->cookie_file) : '';
	}
	
}


?>