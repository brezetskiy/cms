<?php
/**
 * Класс клиента Sphinx Search
 * 
 * @package Pilot
 * @subpackage Search
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class SearchClient {
	
	protected $max_url_length = null;
	protected $excerpt_options = array();
	
	public $results = array();
	public $status = 'ok';
	public $error = '';
	
	protected $query = '';
	
	public function __construct() {}
	
	public function search($index, $query, $rows_per_page, $offset) {
		$Download = new Download();
		$Download->setOption('FOLLOWLOCATION', false);
		
		if (extension_loaded('simlexml')) {
			trigger_error('PHP must be compiled with SimpleXML support', E_USER_ERROR);
		}
		
		$this->query = $query;
		
		$data = array(
			'index' => $index,
			'query' => $query,
			'rows_per_page' => $rows_per_page,
			'offset' => $offset,
			'excerpt_options' => $this->excerpt_options
		);
		
		if ($this->max_url_length > 0) {
			$data['max_url_length'] = $this->max_url_length;
		}
		
		$result = $Download->post('http://search.delta-x.ua/', $data);
		
//		x($result);
		
		$this->results = simplexml_load_string($result);
		
		if (!$this->results) {
			$this->status = 'error';
			$this->error = 'Ошибка в формате данных';
			$this->results = array();
		} elseif (isset($this->results->error)) {
			$this->status = 'error';
			$this->error = $this->results->error;
			$this->results = array();
		}
		
		foreach ($this->results as $result) {
			$result->title = iconv('utf-8', 'cp1251', $result->title);
			$result->content = iconv('utf-8', 'cp1251', $result->content);
			$result->url = iconv('utf-8', 'cp1251', $result->url);
			$result->short_url = iconv('utf-8', 'cp1251', $result->short_url);
//			reset($result);
//			while (list($key,$value) = each($result)) {
//				$this->results[$rkey]->$key = iconv('utf-8', 'cp1251', $value);
//			}
		}
	}
	
	public function saveQueryStat() {
		global $DB;
		
		if ($this->status != 'ok') {
			return;
		}
		
		$query = "
			insert into search_query_stat
			set
				last_dtime = now(),
				query = '".htmlspecialchars($DB->escape($this->query))."',
				results = '".(int)$this->results->info->count."',
				query_time = '".number_format($this->results->info->time, 2, '.', '')."',
				count = 1
			on duplicate key update
				last_dtime = now(),
				count = count+1,
				results = '".(int)$this->results->info->count."'
		";
		$DB->insert($query);
		
		if (rand(0,100)>80) {
			$DB->delete("delete from search_query_stat where last_dtime < now() - interval ".SEARCH_QUERY_STAT_LIFETIME." second");
		}
	}
	
	public function setMaxUrlLength($value) {
		$this->max_url_length = (int)$value;
	}
	
	public function setExcerptOptions($options) {
		$this->excerpt_options = $options;
	}
	
}


?>