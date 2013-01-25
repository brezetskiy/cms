<?php
/**
 * ����� �� ������ � XML �������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ����� �� ������ � XML �������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class MyXML {
	
	/**
	* �������� ����
	* @var resource
	*/
	public $fp;
	
	
	/**
	* ��������� XML ���� ��� ������
	* @param string $filename
	* @return void
	*/
	public function __construct($filename) {
		$this->fp = fopen($filename, 'r');
	}
	
	/**
	* ������ ���� � �����
	* @param string $name
	* @return array
	*/
	private function readNode($name) {
		$start = false;
		$end = false;
		$node = '';
		
		while ($line = fgets($this->fp, 4096)) {
			// ���������� ����� ����
			if (preg_match("/<\/".$name.">/", $line) && $start == true) {
				$end = true;
				break;
			}
			
			// ���������� ������ ����
			if (preg_match("/<".$name.">/", $line)) {
				if ($start == false) {
					// ���������� ������ ����
					$start = true;
					continue;
				} else {
					// ������� �� �����, ���������� ������ ��������� ����
					break;
				}
			}
			
			if ($start == true) {
				$node .= $line;
			}
		}
		
		return $node;
	}
	
	/**
	* ������ ��������� ��������� ����
	* @param string $name
	* @return array
	*/
	function nextSibling($name) {
		$node = $this->readNode($name);
		if (empty($node)) return array();
		preg_match_all('/<([^>]+)>([^<]+)<\/\\1>/U', $node, $matches);
		
		$result = array();
		reset($matches[1]);
		while(list($key,$val) = each($matches[1])) {
			$result[$val] = $matches[2][$key];
		}
		
		return $result;
	}
	
	
	/**
	* ���������� ������
	* @param void
	* @return void
	*/
	public function __destruct() {
		fclose($this->fp);
	}
}
?>