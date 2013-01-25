<?php 
/**
* ������� ������ � ������ �����������
* @package Pilot
* @subpackage Search
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* ����� ������
* @package Search
* @subpackage Libraries
*/
class SearchFinder {
	
	/**
	* �����, ������� ���������� ������������
	* @var string
	*/
	private $search_words = '';
	
	/**
	* REGEXP ����, ������� ���������� ������������
	* @var string
	*/
	private $higliht_regexp = '';
	
	/**
	* id ������
	* @var int
	*/
	private $id = 0;
	
	/**
	* ���������� ������
	* @var array
	*/
	public $result = array();
	
	/**
	* ���������� ��������� �������
	* @var int
	*/
	public $total_rows = 0;
	
	/**
	* ��������� �� ������
	* @var string
	*/
	private $error = '';
	
	/**
	* ����������� ������
	* @access private
	* @param int $id
	* @return object
	*/
	public function __construct($id) {
		$this->id = $id;
		$this->getQueryData();
		$this->getResultData();
	}
	
	/**
	* ���������� �����, �� ������� ������������ �����
	* @param void
	* @return void
	*/
	private function getQueryData () {
		global $DB;
		
		$query = "
			SELECT search_string, declined_words 
			FROM search_cache_query
			WHERE id=".$this->id;
		$search_data = $DB->query_row($query, false);
		$this->search_words = $search_data['search_string'];
		
		$higliht_words = preg_split("/\W/", $search_data['declined_words'], 100, PREG_SPLIT_NO_EMPTY);
		$this->higliht_regexp = "/((?:(?<=[\W])".implode("[\w]*)|(?:(?<=[\W])", $higliht_words)."[\w]*))/i";
	}
	
	/**
	* ��������� ���� � ����������� ������ � ������� ������
	* @param string $content
	* @return string
	*/
	private function higliht ($content) {
		/*
		* ������������ �����
		*/
		$content = preg_replace($this->higliht_regexp, "<b>\\1</b>", $content);
		
		/**
		* �������� ������
		*/
		preg_match("/(?:^|\s).{0,75}<b>[^<]+<\/b>.{0,75}(?:\s|$)/ism", $content, $matches);
		return (isset($matches[0])) ? $matches[0].'</b>' : Misc::word_wrapper($content, 150);
	}
	
	/**
	* ������ �� ����� ���� �����������, � ��������� ���������� ������
	* @param void
	* @return array
	*/
	public function getResultData () {
		global $DB;
		
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				IF (tb_content.title = '', '[ �������� ��� �������� ]', tb_content.title) AS title,
				tb_content.description,
				tb_content.content,
				CONCAT(tb_site.url, tb_page.url) AS url,
				tb_result.strict AS strict
			FROM search_cache_result AS tb_result
			INNER JOIN search_page AS tb_page ON tb_page.id=tb_result.page_id
			INNER JOIN search_site AS tb_site ON tb_site.id=tb_page.site_id
			INNER JOIN search_index AS tb_content ON tb_content.page_id=tb_page.id
			WHERE tb_result.query_id='".$this->id."'
			ORDER BY 
				tb_result.strict ASC,
				tb_result.relevance DESC
			LIMIT ".PAGE_START.", ".SEARCH_RESULTS_PER_PAGE."
		";
		$this->result = $DB->query($query);
		
		$this->total_rows = $DB->result("SELECT FOUND_ROWS()");
		
		reset($this->result);
		while (list($index,) = each($this->result)) {
			$this->result[$index]['title'] = $this->higliht($this->result[$index]['title']);
			$this->result[$index]['content'] = $this->higliht($this->result[$index]['content']);
		}
	}
}
?>