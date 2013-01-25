<?php
/**
 * Класс по работе с коммандами Shell
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */


/**
 * Класс по работе с коммандами Shell
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */
class Shell {
	
		
	/**
	 * Устанавливает флаг, который говорит о том, что надо выполнить определённый shell
	 * скрипт. В скрипте необходимо выполнить Shell::checkQueue для того, что б проверить
	 * стоит ли он в очереди на запуск.
	 *
	 * @param string $flag
	 */
	static function addQueue($flag) {
		touch(RUN_ROOT.$flag.'.queue');
	}
	
	/**
	 * Проверяет, должен ли запускаться скрипт или нет, если нет, то выходит
	 * Вызов функции должен происходить после Shell::collision_catcher
	 *
	 * @param string $flag
	 * @param string $reload argv[1]
	 */
	static function checkQueue($flag, $reload = false) {
		if (is_file(RUN_ROOT.$flag.'.queue')) {
			unlink(RUN_ROOT.$flag.'.queue');
		} elseif ($reload) {
			echo "[i] Reloading\n";
		} else {
			echo "[i] No queued\n";
			exit;
		}
	}
	
	
	/**
	* Перекодировка сообщений с html в ssh синтаксис
	* @var array
	*/
	static public $html2ssh = array(
		"<b>" => "\033[1m",	"</b>" => "\033[0m",
		"<u>" => "\033[4m", "</u>" => "\033[0m",
		"<p>" => "\n", "</p>" => "\n",
		"<br>" => "\n"
	);
	
	/**
	* Открывает процес для работы с внешними процессами с возможностью 
	* отсылать данные на STDIN
	* @param string $stdin - STDIN
	* @param string $error - Error
	* @return string
	*/
	static public function exec_stdin($command, &$stdin, &$error) {
		$stdout = '';
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		$process = proc_open($command, $descriptorspec, $pipes);
		if (is_resource($process)) {
			
			fwrite($pipes[0], $stdin);
			fclose($pipes[0]);
			
			while(!feof($pipes[1])) {
				$stdout .= fgets($pipes[1], 1024);
			}
			fclose($pipes[1]);
			
			while(!feof($pipes[2])) {
				$error .= fgets($pipes[2], 1024);
			}
			fclose($pipes[2]);
					
			/**
			* Перед закрытием процесса, обязательно необходимо закрыть 
			* все файлы этого процесса
			*/
			proc_close($process);
		} else {
			trigger_error(cms_message('CMS', 'Не удается открыть соединение с shell скриптом'), E_USER_WARNING);
		}
		return $stdout;
	}
	
	/**
	* Блокировка параллельного запуска скриптов
	* @param string $uniq_id - флаг, который будет блокировать одновременный запуск любого из скриптов, имеющих такой же флаг в colission_catcher
	* @return void
	*/
	static public function collision_catcher($uniq_id = '', $max_execution_time = false) {
		$sem_id = sem_get(ftok(__FILE__, 'x')); // создаем семафор
		sem_acquire($sem_id); // блокируем выполнение этой части кода другими скриптами
		$my_pid = getmypid();
		$debug  = debug_backtrace();
		$debug  = array_pop($debug);
		$script_name = $debug['file'];
		$status = 'success';
		
		$proc_file = (empty($uniq_id)) ?
			RUN_ROOT . basename($debug['file'], '.php').'_'.abs(crc32(dirname($debug['file']))) . '.pid':
			RUN_ROOT . $uniq_id.'.pid';
		unset($debug);
		
		if (DEBUG) {
			$log = fopen(LOGS_ROOT.'crontab.log', 'a+');
			flock($log, LOCK_EX);
		}
		
		if (is_file($proc_file)) {
			if (!is_readable($proc_file)) {
				$status = 'failed';
				echo "[e] Unable to read file $proc_file check file permissions\n";
				exit;
			}
			
			// Проверяем не нами ли он запущен, если нами, то обновляем время
			$file_pid = file_get_contents($proc_file);
			if ($file_pid == $my_pid) {
				touch($proc_file);
			}
			
			// Проверяем остался ли этот процесс или скрипт был прерван
			$ps = `ps p $file_pid -o etime`;
			$ps = preg_split("/[\n\r]+/", $ps, -1, PREG_SPLIT_NO_EMPTY);
			$time = 0;
			if (count($ps) > 1) {
				
				// определяем сколько секунд работает скрипт
				$etime = preg_split("/[^\d]+/", trim($ps[1]), -1, PREG_SPLIT_NO_EMPTY);
				$etime = array_reverse($etime, false);
				reset($etime);
				while(list($index,$row) = each($etime)) {
					if ($index == 0) {
						$time = $row;
					} elseif ($index == 1) {
						$time += $row * 60;
					} elseif ($index == 2) {
						$time += $row * 3600;
					} elseif ($index == 3) {
						$time += $row * 86400;
					}
				}
				
				if (
					$max_execution_time === true || // второй параметр скрипта это bool
					(!is_bool($max_execution_time) && $time > $max_execution_time) // второй параметр скрипта это число секунд
				) {
					echo "[w] Killing process $file_pid\n";
					echo `/bin/kill -9 $file_pid`; 
					if (DEBUG) fwrite($log, date('Y-m-d H:i:s')." $script_name [kill PID:$file_pid] (".$ps[1].")\n");
				} else {
					$status = 'blocked';
					echo "[e] Concurrent script execution blocked. PID: $file_pid. $time seconds (max: $max_execution_time seconds)\n";
					if (DEBUG) {
						fwrite($log, date('Y-m-d H:i:s')." $script_name [collision PID:$file_pid] (".$ps[1].")\n");
						fclose($log);
					}
					exit;  
				} 
			}
		}
		
		file_put_contents($proc_file, $my_pid);
		if (DEBUG) {
			fwrite($log, date('Y-m-d H:i:s')." $script_name [ok, pid:$my_pid]\n");
			fclose($log); // снимает так же блокировку с файла
		}
		sem_release($sem_id);
		
		register_shutdown_function('unlink', $proc_file);
		
		if (DEBUG) {
			register_shutdown_function(array(new Shell, 'dbLog'), str_replace(SITE_ROOT, '', $script_name), date('Y-m-d H:i:s'), $status); 
		}
	}
	
	/**
	 * Сохранение информации о выполенном скрипте в базе
	 * @param string $url
	 * @param string $start_date
	 */
	static function dbLog($url, $start_date, $status = 'failed'){ 
		global $DB;
		
		if (!DEBUG) return;
		
		$end_date = date('Y-m-d H:i:s');
		$DB->insert("
			INSERT INTO cms_crontab SET url = '$url', start_dtime = '$start_date', end_dtime = '$end_date', status = '$status'
			ON DUPLICATE KEY UPDATE start_dtime=VALUES(start_dtime), end_dtime=VALUES(end_dtime), status=VALUES(status)
		");
		$id = $DB->result("select id from cms_crontab where url='$url'");
		
		$DB->insert("INSERT INTO cms_crontab_history SET crontab_id = '$id', start_dtime = '$start_date', end_dtime = '$end_date', status = '$status'");
		if(rand(0, 100) > 90) $DB->delete("DELETE FROM cms_crontab WHERE start_dtime < NOW() - INTERVAL 7 DAY"); 
		if(rand(0, 100) > 90) $DB->delete("DELETE FROM cms_crontab_history WHERE start_dtime < NOW() - INTERVAL 7 DAY");
	}
	

	
	/**
	 * Подсветка текста в shell, и перекодировка русских букв в транслит
	 * Функция не поддерживает вложенные теги b и u и работает исключительно с тегами в нижнем регистре
	 * 
	 * @param string $str
	 * @return string
	 */
	static public function html($str) {
		if (!defined('STDIN')) {
			// Скрипт запущен не с консоли
			return $str;
		}
		return strtr(Charset::translit($str), Shell::$html2ssh);
	}
	
	/**
	 * Возвращает имя команды по PID процесса
	 * @param int $pid
	 * @return string
	 */
	static function getProcessCmdline($pid) {
		return preg_replace('~\x0~', ' ', trim(file_get_contents("/proc/$pid/cmdline")));
	}
	
	/**
	 * Ожидание до освобождения семафора
	 */
	static function waitSemaphore($short_name) {
		
		$command = self::getProcessCmdline(posix_getpid());
		
		/**
		 * Формируем int номер семафора
		 */
		$sem_token = '';
		for ($i=0; $i<strlen($short_name); $i++) {
			$sem_token .= ord(substr($short_name, $i, 1));
		}
		$sem_token = abs((int)$sem_token);
		
		/**
		 * Получаем семафор
		 */
		$sem_id = sem_get($sem_token);
		if (!$sem_id) {
			exec("logger [SEM-SKIP] $short_name: in $command - unable to get semaphore $sem_token"); 
			return;
		}
		
		/**
		 * Ожидаем своей очереди
		 */
		$start = microtime(true);
		exec("logger [SEM-ACQUIRING] $short_name: in $command");
		sem_acquire($sem_id);
		$sem_wait_time = number_format(microtime(true)-$start, 4, '.', '');
		exec("logger [SEM-WAITTIME] $short_name $sem_wait_time in $command");
		register_shutdown_function('sem_release', $sem_id);
	}
	
	/**
	 * Функция для определения того, работает ли процесс с указанным PID (и опционально - именем процесса)
	 * @param int $pid
	 * @param string $command_part
	 * @return bool
	 */
	static public function isProcessRunning($pid, $command_part = '') {
		$list = trim(`ps -p $pid -o comm=`);
		if (empty($list)) {
			return false;
		} elseif (!empty($command_part) && !preg_match('~'.$command_part.'~', $list)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Запуск простых команд с указанным таймаутом.
	 * Поддерживается возврат вывода, не поддерживается возврат return code.
	 * При успешном выполнении команды возвращает true и результат в переменной $output
	 *
	 * @param string $command
	 * @param int $timeout
	 * @param string $output
	 * @return bool
	 */
	static public function runCommandWithTimeout($command, $timeout, &$output) {
		$tmp_file = tempnam('/tmp', 'delta-cmd-run');
		exec("nohup $command > $tmp_file 2>&1 & echo $!", $out);
		$pid = trim(implode('', $out));
		
		$step = 100000;
		$timeout = $timeout*1000000;
		$return = true;
		usleep($step);
		while (Shell::isProcessRunning($pid)) {
			$timeout -= $step;
			if ($timeout <= 0) {
				if ($pid > 2) {
					`kill -9 $pid`;
				}
				$return = false;
				break;
			}
			usleep(100000);
		}
		
		if ($return) {
			$output = file_get_contents($tmp_file);
			@unlink($tmp_file);
			return true;
		}
		@unlink($tmp_file);
		$output = '';
		return false;
	}
	
	static public function getProcessLimits($pid) {
		$result = array(
			'max cpu time' => 'unknown',              // unlimited            unlimited            seconds
			'max file size' => 'unknown',             // unlimited            unlimited            bytes
			'max data size' => 'unknown',             // unlimited            unlimited            bytes
			'max stack size' => 'unknown',            // 8388608              unlimited            bytes
			'max core file size' => 'unknown',        // 0                    unlimited            bytes
			'max resident set' => 'unknown',          // unlimited            unlimited            bytes
			'max processes' => 'unknown',             // 1024                 63204                processes
			'max open files' => 'unknown',            // 359536               359536               files
			'max locked memory' => 'unknown',         // 65536                65536                bytes
			'max address space' => 'unknown',         // unlimited            unlimited            bytes
			'max file locks' => 'unknown',            // unlimited            unlimited            locks
			'max pending signals' => 'unknown',       // 63204                63204                signals
			'max msgqueue size' => 'unknown',         // 819200               819200               bytes
			'max nice priority' => 'unknown',         // 0                    0
			'max realtime priority' => 'unknown',     // 0                    0
			'max realtime timeout' => 'unknown',      // unlimited            unlimited            us

		);
		if (file_exists("/proc/$pid/limits")) {
			$process_limits = file("/proc/$pid/limits");
			if (is_array($process_limits)) {
				foreach ($process_limits as $line) {
					$name = strtolower(trim(substr($line, 0, 25)));
					$limit = trim(substr($line, 45, 20));
					$result[$name] = $limit;
				}
			}
		}
		
		return $result;
	}
	
	public static function out($message, $bg_color = '') {
		if (preg_match("~\n$~", $message)) {
			$message = preg_replace("~\n$~", '', $message);
			$end = "\n";
		} else {
			$end = "";
		}
		
		if ($bg_color == 'green') {
			$message = "\033[30;42m$message\033[0m";
		} elseif ($bg_color == 'red') {
			$message = "\033[41m$message\033[0m";
		}
		echo $message.$end;
	}
	
	public static function showError($message, $newline = true) {
		self::out("[e] $message".($newline?"\n":''), 'red');
	}
	
	public static function showOk($message, $newline = true) {
		self::out("[i] $message".($newline?"\n":''), 'green');
	}
	
	public static function show($message, $newline = true) {
		self::out("[i] $message".($newline?"\n":''));
	}
}
?>