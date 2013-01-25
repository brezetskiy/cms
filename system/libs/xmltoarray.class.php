<?php
/**
* Класс для разбора небольших XML файлов
* преобразует XML документ в массив
*
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/


/**
 * Класс для преобразования XML документа в массив
 *
 */
class XMLToArray {
	
	/**
	 * XML парсер
	 *
	 * @var resource
	 */
    private $parser;
    
    /**
     * Стек обработанных элементов 
     *
     * @var array
     */
    private $node_stack = array();

    /**
     * Парсит XML документ в многомерный массив 
     *
     * @param string $xml
     * @return array
     */
    public function parseXml($xml) {
        /**
         * Создаем XML парсер и задаем обработчики тегов
         */
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "startElement", "endElement");
        xml_set_character_data_handler($this->parser, "characterData");

        /**
         * Создаем root элемент
         */
        $this->node_stack = array();
        $this->startElement(null, "root", array());

        /**
         * Запускаем парсер
         */
        xml_parse($this->parser, $xml);
        xml_parser_free($this->parser);

        $rnode = array_pop($this->node_stack);
        return $rnode['_elements'];
    }

    /**
     * Обработчик открывающего XML тега
     *
     * @param resource $parser
     * @param string $name
     * @param array $attrs
     */
    
    protected function startElement($parser, $name, $attrs) {
        $node = array();
        $node["_name"] = $name;
        $node["_data"]      = "";
        $node["_elements"]  = array();
        
        reset($attrs);
        while (list($key, $value)=each($attrs)) {
        	$node[$key] = $value;
        }
        
        array_push($this->node_stack, $node);
    }

    /**
     * Обработчик закрывающего XML тега
     *
     * @param resource $parser
     * @param string $name
     */
    protected function endElement($parser, $name) {
        $node = array_pop($this->node_stack);
        $node["_data"] = trim($node["_data"]);

        $lastnode = count($this->node_stack);
        array_push($this->node_stack[$lastnode-1]["_elements"], $node);
    }


    /**
     * Обработчик текстовых блоков XML документа
     *
     * @param resource $parser
     * @param string $data
     */
    protected function characterData($parser, $data) {
        $lastnode = count($this->node_stack);
        $this->node_stack[$lastnode-1]["_data"] .= $data;
    }

}
?>