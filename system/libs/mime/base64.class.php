<?php
/**
* �����, ����������� MIME-���������
* @package Pilot
* @subpackage CMS
* @version 1.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

/**
* �����, ����������� MIME-���������
* @package Maillist
* @subpackage Libraries
*/
class MimeBase64 {
	
	/**
	* �����������
	* @return object
	*/
	protected function __construct($from, $to) {
	}
	
	/**
	* ����������� ������ ��� ���������� ������� � ���������
	* @param string $string  ������ ��� ��������������
	* @return string
	*/
	public static function formatString($string) {
		if (preg_match("/^[a-zA-Z0-9@\.,_<> ]+$/", $string)) return $string;
		//$charset = Charset::detectCyrCharset($string);
		$charset = 'Windows-1251';
		/**
		 * M$ Outlook �� �������� ��������� CP1251, ������� ������ �� �� windows-1251
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
	* �������, ������� ��������� ������ base64 ������ ������
	* @param  string $string
	* @return string
	*/
	public static function encode($string) {
		return chunk_split(base64_encode(($string)));
	}
}
