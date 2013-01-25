<?php
/**
 * Класс для получения данных о сайте в базе Google Safe Browsing
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class GoogleSafeBrowsing {
	
	protected $api_key = '';
	protected $bases_root = '';
	
	/**
	 * Контент баз для проверки по нему
	 * @var array
	 */
	protected $base_content = array();
	
	/**
	 * Конструктор класса
	 *
	 * @param string $api_key
	 * @param string $bases_root
	 */
	public function __construct($api_key, $bases_root) {
		$this->bases_root = preg_replace('~/$~', '', $bases_root).'/';
		$this->api_key = $api_key;
	}
	
	/**
	 * Поиск сайта по базам
	 *
	 * @param string $host
	 * @return array
	 */
	public function getSiteWarnings($host) {
		
		$warnings = array();
		
		while (true) {
			if ($this->rawCheck('black', $host.'/')) {
				$warnings['black'] = 'black';
			}
			if ($this->rawCheck('malware', $host.'/')) {
				$warnings['malware'] = 'malware';
			}
			
			$host = preg_replace('~^[^.]+\.~', '', $host);
			if (!strstr($host, '.')) {
				break;
			}
		}
		
		return $warnings;
	}
	
	/**
	 * Поиск строки по базе
	 *
	 * @param string $list
	 * @param string $string
	 * @return bool
	 */
	protected function rawCheck($list, $string) {
//		if (!isset($this->base_content[$list])) {
//			$this->base_content[$list] = file_get_contents($this->bases_root."$list.hash");
//		}
	
		$hash_file = $this->bases_root."$list.hash";
		exec("/bin/grep -m 1 ".md5($string)." $hash_file", $out, $return);

//		if (preg_match('~^'.md5($string).'\x09?$~m', $this->base_content[$list])) {
		if ($return == 0) {
			// строка найдена
			return true;
		} else {
			// строка не найдена
			return false;
		}
	}
	
	/**
	 * Выполняет обновление локальных баз заблокированных сайтов
	 * @return void
	 */
	public function update($force_full_download = false) {
		$version_file = $this->bases_root.'version.txt';
		if (file_exists($version_file) && !$force_full_download) {
			$versions = unserialize(file_get_contents($version_file));
			$this->log("Versions loaded: ".implode(', ',$versions));
		} else {
			$versions = array('black' => -1, 'malware' => -1);
			$this->log("Initial versions");
		}
		
		/**
		 * 1) Импорт черного списка сайтов (фишинг)
		 */
		$this->log("Get black list...");
		$hash = @file_get_contents("http://sb.google.com/safebrowsing/update?client=api&apikey={$this->api_key}&version=goog-black-hash:1:$versions[black]");
		if ($hash === false) {
			$this->log("Unable to get black-list");
		} elseif (empty($hash)) {
			$this->log("No changes since last version");
		} else {
			$this->log("Importing black list...");
			$tmp_file = tempnam(TMP_ROOT, 'GoogleSafeBrowsing');
			file_put_contents($tmp_file, $hash);
			unset($hash);
			$versions['black'] = $this->import('black', $tmp_file);
			@unlink($tmp_file);
		}
		
		/**
		 * 2) Импорт malware сайтов
		 */
		$this->log("Get malware list...");
		$hash = file_get_contents("http://sb.google.com/safebrowsing/update?client=api&apikey={$this->api_key}&version=goog-malware-hash:1:$versions[malware]");
		if ($hash === false) {
			$this->log("Unable to get malware-list");
		} elseif (empty($hash)) {
			$this->log("No changes since last version");
		} else {
			$this->log("Importing malware list...");
			$tmp_file = tempnam(TMP_ROOT, 'GoogleSafeBrowsing');
			file_put_contents($tmp_file, $hash);
			unset($hash);
			$versions['malware'] = $this->import('malware', $tmp_file);
			@unlink($tmp_file);
		}
		
		/**
		 * 3) Сохраняем версии полученных файлов
		 */
		file_put_contents($version_file, serialize($versions));
	}
	
	/**
	 * Импорт файла с базой подозрительных сайтов
	 *
	 * @param string $list_name
	 * @param string $file
	 * @return int
	 */
	protected function import($list_name, $file) {
		$hash_file = $this->bases_root."$list_name.hash";
		
		/**
		 * Получаем первую строку для анализа версии файла
		 */
		$f = fopen($file, 'r');
		$first_line = fgets($f);
		fclose($f);
		
		if (!preg_match("~\[goog-$list_name-hash 1\.([0-9]+)(\s+update)?\]~", $first_line, $match)) {
			$this->log("Line 0 of $list_name doesn't contain version info");
			return false;
		} else {
			$version = $match[1];
			$update = (isset($match[2]) && trim($match[2]) == 'update');
		}
		
		if (!$update) {
			/**
			 * Полная версия файла - просто удаляем лишнее и сохраняем весь результат в файл
			 */
			
			passthru("/bin/cat $file | /bin/sed -e '1d' -e 's/\+//g' > $hash_file");
			
		} else {
			/**
			 * Обновление для текущего файла
			 */
			
			$lines = file($file);
			unset($lines[0]);
			
			$delete = array();
			
			reset($lines);
			while (list(,$row) = each($lines)) {
				if (preg_match('~^([+-])([a-z0-9]{32})$~', $row, $match)) {
					if ($match[1] == '+') {
						passthru("/bin/echo \"$match[2]\" >> $hash_file");
						echo "/bin/echo \"$match[2]\" >> $hash_file";
					} elseif ($match[1] == '-') {
						$delete[] = $match[2];
						
						if (count($delete) > 15) {
							$command = "";
							foreach ($delete as $delete_line) {
								$command .= "-e '/$delete_line/d' ";
							}
							
							passthru("/bin/cat $hash_file | /bin/sed $command > $hash_file.new");
							echo "/bin/cat $hash_file | /bin/sed $command > $hash_file.new\n";
							rename("$hash_file.new", $hash_file);
							$delete = array();
						}
					}
				}
			}
			
			if (count($delete) > 0) {
				$command = "";
				foreach ($delete as $delete_line) {
					$command .= "-e '/$delete_line/d' ";
				}
				
				passthru("/bin/cat $hash_file | /bin/sed $command > $hash_file.new");
				echo "/bin/cat $hash_file | /bin/sed $command > $hash_file.new\n";
				rename("$hash_file.new", $hash_file);
				$delete = array();
			}
		}
		
		return $version;
	}
	
	/**
	 * Вывод отладочной информации
	 * @param string $message
	 */
	protected function log($message) {
		if (defined('STDIN')) {
			echo "[i] $message\n";
		} elseif (IS_DEVELOPER) {
			x($message);
		}
	}
	
}
