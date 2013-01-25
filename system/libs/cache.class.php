<?php
/** 
 * �����, ���������� �� ����������� ������� 
 * @package Pilot
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 


/**
 * �����, ���������� �� ����������� �������
 * @package Cache
 * @subpackage CMS
 */
class Cache {
	
	/**
	 * �������������� ���������� ���� �������������� �������
	 * @var bool
	 */
	private static $abort = true;
	
	/**
	 * ��� �����, � ������� ����� ������� ��� ������� ��������
	 * @var string
	 */
	public static $file = '';
	
	/**
	 * ������ ������� ����������� ������
	 * @param string $prefix - ���������, ������� ��������� ��� ��� ������ ������ �����
	 * @param int $timelimit - ����� ����� ��������������� �����
	 * @return void
	 */
	public static function start($prefix = 'site_structure', $timelimit = 86400) {

		/**
		 * ���������� ��� ����� � �����
		 */
		$get = array();
		reset($_GET);
		while (list($key, $val) = each($_GET)) {
			if (is_array($val) || $key == '_REWRITE_URL') {
				continue;
			}
			$get[$key] = $key.'='.$val;
		}
		ksort($get);
		if (empty($get)) {
			$get[] = 'index';
		}
		
		self::$file = CACHE_ROOT.$prefix.'/'.LANGUAGE_CURRENT.'/'.strtolower($_GET['_REWRITE_URL']).implode('&', $get).'.html';
		
		// ����������, �� ������� �� ����� ����� ��������������� �����
		$stat['mtime'] = time();
		if (is_file(self::$file)) {
			$stat = stat(self::$file);
		}
		
		/**
		 * ���� ���� �������������� ��������, �� ��������� ��
		 */
		if (
			is_file(self::$file) 
			&& !isset($_GET['nocache'])
			&& $stat['mtime'] > time() - $timelimit
		) {
			echo mod_deflate(file_get_contents(self::$file));
			exit;
		} else {
			self::$abort = false;
		}
		
	}
	
	/**
	 * �������������� ����������� ��������
	 * @param void
	 * @return void
	 */
	public static function abort() {
		self::$abort = true;
	}
	
	/**
	 * ���������� �������� � ����� � �����
	 * @param string $content
	 * @return void
	 */
	public static function save($content) {
		if (self::$abort == false && !defined('ERROR_OCCUR') && !empty(self::$file)) {
			if (!is_dir(dirname(self::$file))) {
				mkdir(dirname(self::$file), 0777, true);
			}
			
			file_put_contents(self::$file, $content);	
		}
	}
	
	
}
?>