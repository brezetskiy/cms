<?php
/** 
 * Класс для разбора XML с использованием SAX 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 


/**
 * Класс для быстрого и не требовательного к памяти парсинга XML с использованием SAX
 */
class SaxXmlParser {
	/**
	 * XML Parser
	 * @var resource
	 */
	private $parser;
	
	/**
	 * Содержимое XML документа
	 * @var string
	 */
	public $xml;
	
	/**
	 * Callback'и для обработки тегов
	 * @var unknown_type
	 */
	private $callbacks = array();
	
	/**
	 * Обработчик по умолчанию (для тегов, которые не описаны в $this->callbacks)
	 * @var callback
	 */
	private $default_callback = null;
	
	/**
	 * Обработчик текстовых данных
	 * @var callback
	 */
	private $char_data_callback = null;
	
	/**
	 * Текущий стек тегов (используется для формировании пути к текущему тегу)
	 * @var array
	 */
	private $stack = array();
	
	const TAG_START = 1;
	const TAG_END 	= 2;
	
	/**
	 * Конструктор класса
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
	 * Парсит файл (можно указывать имя файла или URL)
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
					// бага в xml templatemonster
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
	 * Парсит XML, переданный в виде строки. XML должен быть в кодировке UTF-8
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
		// не очищать - xml записывается в логи
		//unset($this->xml);
	}
	
	/**
	 * Устанавливает обработчик по умолчанию (для тегов, у которых не задан специфический обработчик)
	 * Формат callback-функции: function char_callback($type (tag start/end), $tag_name, $tag_path, $tag_attributes = array())
	 * @param callback $function
	 */
	public function setDefaultCallback($function) {
		$this->checkCallback($function);
		$this->default_callback = $function;
	}
	
	/**
	 * Устанавливает обработчик текстовых данных
	 * Формат callback-функции: function char_callback($tag_name, $tag_path, $characted_data)
	 * @param callback $function
	 */
	public function setCharDataCallback($function) {
		$this->checkCallback($function);
		$this->char_data_callback = $function;
	}
	
	/**
	 * Устанавливает обработчик тега
	 * Имя тега указывается в виде пути, например: /root/person/name
	 * Формат callback-функции: function char_callback($type (tag start/end), $tag_name, $tag_path, $tag_attributes = array())
	 * @param string $tag
	 * @param callback $function
	 */
	public function setCallback($tag, $function) {
		$this->checkCallback($function);
		$this->callbacks[$tag] = $function;
	}
	
	/**
	 * Проверяет, существует ли указанная функция или метод
	 * @param callback $function
	 */
	private function checkCallback($function) {
		if ($function === null) {
			// разрешаем пустые обработчики
			return;
		} elseif (is_string($function)) {
			// указано имя функции (строка)
			if (!function_exists($function)) {
				throw new Exception("Callback function '$function' not found");
			}
		} elseif (is_array($function)) {
			// указано имя метода (массив)
			if (!method_exists($function[0], $function[1])) {
				throw new Exception("Callback method '".get_class($function[0])."::$function[1]' not found");
			}
		} else {
			// переданный параметр не похож на функцию или метод
			throw new Exception("Unknown callback format");
		}
	}
	
	/**
	 * Обработчик текстовых данных
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
	 * Обработчик открывающего тега
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
	 * Обработчик закрывающего тега
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