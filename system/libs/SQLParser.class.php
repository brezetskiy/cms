<?php
/**
 * ����� ��������� � ��������� SQL ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ����� ��������� � ��������� SQL ��������
 * @package CMS
 * @subpackage SQLParser
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
abstract class SQLParser {
	
	/**
	 * ���������� � ��
	 *
	 * @var DB
	 */
	protected $DBServer;
	
	/**
	 * ���������������� ������, ������� ��������� �����. �������� ������ ���������
	 * ��������� ���� ����� ����, ��� ������ ����� ������ ������� execQuery, �� �����
	 * �������� ��������� - ������
	 *
	 * @var string
	 */
	public $debug = '';
	
	/**
	 * ����������� SQL ������
	 *
	 * @var array
	 */
	protected $parsed_sql = array();
	
	/**
	 * ���������� ����� � �������
	 *
	 * @var int
	 */
	public $total_rows = 0;
	
	/**
	 * �������, ������� ������������ � SQL ������� [alias] => table_name
	 *
	 * @var array
	 */
	public $tables = array();
	
	abstract public function __construct(DB $DBServer, $query);
	abstract public function changeOrder($order_fields);
	abstract public function changeCondition($conditions, $having_condition = false);
	abstract public function execQuery($start = null, $offset = null);
	abstract protected function parseSQL($query);
	abstract public function getQuery();
	abstract public function getQueryArray();
	abstract protected function getTables();
	
	/**
	 * ���������� ��� �������, � ������� ������ ������
	 * @return string
	 */
	public function getTableName() {
		$found = preg_match_all("/FROM[\s\n\t\r]+([`a-z0-9_\.]+)/ism", $this->parsed_sql['FROM'], $matches);
		if ($found == 0) {
			// ���������� ���������� ��� �������. ��������� ������������ SQL �������, ����� ���� FROM.
			trigger_error(cms_message('CMS', '���������� ���������� ��� �������. ��������� ������������ SQL �������, ����� ���� FROM.'), E_USER_ERROR);
		}
		
		$table_name = str_replace('`', '', $matches[1][0]);
		unset($matches);
		
		// �������� ��� �� �� ����� �������
		if (strpos($table_name, '.') !== false) {
			return substr($table_name, strpos($table_name, '.') + 1);
		} else {
			return $table_name;
		}
	}
	
	/**
	 * �������� ���� ������������ �������
	 *
	 * @param string $from_language
	 * @param string $to_language
	 * @return void
	 */
	public function changeTableLanguage($from_language, $to_language) {
		reset($this->parsed_sql);
		while (list($key, $val) = each($this->parsed_sql)) {
			$this->parsed_sql[$key] = preg_replace('/_'.$from_language.'(?=\W+)/', '_'.$to_language, $val);
		}
	}
	
	
	/**
	 * ��������� ������� ���������� SQL �������
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	protected function getQuery_callback($a, $b) {
		if ($a == $b) {
			return 0;
		}
		$a = array_search($a, $this->sql_statements);
		$b = array_search($b, $this->sql_statements);
		return ($a < $b) ? -1 : 1;
	}
	
}
?>