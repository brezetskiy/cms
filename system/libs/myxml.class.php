<?php
/**
 * Класс по работе с XML файлами
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Класс по работе с XML файлами
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class MyXML {
	
	/**
	* Открытый файл
	* @var resource
	*/
	public $fp;
	
	
	/**
	* Открывает XML файл для чтения
	* @param string $filename
	* @return void
	*/
	public function __construct($filename) {
		$this->fp = fopen($filename, 'r');
	}
	
	/**
	* Читает ноду с файла
	* @param string $name
	* @return array
	*/
	private function readNode($name) {
		$start = false;
		$end = false;
		$node = '';
		
		while ($line = fgets($this->fp, 4096)) {
			// Определяем конец ноды
			if (preg_match("/<\/".$name.">/", $line) && $start == true) {
				$end = true;
				break;
			}
			
			// Определяем начало ноды
			if (preg_match("/<".$name.">/", $line)) {
				if ($start == false) {
					// Достигнуто начало ноды
					$start = true;
					continue;
				} else {
					// выходим из цикла, достигнуто начало следующей ноды
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
	* Читает следующую указанную ноду
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
	* Деструктор класса
	* @param void
	* @return void
	*/
	public function __destruct() {
		fclose($this->fp);
	}
}
?>