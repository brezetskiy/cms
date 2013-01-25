<?php
/**
*  ласс дл€ написани€ числа прописью
*
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/


/**
 *  ласс дл€ написани€ числа прописью
 */
class Num2Str {
	
	static public function convert($number, $language, $show_currency = true, $show_cents = true){ 
		$result=" "; 
		$words=" "; 
		
		/**
		 * ќпредел€ем количество копеек
		 */
		$cents = intval(($number*100 - intval($number)*100));
		
		/**
		 * ќпредел€ем рубли
		 */
		$number = intval($number);
		
		/**
		 * ћиллиарды
		 */
		if($number>=1000000000){
			$index = 0;
			self::semantic(intval($number/1000000000), $words, $index, 3, $language); 
			$result .= $words . self::$milliards[$language][$index]; 
			$number%=1000000000; 
		} 
	
		/**
		 * ћиллионы
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
		 * “ыс€чи
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
		 * —отни, дес€тки и единицы
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
		 *  опейки
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
			1 => "одна ", 
			2 => "дв≥ "
			),
		"ru" => array(
			1 => "одна ", 
			2 => "две "
		)
	);
	
	static private $_1_19 = array (
		"ua" => array(
			1 => "одна ",
			2 => "дв≥ ",
			3 => "три ",
			4 => "чотири ",
			5 => "п'€ть ",
			6 => "ш≥сть ",
			7 => "с≥м ",
			8 => "в≥с≥м ",
			9 => "дев'€ть ",
			10 => "дес€ть ",
			11 => "одинадц€ть ",
			12 => "дванадц€ть ",
			13 => "тринадц€ть ",
			14 => "чотирнадц€ть ",
			15 => "п'€тнадц€ть ",
			16 => "ш≥стнадц€ть ",
			17 => "с≥мнадц€ть ",
			18 => "в≥с≥мнадц€ть ",
			19 => "дев'€тнадц€ть "
		),
		"ru" => array(
			1 => "один ",
			2 => "два ",
			3 => "три ",
			4 => "четыре ",
			5 => "п€ть ",
			6 => "шесть ",
			7 => "семь ",
			8 => "восемь ",
			9 => "дев€ть ",
			10 => "дес€ть ",
			11 => "одиннацать ",
			12 => "двенадцать ",
			13 => "тринадцать ",
			14 => "четырнадцать ",
			15 => "п€тнадцать ",
			16 => "шестнадцать ",
			17 => "семнадцать ",
			18 => "восемнадцать ",
			19 => "дев€тнадцать "
		)
	);
	
	static private $tens = array (
		"ua" => array (
			2 => "двадц€ть ",
			3 => "тридц€ть ",
			4 => "сорок ",
			5 => "п€тдес€т ",
			6 => "ш≥стдес€т ",
			7 => "с≥мдес€т ",
			8 => "в≥с≥мдес€т ",
			9 => "дев'€носто "
		),
		"ru" => array (
			2 => "двадцать ",
			3 => "тридцать ",
			4 => "сорок ",
			5 => "п€тьдес€т ",
			6 => "шестьдес€т ",
			7 => "семьдес€т ",
			8 => "восемдес€т ",
			9 => "дев€носто "
		)
	);
	
	static private $hundreds = array (
		"ua" => array (
			1 => "сто ",
			2 => "дв≥ст≥ ",
			3 => "триста ",
			4 => "чотириста ",
			5 => "п'€тсот ",
			6 => "ш≥стсот ",
			7 => "с≥мсот ",
			8 => "в≥с≥мсот ",
			9 => "дев'€тсот "
		),
		"ru" => array (
			1 => "сто ",
			2 => "двести ",
			3 => "триста ",
			4 => "четыреста ",
			5 => "п€тьсот ",
			6 => "шестьсот ",
			7 => "семьсот ",
			8 => "восемьсот ",
			9 => "дев€тьсот "
		)
	);
	
	static private $thousands = array(
		"ua" => array (
			1 => "тис€ча ",
			2 => "тис€ч≥ ",
			3 => "тис€ч "
		),
		"ru" => array (
			1 => "тыс€ча ",
			2 => "тыс€чи ",
			3 => "тыс€ч "
		)
	);
	
	static private $millions = array (
		"ua" => array (
			1 => "м≥льйон ",
			2 => "м≥льйона ",
			3 => "м≥льйон≥в "
		),
		"ru" => array (
			1 => "миллион ",
			2 => "миллиона ",
			3 => "миллионов "
		)
	);
	
	static private $milliards = array (
		"ua" => array (
			1 => "м≥л≥ард ",
			2 => "м≥л≥арда ",
			3 => "м≥л≥ард≥в "
		),
		"ru" => array (
			1 => "миллиард ",
			2 => "миллиарда ",
			3 => "миллиардов "
		)
	);
	
	static private $currency = array (
		"ua" => array (
			1 => "гривн€ ",
			2 => "гривн≥ ",
			3 => "гривень "
		),
		"ru" => array (
			1 => "рубль ",
			2 => "рубл€ ",
			3 => "рублей "
		)
	);
	
	static private $cents = array(
		"ua" => array (
			1 => "коп≥йка ",
			2 => "коп≥йки ",
			3 => "коп≥йок "
		),
		"ru" => array (
			1 => "копейка ",
			2 => "копейки ",
			3 => "копеек "
		)
	);

}
?>