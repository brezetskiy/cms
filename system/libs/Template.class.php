<?php
/**
* Класс по работе с шаблоном страницы
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* Пользовательские функции
*/
require_once(LIBS_ROOT . 'TemplateUDF.class.php');


/**
* Класс по работе с шаблоном страницы
* @package Template
* @subpackage CMS
*/
class Template {
	
	/**
	 * Дата последней модификации шаблона, используется при выводе статического контента
	 * @var int
	 */
	public $last_modified = 0;
	
	/**
	 * RegEx для обработки Iterate шаблонов
	 * @var string
	 */
	protected $regex_section = '/<tmpl:([a-z0-9_\.]+)>(.*)<\/tmpl:\\1>/ismU';
	
	/**#@+
	 * RegEx для обработки переменных, которые встечаются в конструкции if, elseif, udf, учесть что конструкция типа "$test." 
	 * должна быть обработана просто как "$test".
	 * @var string 
	 */
	protected $regex_vars = "/([\\\$\@\#\%]+)([a-z_]+(?:\.?[\\\$\@\#\%a-z_0-9]+)*)/i";
	protected $pattern_vars = "([\\\$\@\#\%]+)([a-z_]+(?:\.?[\\\$\@\#\%a-z_0-9]+)*)";
	/**#@- */
	
	/**
	 * RegEx для обработки функций, разделяет параметры, которые разделены :, но при этом позволяет писать : в кавычках и сами кавычки с обратным слешем
	 * @var string 
	 */
	protected $regex_function_param = '/([a-z_]+)\=((?:[0-9\.]+?)|(?:".+(?<!\\\)")|(?:\'.+(?<!\\\)\')|(?:[\$\#\@\%][a-z_]+[a-z0-9\._]*?))/iU';
	
	/**
	* RegEx для обработки переменных которые выводятся командой echo и в них есть знаки арифмитических операций
	* @var string
	*/
	protected $regex_all = '';
	
	/**
	* RegEx по обработке функций (pattern - это только часть шаблона без параметров /pattern/iU) 
	* @var string
	*/
	protected $pattern_udf = '';
	
	/**
	* Глобальные переменные
	* @var array
	*/
	protected $global = array();
	
	/**
	* Родительская нода, используется при компиляции шаблона
	* @var array
	*/
	protected $compile_parent = array();
	
	/**
	* Переменные, которые будут установлены в шаблон
	* @var array
	*/
	protected $vars = array();
	
	/**
	* Имя скомпилированного файла
	* @var string
	*/
	protected $compiled_file = '';
	
	/**
	* Счетчик структур
	* @var int
	*/
	protected $section_counter = 0;
	
	/**
	* Название тещей секции
	* @var string
	*/
	protected $section_var_name = '';
	
	/**
	* Глобальные переменные
	* @var array
	*/
	protected $global_vars = array();
	
	
	/**
	 * Конструктор
	 * 
	 * @param string $filename - путь к обрабатываемому файлу можно задавать как относительно TEMPLATE_ROOT, так и корня диска [19/03/2009 rudenko@delta-x.ua].
	 * @return object
	 */
	public function __construct($filename, $language = null) {
		
		$language = (is_null($language)) ? LANGUAGE_CURRENT: $language;
		if (substr($filename, 0, strlen(SITE_ROOT)) != SITE_ROOT) {
			// Путь задан относительно папки TEMPLATE_ROOT
			$this->compiled_file = CACHE_ROOT."template/$filename.$language.inc.php";
			$template_file = TEMPLATE_ROOT."$filename.$language.tmpl";
		} else {
			$this->compiled_file = CACHE_ROOT."template/".substr($filename, strlen(SITE_ROOT)).".$language.php";
			$template_file = $filename.".$language.tmpl";
		}
		
		if (!is_file($template_file)) {
			trigger_error(cms_message('CMS', 'Файл с шаблоном %s - не существует.', $template_file), E_USER_ERROR);
		}
		
		$this->initRegularExpressions();
		
		/**
		 * Компилируем шаблон если он был изменен или если был изменен класс-обработчик шаблона
		 */
		$compile = true;
		if (is_file($this->compiled_file)) {
			$class_stat = stat(__FILE__);
			$compiled_stat = stat($this->compiled_file);
			$template_stat = stat($template_file);
			$this->last_modified = $template_stat['mtime'];
			if ($compiled_stat['mtime'] > $template_stat['mtime'] && $compiled_stat['mtime'] > $class_stat['mtime']) {
				$compile = false;
			}
		}
		if ($compile) {
			$this->saveCompiledTemplate($this->compiled_file, file_get_contents($template_file));
		}

	}
	
	/**
	 * Формируем регулярное выражение, которое будет соответствовать
	 * всем функциям, переменным и конструкциям
	 */
	protected function initRegularExpressions() {
		$this->pattern_udf = '(?:'.implode(')|(?:', get_class_methods('TemplateUDF')).')';
		$any_symbol = '(?:".*(?<!\\\)")|(?:\'.*(?<!\\\)\')|(?:[^\'"]+)'; // Исключение остановок в случае если на пути встретится знак '}' или "}" т.е. он будет как значаение. учитывает также пустые множества типа '' или ""
		$this->regex_all = '/\{((?:(?:[\$\#\%\@\/])|(?:if )|(?:elseif )|'.$this->pattern_udf.')(?:'.$any_symbol.')*)(?<!\\\)\}/U';
	}
	
	/**
	 * Сохранение скомпилированного шаблона
	 *
	 * @param string $filename
	 * @param string $content
	 */
	protected function saveCompiledTemplate($filename, $content) {
		if (!is_dir(dirname($filename))) {
			// Создаем директорию для скомпилированного шаблона
			if(!mkdir(dirname($filename), 0777, true)) {
				trigger_error('Невозможно создать директорию, возможно переполнен диск', E_USER_ERROR);
			}
		}
		file_put_contents($filename, $this->compile($content));
	}
	
	/**
	 * Определяет, на каком языке доступен шаблон
	 *
	 * @param string $filename
	 * @return mixed
	 */
	static public function getLanguage($filename) {
		$filename = (substr($filename, 0, strlen(SITE_ROOT)) != SITE_ROOT) ? TEMPLATE_ROOT.strtolower($filename) : $filename;
		if (is_file($filename.'.'.LANGUAGE_CURRENT.'.tmpl')) {
			return LANGUAGE_CURRENT;
		} elseif (is_file($filename.'.'.constant('LANGUAGE_'.CMS_INTERFACE.'_DEFAULT').'.tmpl')) {
			return constant('LANGUAGE_'.CMS_INTERFACE.'_DEFAULT');
		} else {
			return false;
		}
	}
	
	
	/**
	 * Упрощенный обработчик, который используется для вызова методов класса TemplateUDF на страницах со статическим 
	 * контентом, который редактируется через редактор
	 *
	 * @param array $matches
	 * @return string
	 */
	static public function staticContentCallback($matches) {
 		preg_match_all("/([a-z_]+)=\"([^\"]+)\"/iU", $matches[2], $var);
 		if (empty($var[1])) return false;
 		$vars = array_combine($var[1], $var[2]);
 		return TemplateUDF::$matches[1]($vars);
 	}

	
	/**
	* Компилятор
	*/
	
	/**
	 * Преобразовывает HTML шаблон в PHP обработчик
	 *
	 * @param string $content
	 * @return string
	 */
	protected function compile($content) {
		// Удаляем комментарии
		$content = preg_replace("/\{\*.+\*\}/sU", '', $content);
		
		// Заменяем /if и else
		$content = str_replace('{/if}', '<?php endif; ?>', $content);
		$content = str_replace('{else}', '<?php else: ?>', $content);
		
		// Парсим секции
		$content = $this->compileSections($content);
		
		// Обрабатываем корневые элементы
		$this->section_var_name = "\$this->vars";
		
		// Переменные
		$content = preg_replace_callback($this->regex_all, array(&$this, 'allCallback'), $content);
		
		return $content;
	}
		
	/**
	* Компилирует секции
	* @param string $content
	* @return void
	*/
	protected function compileSections ($content) {
		return preg_replace_callback($this->regex_section, array(&$this, 'compileSectionsCallback') , $content);
	}
	
	/**
	* Callback функция для компилятора секций
	* @param array $matches
	* @return string
	*/
	protected function compileSectionsCallback ($matches) {
		$content = $matches[2];
		$parent = $this->compile_parent;
		$this->compile_parent[] = $matches[1];
		
		/**
		* Определяем есть ли подсекции
		*/
		if (preg_match($this->regex_section, $content)) {
			$content = $this->compileSections($content, '');
		}
		
		// Обрабатываем секцию
		$this->section_var_name = "\$this->vars['/".implode("/", $this->compile_parent)."/'][\$_".implode("_", $parent)."_key][\$_".implode('_', $this->compile_parent)."_key]";
		
		// Обрабатываем переменные
		$content = preg_replace_callback($this->regex_all , array(&$this, 'allCallback'), $content);
		
		
		// Устанавливаем обработчик секции
		$content = '<?php
			reset($this->vars[\'/'.implode('/', $this->compile_parent).'/\'][$_'.implode('_', $parent).'_key]);
			while(list($_'.implode('_',$this->compile_parent).'_key,) = each($this->vars[\'/'.implode('/', $this->compile_parent).'/\'][$_'.implode('_', $parent).'_key])):
			?>'.$content.'<?php 
			endwhile;
			?>';
		array_pop($this->compile_parent);
		return $content;
	}
	
	/**
	* Обрабатывает переменные
	* @param array $matches
	* @return string
	*/
	protected function allCallback($matches) {
		$expression = $matches[1];
		if (preg_match("/^[\#\@\%\\\$]/", $expression)) {
			
			/**
			 * Переменная, константа или глобальная переменная, возможно с арифметикой
			 */
			return '<?php echo '.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), $expression).'; ?>';
			
		} elseif (preg_match("/^((?:else)?if) (.+)$/", $expression, $matches)) {
			
			/**
			 * Конструкция if, elseif
			 */
			return '<?php '.$matches[1].'('.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), $matches[2]).'): ?>';
			
		} elseif (preg_match('/^('.$this->pattern_udf.')$/', $expression, $matches)) {
			
			/**
			 * Функция без параметров
			 */
			return '<?php echo TemplateUDF::'.$matches[1].'(array()); ?>';
			
		} elseif (preg_match("/^".$this->pattern_udf."\s/", $expression, $matches)) {
			/**
			 * Функция
			 */
			preg_match_all($this->regex_function_param, $expression, $param);
			
			$arguments = array();
			reset($param[1]);
			while(list($index,) = each($param[1])) {
				
				if (preg_match("/^(['\"])/", $param[2][$index], $quotes)) {
					// Добавлен regexp, который позволяет в качестве ключа к массиву указывать переменную. Пример: test[$i][foo] (06.09.2005)
					$arguments[] = "'".$param[1][$index]."'=>".preg_replace($this->regex_vars, $quotes[1].'.\1\2.'.$quotes[1], $param[2][$index]);
				} else {
					$arguments[] = "'".$param[1][$index]."'=>".$param[2][$index];
				}
			}
			return '<?php echo TemplateUDF::'.$matches[0].'(array('.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), implode(',', $arguments)).')); ?>';
			
		} else {
			
			/**
			* Что-то непонятное, проверяем есть ли в этой конструкции другие подшаблоны например при обработку js типа
			* {x={$y}}, первый шаблон будет проигнорирован, а второй {$y} - обработан
			*/
			return '{'.preg_replace_callback($this->regex_all, array(&$this, 'allCallback'), $expression).'}';
		}
	}
	
	/**
	* Определяет тип переменной и преобразовывает ее в конструкцию php
	* @access protected
	* @param string $matches
	* @return string
	*/
	protected function varCallback($matches) {
		// Определяем тип переменной
		switch ($matches[1]) {
			case '$':
				// Локальная переменная
				$var = $this->section_var_name.$this->varArray($matches[2]);
				break;
			case '@':
				// Глобальная переменная
				$var = "\$this->global_vars".$this->varArray($matches[2]);
				break;
			case '#':
				// Константа
				$var = $matches[2];
				break;
			case '%':
				// Superglobal
				$matches[2] = preg_split("/\./", $matches[2], -1, PREG_SPLIT_NO_EMPTY);
				$var_name = array_shift($matches[2]);
				$var = "\$".$var_name.$this->varArray(implode('.', $matches[2]));
				break;
			default:
				// Строки, числа и прочая фигня
				$var = $matches[2];
			break;
		}
		
		return $var;
	}
	
	/**
	* Обработка массивов типа $foo.$i.bar в $foo[$i]['bar']
	* @access protected
	* @param array $elements
	* @return string
	*/
	protected function varArray($var) {
		$var = preg_split("/\./", $var, -1, PREG_SPLIT_NO_EMPTY);
		reset($var);
		while(list($index,) = each($var)) {
			if (!preg_match($this->regex_vars, $var[$index])) {
				$var[$index] = "['".$var[$index]."']";
			} else {
				$var[$index] = "[".preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), $var[$index])."]";
			}
		}
		return implode('', $var);
	}
	/**
	* Парсер
	*/
	
	/**
	* Присваивает значение переменной, перегружаемые методы
	* @access public
	* @param mixed $name
	* @param mixed $value
	* @return void
	*/
	protected function setLocalVar($name, $value) {
		$this->vars[$name] = $value;
	}
	protected function setLocalArray($name) {
		$this->vars = array_merge($this->vars, $name);
	}
	protected function setGlobalVar($name, $value) {
		$this->global_vars[$name] = $value;
	}
	protected function setGlobalArray($name) {
		$this->global_vars = array_merge($this->global_vars, $name);
	}
	
	
	/**
	 * Очищает итерацию от данных
	 * @access public
	 * @param string $section
	 * @return void
	 */
	public function cleanIterate($section) {
		unset($this->vars[$section]);
	}
	
	/**
	* Итерация структур
	* @access public
	* @param string $section
	* @param int $parent
	* @param array $data
	* @return int
	*/
	public function iterate($section, $parent = 0, $data = array()) {
		$this->section_counter++;
		$section = preg_split("/\//", $section, -1, PREG_SPLIT_NO_EMPTY);
		$parent = (is_null($parent)) ? 0 : $parent;
		$this->vars['/'.implode('/', $section).'/'][$parent][$this->section_counter] = $data;
		return $this->section_counter;
	}
	
	/**
	 * Обработка готовых структур. Формат поля дата должен быть таким же
	 * какой выводит метод DB::query(). Функция возвращает массив, в качестве ключей
	 * которого используются ключи параметра $data, а в качестве значений - уникальные
	 * номера секций.
	 *
	 * @param string $section
	 * @param int $parent
	 * @param array $data
	 */
	public function iterateArray($section, $parent, $data) {
		$return = array();
		$section = preg_split("/\//", $section, -1, PREG_SPLIT_NO_EMPTY);
		$parent = (is_null($parent)) ? 0 : $parent;
		reset($data);
		while(list($index, $row) = each($data)) {
			$this->section_counter++;
			$this->vars['/'.implode('/', $section).'/'][$parent][$this->section_counter] = $row;
			$return[$index] = $this->section_counter;
		}
		return $return;
	}
	
	/**
	* Обработка и вывод шаблона на экран
	* @access public
	* @param void
	* @return string
	*/
	public function display() {
		if (!is_file($this->compiled_file) || !is_readable($this->compiled_file)) {
			trigger_error('Unable to read file '.$this->compiled_file, E_USER_ERROR);
		}
		$error_reporting = error_reporting(E_ERROR | E_PARSE);
		ob_start();
		$__key = 0; // Для поддержки корневых структур
		require($this->compiled_file);
		error_reporting($error_reporting);
		return ob_get_clean();
	}
	
	
	
	/**
	* Объектная модель
	*/
	
	/**
	* Перегрузка методов
	* @access public
	* @param string $method
	* @param array $param
	* @return void
	*/
	public function __call($method, $param) {
		if ($method == 'set' && count($param) == 2) {
			// Локальная переменная
			$this->setLocalVar($param[0], $param[1]);
		} elseif ($method == 'set' && count($param) == 1) {
			// Локальный массив
			$this->setLocalArray($param[0]);
			
		} elseif ($method == 'setGlobal' && count($param) == 2) {
			// Глобальная переменная
			$this->setGlobalVar($param[0], $param[1]);
			
		} elseif ($method == 'setGlobal' && count($param) == 1) {
			// Глобальный массив
			$this->setGlobalArray($param[0]);
			
		} else {
			trigger_error(cms_message('CMS', 'Запрошенный метод %s не определен.', $method), E_USER_ERROR);
			
		}
	}
	
	
	/**
	* Контроль чтения несуществующих свойств
	* @param string $name
	* @return void
	*/
	public function __get($name) {
		trigger_error(cms_message('CMS', 'Свойство %s - не существует.', $name), E_USER_WARNING);
	}
	/**
	* Контроль записи несуществующих свойств
	* @param string $name
	* @return void
	*/
	public function __set($name, $var) {
		trigger_error(cms_message('CMS', 'Свойство %s - не существует.', $name), E_USER_WARNING);
	}

}
?>