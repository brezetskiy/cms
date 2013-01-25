<?php
/**
 * Работа с курсами валют
 * @package Pilot
 * @subpackage Currency
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */
class Currency {
	
	
	/**
	 * Курс валют в определенный период времени
	 *
	 * @param int $from_id
	 * @param int $to_id
	 * @param int $tstamp
	 * @return float
	 */
	public static function getRate($from_id, $to_id, $tstamp) {
		global $DB;
		
		if ($from_id == $to_id) return 1;
		
		return $DB->result("
			SELECT rate
			FROM currency_rate
			WHERE dtime <= from_unixtime('$tstamp')
				and currency_from_id='$from_id'
				and currency_to_id='$to_id'
			ORDER BY dtime desc
			LIMIT 1
		");
	}
	
	
	/**
	 * Курсы валют
	 *
	 * @param array $data
	 * @return array
	 */
	public static function getRatesBy($data) {
		global $DB;
		
		$query = "
			SELECT 
				tb_rate.currency_from_id, 
				tb_rate.currency_to_id, 
				tb_rate.rate, 
				tb_rate.dtime,
				tb_list.symbol,
				tb_list.code
			FROM currency_rate_current as tb_rate
			INNER JOIN currency_list as tb_list ON tb_list.id = tb_rate.currency_to_id
			WHERE 1<>1 
		";
		
		for($i=0; $i<count($data); $i++){
			$query .= " OR (currency_from_id='".$data[$i]['from_id']."' and  currency_to_id='".$data[$i]['to_id']."')";
		}
		
		$query .= "	ORDER BY currency_to_id asc";
		return $DB->query($query);
	}
 
	
	/**
	 * Текущий курс валют 
	 *
	 * @param int $from_id
	 * @param int $to_id
	 * @return float
	 */
	public static function getRateCurrent($from_id, $to_id) {
		global $DB;
		
		if ($from_id == $to_id) return 1;
		
		return $DB->result(" 
			SELECT tb_rate.rate
			FROM currency_rate_current as tb_rate
			INNER JOIN currency_list as tb_list ON tb_list.id = tb_rate.currency_to_id
			WHERE (currency_from_id='$from_id' and  currency_to_id='$to_id')
			ORDER BY currency_to_id asc
		");
	}
	
	
	/**
	 * Список включенных валют
	 *
	 * @return array
	 */
	public static function getList($exceptions = array()){
		global $DB;
		
		return $DB->query("
			SELECT 
				tb_list.id, 
				tb_list.code, 
				tb_list.symbol,
				tb_rate.rate 
			FROM currency_list as tb_list
			INNER JOIN currency_rate_current as tb_rate ON tb_list.id = tb_rate.currency_from_id 
				AND tb_rate.currency_to_id = '980'
			WHERE tb_list.active = 'true'
			".((!empty($exceptions)) ? "AND tb_list.id NOT IN (".implode(',', $exceptions).")" : "")."
		", 'id'); 
	}
	
	
	/**
	 * Возвращает код валюты по id
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getCurrency($id = 0){
		global $DB;
		
		if(empty($id)) $id = (!empty($_SESSION['currency_current'])) ? $_SESSION['currency_current'] : CURRENCY_DEFAULT;
		
		$result = $DB->query_row("SELECT * FROM currency_list WHERE id = '$id'"); 
		$result['rate'] = (CURRENCY_DEFAULT != $id) ? self::getRateCurrent(CURRENCY_DEFAULT, $id) : 1;
		return $result; 
	}
	
	
	/**
	 * Конвертирование значения в соответствии с переданным курсом
	 *
	 * @param float $value
	 * @param float $rate
	 * @return float
	 */
	public static function convert($value, $rate){
		return number_format(round($value*$rate, 2), 2, '.', ''); 
	}
	
	
	/**
	 * Возвращает глобальную форму смены валют
	 *
	 * @return string
	 */
	public static function getCurrencyGlobalForm(){
		global $DB;
		
		$TmplCurrencyForm = new Template('currency/form_global');
		
		$currency_list = explode(",", CURRENCY_LIST); 
		$currency_list_id = $DB->fetch_column("SELECT code, id FROM currency_list WHERE code IN ('".implode("','", $currency_list)."')", 'code', 'id');
		
		$data  = array();
		$rates = array();
		
		reset($currency_list);
		while(list(, $currency) = each($currency_list)){
			if(empty($currency_list_id[$currency])) continue;
			$data[] = array('from_id' => CURRENCY_DEFAULT, 'to_id' => $currency_list_id[$currency]);
		}
		
		$rates = Currency::getRatesBy($data);
		$currency_default = self::getCurrency(CURRENCY_DEFAULT);
		array_unshift($rates, array('currency_from_id' => CURRENCY_DEFAULT, 'currency_to_id' => CURRENCY_DEFAULT, 'code' => $currency_default['code'], 'rate' => 1, 'symbol' => $currency_default['symbol']));
		
		reset($rates);
		while(list(, $rate) = each($rates)){ 
			$TmplCurrencyForm->iterate("/rates/", null, $rate); 
		}
		
		return $TmplCurrencyForm->display();
	}
	
		
	/**
	 * Возвращает локальную форму смены валют
	 *
	 * @return string
	 */
	public static function getCurrencyLocalForm(){
		global $DB;
		
		$TmplCurrencyForm = new Template('currency/form_local');
		
		$currency_default = self::getCurrency(CURRENCY_DEFAULT);
		$currency_list[$currency_default['code']] = CURRENCY_DEFAULT;
		
		$currency_list_alt = $DB->fetch_column("SELECT code, id FROM currency_list WHERE code IN ('".implode("','", explode(",", CURRENCY_LIST))."') ORDER BY id asc", 'code', 'id');
		$currency_list = array_merge($currency_list, $currency_list_alt);
		
		reset($currency_list);
		while(list($code, $id) = each($currency_list)){ 
			$class = (!empty($_SESSION['currency_current']) && $_SESSION['currency_current'] == $id) ? 'switched' : '';
			$TmplCurrencyForm->iterate("/list/", null, array('id' => $id, 'code' => $code, 'class' => $class)); 
		}
		
		return $TmplCurrencyForm->display();
	}
}

?>