<?php
/** 
 * ����� ��� ������� XML � �������������� SAX 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 


/**
 * ����� ��� �������� � �� ��������������� � ������ �������� XML � �������������� SAX
 */
class SaxXmlParser {
	/**
	 * XML Parser
	 * @var resource
	 */
	private $parser;
	
	/**
	 * ���������� XML ���������
	 * @var string
	 */
	public $xml;
	
	/**
	 * Callback'� ��� ��������� �����
	 * @var unknown_type
	 */
	private $callbacks = array();
	
	/**
	 * ���������� �� ��������� (��� �����, ������� �� ������� � $this->callbacks)
	 * @var callback
	 */
	private $default_callback = null;
	
	/**
	 * ���������� ��������� ������
	 * @var callback
	 */
	private $char_data_callback = null;
	
	/**
	 * ������� ���� ����� (������������ ��� ������������ ���� � �������� ����)
	 * @var array
	 */
	private $stack = array();
	
	const TAG_START = 1;
	const TAG_END 	= 2;
	
	/**
	 * ����������� ������
	 *
	 * @param callback $char_data_callback
	 * @param callback $default_callback
	 */
	public function __construct($char_data_callback = null, $default_callback = null) {
		$this->parser = xml_parser_create();
		xml_set_element_handler($this->parser, array($this, "startElement"), array($this, "endElement"));
		xml_set_character_data_handler($this->parser, array($this, "charData"));
		
		$this->setCharDataCallback($char_data_callback);
		$this->setDefaultCallback($default_callback);
	}
	
	/**
	 * ������ ���� (����� ��������� ��� ����� ��� URL)
	 * @param string $filename
	 * @throws Exception
	 */
	public function parseFile($filename) {
		echo "[i] Opening '$filename'...\n";
		if (preg_match("~^http://~i", $filename)) {
			$Download = new Download();
			$Download->setTimeLimit(60, 600);
			$start = microtime(true);
			if(!$this->xml = $Download->get($filename)) {
				throw new Exception("Unable to download file '$filename': ".$Download->getErrorMessage());
				return;
			}
			echo "[T] Download time: ".round(microtime(true)-$start, 4)." sec.\n";
		} else {
			if (defined('HACK')) {
				
				if (!file_exists($filename)) {
					throw new Exception("Unable to open file '$filename'");
					return;
				}
				
				$fp = fopen($filename, 'r');
				echo "[i] File opened. Parsing XML...\n";
				
				do {
					$this->xml = fread($fp, 1000000);
					// ���� � xml templatemonster
					//$this->xml = str_replace('undertaker'.chr(0x92).'s', 'undertakers', $this->xml);
					//$this->xml = str_replace('kid'.chr(0x92).'s', 'kids', $this->xml);
					//$this->xml = str_replace('children'.chr(0x92).'s', 'childrens', $this->xml);
					$this->xml = str_replace(chr(0x92), '', $this->xml);
					$this->parseXml($this->xml, feof($fp));
				} while (!feof($fp));
				fclose($fp);
				
				
			} elseif(!$this->xml = file_get_contents($filename)) {
				throw new Exception("Unable to open file '$filename'");
				return;
			}
		}
		
		if (!defined('HACK')) {
			return $this->parseXml($this->xml);
		}
	}
	
	/**
	 * ������ XML, ���������� � ���� ������. XML ������ ���� � ��������� UTF-8
	 * @param string $xml
	 */
	public function parseXml(&$xml, $last_piece = null) {
		$this->xml = $xml;
		
		if(!xml_parse($this->parser, $this->xml, $last_piece)) {
			x($this->xml);
			throw new Exception("XML Parsing Error: ".xml_error_string(xml_get_error_code($this->parser))." in line ".xml_get_current_line_number($this->parser));
			return 0;
		}
		
		if ($last_piece === null || $last_piece === true) {
			xml_parser_free($this->parser);
		}
		// �� ������� - xml ������������ � ����
		//unset($this->xml);
	}
	
	/**
	 * ������������� ���������� �� ��������� (��� �����, � ������� �� ����� ������������� ����������)
	 * ������ callback-�������: function char_callback($type (tag start/end), $tag_name, $tag_path, $tag_attributes = array())
	 * @param callback $function
	 */
	public function setDefaultCallback($function) {
		$this->checkCallback($function);
		$this->default_callback = $function;
	}
	
	/**
	 * ������������� ���������� ��������� ������
	 * ������ callback-�������: function char_callback($tag_name, $tag_path, $characted_data)
	 * @param callback $function
	 */
	public function setCharDataCallback($function) {
		$this->checkCallback($function);
		$this->char_data_callback = $function;
	}
	
	/**
	 * ������������� ���������� ����
	 * ��� ���� ����������� � ���� ����, ��������: /root/person/name
	 * ������ callback-�������: function char_callback($type (tag start/end), $tag_name, $tag_path, $tag_attributes = array())
	 * @param string $tag
	 * @param callback $function
	 */
	public function setCallback($tag, $function) {
		$this->checkCallback($function);
		$this->callbacks[$tag] = $function;
	}
	
	/**
	 * ���������, ���������� �� ��������� ������� ��� �����
	 * @param callback $function
	 */
	private function checkCallback($function) {
		if ($function === null) {
			// ��������� ������ �����������
			return;
		} elseif (is_string($function)) {
			// ������� ��� ������� (������)
			if (!function_exists($function)) {
				throw new Exception("Callback function '$function' not found");
			}
		} elseif (is_array($function)) {
			// ������� ��� ������ (������)
			if (!method_exists($function[0], $function[1])) {
				throw new Exception("Callback method '".get_class($function[0])."::$function[1]' not found");
			}
		} else {
			// ���������� �������� �� ����� �� ������� ��� �����
			throw new Exception("Unknown callback format");
		}
	}
	
	/**
	 * ���������� ��������� ������
	 * @param resource $parser
	 * @param string $data
	 */
	private function charData($parser,$data) {
		$path = '/'.implode('/', $this->stack);
		if ($this->char_data_callback !== null) {
			call_user_func_array($this->char_data_callback, array(end($this->stack), $path, $data));
		}
	}
	
	/**
	 * ���������� ������������ ����
	 * @param resource $parser
	 * @param string $name
	 * @param array $attrs
	 */
	private function startElement($parser, $name, $attrs) {
		$this->stack[] = $name;
		$path = '/'.implode('/', $this->stack);
		if (isset($this->callbacks[$path])) {
			call_user_func_array($this->callbacks[$path], array(self::TAG_START, $name, $path, $attrs));
		} elseif ($this->default_callback !== null) {
			call_user_func_array($this->default_callback, array(self::TAG_START, $name, $path, $attrs));
		}
	}
	
	/**
	 * ���������� ������������ ����
	 * @param resource $parser
	 * @param string $name
	 */
	private function endElement($parser, $name) {
		$path = '/'.implode('/', $this->stack);
		if (isset($this->callbacks[$path])) {
			call_user_func_array($this->callbacks[$path], array(self::TAG_END, $name, $path));
		} elseif ($this->default_callback !== null) {
			call_user_func_array($this->default_callback, array(self::TAG_END, $name, $path));
		}
		array_pop($this->stack);
	}
}
?>