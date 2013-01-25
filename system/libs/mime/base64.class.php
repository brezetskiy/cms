<?php
/**
* Класс, формирующий MIME-сообщение
* @package Pilot
* @subpackage CMS
* @version 1.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

/**
* Класс, формирующий MIME-сообщение
* @package Maillist
* @subpackage Libraries
*/
class MimeBase64 {
	
	/**
	* Конструктор
	* @return object
	*/
	protected function __construct($from, $to) {
	}
	
	/**
	* Форматирует строку для корректной вставки в заголовок
	* @param string $string  Строка для преобразования
	* @return string
	*/
	public static function formatString($string) {
		if (preg_match("/^[a-zA-Z0-9@\.,_<> ]+$/", $string)) return $string;
		//$charset = Charset::detectCyrCharset($string);
		$charset = 'Windows-1251';
		/**
		 * M$ Outlook не понимает кодировку CP1251, поэтому меняем ее на windows-1251
		 */
		/*if (strtoupper($charset) == 'CP1251') {
			$charset = 'Windows-1251';
		}*/
		
		if (strlen(base64_encode($string)) > 40) {
			$strings = explode("\r\n", substr(chunk_split(base64_encode($string), 40, "\r\n"), 0, -2));
		} else {
			$strings = array(base64_encode($string));
		}
		return "=?$charset?B?".implode("?=\r\n =?$charset?B?", $strings)."?=";
	}
	
	/**
	* Функция, которая правильно делает base64 версию текста
	* @param  string $string
	* @return string
	*/
	public static function encode($string) {
		return chunk_split(base64_encode(($string)));
	}
}
