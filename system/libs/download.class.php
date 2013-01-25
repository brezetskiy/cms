<?php
/**
 * Класс, который отвечает за скачивание файлов с удалённых серверов
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Класс, который отвечает за скачивание файлов с удалённых серверов
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class Download {
	
	/**
	 * Имя файла с куками
	 * 
	 * @var string
	 */
	private $cookie_file = '';
	
	/**
	 * Включение режима отладки
	 *
	 * @var bool
	 */
	private $debug = false;
	
	/**
	 * Дополнительная информация о работе CURL
	 * @var array
	 */
	private $info = array();
	
	/**
	 * Значение поля REFERER
	 * 
	 * @var string
	 */
	private $referer = '';
	
	/**
	 * Заголовки, передаваемые с запросом
	 * Все ключи этого массива должны быть в нижнем регистре!
	 * @var array
	 */
	private $request_headers = array(
		'accept' => '*/*',
		'accept-charset' => 'Windows-1251, *;q=0.1',
		'accept-language' => 'ru;q=1.0, uk;q=0.9, en;q=0.8',
		'cache-control' => 'no-cache' 
	);
	
	private $options = array(
		'NOBODY' => false, // исключать ли тело ответа с вывода
		// 'AUTOREFERER' => true, // автоматически устанавливать Referer при получении заголовка Location, работает с PHP >= 5.1.0
		'RETURNTRANSFER' => true, // возвращать результат выполнения запроса
		'FOLLOWLOCATION' => true, // переходить по заголовкам Location
		'UNRESTRICTED_AUTH' => true, // продолжать посылать запрос для 401 страницы даже при переходе по Location
		'MAXREDIRS' => 5, // количество переходов по CURLOPT_FOLLOWLOCATION
		'FAILONERROR' => true, // если код ошибки больше 400, то не выводить страницу
		'HEADER' => false, // писать заголовки с содержимым
		'ENCODING' => '', // Пустая строка - поддержка всех типов сжатия, которые поддерживает CURL
		'CONNECTTIMEOUT' => 60, // количество секунд, которые CURL будет ждать для установки соединения, 0 - бесконечно
		'TIMEOUT' => 300, // максимальная длительносты выполнения CURL запроса
		'USERAGENT' => '',
		'CURLOPT_SSL_VERIFYPEER' => 0, // проверка сертификата удаленного узла при подключении по SSL
	
		// cookie
		'COOKIEFILE' => '', // имя файла, в котором хранятся cookie, устанавливается в конструкторе
		'COOKIEJAR' => '', // имя файла, в который будут сохранены переданные куки, устанавливается в конструкторе
		
		// отладка
		'NOPROGRESS' => true, // вывод строки закачки файла
		'VERBOSE' => false
	);
	
	private $set_cookie = array();
	
	/**
	 * Заголовки, полученные при ответе на запрос
	 * @var array
	 */
	private $response_headers = array();
	
	/**
	 * Номер ошибк, которую вернул CURL
	 * все номера ошибок можно смотреть на сайте http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @var int
	 */
	private $error_number = 0;
	
	/**
	 * Ошибка, которая произошла, в текстовом виде
	 * все номера ошибок можно смотреть на сайте http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @var string
	 */
	private $error_message = '';
	
	/**
	 * Конструктор класса
	 * @return object
	 */
	public function __construct() {
		/**
		 * Обработка хранения Cookie
		 */
		$this->cookie_file = TMP_ROOT.'cookie_'.uniqid().'.txt';
		touch($this->cookie_file);
		$this->options['COOKIEFILE'] = $this->cookie_file;
		$this->options['COOKIEJAR'] = $this->cookie_file; 
		register_shutdown_function(array($this, '__destruct'));
		
		/**
		 * Установка дополнительных параметров
		 */
		$this->options['USERAGENT'] = ini_get('user_agent');
		$this->options['HEADERFUNCTION'] = array(&$this, 'parseHeader');
		
		/**
		 * Проверяем наличие CURL
		 */
		if (!extension_loaded('curl')) {
			// Программе нужна поддержка CURL
			trigger_error(cms_message('CMS', 'Необходимо скомпилировать PHP с поддержкой CURL.'), E_USER_ERROR);
		}
		 
		if (ini_get('safe_mode') || ini_get('open_basedir')) {
			// В безопасном режиме этот параметр работать не будет
			unset($this->options['FOLLOWLOCATION']);
		} 
	}
	
	
	/**
	 * Добавление заголовка запроса
	 * @param string $key
	 * @param string $value
	 */
	public function setHeader($key, $value) {
		$this->request_headers[strtolower($key)] = $value;
	}
	
	/**
	 * Добавление массива заголовков запроса
	 * @param array $headers
	 */
	public function addHeaders($headers) {
		reset($headers); 
		while (list($key,$value) = each($headers)) { 
			$this->setHeader($key, $value); 
		}
	}
	
	/**
	 * Удаление заголовка запроса
	 * @param string $key
	 */
	public function deleteHeader($key) {
		unset($this->request_headers[strtolower($key)]);
	}
	
	/**
	 * Возвращает заголовки ответа
	 * @return array
	 */
	public function getResponseHeaders() {
		return $this->response_headers;
	}
	
	/**
	 * Ограничение работы CURL запроса по времени
	 *
	 * @param int $connect - max время на установку соединения
	 * @param int $total - max время на работу библиотеки
	 */
	public function setTimeLimit($connect, $total) {
		$this->options['CONNECTTIMEOUT'] = (int)$connect;
		$this->options['TIMEOUT'] = (int)$total;
	}
	
	/**
	 * Устанавливает ограничение по скорости, если закачка будет держаться
	 * $duration секунд на скорости $rate байт в секунду, то соединение будет разорвано
	 *
	 * @param int $duration
	 * @param int $rate
	 */
	public function setSpeedLimit($duration, $rate) {
		$this->options['LOW_SPEED_LIMIT'] = $rate;
		$this->options['LOW_SPEED_TIME'] = $duration;
	}
	
	/**
	 * Включение режима отладки
	 *
	 * @param bool $status
	 */
	public function debugMode($status) {
		$this->debug = ($status == true) ? true : false;
		
		// Закрываем  соединение, если оно было открыто
		if (isset($this->options['STDERR']) && is_resource($this->options['STDERR'])) {
			fclose($this->options['STDERR']);
		}
		
		// Переключаем debug режим
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
	 * Устанавливает Cookie
	 * не оттестирована, да и сама по себе работает наверное неправильно
	 * посмотреть как на сервер передаются разные cookie со знаками ; и 
	 */
	public function setCookie($name, $value = null) {
		if (is_null($value)) {
			// Удаляем cookie
			unset($this->set_cookie[$name]);
		} else {
			$this->set_cookie[$name] = "$name=$value";
//			Будет ли работать с COOKIE_FILE или стоит их добавлять в COOKIE_FILE, 
// 			возможно ли устанавливать несколько cookie
//			'CURLOPT_COOKIE' = '';
//			curl_setopt($ch, CURLOPT_COOKIE, "name1=value1; name2=value2"); 
		}
		
		if (!empty($this->set_cookie)) {
			$this->options['COOKIE'] = implode("; ", $this->set_cookie);
		}
	}
	
	
	/**
	 * Устанавливает значение для заголовка Referer
	 *
	 * @param string $referer
	 */
	public function setReferer($referer) {
		$this->referer = $referer;
	}
	
	/**
	 * Читаем информацию о результате выполнения запроса
	 * 
	 * @return array
	 */
	public function getInfo() {
		return $this->info;
	}
	
	/**
	 * Сообщение об ошибке
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->error_message;
	}
	
	
	
	/**
	 * Запрос методом GET
	 *
	 * @param string $url
	 * @param array $data - информация, которую необходимо передать методом GET
	 */
	public function get($url, $data = array(), $if_modified_since = 0) {
		// Формируем GET заголовок
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
		 * Запрос документа IF Modified Since, надо оттестировать, хорошо для поиска
		 * IMHO Этот тип запроса работает только с GET запросами, хотя не факт
		 */
		if ($if_modified_since > 0) {
			$options['TIMECONDITION'] = 'CURL_TIMECOND_IFMODSINCE'; // Вернёт документ толко в случае, если он был изменён с указанной в параметре CURLOPT_TIMEVALUE даты
			$options['TIMEVALUE'] = $if_modified_since;	// если документ не изменён с указанной даты, то выводит 304 Not Modified заголовок
		}
		
		$this->referer = $url;
		return $this->doRequest($options);
		
	}
	
	/**
	 * Запрос методом POST
	 *
	 * @param string $url
	 * @param array $data - информация, которую необходимо передать методом POST
	 */
	public function post($url, $data = array()) {
		// Формируем POST заголовок
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
		
		// Выполнение запроса
		$ch = curl_init();
		$this->curl_setopt_array($ch, $this->options);
		$this->curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$this->info = curl_getinfo($ch);
		
		// Обработка ошибок
		if ($this->error_number = curl_errno($ch)) {
			$this->error_message = curl_error($ch);
		} else {
			$this->error_message = '';
		}
		curl_close($ch);
		
		// Преобразовываем кодировку в системную
		// 02.10.2010 bugfix rudenko - при редиректах $this->response_headers['content-type'] содержит массив, в таких случаях берем последнее значение
		if (isset($this->response_headers['content-type']) && is_array($this->response_headers['content-type'])) {
			$this->response_headers['content-type'] = end($this->response_headers['content-type']);
		}
		// 02.10.2010 Некоторые сервера после указания кодировки ставят ; например: windows-1251;
		if (isset($this->response_headers['content-type']) && preg_match("/charset=([^;]+);?$/", $this->response_headers['content-type'], $matches)) {
			if (strtolower($matches[1]) != strtolower(CMS_CHARSET)) {
				$result = iconv($matches[1], CMS_CHARSET.'//IGNORE', $result);
			}
		}
		
		// В случае возникновения ошибки возвращаем false
		if ($this->error_number) {
			return false;
		} else {
			return $result;
		}
	}
	
	/**
	 * Устанавливает параметры соединения с CURL
	 *
	 * @param resource $ch 
	 * @param array $options - параметрысоединения
	 */
	private function curl_setopt_array(&$ch, $options) {
		reset($options);
		while(list($key,$val) = each($options)) {
			// Возможность указывать параметры без префикса CURLOPT_
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
	* Разбор заголовков
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
		 * Если предыдущий заголовок - пустая строка, то значит 
		 * сервер вернул несколько заголовков. Поэтому необходимо очистить
		 * буффер заголовков.
		 */
		if (empty($prev_header)) {
			$this->response_headers = array();
		}
		
		// Функция должна возвращать длинну заголовка
		$length = strlen($header);
		
		// Обработка заголовков ключ: значение
		if (!preg_match("/^(.+):(.+)$/iU", $header, $matches)) {
			$prev_header = $header;
			return $length;
		}
		
		$header_key = strtolower(trim($matches[1]));
		$header_val = trim($matches[2]);
		
//		if ($header_key == 'set-cookie') {
//			Надо переписать эту часть кода, если она вообще нужна
// 
//			preg_match_all("/(?:([a-z0-9_\-\.]+)\=([^;]*)[;\t]*)+/i", $header_val, $cookie);
//			
//			$parsed_cookie = array();
//			reset($cookie);
//			while(list($index,) = each($cookie[1])) {
//				$parsed_cookie[$cookie[1][$index]] = $cookie[2][$index];
//			}
//			
//			// Определяем название переменной, которую должна установить принятая кука
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
			// Для дублирующихся параметров, которые уже обработаны
			$this->response_headers[$header_key][] = $header_val;
		} elseif (isset($this->response_headers[$header_key])) {
			// Дублирующиеся параметры преобразоваваем в массив
			$this->response_headers[$header_key] = array($this->response_headers[$header_key], $header_val);
		} else {
			$this->response_headers[$header_key] = $header_val;
		}
		
		$prev_header = $header;
		return $length;
	}
	
	/**
	 * Функция формирует массив, содержащий заголовки запроса
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
	 * Деструктор класса
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