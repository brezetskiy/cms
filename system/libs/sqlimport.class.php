<?php
/**
* Класс экспорта-импорта данных из таблиц
* @package Pilot
* @subpackage SDK
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X ltd, 2005
*/

/**
* Класс экспорта-импорта данных из таблиц
* @package Database
* @subpackage SDK
*/
class SQLImport {
	
	/**
	 * id родительских разделов
	 * @var array
	 */
	private $parents = array();
	
	/**
	 * Изменение id разделов
	 * @var array
	 */
	public $new_id = array();
	
	
	/**
	 * Запускает импорт разделов
	 * 
	 * @param string $file
	 */
	public function start($file, $start_id) {
		global $DB;
		
		// Устанавливаем id раздела, в который будет добавлена информация
		$this->parents = array($start_id);
		
		$query = "START TRANSACTION";
		$DB->query($query);
		
		$xml_data = file_get_contents($file);
		$xml_data = preg_replace("/^<\?xml[^>]+>[\s\n\r\t]*/i", '', $xml_data);
		$xml_parser = xml_parser_create();
		xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 'UTF-8');
		xml_set_element_handler($xml_parser, array(&$this, "startElement"), array(&$this, "endElement")); 
		// xml_set_character_data_handler($xml_parser, "characterData");
		xml_parse($xml_parser, $xml_data, true) or die(sprintf("XML error: %s at line %d",  xml_error_string(xml_get_error_code($xml_parser)),  xml_get_current_line_number($xml_parser))); 
		xml_parser_free($xml_parser);
		
		$query = "COMMIT";
		$DB->query($query);
	}

	/**
	 * Обработка нового тега
	 *
	 * @param resource $parser
	 * @param string $tagName
	 * @param array $attrs
	 */
	private function startElement($parser, $table_name, $attrs) {
		global $DB;
		
		
		// Игнорируем корневой раздел
		if ($table_name == 'root') {
			return;
		}
		
		// Определяем имя родительской колонки для данной таблицы
		$query = "SELECT name FROM cms_field WHERE id=(SELECT parent_field_id FROM cms_table WHERE name='".$table_name."')";
		$parent_field = $DB->result($query);
		
		// Сохраняем id старого раздела
		$old_id = $attrs['id'];
		unset($attrs['id']);
		
		// Добавляем запись
		$query = "INSERT INTO `tmp_".$table_name."` (";
		if (!empty($parent_field)) $query .= "`".$parent_field."`, ";
		$query .= "`".implode("`, `", array_keys($attrs))."`) VALUES (";
		if (!empty($parent_field)) $query .= "'".end($this->parents)."', ";
		reset($attrs); 
		while (list($index,$value) = each($attrs)) { 
			$attrs[$index] = $DB->escape($value);
		}
		$query .= "'".iconv('UTF-8', CMS_CHARSET, implode("', '", $attrs))."')";
		
		echo $query.'<br>';
		
		$this->parents[] = $new_id = $DB->insert($query);
		
		// Связь старый id - новый id
		$this->new_id[$table_name][$old_id] = $new_id;
	}
	
	/**
	 * Обработка конца тага
	 *
	 * @param resource $parser
	 * @param string $tagName
	 */
	private function endElement($parser, $tagName) { 
		array_pop($this->parents);
	}
	

}

?>