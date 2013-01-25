<?php
/**
 * ���� ��� SEO ����������� 
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Markovskiy Dima <dima@delta-x.ua>
 * @copyright Copyright 2009, Delta-X ltd.
 */

/**
 * ���� ���������� ���������� ����� �� ������ � ����������� phpMorphy. 
 * ������������ ��� ������������ �������� ���� (keywords) � �������� �������� (description).
 * �������� �������� ��������� ������������ �������� ���� ��������� �� 2 � 3 ����.  
 */

class Morphy extends phpMorphy {
	
	/**
	 * ��� ����� �������� �� �������� 
	 * ����� �������� �������� ����� � ��������
	 *
	 * @var string
	 */
	protected  $filename;
	
	/**
	 * ���������� ���� � keywords
	 * @var int
	 */
	protected  $numberonewords ;
	
	/**
	 * ������ ����� phpmorphy  
	 * @var unknown_type
	 */
	protected $phpmorphy;  

	/**
	 * ���������� ���� ��������� 
	 * �� ���� ����
	 *
	 * @var unknown_type
	 */
	protected  $numbertwoworbs;
	
	/**
	 * ���������� ���� �� ���� ����
	 * @var unknown_type
	 */
	protected $numberthreewords;
	

	/**
	 * ������ � ��������� �����
	 * ������������ ��� ��������
	 * 
	 * @var unknown_type
	 */
	protected $line; 
	
	/**
	 * id ������ ��� ������� ����������� 
	 * �������� ����� � ��������
	 * @var int
	 */
	protected $id;
	
	/**
	 * ��� ������� � ������� ����� ������� ����������
	 * @var string
	 */
	protected $tablename;
	
	/**
	 * ����� ���� ���� �������� 
	 * ���������� � ������������� ������
	 * @var array
	 */
	protected $word = array();
	
	/**
	 * �����, ������� ������ ����������� 
	 * ��� ����������� �������� ���� ����� ����
	 * � - ��������������� 
	 * � - ��������������
	 * @var array
	 */
	protected  $acsecclangpart = array('�', '�');
	

	/**
	 * ����������� ������
	 * ��������� ������� ��� ���������� ������
	 * @param string $content
	 */
	public function __construct($content) {
		$this->line = strip_tags($content);
		$this->line = trim($this->line);				
	}
	
	
	/**
	 * ��������� ������� �������� 
	 * �������� �������� - description 
	 * @param string $filenme
	 * @param int $id
 	 * @param int $lenght description 
 	 * @return string
	 */
	public function getDescription($lenght = 250) {
		if (!empty($this->line)) {
			$search = array('/\s+/');
			$line = preg_replace($search, ' ', $this->line);
			$line = htmlspecialchars($line);
			if (strlen($line) <= $lenght) {
				$description = $line;
			} else {
				$description = substr($line, 0, strpos($line, ' ', $lenght));
			}
		}		
		return $description;
	}
	
	
	
	/**
	 * ��������� �������� ����� ����������������
	 * @param string $filenme
	 * @param int $id 
 	 * @param int $quantityone words
 	 * @param int $quantitytwo words
 	 * @param int $quantitythree words
	 */
	public function updateKeywords ($numbone = 6, $numbtwo = 5, $numbthree = 4) {
		if(!empty($this->line)) {
			$this->getWords($this->line);
			
			$string = '';
			$this->numberonewords = $numbone;
			$this->numbertwoworbs = $numbtwo;
			$this->numberthreewords = $numbthree; 
			
			if(count($this->getWords()) <36) {
				$string .= substr($this->getOneWords(), 0, -1);
			} elseif (count($this->getWords()) >= 36 && count($this->getWords()) < 120) {
				$string .= substr($this->getOneWords().$this->getTwoWords(), 0 ,-1);
			} elseif (count($this->getWords()) > 120) {
				$string .= substr($this->getOneWords().$this->getTwoWords().$this->getThreeWords(), 0, -1);	
			}
			
			if (!empty($string)) {
				$result = $DB->update("UPDATE ".$this->tablename." SET `keywords_".LANGUAGE_CURRENT."` = '".$DB->escape($string)."' WHERE id = '".$this->id."'");
			}
		}
		return $string; 	
	}
	
	
	/**
	 * ����� ������� �������� ��� ����� �������� � ������������� ������ 
	 * ��������� ���������� ����� PHPMORPHY 
	 * @return arrey;
	 */
	private function getWords($string) {
		$line = array();
		$line = $this->readyWords($string);
		
		//������������� ����� �� ������ � �������� 
		$opts = array(
			'storage' => PHPMORPHY_STORAGE_MEM,
			'with_gramtab' => true,
			'predict_by_suffix' => true, 
			'predict_by_db' => true
		);

		//���� � ����� ��� ����� �������
		$dir = SITE_ROOT.'extras/phpmorphy/dicts';
		
		// ��������� ������� ���������� ��� ������ � ������� ���������
		$dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');
		
		// ������� ������� ��������� �����
		$flag = false;
		try {
			$this->phpmorphy = new phpMorphy($dict_bundle, $opts);
			$flag = true;
		} catch(phpMorphy_Exception $e) {
			$flag = false;
		}
		
		if ($flag) {
			$result = array();
			$correct_words = array();
			for ($i=0;$i<=count($line);$i++) {
				if (!empty($line[$i]) && $line[$i] != ' ' && (strlen($line[$i]) > 3)) {
					$word = strtoupper($line[$i]);
					$base_form = $this->phpmorphy->getBaseForm($word);
					$correct_words[$i] = $line[$i];
					if (!empty($base_form[0])) {
						$this->word[] = $base_form[0];
					}
				}
			}
		} 
		return $this->word; 
	}
	
	/**
	 * ����� �������� ����� ���� 
	 * �� ������ ��������
	 * @param string $string
	 * @return string
	 */
	private function readyWords($string) {
		$search = array('/\s+\w{1,3}\s+/ismU',  '/\�/ismU', '/\s+/ismU', '/\,/ismU', '/\�/ismU', '/\d+/ismU', "/\"/", "/\)/", "/\(/", "/\./", "/\%/");
		$string = preg_replace($search, ' ', $string);
		$string = preg_split('/\s+/', $string, -1, PREG_SPLIT_NO_EMPTY);
		return  $string;
	}
	
	
	
	/**
	 * ����� ��������� �������� ��������� 
	 * ������ �� ������������������� � ������������ ������
	 *   
	 * @return string
	 */
	private function getOneWords() {
		$literal = array();
		$result = array();
		$literal = $this->word;
		$result = array_count_values($literal);
		arsort($result);
		$key = array_keys($result);
		$string = '';
		$i = 0;
		while ($i <= $this->numberonewords && $i < count($result)) {
			$word = $this->phpmorphy->getAllFormsWithGramInfo($key[$i]);
			if($word[0]['all'][0]{0} != '�') {
				$this->numberonewords++;
			} else {
				$string .= strtolower($word[0]['forms'][0]). ',';
			}
			$i++;
		}
		return $string;
	}
	
	
	
	
	/**
	 * �����  ��������� ��������� �������� �� ���� ����
	 * � ������ �� ����� ������������ ����� ���� $this->word, 
	 * ��� ��� �� �������� ��� ���������� � ������������� ������ �����.  
	 * � ������ ���������� this->getVariant ��� ���� ����� ������������ 
	 * ��������� ���������� ��������������:
	 *  �������������� - *
	 *  * - ��������������
	 * 	��������������� - ���������������
	 * 	������ - ���������������
	 * 
	 * 
	 * @return string
	 */
	private function getTwoWords() {
		$word = array();
		$result = array();
		$word = $this->readyWords();
		for ($i = 0; $i <= count($word) - 1; $i++) {
			if ((!empty($word[$i]) && strlen($word[$i]) > 3) && (!empty($word[$i+1]) && strlen($word[$i+1]) > 3)) {
				if($this->getVariant($word[$i], $word[$i+1]) != '') {
					$result[] = $this->getVariant($word[$i], $word[$i+1]);
				}
			} else {
				
			}
		}
		$result = array_count_values($result);
		arsort($result);
		$key = array_keys($result);
		$string = '';
		
		for($i = 0; $i <= $this->numbertwoworbs; $i++) {
			if (isset($key[$i]) && !empty($key[$i])) {
				$line = preg_split('/\s+/',$key[$i] , -1, PREG_SPLIT_NO_EMPTY);			
				if ((isset($line[0]) && $line[0] != ' ' && strlen($line[0]) > 2) && (isset($line[1]) && $line[1] != ' ' && strlen($line[1]) > 2)) {
					$string .= strtolower($key[$i]).',';
				} else {
					$this->numbertwoworbs++;
				}
			}
		}
		return $string;
	}

	
	
	/**
	 * ��������� ����� � ���� ����
	 * @return string
	 */
	private function getThreeWords() {
		$word = array();
		$word = $this->readyWords();
		$result = array();
		
		for ($i = 0; $i <= count($word) - 2; $i++) {
			if ((!empty($word[$i]) && strlen($word[$i]) > 2) && (!empty($word[$i+1]) && strlen($word[$i+1]) > 2) && (!empty($word[$i+2]) && strlen($word[$i+2])>2)) {
				$result[] = $this->getVariant($word[$i], $word[$i+1], $word[$i+2]);
			}
		}

		$result = array_count_values($result);
		arsort($result);
		$key = array_keys($result);
		$string = '';
		for($i = 0; $i <= $this->numberthreewords; $i++) {
			if (isset($key[$i]) && !empty($key[$i])) {
				$line = preg_split('/\s+/',$key[$i] , -1, PREG_SPLIT_NO_EMPTY);	
				if (isset($line[0]) && isset($line[1]) && isset($line[2]) && ($line[0] != ' ') && ($line[1] != ' ') && ($line[2]) != ' ') {
					$string .= strtolower($key[$i]).',';
				} else {
					$this->numberthreewords++;
				}	
			}
		}
		return $string;
	}
	
	/**
	 * � ����������� �� ���������� ���� � �� ���� 
	 * �������� ��������� ������ 
	 * ������������ �������� ����
	 *
	 * @param string $word1
	 * @param string $word2
	 * @param string $word3
	 * @return string
	 */
	private function getVariant($word1, $word2, $word3 = null) {
		$result = '';
		$firstwordform = $this->phpmorphy->getAllFormsWithGramInfo(strtoupper($word1));
		$secondwordform = $this->phpmorphy->getAllFormsWithGramInfo(strtoupper($word2));

		
		//���������� ����� ���� 
		$firstlangpart = substr($firstwordform[0]['all'][0], 0, strpos($firstwordform[0]['all'][0], ' '));
		$secondlangpart = substr($secondwordform[0]['all'][0], 0, strpos($secondwordform[0]['all'][0], ' '));
					
		if($word3 != null){

			//���������� ����� ���� 
			$thirdwordform = $this->phpmorphy->getAllFormsWithGramInfo(strtoupper($word3));
			$thirdlangpart = substr($thirdwordform[0]['all'][0], 0, strpos($thirdwordform[0]['all'][0], ' '));
				
			if($firstlangpart == '�' && $secondlangpart == '�' && $thirdlangpart == '�') {
//				x('���');
				$result = $this->subadjectsubWord($firstwordform, $secondwordform, $thirdwordform);
			}elseif ($firstlangpart == '�' && $secondlangpart == '�' && $thirdlangpart == '�') {
//				x('���');
				$result = $this->adjectsubWord($firstwordform, $secondwordform, $thirdwordform);
			}elseif ($firstlangpart == '�' && $secondlangpart == '�' && $thirdlangpart == '�') {
//				x('���');
				$result = $this->allSubstantiveWord($firstwordform, $secondwordform, $thirdwordform);
			}elseif ($firstlangpart == '�' && $secondlangpart == '�' && $thirdlangpart == '�') {
//				x('���');
				$result = $this->subAdjectiveWord($firstwordform, $secondwordform, $thirdwordform); 
			}
			
		} else {
					
			if(($firstlangpart == '�' || $secondlangpart == '�') && ($firstlangpart != $secondlangpart)) {
				$result = $this->adjectiveWord($firstwordform, $secondwordform);
			} elseif (($firstlangpart == '�') && ($secondlangpart == '�') && ($firstwordform[0]['forms'][0] != $secondwordform[0]['forms'][0])) {
				$result = $this->substantiveWord($firstwordform, $secondwordform);
			} elseif ($firstlangpart == '�' && $secondlangpart == '�') {
				$result = $this->verbWord($firstwordform, $secondwordform);
			}
		}
		
		return $result;
	} 
	
	
	/**
	 * ������ ��� ������������ �������� ���� 
	 * ��������� �� ���� ���� ������ �����
	 */
	
	/**
	 * ����� ��������� �������� ����� ���� ���� �� ���� �� ��� �������������� 
	 * (���������� �� ���������)
	 * @param array $firstwordform
	 * @param array $secondwebform
	 */
	protected function adjectiveWord($firstwordform, $secondwordform) {
		if (!is_array($firstwordform)) return '';
		$result = '';
		reset($firstwordform);
		while (list($index,$row) = each($firstwordform[0]['all'])) {
			//��������� �� ������������ ���������� ��,��,��,�� == ��,��,��,��
			if(substr($row, 2, 8) == substr($secondwordform[0]['all'][0], 2, 8)) {
				$result .= $firstwordform[0]['forms'][$index].' '.$secondwordform[0]['forms'][0];
				break; 
			} 
		}
		return $result;
	}
	
	
	
	/**
	 * ����� ������������ ������������ ��� ���� 
	 * ��������������� ���������������
	 * 
	 * @param array $firstwordform
	 * @param array $secondwordform
	 * @return string
	 */
	protected function substantiveWord($firstwordform, $secondwordform) {
		if (!is_array($firstwordform)) return '';
		$result = '';
			
		reset($firstwordform);
		reset($secondwordform);
		
		while(list($index,$row) = each($secondwordform[0]['all'])) {
			//��� ������������� ���� �� ���� ���� ��������������� ���������� ����� ������ ��� � ����������� ������
			if ((substr($row, 2, 5) == substr($firstwordform[0]['all'][0], 2, 5)) && (substr($row, 8, 2) == '��')) {
				$result = $firstwordform[0]['forms'][0].' '.$secondwordform[0]['forms'][$index];
				break;
			} 
		}
		return $result;
	}
	
	
	
	/**
	 * ����� ��������� ��������� ������� ������� 
	 * �� ������� ������� � ������� ���������������� 
	 * 
	 * @param array $firswordform
	 * @param array $secondwordform
	 */
	protected function verbWord($firstwordform, $secondwordform) {
		if (!is_array($firstwordform)) return '';
		$result = '';
		
		reset($firstwordform);
		reset($secondwordform);
		
		reset($secondwordform[0]['all']);
		while (list($index,$row) = each($secondwordform[0]['all'])) {
			if (substr($row, 2, 2) == '��' && substr($row, 8, 2) == '��') {
				$result = $firstwordform[0]['forms'][0].' '.$secondwordform[0]['forms'][$index];
				break;
			}elseif (substr($row, 2, 2) == '��' && $index == 0) {
				$result = $firstwordform[0]['forms'][0].' '.$secondwordform[0]['forms'][$index];
				break;
			}
		}
	}
	
	/**
	 * ���������� ������� ������������ �������� ���� 
	 * ��������� �� ���� ���� 
	 */
	
	/**
	 * ����� ��������� �������� ����� ������� ��������� �����������
	 * ��������������� �������������� ���������������
	 *
	 * @param array $firswordform
	 * @param array $secondwordform
	 * @param array $thirdwordform
	 */
	protected function subadjectsubWord($firswordform, $secondwordform, $thirdwordform) {
		if (empty($thirdwordform[0]['all'][1])) return '';
		
		$result = '';
		
		reset($firswordform);
		reset($secondwordform);
		reset($thirdwordform);
		
		while(list($index,$row) = each($secondwordform[0]['all'])) {
			$langpart = preg_split("/\;/", $row, -1, PREG_SPLIT_NO_EMPTY);
			for ($i=0; $i < count($langpart); $i++) {
				if ((substr($langpart[$i], 2, 8) == substr($thirdwordform[0]['all'][1], 2, 8)) ) {
					$result = $firswordform[0]['forms'][0].' '.$secondwordform[0]['forms'][$index].' '.$thirdwordform[0]['forms'][1];
					break 2;
				} 
			}
		} 
		return $result;
	}
	

	/**
  	 * ����� ��������� �������� ����� ������� ��������� �����������
	 * ��������������� �������������� ���������������
	 *
	 * @param array $firswordform
	 * @param array $secondwordform
	 * @param array $thirdwordform
	 */
	protected function adjectsubWord($firstwordform, $secondwordform, $thirdwordform) {
		if(empty($thirdwordform[0]['forms'][0])) return '';
		$result = '';
		
		reset($firstwordform);
		reset($secondwordform);
		reset($thirdwordform);

		while (list($index,$row) = each($firstwordform[0]['all'])) {
			$langpart = preg_split("/\;/", $row, -1, PREG_SPLIT_NO_EMPTY);
			for ($i=0; $i < count($langpart); $i++) {
				if(substr($langpart[$i], 2, 8) == substr($thirdwordform[0]['all'][0], 2, 8)) {
					$result .= $firstwordform[0]['forms'][$index].' ';
					break 2; 
				} 
			}
		}
		
		while (list($index,$row) = each($secondwordform[0]['all'])) {
			$langpart = preg_split("/\;/", $row, -1, PREG_SPLIT_NO_EMPTY);
			for ($i=0; $i < count($langpart); $i++) {
				if(substr($langpart[$i], 2, 8) == substr($thirdwordform[0]['all'][0], 2, 8)) {
					$result .= $secondwordform[0]['forms'][$index].' ';
					break 2; 
				} 
			}
		}	
		
		$result .= $thirdwordform[0]['forms'][0]; 
		return $result;
	}
	
	/**
	 * ����� ��������� �������� ����� ��� ��� ����� ��� ���������������
	 *
	 * @param unknown_type $firstwordform
	 * @param unknown_type $secondwordform
	 * @param unknown_type $thirdwordform
	 */
	protected function allSubstantiveWord($firstwordform, $secondwordform, $thirdwordform) {
		if(empty($firstwordform[0]['forms'][0]) || empty($secondwordform[0]['forms'][1]) || empty($thirdwordform[0]['forms'][1])) return '';
		$result = '';
				
		reset($firstwordform);
		reset($secondwordform);
		reset($thirdwordform);
		
		if(
			($firstwordform[0]['forms'][0] != $secondwordform[0]['forms'][0]) && 
			($firstwordform[0]['forms'][0] != $thirdwordform[0]['forms'][0]) && 
			($thirdwordform[0]['forms'][0] != $secondwordform[0]['forms'][0])
		) {
			$result = $result = $firstwordform[0]['forms'][0].' '.$secondwordform[0]['forms'][1].' '.$thirdwordform[0]['forms'][1]; 
		}
		return $result;
	}
	
	/**
	 * ����� ������������ �������� ���� �� 3 ���� 
	 * ���� �-�-�
	 *
	 * @param array $firstwordform
	 * @param array $secondwordform
	 * @param array $thirdwordform
	 * @return string
	 */
	protected function subAdjectiveWord($firstwordform, $secondwordform, $thirdwordform) {
		if(empty($secondwordform[0]['forms'][0]) || empty($thirdwordform[0]['forms'][1])) return '';
		
		$result = '';
		reset($firstwordform);
		reset($secondwordform);
		reset($thirdwordform);
		while (list($index,$row) = each($firstwordform[0]['all'])) {
			$langpart = preg_split("/\;/", $row, -1, PREG_SPLIT_NO_EMPTY);
			for ($i=0; $i < count($langpart); $i++) {
				if(substr($langpart[$i], 2, 8) == substr($secondwordform[0]['all'][0], 2, 8)) {
					$result .= $firstwordform[0]['forms'][$index].' ';
					break 2; 
				} 
			}
		}	
		$result .= $secondwordform[0]['forms'][0].' '.$thirdwordform[0]['forms'][1];
		return $result;
	}
}

?>