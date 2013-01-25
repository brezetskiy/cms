<?php
/** 
 * Класс для приведения слова к основе с использованием стеммера mystem 
 * @package Pilot 
 * @subpackage Search 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

class MyStem {
	
	/**
	 * Содержит последнюю возникшую ошибку
	 * @var string
	 */
	static public $error;
	
	/**
	 * Приводит все слова в фразе к базовой форме, возвращает строку,
	 * в которой содержатся все слова в базовой форме (повторы удалены,
	 * каждое слово может иметь несколько подходящих базовых форм)
	 * Возвращает FALSE в случае ошибки
	 *
	 * @param string $phrase
	 * @return string|false
	 */
	static public function stemToString($phrase) {
		$stem = self::stemToArray($phrase);
		
		if ($stem === false) {
			return false;
		}
		
		return implode(' ', $stem);
	}
	
	/**
	 * Приводит все слова в фразе к базовой форме, возвращает
	 * ассоциативный массив в формате
	 * [<исходное_слово>] => Array(
	 * 	  0 => <базовая_форма_0>,
	 * 	  1 => <базовая_форма_1>,
	 *    ...
	 * )
	 * Возвращает FALSE в случае ошибки
	 *
	 * @param string $phrase
	 * @return array|false
	 */
	static public function stemToAssocArray($phrase) {
		return self::stem($phrase);
	}
	
	/**
	 * Приводит все слова в фразе к базовой форме, возвращает массив,
	 * в котором содержатся все слова в базовой форме (повторы удалены,
	 * каждое слово может иметь несколько подходящих базовых форм)
	 * Возвращает FALSE в случае ошибки
	 *
	 * @param string $phrase
	 * @return array|false
	 */
	static public function stemToArray($phrase) {
		$stem = self::stem($phrase);
		
		if ($stem === false) {
			return false;
		}
		
		$result = array();
		
		reset($stem); 
		while (list(,$stems) = each($stem)) {
			reset($stems); 
			while (list(,$word) = each($stems)) { 
				if (!in_array($word, $result)) {
					$result[] = $word;
				} 
			}
		}
		
		return $result;
	}
	
	/**
	 * Создает результат стемминга в формате XML
	 *
	 * @param string $phrase
	 * @return string
	 */
	static public function createXmlResult($phrase) {
		$result = self::stem($phrase);
		
		$xml = '<?xml version="1.0" encoding="Windows-1251" ?>'."\n<root>\n";
		
		reset($result); 
		while (list($original_word,$stems) = each($result)) { 
			$xml .= "<word>\n	<original>$original_word</original>\n	<stems>\n";
			
			reset($stems); 
			while (list(,$stem) = each($stems)) { 
				$xml .= "		<stem>$stem</stem>\n";
			}
			
			$xml .= "	</stems>\n</word>\n";
		}
		
		$xml .= '</root>';
		return $xml;
	}
	
	/**
	 * Приводит слова в фразе к основе
	 * @param string $phrase
	 * @return array|false
	 */
	static private function stem($phrase) {
		if (trim($phrase) == '') return array();
		
		if (SEARCH_MYSTEM_LOCAL) {
			return self::executeLocal($phrase);
		} else {
			return self::executeRemote($phrase);
		}
	}
	
	/**
	 * Выполняет стемминг с использованием локальной программы mystem
	 * @param string $phrase
	 * @return array|false
	 */
	static private function executeLocal($phrase) {
		$result = Shell::exec_stdin(SEARCH_MYSTEM_PATH.' -n', $phrase, $error);
		if (!empty($error)) {
			self::$error = $error;
			return false;
		}
		return self::parseResult($result);
	}
	
	/**
	 * Выполняет стемминг с использованием удаленного XML-интерфейса
	 * @param string $phrase
	 * @return array|false
	 */
	static private function executeRemote($phrase) {
		$Download = new Download();
		$result = $Download->post(SEARCH_MYSTEM_URL, array('phrase' => $phrase));
		if ($result === false) {
			self::$error = $Download->getErrorMessage();
			return false;
		}
		return self::parseXmlResult($result);
	}
	
	/**
	 * Парсит результат выполнения команды mystem
	 * @param string $mystem_result
	 * @return array
	 */
	static private function parseResult($mystem_result) {
//		очепятки{очепятка?|очепятки?|очепяток?}
//		морфологического{морфологический}
		$result = array();
		$words = preg_split('~[\r\n]+~i', $mystem_result, -1, PREG_SPLIT_NO_EMPTY);
		reset($words); 
		while (list(,$row) = each($words)) { 
			if (preg_match("~^([^{]+){([^}]+)}~i", $row, $match)) {
				$result[strtolower($match[1])] = preg_split('~(\?\|?|\|)~i', $match[2], -1, PREG_SPLIT_NO_EMPTY);
			}
		}
		return $result;
	}
	
	/**
	 * Парсит результат выполнения удаленной версии mystem
	 * @param string $mystem_result
	 * @return array|false
	 */
	static private function parseXmlResult($mystem_result) {
//	<word>
//        <original>постоянные</original>
//        <stems>
//                <stem>постоянная</stem>
//                <stem>постоянный</stem>
//        </stems>
//	</word>
		
		$result = array();
		$xml = simplexml_load_string($mystem_result);
		if ($xml === false) {
			return false;
		}

		$i = 0;
		while ($xml->word[$i]) {
			$j = 0;
			
			while ($xml->word[$i]->stems->stem[$j]) {
				$result[(string)$xml->word[$i]->original][] = $xml->word[$i]->stems->stem[$j];
				$j++;
			}
			$i++;
		}
		
		return $result;
	}
}

?>