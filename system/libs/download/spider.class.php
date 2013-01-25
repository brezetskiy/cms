<?php
/**
 * ���������� ��� ������� Download, ������� ��������� ���������� ��� multy_query
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ���������� ��� ������� Download, ������� ��������� ���������� ��� multy_query
 * @package Pilot
 * @subpackage Libraries
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class DownloadSpider extends Download {
	
	/**
	 * ������ ���������� CURL
	 *
	 * @var resource
	 */
	private $ch;
	
	
	/**
	 * �������, ����������� ������ � ������������ ��������� ����� �������
	 *
	 * @param array $options
	 * @return string
	 */
	private function doRequest($options) {
		$this->response_headers = array();
		
		// ���������� �������
		$this->ch = curl_init();
		$this->curl_setopt_array($ch, $this->options);
		$this->curl_setopt_array($ch, $options);
		return $this->ch;
	}
	
	/**
	 * ����������� ������ ���������� � multy_query
	 *
	 * @return bool
	 */
	public function close() {
		$this->info = curl_getinfo($this->ch);
		
		// ��������� ������
		if ($this->error_number = curl_errno($this->ch)) {
			$this->error_message = curl_error($this->ch);
		} else {
			$this->error_message = '';
		}
		
		curl_close($this->ch);
		
		// � ������ ������������� ������ ���������� false
		if ($this->error_number) {
			return false;
		} else {
			return true;
		}
	}	
}
?>