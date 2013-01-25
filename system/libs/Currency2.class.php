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
	static function getRate($from_id, $to_id, $tstamp) {
		global $DB;
		
		if ($from_id == $to_id) {
			return 1;
		}
		
		$query = "
			SELECT rate
			FROM currency_rate
			WHERE 
				dtime <= from_unixtime('$tstamp')
				and currency_from_id='$from_id'
				and currency_to_id='$to_id'
			ORDER BY dtime desc
			LIMIT 1
		";
		return $DB->result($query);
	}
	
	
	/**
	 * Курсы валют
	 *
	 * @param array $data
	 * @return array
	 */
	static function getRatesBy($data) {
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
			WHERE 1!=1 
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
	static function getRateCurrent($from_id, $to_id) {
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
	static function getList($exceptions = array()){
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
	 * Возвращает код валюты по ID
	 *
	 * @param int $id
	 * @return string
	 */
	static public function getCode($id){
		global $DB;
		
		return $DB->result("SELECT code FROM currency_list WHERE id = '$id'");
	}
	
	
	/**
	 * Форма смены валют
	 *
	 * @param bool $show_only_value
	 * @param string $trigger_before
	 * @param string $trigger_after
	 * @return string
	 */
	static public function getForm($show_only_value = false, $trigger_before = '', $trigger_after = ''){
		$enabled = explode(',', CURRENCY_ENABLE);
		$rates_params = array();
		
		for($i=0; $i < count($enabled); $i++){
			for($j=0; $j < count($enabled); $j++){
				if($enabled[$i] == $enabled[$j]) continue;  
				$rates_params[] = array('from_id' => $enabled[$i], 'to_id' => $enabled[$j]);
			}
		}
			 
		$rates = array();
		$rates_list = Currency::getRatesBy($rates_params); 
		
		for($i=0; $i < count($rates_list); $i++){
			$rates[$rates_list[$i]['currency_to_id']][$rates_list[$i]['currency_from_id']] = $rates_list[$i]['rate'];
			$rates[$rates_list[$i]['currency_to_id']]['symbol'] = @iconv('windows-1251', 'utf-8', $rates_list[$i]['symbol']);
			$rates[$rates_list[$i]['currency_to_id']]['code'] = $rates_list[$i]['code'];
		}
		 
		$rates_sorted = array(); 
		 
		reset($enabled);
		while(list(, $currency_id) = each($enabled)){
			$rates_sorted[$currency_id] = $rates[$currency_id];
		}
		
		$TmplCurrencyForm = new Template("currency/form");
		$TmplCurrencyForm->setGlobal('currency_rates', json_encode($rates_sorted));
		$TmplCurrencyForm->setGlobal('currency_current', globalVar($_COOKIE['currency_current'], 980));
		$TmplCurrencyForm->setGlobal('trigger_before', $trigger_before);
		$TmplCurrencyForm->setGlobal('trigger_after', $trigger_after);
		$TmplCurrencyForm->setGlobal('only_value', (!empty($show_only_value)) ? 1 : 0);
		
		reset($rates_sorted);
		while(list($currency_to, $row) = each($rates_sorted)){
			$TmplCurrencyForm->iterate('/currencies/', null, array('id' => $currency_to, 'code' => $row['code']));
		}
		
		return $TmplCurrencyForm->display();
	}

}

?>