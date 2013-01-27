<?php
/**
 * Набор функций, которые нельзя отнести к какому-либо классу
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

// список переменных, которые были обработаны функциями globalVar, globalEnum, globalDate
$get_vars = array();

/**
 * Обрабатывает мета-теги перед их выводом
 *
 * @param string $name
 * @param string $headline
 * @param string $title
 * @param string $desciption
 * @param string $keywords
 * @return array
 */
function parse_headers($name, $headline, $title, $description) {
	
	$headline = (empty($headline) && is_null($headline)) ? $name : $headline;
	$title = (empty($title) && is_null($title)) ? $headline : $title;
	$description = (empty($description) && is_null($description)) ? $title : $description;
	
	// Удаляем теги с мета тегов и заголовка страницы
	$result = array();
	$result['headline'] = str_replace('&nbsp;', '', strip_tags($headline));
	$result['title'] = str_replace('&nbsp;', '', strip_tags($title));
	$result['description'] = str_replace('&nbsp;', '', strip_tags($description));
	
	return $result;
}

/**
 * Проверяет - пересекаются ли заданные интервалы
 *
 * @param int $x_from
 * @param int $x_to
 * @param int $y_from
 * @param int $y_to
 * @return bool
 */
function interval_intersect($x_from, $x_to, $y_from, $y_to) {
	if ($x_from > $x_to || $y_from > $y_to) {
		return false;
	}
	
	if ($x_from <= $y_from                     && $x_to >= $y_from && $x_to <= $y_to) {
		return true;
	} elseif ($x_from >= $y_from && $x_from <= $y_to && $x_to >= $y_from && $x_to <= $y_to) {
		return true;
	} elseif ($x_from >= $y_from && $x_from <= $y_to                     && $x_to >= $y_to) { 
		return true;
	} elseif ($x_from <= $y_from                                         && $x_to >= $y_to) {
		return true;
	} else {
		return false;
	}
}

/**
 * Добавляет параметры в строку запроса
 *
 * @param string $url
 * @param mixed $data
 * @return string
 */
function add_url_param($url, $data) {
	$url .= (strstr($url, '?') === false) ? '?' : '';
	
	reset($data); 
	while (list($param, $value) = each($data)) { 
		if (is_array($value)) {
			reset($value); 
			while (list($key,$val) = each($value)) { 
				 $url .= '&'.urlencode($param).'['.$key.']='.urlencode($val);
			}
		} else {
			$url .= '&'.urlencode($param).'='.urlencode($value);
		}
	}
	return $url;
}

/**
 * Сортирует массив для вывода в виде таблицы с заданным количеством колонок
 *
 * @param array $data
 * @param int $column_count
 * @return array
 */
function columns($data, $column_count) {
	$devider = ceil(count($data) / $column_count);
	$return = array();
	for($i=0;$i<$devider;$i++) {
		for ($step=0; $step < $column_count; $step++) {
			if (!isset($data[$i + $step * $devider])) {
				$return[] = false;
				continue;
			}
			$return[] = $data[$i + $step * $devider];
		}
	}
	return $return;
}

/**
 * Проверяет, установлен и активирован ли модуль
 * Эту функцию необходимо использовать перед выводом специфических для 
 * модуля данных (особенно с обращением к БД и на страницах, которые не относятся к модулю)
 *
 * @param string $module_name
 */
function is_module($module_name) {
	global $DB;
	static $installed_modules = array();
	if (empty($installed_modules)) {
		$installed_modules = $DB->fetch_column("SELECT LOWER(name) FROM cms_module");
	}
	return in_array(strtolower($module_name), $installed_modules);
}

/**
 * Функция, которая удаляет начальные и конечные пробелы в массиве
 * использование array_walk($array, 'array_trim')
 * @param mixed $item
 * @return void
 */
function array_trim(&$item) {
	$item = trim($item);
}
function stripslashes_callback(&$val) {
	if (!is_array($val)) {
		$val = htmlspecialchars(stripslashes($val));
	}
}

/**
 * Добавляет значение в строку запроса, при этом удяля старое значение этого параметра.
 * Если не указывать третий параметр то переменная будет удалена из запроса
 * 
 * @param string $query_string строка запроса, в которой необходимо установить или удалить параметр
 * @param string $name название параметра
 * @param string $value значение параметра, если не указывать, то параметр будет удалён
 * @return string
 */
function set_query_param($query_string, $name, $value = null) {
	
	// Делим строку на части URL и переменные переданные методом GET
	$url = @parse_url($query_string);
	
	if (!isset($url['query'])) {
		$url['query'] = '';
	}
	if (!isset($url['path'])) {
		$url['path'] = '';
	}
	
	/**
	 * ВАЖНО!!! Добавление и удаление параметра происходит через добавление/удаление в $url['query']
	 * значения &$name=$value для того, что б была возможность добавлять многомерные массивы
	 * через эту функцию. Так как если передать в параметре name массив типа a[b][c], то
	 * он будет urlencode т.е. иметь вид a%5Bb%5D%5bc%5D
	 */
	// Удаляем старое значение параметра
	$url['query'] = preg_replace('/'.preg_quote(urlencode($name), '/').'=[^&]*(&|$)/', '', $url['query']);
	
	// Если указан третий параметр, то устанавливаем новое значение
	if (!is_null($value)) {
		$url['query'] .= '&'.$name.'='.urlencode($value);
	}
	
	parse_str($url['query'], $get_variables);
	array_walk_recursive($get_variables, 'stripslashes_callback');
	
	return $url['path'].'?'.http_build_query($get_variables);
}

/**
* В связи с тем, что в Windows команда mkdir не работает рекурсивно с разделителями директорий UNIX
* приходится использовать свой конвертор
* @param string $dir
* @param int $mode
* @param bool $recursive
* @return bool
*/
function makedir($dir, $mode = 0777, $recursive = false) {
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		$dir = str_replace('/', '\\', $dir);
	}
	return mkdir($dir, $mode, $recursive);
}

/**
 * Вывод многоязычных сообщений
 *
 * @param string $message
 */
function cms_message($module_name, $message) { 
	global $DB;
	static $cache;
	
	$module_name = strtolower(trim($module_name));
	$message = trim($message);
	$file = CACHE_ROOT.strtolower("msg_$module_name.".LANGUAGE_CURRENT.'.txt');
	$param = func_get_args();
	unset($param[0]);
	
	// подгружаем сообщения для модуля, если их ещё нет
	if (!isset($cache[$module_name]) && is_file($file)) {
		$cache[$module_name] = unserialize(file_get_contents($file));
	}
	   
	// проверяем, есть ли запрошенное сообщение в кеше
	if (isset($cache[$module_name][md5($message)])) {
		$param[1] = $cache[$module_name][md5($message)];
		return (count($param) == 1) ? $param[1] : call_user_func_array('sprintf', $param);
	}
	
	// Создаём запись о новом сообщении в файле. В базу его не вставляем, так как 
	// таблицы могут быть заблокированы
	$file = CACHE_ROOT.'msg_queue.txt';
	if (!is_file($file)) {
		touch($file); 
		$data = array();
	} else {
		$data = unserialize(file_get_contents($file));
	}
	$data[$module_name][md5($message)] = $message;
	file_put_contents($file, serialize($data));
	
	return (count($param) == 1) ? $message : call_user_func_array('sprintf', $param);
}


/**
* Получение IP адреса клиента
* @param void
* @return string
*/
function getIP() {
	if (isSet($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (isSet($_SERVER['HTTP_CLIENT_IP'])) {
		$realip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$realip = $_SERVER['REMOTE_ADDR'];
	} else {
		// При запуске скриптов из cron'а нет параметров, указывающих IP
		// адрес компа
		$realip = 'shell';
	}
	
	if ($realip == 'unknown') {
		$realip = $_SERVER['REMOTE_ADDR'];
	}
	return $realip;
}

/**
 * Функция, которая принимает дату и время
 * 
 * @param array $date
 * @return int
 */
function globalDate(&$date, $tstamp = -1) {
	if ($tstamp < 0) {
		$tstamp = time();
	}
	if (!isset($date['day']) || !isset($date['month']) || !isset($date['year'])) {
		// Дата не установлена, возвращаем указанное время
		return $tstamp;
	}
	
	if (!isset($date['hour']) || !isset($date['minute'])) {
		// Время не установлено, возвращаем дату + 00:00:00
		$date['hour'] = 0;
		$date['minute'] = 0;
		$date['second'] = 0;
	} elseif (!isset($date['second'])) {
		// не установлены секунды
		$date['second'] = 0;
	}
	
	return mktime((int)$date['hour'],(int)$date['minute'],(int)$date['second'],(int)$date['month'],(int)$date['day'],(int)$date['year']);
}


/**
 * Типизация enum значений
 * 
 * Если $global_var не является не одним из указанных значений из массива $values,
 * то берётся первое значение из этого массива
 *
 * @param mixed $global_var
 * @param array $values
 */
function globalEnum(&$global_var, $values) {
	$global_var = trim($global_var);
	
	if (!isset($global_var) || !in_array($global_var, $values)) {
		// переменная не установлена
		return reset($values);
	} else {
		return $global_var;
	}
}

/**
 * Типизация принятых данных
 *
 * @param string $name
 * @param mixed $global_var
 * @param mixed $default_value
 * @return mixed
 */
function getVar($name, &$global_var, $default_value) {
	global $get_vars;
	
	if (isset($get_vars[$name])) {
		$get_vars[$name] = 1;
	} else {
		$get_vars[$name]++;
	}
	
	return globalVar($global_var, $default_value);
}

/**
* Типизация переменных
* @param mixed $global_var
* @param mixed $default_value
* @param bool $trim
* @return void
*/
function globalVar(&$global_var, $default_value, $trim = true) {
	if (!isset($global_var)) {
		// Переменная не установлена
		return $default_value;
	} elseif (gettype($default_value) == 'integer' && !is_numeric(str_replace(',', '.', trim($global_var)))) {
		// Переменная не является числом
		return $default_value;
	} elseif (gettype($default_value) == 'array' && !is_array($global_var)) {
		// Переменная не является массивом
		return $default_value;
	} elseif (gettype($default_value) == 'string' && !is_string($global_var)) {
		// Переменная не является строкой
		return $default_value;
	} elseif (gettype($default_value) == 'string' && is_string($global_var) && $trim) {
		// Переменная является строкой, убираем лишние пробелы
		return trim($global_var);
	} elseif (gettype($default_value) == 'integer' && (strstr($global_var, '.') || strstr($global_var, ','))) {
		// Переменная является десятичным числом. Число с точкой нормально понимает PHP и MySQL
		// А с запятой MySQL не любит.
		return str_replace(array('.', ','), '.', $global_var);
	} else {
		// Все ок
		return $global_var;
	}
}


/**
* Отладчик
* @param mixed
* @return void
*/
function x($array, $show = false) {
	if (!defined('ERROR_OCCUR')) define('ERROR_OCCUR', 1);
	if (!IS_DEVELOPER && !$show && !DEBUG) return;
	$debug_backtrace = debug_backtrace();
	if (is_array($array)) {
		array_walk_recursive($array, 'set_null');
	}
	if (defined('STDIN')) {
		// Shell output
		echo "\n".Shell::html('<b>'.$debug_backtrace[0]['file'] . ' ('. $debug_backtrace[0]['line'] . ")</b>\n".str_repeat('-', 80)."\n");
		print_r($array);
		echo str_repeat('-', 80)."\n";
	} else {
		echo '<PRE style="text-align:left;font-size:12px;background-color: #f0f0f0; padding: 10px; mardin-top: 20px; margin-bottom: 20px;"><b><font style="color:red;">Вывод дампа:</font></b><br>'.$debug_backtrace[0]['file'] . ' ('. $debug_backtrace[0]['line'] . ')<BR>';
		print_r($array);
		echo '</PRE>';
	}
}

function set_null(&$val) {
	if (is_null($val)) {
		$val = '<i>NULL</i>';
	}
}

/**
* Отладчик, который выводит таблицу
* @param array $array
* @param string $show_comments - показывать в каком ряду произошло обращение к функции
* @return void
*/
function z($array, $show_comments = true) {
	if (!defined('ERROR_OCCUR')) define('ERROR_OCCUR', 1);
	if (!IS_DEVELOPER && !DEBUG) return;
	$debug_backtrace = debug_backtrace();
	$test = reset($array); 
	if (defined('STDIN')) {
		// Shell output
		x($array);
	} elseif (!is_array($test)) {
		if ($show_comments) {
			echo '<pre style="font-size:12px;background-color: #f0f0f0; padding: 10px; mardin-top: 20px; margin-bottom: 20px;"><b><font style="color:red;">Вывод дампа:</font></b><br>'.$debug_backtrace[0]['file'] . ' ('. $debug_backtrace[0]['line'] . ')';
		}
		x($array);
		if ($show_comments) {
			echo '</pre>';
		}
	} else {
		if ($show_comments) {
			echo '<PRE style="font-size:12px;background-color: #f0f0f0; padding: 10px; mardin-top: 20px; margin-bottom: 20px;"><b><font style="color:red;">Вывод дампа:</font></b><br>'.$debug_backtrace[0]['file'] . ' ('. $debug_backtrace[0]['line'] . ')';
		}
		if (empty($array)) {
			echo '<br><b>Empty result set</b></pre>';
			return;
		}
		echo '<table border="1" cellpadding="2" cellspacing="0" style="font-family: Verdana; font-size: 12px;">
			<thead>';
		$separator = '<tr style="background-color: #FFCC00;"><th>#</th>';
		$columns = reset($array);
		while (list($name,) = each($columns)) {
			$separator .= '<th>'.$name.'</th>';
		}
		$separator .= '</tr>';
		echo $separator.'</thead><tbody>';
		
		$counter = 0;
		reset($array);
		while(list(,$row) = each($array)) {
			$counter++;
			if ($counter % 50 == 0) {
				echo $separator;
			}
			echo '<tr><td>'.$counter.'</td>';
			reset($row);
			while(list(,$td) = each($row)) {
				if (empty($td) && is_null($td)) {
					echo '<td><i>NULL</i></td>';	
				} elseif (empty($td) && !is_numeric($td)) {
					echo '<td>&nbsp;</td>';	
				} else {
					echo '<td>'.htmlspecialchars($td, ENT_COMPAT, 'cp1251').'</td>';
				}
			}
			echo '</tr>';
		}
		echo '</tbody></table></PRE>';
	}
}


/**
* Модуль компрессии
* @param string $content
* @return string
*/
function mod_deflate($content) {
	// Сжимаем файл gzip, если браузер поддерживает его
	if (CMS_COMPRESSION != 0 && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			$content = str_repeat(' ', 2048).$content;
		}
		$content = gzencode($content, 6, FORCE_GZIP); 
		header("Content-Encoding: gzip");
		header("Content-Type: text/html; charset=".CMS_CHARSET);
	}
	header("Content-Length: ".strlen($content));
	return $content;
}


/**
* Преобразовываем {$url_xxx} в локальные url адреса
* @param string $content
* @param bool $editor - контент грузится в HTML редактор
* @return string
*/
function id2url($content, $editor = false) {
	global $DB;
	
	// Шаг 1. Определяем id всех ссылок
	if (0 == preg_match_all('/{url:([\d]+)}/', $content, $matches)) {
		return $content;
	}
	
	$all_id = array_flip($matches[1]);
	
	// Шаг 2. Находим для этих ссылок url адреса
	$query = "SELECT id, concat(url, '/') as url FROM site_structure WHERE id IN(0".implode(',', array_unique($matches[1])).")";
	$id2url = $DB->fetch_column($query, 'id', 'url');
	
	// Шаг 3. Заменяем в контенте найденные ссылки
	reset($id2url);
	while (list($id, $url) = each($id2url)) {
		$pos = strpos($url, '/');
		$content = str_replace('{url:'.$id.'}', 'http://'.substr($url, 0, $pos+1).LANGUAGE_URL.substr($url, $pos+1), $content);
		unset($all_id[$id]);
	}
	
	// Шаг 4. Заменяем в контенте ссылки, которые небыли найдены
	// При редактировании страницы в редакторе не надо выдавать alert
	if (!$editor) {
		reset($all_id);
		while (list($id, ) = each($all_id)) {
			// К сожалению ссылка ведет на несуществующий раздел сайта.
			$content = str_replace('{url:'.$id.'}', 'javascript:alert(\''.cms_message('CMS', 'К сожалению ссылка ведет на несуществующий раздел сайта.').'\');', $content);
		}
	}
	
	// Возвращаем результат
	return $content;
}

/**
 * Универсальный конвертор дат, в качестве формата используется строка типа "dd/mm/yyyy h:i:s a" или
 * d.m.y или h:i:s. Формат разделителей роли не играет тоесть h:i:s == h/i/s, количество и регистр символов
 * описывающих формат - тоже: dd.mm.yyyy == d.m.y. Символ a обозначает, что в дате будет присутствовать AM или PM
 * 
 * @param string $format_center
 * @param string $date
 * 
 * @return int
 */
function convert_date($format, $date) {
	$monthes = array("jan"=>1, "feb"=>2, "mar"=>3, "apr"=>4, "may"=>5, "jun"=>6, "jul"=>7, "aug"=>8, "sep"=>9, "oct"=>10, "nov"=>11, "dec"=>12);
	$default = array(
		'd' => date("d"),
		'm' => date("m"), 
		'y' => date("Y"),
		'h' => date("H"),
		'i' => date("i"),
		's' => date("s"),
		'a' => false
	);
	$date = trim(strtolower($date));
	$date = preg_split("/[^0-9a-z]+/", $date, -1, PREG_SPLIT_NO_EMPTY);
	$format = preg_replace("/([a-z])[a-z]*/", "$1", strtolower($format));
	$keys = preg_split("/[^a-z]+/", $format, -1, PREG_SPLIT_NO_EMPTY);
	if (count($keys) != count($date)) {
		return false;
	}
	for($i=0; $i<count($date); $i++){
		if(in_array($date[$i], array_keys($monthes))){
			$date[$i] = $monthes[$date[$i]];
		}
	}
	
	$date = array_combine($keys, $date);
	$date = array_merge($default, $date);
	if ($date['a'] == 'pm') {
		$date['h'] += 12;
	}
	if (strlen($date['y']) == 2) {
		$date['y'] = substr(date('Y'), 0, 2).$date['y'];
	}
	return mktime($date['h'],$date['i'],$date['s'],$date['m'],$date['d'],$date['y']);
}


/**
* Обработчик ошибок
* @param string $file имя файла
* @param int $line строка
* @param string $error_number int
* @param string $message
* @return void
*/
function errorHandler($error_number, $message, $file, $line) {
	global $DB;
	
	$error_name = array(
		E_WARNING 		=> 'E_WARNING',
		E_NOTICE 		=> 'E_NOTICE',
		E_USER_ERROR 	=> 'E_USER_ERROR',
		E_USER_WARNING 	=> 'E_USER_WARNING',
		E_USER_NOTICE 	=> 'E_USER_NOTICE',
		E_STRICT		=> 'E_STRICT',
		E_RECOVERABLE_ERROR		=> 'E_RECOVERABLE_ERROR'
	);
	
	// Если стоит подавление ошибки, то не обрабатываем эту ошибку __ Отключено, так как ошибку нужно залогировать и выйти, а не продолжать работу
	if (0 == intval(ini_get('error_reporting') & $error_number)) {
		return;
	}
	
	
	// Устанавливаем флаг, указывающий на то, что на странице есть ошибка
	if (!defined('ERROR_OCCUR')) define('ERROR_OCCUR', 1);
	
	// Выводим сообщение с ошибкой для разработчиков
	if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 502) {
		/**
		 * Строчка добавлена, потому что при выводе в режиме debug=502
		 * не указывается в каком месте произошла ошибка и ее трудно искать
		 * @author Eugen Golubenko
		 * @since 2007-04-12 
		 */
		$backtrace = array();
		$place_specification = "In $file on line $line";
	} else {
		$backtrace = debug_backtrace();
		$place_specification = '';
	}
	$debug = array();
	reset($backtrace);
	while(list($index,) = each($backtrace)) {
		if (isset($backtrace[$index]['file'])) {
			$debug[] = $backtrace[$index]['file'].' ('.$backtrace[$index]['line'].')';
		}
	}
	
	if (defined('STDIN')) {
		// Сообщение выводится через терминал
		$output = '<br><b>'.$error_name[$error_number].':</b><br>'.$message.'<br>'.implode(' =><br>', $debug);
		echo Shell::html($output);
	} elseif (Auth::isAdmin() || DEBUG) {
		// Сообщение выводится на сайт
		$output = '<pre style="font-size:12px;background-color:#f0f0f0; padding: 10px; margin-top: 20px; margin-bottom:20px;"><b><font style="color:red">'.$error_name[$error_number].':</font></b><br>'.$message.'<br>'.implode(' =><br>', $debug).''.$place_specification.'.</pre>';
		echo $output;
	} elseif ($error_number == E_USER_ERROR) {
		// Сообщение для обычных пользователей, в случае критической ошибки
		echo cms_message('CMS', 'На странице произошла критическая ошибка. Сообщение об ошибке отправлено администратору сайта.');
	}
	
	$data = array(
			'site_name' => CMS_HOST,
			'cms_version' => (defined('CMS_VERSION')) ? CMS_VERSION: '',
			'date' => date('Y-m-d H:i:s'), 
			'url' => (defined('CURRENT_URL_FORM') ? "http://".CMS_HOST.CURRENT_URL_FORM : 'shell'),
			'ip' =>  HTTP_IP." (".HTTP_LOCAL_IP.")",
			'file' => substr($file, strlen(SITE_ROOT)),
			'line' => $line,
			'type' => $error_name[$error_number],
			'refferer'   => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
			'user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),
			'message' => $message,
	);
	
	if (!is_dir(LOGS_ROOT)) {
		mkdir(LOGS_ROOT, 0755, true);
	}
	if (!is_file(LOGS_ROOT.'error.log')) {
		touch(LOGS_ROOT.'error.log');
	}
	
	if (is_writable(LOGS_ROOT.'error.log') && filesize(LOGS_ROOT.'error.log')/(1000 * 1000) < 100) {
		$fp = fopen(LOGS_ROOT . 'error.log', 'a');
		// При ошибках базы данных удаляем из процесса первые две записи
		if (isset($debug[0]) && isset($debug[1]) && strpos($debug[0], 'mysqli.class.php') && strpos($debug[1], 'mysqli.class.php')) {
			unset($debug[0], $debug[1]);
			if (isset($debug[2]) && preg_match("/^(.+)\s\((\d+)\)$/", $debug[2], $matches)) {
				$file = $matches[1];
				$line = $matches[2];
			}
		}
		// Определяем дату последнего изменения файла, в котором произошла ошибка
		if (is_file($file)) {
			$stat = stat($file);
		}  
	
		$_SERVER['HTTP_REFERER'] = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "unknown";
		$_SERVER['HTTP_USER_AGENT'] = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "unknown";
		
		fwrite($fp, "\n
[BEGIN]".str_repeat('-', 50)."
Date: ".date('Y-m-d H:i:s')."
URL: ".(defined('CURRENT_URL_FORM') ? "http://".CMS_HOST.CURRENT_URL_FORM : 'shell')."
IP: ".HTTP_IP." (".HTTP_LOCAL_IP.")
File: ".substr($file, strlen(SITE_ROOT))."
Mtime: ".$stat['mtime']."
Line: ".$line."
Type: ".$error_name[$error_number]."
Refferer: ".$_SERVER['HTTP_REFERER']."           
UserAgent: ".$_SERVER['HTTP_USER_AGENT']."   
Message: ".$message."
Process: 
".implode(" => \n", $debug)."
[END]".str_repeat("-", 50)."\n");
		fclose($fp);
	}
	// Прекращаем выполнение скрипта при ошибке 256 (E_USER_ERROR)
	if ($error_number == E_USER_ERROR) {
		exit;
	}
}

/**
 * Добавляет where условие в SQL запрос
 *
 * @param string $field
 * @param mixed $value
 * @param string $function
 * @return string
 */
function where_clause($field, $value, $function = '', $flag = true, $alternative = '') {
	if (empty($value) || $flag == false) {
		return $alternative;
	}
	
	if (is_array($value)) {
		return " AND $field in ('".implode("','", $value)."')";
	} elseif (!empty($function) && in_array($function, array('>', '<', '>=', '<=', '!=', '<>'))) {
		return " AND $field $function '$value'";
	} elseif (!empty($function)) {
		return " AND $field=$function('$value')";
	} else {
		return " AND $field='$value'";
	}
}

/**
 * Добавляет join условие, если !empty($value)
 *
 * @param string $join
 * @param mixed $value
 * @return string
 */
function join_clause($join, $value) {
	if (empty($value)) return '';
	return $join;
}


/**
 * Функция, которая используется при выводе подзаголовков в шаблонах
 *
 * @param string $name
 * @param string $subtitle
 * @return string
 */
function make_subtitle($name, $subtitle) {
	static $previous = array();
	if (!isset($previous[$name]) || $previous[$name] != $subtitle) {
		$previous[$name] = $subtitle;
		return $subtitle;
	} else {
		return '';
	}
}

/**
 * Формирование ссылки, доступной только авторизованным пользователям
 * Для неавторизованных - сначала показывается окно авторизации
 *
 * @param string $href
 * @return string
 */
function auth_link($href) {
	if (Auth::isLoggedIn()) {
		return " href=\"$href\" ";
	} else {
		if (empty($href)) $href = '/';
		return "onclick=\"showAuthWindow(this.href); return false;\" href=\"$href\"";
	}
}

/**
 * Выводит ошибку 404
 *
 */
function error404() {
	global $Site;
	$Template = new Template(SITE_ROOT.'design/'.$Site->error_template_name);
	header('Status: 404 Not Found');
	echo $Template->display();
	exit;
}

set_error_handler('errorHandler');


/**
 * Сортирует массив по заданным параметрам
 * @param array $ary
 * @param string $clause
 */
function order_structure(&$ary, $clause) { 
    $keys   = explode(',', $clause);
    $dir_map = array('desc' => 1, 'asc' => -1);
    $def    = "asc";
	
    $key_ary = array();
    $dir_ary = array();
    
    reset($keys);
    while(list(, $key) = each($keys)){
    	$key = explode(' ', trim($key)); 
    	$key_ary[] = trim($key[0]);
    	 
    	if(isset($key[1])) {
            $dir = strtolower(trim($key[1]));
            $dir_ary[] = $dir_map[$dir] ? $dir_map[$dir] : $def;
        } else {
            $dir_ary[] = $def;
        }
    }
	
    $fn_body = "";
    for($i=count($key_ary)-1; $i>=0; $i--) {
        $k = $key_ary[$i];
        $t = $dir_ary[$i];
        $f = -1 * $t;
        $aStr = '$a[\''.$k.'\']';
        $bStr = '$b[\''.$k.'\']';
		
        if($fn_body == "") {
            $fn_body .= "if({$aStr} == {$bStr}) { return 0; }\n";
            $fn_body .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";               
        } else {
            $fn_body = "if({$aStr} == {$bStr}) {\n" . $fn_body;
            $fn_body .= "}\n";
            $fn_body .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
        }
    }
	
    if($fn_body) {
        $sortFn = create_function('$a,$b', $fn_body);
        usort($ary, $sortFn);       
    }
}


/**
 * Возвращает путь к документу по id или уникальному имени
 * @param mixed $name_or_id
 * @return mixed
 */
function get_document($name_or_id){
	global $DB;
	
	if(is_numeric($name_or_id)){
		$query = "SELECT id, file FROM cms_document WHERE id = '$name_or_id'";
	} else {
		$name_or_id = strtolower($name_or_id); 
		$query = "SELECT id, file FROM cms_document WHERE uniq_name = '$name_or_id'";
	} 
	$result = $DB->query_row($query);
	
	if(empty($result['id']) || empty($result['file'])){
		return false;
	}
	return UPLOADS_DIR."cms_document/file/".Uploads::getIdFileDir($result['id']).".$result[file]";  
}



/**
 * Преобразовывает название раздела в URL
 *
 * @param string $name
 * @param int $max_length
 * @return string
 */
function name2url($name, $max_length = 32) {
	$name = Charset::translit($name);
	$name = str_replace("'", '', $name);
	$name = preg_replace("/[^a-z0-9_\.]+/i", "-", $name);
 	return substr($name, 0, $max_length);
}


/**
 * Ведение логов
 *
 * @param int $partner_id
 * @param unknown_type $action
 * @param unknown_type $content
 * @param unknown_type $stage
 */
function html_screen($path, $content, $action='', $stage=''){
		 
	/** 
	 * Создаем папку для лога, если она не существует
	 */ 
	$logs_dir_local = LOGS_ROOT.$path.date('Y-m-d').'/'.$action;   
	if(!is_dir($logs_dir_local)) mkdir($logs_dir_local, 0777, true);
	
	$logs_file_path = $logs_dir_local.'/'.date('H_i_s').'-'.$stage.'.html'; 
	file_put_contents($logs_file_path, $content);	   
	exec("chown -R hoster:hoster ".LOGS_ROOT); 
	
	/**
	 * Удаление старых логов
	 */
	if(rand(0, 1000) > 900){
		$logs = array_values(array_diff(scandir(LOGS_ROOT.$path), array( ".", ".." )));
		reset($logs);
		while (list(, $date) = each($logs)) {
			$tstamp = convert_date('Y-m-d', $date); 
			if($tstamp < time()-86400*3) Filesystem::delete(LOGS_ROOT.$path.$date);  
		}
	} 
}


/**
 * Обрабатывает телефонные номера перед их сохранением
 *
 * @param string $phone
 * @return mixed
 */ 
function parse_phone($phone){
	$phone = str_replace(array('(', ')', '-', ' '), '', trim($phone));
	if(!preg_match('/\+[0-9]{11,12}/', $phone)) return false;
	$phone = str_replace('+0', '+', $phone);
	
	return $phone;
}


/**
 * Авто генерирование паролей
 *
 * @param int $len
 * @return string
 */
function gen_password($len = 6){  
    return substr(md5(uniqid(rand().rand(), true)), 0, $len);
}


/**
 * Очистка патришнов таблицы с данными, что старше $days_count дней, но не более 28 дней
 * Необходимое условие использования: таблица должна быть разбита методом PARTITION BY HASH(to_days(`date`)) PARTITIONS 31;
 *
 * @param string $table_name
 * @param int $days_count
 */
function table_partitions_truncate($table_name, $days_count){
	global $DBStat;
	
	if($days_count > 28) return false;
	
	$partitions = array();
	$days = array();  
	
	for ($i=0; $i<=$days_count; $i++){
		$days[] = $DBStat->query_row("explain partitions select * from $table_name where date = current_date() - interval $i day;");
	}
	
	reset($days);
	while(list($index, $row) = each($days)){
		if(!empty($row['partitions'])) $partitions[] = $row['partitions'];
	}
	
	$partitions_on_delete = $DBStat->fetch_column(" 
		select partition_name 
		from information_schema.partitions 
		where table_name='$table_name' 
			and partition_name not in ('".implode("','", $partitions)."')
	"); 
	   
	if (!empty($partitions_on_delete)) {
		echo "[i] ALTER TABLE $table_name TRUNCATE PARTITION ".implode(",", $partitions_on_delete)."\n";
		$DBStat->delete("ALTER TABLE $table_name TRUNCATE PARTITION ".implode(",", $partitions_on_delete));
	}
	
	return true;
}



/**
 * Вывод слова в соответствии с кол-вом
 *
 * @param string $word_core
 * @param int $digit
 * @return string
 */
function word_digit_correct($word_core, $digit){
	$last_word_char = substr($word_core, -1);
	$last_digit_char = substr($digit, -1);
	
	if($last_word_char == 'ь'){
		if($last_digit_char == '1' && $digit != 11){
			return $word_core;
		} elseif(in_array($last_digit_char, array('2', '3', '4')) && !in_array($digit, array(12, 13, 14))){
			return substr($word_core, 0, strlen($word_core)-1).'я';
		} else return substr($word_core, 0, strlen($word_core)-1).'ей';
	}
	
	if($last_digit_char == '1' && $digit != 11){
		return $word_core.'а';
	} elseif(in_array($last_digit_char, array('2', '3', '4')) && !in_array($digit, array(12, 13, 14))){
		return $word_core.'ы';
	} else return $word_core;
}


/**
 * Декодирует коды \u0...
 *
 * @param array $match
 * @return string
 */
function decode_unicode_sequence($content) {

	function replace_unicode_escape_sequence($match) {
	    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
	}
	
	return stripslashes(stripslashes(preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $content)));  
}


/**
 * Возвращает значение константы по алиасу базы данных и названию параметра
 *
 * @param string $param
 * @param string $db_alias
 * @return mixed 
 */
function db_config_constant($param, $db_alias = 'default'){
	return constant(strtoupper("DB_".$db_alias."_".$param));
}


?>
