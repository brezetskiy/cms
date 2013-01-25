<?php
/**
 * Функции, по работе с сетью
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Copyright 2005, Delta-X ltd.
 */

/**
 * Набор методов по работе с сетью
 * @package Pilot
 * @subpackage CMS
 */
class Net {
	
	/**
	* Возвращает маску сети
	* @param int $netbits
	* @return string
	*/
	public static function netmask($netbits) {
		return (long2ip(ip2long("255.255.255.255") << (32-$netbits)));
		return (long2ip(ip2long("255.255.255.255") << (32-$netbits)));
	}
	
	/**
	* Возвращает адрес сети
	* @param string $ip
	* @param int $netbits
	* @return string
	*/
	public static function network($ip, $netbits) {
		return (long2ip((ip2long($ip)) & (ip2long(Net::netmask($netbits)))));
	}
	
	/**
	* Возвращает широковещательный адрес сети
	* @param string $ip
	* @param int $netbits
	* @return string
	*/
	public static function broadcast($ip, $netbits) {
		return (long2ip(ip2long(Net::network($ip, $netbits)) | (~(ip2long(Net::netmask($netbits))))));
	}
	
	/**
	* Возвращает обратную маску от маски сети
	* @param int $netbits
	* @return string
	*/
	public static function inverse($netbits) {
		return (long2ip(~(ip2long("255.255.255.255") << (32-$netbits))));
	}
	
	
		
	/**
	* Возвращает разряд маски
	* @param string $netmask
	* @return string
	*/
	public static function netmaskSimple($netmask) {
		$count = 0;
		$numbers = explode('.',$netmask);
		for($i=0; $i<4; $i++){
			$code = Net::getBinaryCode($numbers[$i]);
			$count += substr_count($code,'1');
		}
		return $count;
	}
	
	/**
	* Возвращает адрес сети
	* @param string $ip
	* @param string $netmask
	* @return string
	*/
	public static function networkSimple($ip, $netmask) {
		return (long2ip((ip2long($ip)) & (ip2long($netmask))));
	}
	
	/**
	* Возвращает двоичный код числа
	* @param int $number
	* @return string
	*/
	public static function getBinaryCode($number){
		$binary_code = '';
		while($number != 0){
			if($number % 2 != 0) $binary_code .= '1';
			else $binary_code .= '0';
			$number = $number/2;
		}
		
		return strrev($binary_code);
	}
	
	/**
	 * Определяет маску сети по диапазону IP
	 *
	 * @param string $ip_start
	 * @param string $ip_end
	 * @return int
	 */
	public static function ip2netmask($ip_start, $ip_end) {
		if(long2ip(ip2long($ip_start))!= $ip_start or long2ip(ip2long($ip_end))!=$ip_end) return NULL;
		$ipl_start=(int)ip2long($ip_start);
		$ipl_end=(int)ip2long($ip_end);
		if($ipl_start>0 && $ipl_end<0) $delta=($ipl_end+4294967296)-$ipl_start;
		else $delta=$ipl_end-$ipl_start;
		$netmask=str_pad(decbin($delta),32,"0","STR_PAD_LEFT");
		if(ip2long($ip_start)==0 && substr_count($netmask,"1")==32) return "0.0.0.0/0";
		if($delta<0 or ($delta>0 && $delta%2==0)) return NULL;
		for($mask=0;$mask<32;$mask++) if($netmask[$mask]==1) break;
		if(substr_count($netmask,"0")!=$mask) return NULL;
		return $mask;
	}

	/**
	 * Определение вхождения IP адреса в указанную сеть
	 *
	 * @param string $ip
	 * @param string $network
	 * @return bool
	 */
	public static function isIpInNetwork($ip, $network) {
		list($range, $netmask) = explode('/', $network, 2);
		
		$x = explode('.', $range);
		while(count($x)<4) $x[] = '0';
		list($a,$b,$c,$d) = $x;
		
		$range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
		$range_dec = ip2long($range);
		$ip_dec = ip2long($ip);
		
		$wildcard_dec = pow(2, (32-$netmask)) - 1;
		$netmask_dec = ~ $wildcard_dec;
		
		return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
	}
	
	
	/**
	 * Уделение маршрутов для указаного 
	 * ip адресса 
	 *
	 * @param string $ip
	 * @return bool
	 */
	public static function cleanRoute($ip) {
		$count = `/sbin/route -n | grep -c $ip`; 
		for($i = 0; $i < $count; $i++) {
			`/sbin/route del $ip`;
		}
	}
	

	/**
	 * Функция возврашает список всех ip адресов на 
	 * каких розположен сайт
	 *
	 * @param string $url
	 * @return array
	 */
	public static function getSiteIp($url) {
		$result = array();
		$list = (is_array($url)) ? $url: array($url);
		reset($list);
		while (list(, $url) = each($list)) {
			
			$url = str_replace(array('http://', 'www'), '', $url);
			if(strpos($url, '/')) {
				$url = substr($url, 0, (strpos($url, '/') - 1));
			}
			
			$data = shell_exec("dig $url a +short");
			preg_match_all("/^\d+\.\d+\.\d+\.\d+$/ismU", $data, $matches);
			$result = array_merge($result, $matches[0]);
		}
		return $result;
	}
	

	
}

?>