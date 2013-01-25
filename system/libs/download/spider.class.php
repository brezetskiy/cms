<?php
/**
 * Надстройка над классом Download, которая формирует соединения для multy_query
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Надстройка над классом Download, которая формирует соединения для multy_query
 * @package Pilot
 * @subpackage Libraries
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class DownloadSpider extends Download {
	
	/**
	 * Ресурс соединения CURL
	 *
	 * @var resource
	 */
	private $ch;
	
	
	/**
	 * Функция, выполняющая запрос и возвращающая результат этого запроса
	 *
	 * @param array $options
	 * @return string
	 */
	private function doRequest($options) {
		$this->response_headers = array();
		
		// Выполнение запроса
		$this->ch = curl_init();
		$this->curl_setopt_array($ch, $this->options);
		$this->curl_setopt_array($ch, $options);
		return $this->ch;
	}
	
	/**
	 * Заканчивает работу соединения в multy_query
	 *
	 * @return bool
	 */
	public function close() {
		$this->info = curl_getinfo($this->ch);
		
		// Обработка ошибок
		if ($this->error_number = curl_errno($this->ch)) {
			$this->error_message = curl_error($this->ch);
		} else {
			$this->error_message = '';
		}
		
		curl_close($this->ch);
		
		// В случае возникновения ошибки возвращаем false
		if ($this->error_number) {
			return false;
		} else {
			return true;
		}
	}	
}
?>