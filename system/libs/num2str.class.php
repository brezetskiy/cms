<?php
/**
* ����� ��� ��������� ����� ��������
*
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/


/**
 * ����� ��� ��������� ����� ��������
 */
class Num2Str {
	
	static public function convert($number, $language, $show_currency = true, $show_cents = true){ 
		$result=" "; 
		$words=" "; 
		
		/**
		 * ���������� ���������� ������
		 */
		$cents = intval(($number*100 - intval($number)*100));
		
		/**
		 * ���������� �����
		 */
		$number = intval($number);
		
		/**
		 * ���������
		 */
		if($number>=1000000000){
			$index = 0;
			self::semantic(intval($number/1000000000), $words, $index, 3, $language); 
			$result .= $words . self::$milliards[$language][$index]; 
			$number%=1000000000; 
		} 
	
		/**
		 * ��������
		 */
		if($number >= 1000000){ 
			$index = 0; 
			self::semantic(intval($number/1000000), $words, $index, 2, $language); 
			$result .= $words . self::$millions[$language][$index]; 
			$number%=1000000; 
			if($number == 0 && $show_currency){ 
				$result .= self::$currency[$language][3]; 
			} 
		}
	
		/**
		 * ������
		 */
		if($number >= 1000){
			$index = 0; 
			self::semantic(intval($number/1000), $words, $index, 1, $language); 
			$result .= $words . self::$thousands[$language][$index]; 
			$number%=1000; 
			if($number == 0 && $show_currency){ 
				$result .= self::$currency[$language][3]; 
			} 
		}
	
		/**
		 * �����, ������� � �������
		 */
		if($number != 0){
			$index = 0;
			self::semantic($number, $words, $index, 0, $language);
			$result .= $words;
			if ($show_currency) {
				$result .= self::$currency[$language][$index];
			}
		}
	
		/**
		 * �������
		 */
		if ($show_cents) {
			if($cents > 0){ 
				$index = 0; 
				self::semantic($cents, $words, $index, 1, $language); 
				$result .= $words . self::$cents[$language][$index]; 
			}
			else {
				$result .= " 00 " . self::$cents[$language][3]; 
			}
		}
		return $result; 
	}
	
	static private function semantic($number, &$words, &$fem, $f, $language){ 
		$words = "";
		$fl = 0;
		
		if($number >= 100){
			$words .= self::$hundreds[$language][intval($number/100)];
			$number%=100; 
		}
		
		if($number >= 20){ 
			$words .= self::$tens[$language][intval($number / 10)]; 
			$number%=10; 
			$fl=1; 
		}
		
		switch($number){ 
			case 1:  $fem=1; break; 
			case 2: 
			case 3: 
			case 4:  $fem=2; break; 
			default: $fem=3; break; 
		}
		
		if( $number ){ 
			if( $number < 3 && $f > 0 ){ 
				if ( $f >= 2 ) { 
					$words .= self::$_1_19[$language][$number]; 
				}
				else {
					$words .= self::$_1_2[$language][$number]; 
				} 
			}
			else { 
				$words .= self::$_1_19[$language][$number]; 
			} 
		}
	}
	

	static private $_1_2 = array(
		"ua" => array(
			1 => "���� ", 
			2 => "�� "
			),
		"ru" => array(
			1 => "���� ", 
			2 => "��� "
		)
	);
	
	static private $_1_19 = array (
		"ua" => array(
			1 => "���� ",
			2 => "�� ",
			3 => "��� ",
			4 => "������ ",
			5 => "�'��� ",
			6 => "����� ",
			7 => "�� ",
			8 => "��� ",
			9 => "���'��� ",
			10 => "������ ",
			11 => "���������� ",
			12 => "���������� ",
			13 => "���������� ",
			14 => "������������ ",
			15 => "�'��������� ",
			16 => "����������� ",
			17 => "��������� ",
			18 => "���������� ",
			19 => "���'��������� "
		),
		"ru" => array(
			1 => "���� ",
			2 => "��� ",
			3 => "��� ",
			4 => "������ ",
			5 => "���� ",
			6 => "����� ",
			7 => "���� ",
			8 => "������ ",
			9 => "������ ",
			10 => "������ ",
			11 => "���������� ",
			12 => "���������� ",
			13 => "���������� ",
			14 => "������������ ",
			15 => "���������� ",
			16 => "����������� ",
			17 => "���������� ",
			18 => "������������ ",
			19 => "������������ "
		)
	);
	
	static private $tens = array (
		"ua" => array (
			2 => "�������� ",
			3 => "�������� ",
			4 => "����� ",
			5 => "�������� ",
			6 => "��������� ",
			7 => "������� ",
			8 => "�������� ",
			9 => "���'������ "
		),
		"ru" => array (
			2 => "�������� ",
			3 => "�������� ",
			4 => "����� ",
			5 => "��������� ",
			6 => "���������� ",
			7 => "��������� ",
			8 => "���������� ",
			9 => "��������� "
		)
	);
	
	static private $hundreds = array (
		"ua" => array (
			1 => "��� ",
			2 => "���� ",
			3 => "������ ",
			4 => "��������� ",
			5 => "�'����� ",
			6 => "������� ",
			7 => "����� ",
			8 => "������ ",
			9 => "���'����� "
		),
		"ru" => array (
			1 => "��� ",
			2 => "������ ",
			3 => "������ ",
			4 => "��������� ",
			5 => "������� ",
			6 => "�������� ",
			7 => "������� ",
			8 => "��������� ",
			9 => "��������� "
		)
	);
	
	static private $thousands = array(
		"ua" => array (
			1 => "������ ",
			2 => "������ ",
			3 => "����� "
		),
		"ru" => array (
			1 => "������ ",
			2 => "������ ",
			3 => "����� "
		)
	);
	
	static private $millions = array (
		"ua" => array (
			1 => "������ ",
			2 => "������� ",
			3 => "������� "
		),
		"ru" => array (
			1 => "������� ",
			2 => "�������� ",
			3 => "��������� "
		)
	);
	
	static private $milliards = array (
		"ua" => array (
			1 => "����� ",
			2 => "������ ",
			3 => "������ "
		),
		"ru" => array (
			1 => "�������� ",
			2 => "��������� ",
			3 => "���������� "
		)
	);
	
	static private $currency = array (
		"ua" => array (
			1 => "������ ",
			2 => "����� ",
			3 => "������� "
		),
		"ru" => array (
			1 => "����� ",
			2 => "����� ",
			3 => "������ "
		)
	);
	
	static private $cents = array(
		"ua" => array (
			1 => "������ ",
			2 => "������ ",
			3 => "������ "
		),
		"ru" => array (
			1 => "������� ",
			2 => "������� ",
			3 => "������ "
		)
	);

}
?>