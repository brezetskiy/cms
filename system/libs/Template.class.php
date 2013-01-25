<?php
/**
* ����� �� ������ � �������� ��������
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* ���������������� �������
*/
require_once(LIBS_ROOT . 'TemplateUDF.class.php');


/**
* ����� �� ������ � �������� ��������
* @package Template
* @subpackage CMS
*/
class Template {
	
	/**
	 * ���� ��������� ����������� �������, ������������ ��� ������ ������������ ��������
	 * @var int
	 */
	public $last_modified = 0;
	
	/**
	 * RegEx ��� ��������� Iterate ��������
	 * @var string
	 */
	protected $regex_section = '/<tmpl:([a-z0-9_\.]+)>(.*)<\/tmpl:\\1>/ismU';
	
	/**#@+
	 * RegEx ��� ��������� ����������, ������� ���������� � ����������� if, elseif, udf, ������ ��� ����������� ���� "$test." 
	 * ������ ���� ���������� ������ ��� "$test".
	 * @var string 
	 */
	protected $regex_vars = "/([\\\$\@\#\%]+)([a-z_]+(?:\.?[\\\$\@\#\%a-z_0-9]+)*)/i";
	protected $pattern_vars = "([\\\$\@\#\%]+)([a-z_]+(?:\.?[\\\$\@\#\%a-z_0-9]+)*)";
	/**#@- */
	
	/**
	 * RegEx ��� ��������� �������, ��������� ���������, ������� ��������� :, �� ��� ���� ��������� ������ : � �������� � ���� ������� � �������� ������
	 * @var string 
	 */
	protected $regex_function_param = '/([a-z_]+)\=((?:[0-9\.]+?)|(?:".+(?<!\\\)")|(?:\'.+(?<!\\\)\')|(?:[\$\#\@\%][a-z_]+[a-z0-9\._]*?))/iU';
	
	/**
	* RegEx ��� ��������� ���������� ������� ��������� �������� echo � � ��� ���� ����� �������������� ��������
	* @var string
	*/
	protected $regex_all = '';
	
	/**
	* RegEx �� ��������� ������� (pattern - ��� ������ ����� ������� ��� ���������� /pattern/iU) 
	* @var string
	*/
	protected $pattern_udf = '';
	
	/**
	* ���������� ����������
	* @var array
	*/
	protected $global = array();
	
	/**
	* ������������ ����, ������������ ��� ���������� �������
	* @var array
	*/
	protected $compile_parent = array();
	
	/**
	* ����������, ������� ����� ����������� � ������
	* @var array
	*/
	protected $vars = array();
	
	/**
	* ��� ����������������� �����
	* @var string
	*/
	protected $compiled_file = '';
	
	/**
	* ������� ��������
	* @var int
	*/
	protected $section_counter = 0;
	
	/**
	* �������� ����� ������
	* @var string
	*/
	protected $section_var_name = '';
	
	/**
	* ���������� ����������
	* @var array
	*/
	protected $global_vars = array();
	
	
	/**
	 * �����������
	 * 
	 * @param string $filename - ���� � ��������������� ����� ����� �������� ��� ������������ TEMPLATE_ROOT, ��� � ����� ����� [19/03/2009 rudenko@delta-x.ua].
	 * @return object
	 */
	public function __construct($filename, $language = null) {
		
		$language = (is_null($language)) ? LANGUAGE_CURRENT: $language;
		if (substr($filename, 0, strlen(SITE_ROOT)) != SITE_ROOT) {
			// ���� ����� ������������ ����� TEMPLATE_ROOT
			$this->compiled_file = CACHE_ROOT."template/$filename.$language.inc.php";
			$template_file = TEMPLATE_ROOT."$filename.$language.tmpl";
		} else {
			$this->compiled_file = CACHE_ROOT."template/".substr($filename, strlen(SITE_ROOT)).".$language.php";
			$template_file = $filename.".$language.tmpl";
		}
		
		if (!is_file($template_file)) {
			trigger_error(cms_message('CMS', '���� � �������� %s - �� ����������.', $template_file), E_USER_ERROR);
		}
		
		$this->initRegularExpressions();
		
		/**
		 * ����������� ������ ���� �� ��� ������� ��� ���� ��� ������� �����-���������� �������
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
	 * ��������� ���������� ���������, ������� ����� ���������������
	 * ���� ��������, ���������� � ������������
	 */
	protected function initRegularExpressions() {
		$this->pattern_udf = '(?:'.implode(')|(?:', get_class_methods('TemplateUDF')).')';
		$any_symbol = '(?:".*(?<!\\\)")|(?:\'.*(?<!\\\)\')|(?:[^\'"]+)'; // ���������� ��������� � ������ ���� �� ���� ���������� ���� '}' ��� "}" �.�. �� ����� ��� ���������. ��������� ����� ������ ��������� ���� '' ��� ""
		$this->regex_all = '/\{((?:(?:[\$\#\%\@\/])|(?:if )|(?:elseif )|'.$this->pattern_udf.')(?:'.$any_symbol.')*)(?<!\\\)\}/U';
	}
	
	/**
	 * ���������� ����������������� �������
	 *
	 * @param string $filename
	 * @param string $content
	 */
	protected function saveCompiledTemplate($filename, $content) {
		if (!is_dir(dirname($filename))) {
			// ������� ���������� ��� ����������������� �������
			if(!mkdir(dirname($filename), 0777, true)) {
				trigger_error('���������� ������� ����������, �������� ���������� ����', E_USER_ERROR);
			}
		}
		file_put_contents($filename, $this->compile($content));
	}
	
	/**
	 * ����������, �� ����� ����� �������� ������
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
	 * ���������� ����������, ������� ������������ ��� ������ ������� ������ TemplateUDF �� ��������� �� ����������� 
	 * ���������, ������� ������������� ����� ��������
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
	* ����������
	*/
	
	/**
	 * ��������������� HTML ������ � PHP ����������
	 *
	 * @param string $content
	 * @return string
	 */
	protected function compile($content) {
		// ������� �����������
		$content = preg_replace("/\{\*.+\*\}/sU", '', $content);
		
		// �������� /if � else
		$content = str_replace('{/if}', '<?php endif; ?>', $content);
		$content = str_replace('{else}', '<?php else: ?>', $content);
		
		// ������ ������
		$content = $this->compileSections($content);
		
		// ������������ �������� ��������
		$this->section_var_name = "\$this->vars";
		
		// ����������
		$content = preg_replace_callback($this->regex_all, array(&$this, 'allCallback'), $content);
		
		return $content;
	}
		
	/**
	* ����������� ������
	* @param string $content
	* @return void
	*/
	protected function compileSections ($content) {
		return preg_replace_callback($this->regex_section, array(&$this, 'compileSectionsCallback') , $content);
	}
	
	/**
	* Callback ������� ��� ����������� ������
	* @param array $matches
	* @return string
	*/
	protected function compileSectionsCallback ($matches) {
		$content = $matches[2];
		$parent = $this->compile_parent;
		$this->compile_parent[] = $matches[1];
		
		/**
		* ���������� ���� �� ���������
		*/
		if (preg_match($this->regex_section, $content)) {
			$content = $this->compileSections($content, '');
		}
		
		// ������������ ������
		$this->section_var_name = "\$this->vars['/".implode("/", $this->compile_parent)."/'][\$_".implode("_", $parent)."_key][\$_".implode('_', $this->compile_parent)."_key]";
		
		// ������������ ����������
		$content = preg_replace_callback($this->regex_all , array(&$this, 'allCallback'), $content);
		
		
		// ������������� ���������� ������
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
	* ������������ ����������
	* @param array $matches
	* @return string
	*/
	protected function allCallback($matches) {
		$expression = $matches[1];
		if (preg_match("/^[\#\@\%\\\$]/", $expression)) {
			
			/**
			 * ����������, ��������� ��� ���������� ����������, �������� � �����������
			 */
			return '<?php echo '.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), $expression).'; ?>';
			
		} elseif (preg_match("/^((?:else)?if) (.+)$/", $expression, $matches)) {
			
			/**
			 * ����������� if, elseif
			 */
			return '<?php '.$matches[1].'('.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), $matches[2]).'): ?>';
			
		} elseif (preg_match('/^('.$this->pattern_udf.')$/', $expression, $matches)) {
			
			/**
			 * ������� ��� ����������
			 */
			return '<?php echo TemplateUDF::'.$matches[1].'(array()); ?>';
			
		} elseif (preg_match("/^".$this->pattern_udf."\s/", $expression, $matches)) {
			/**
			 * �������
			 */
			preg_match_all($this->regex_function_param, $expression, $param);
			
			$arguments = array();
			reset($param[1]);
			while(list($index,) = each($param[1])) {
				
				if (preg_match("/^(['\"])/", $param[2][$index], $quotes)) {
					// �������� regexp, ������� ��������� � �������� ����� � ������� ��������� ����������. ������: test[$i][foo] (06.09.2005)
					$arguments[] = "'".$param[1][$index]."'=>".preg_replace($this->regex_vars, $quotes[1].'.\1\2.'.$quotes[1], $param[2][$index]);
				} else {
					$arguments[] = "'".$param[1][$index]."'=>".$param[2][$index];
				}
			}
			return '<?php echo TemplateUDF::'.$matches[0].'(array('.preg_replace_callback($this->regex_vars, array(&$this, 'varCallback'), implode(',', $arguments)).')); ?>';
			
		} else {
			
			/**
			* ���-�� ����������, ��������� ���� �� � ���� ����������� ������ ���������� �������� ��� ��������� js ����
			* {x={$y}}, ������ ������ ����� ��������������, � ������ {$y} - ���������
			*/
			return '{'.preg_replace_callback($this->regex_all, array(&$this, 'allCallback'), $expression).'}';
		}
	}
	
	/**
	* ���������� ��� ���������� � ��������������� �� � ����������� php
	* @access protected
	* @param string $matches
	* @return string
	*/
	protected function varCallback($matches) {
		// ���������� ��� ����������
		switch ($matches[1]) {
			case '$':
				// ��������� ����������
				$var = $this->section_var_name.$this->varArray($matches[2]);
				break;
			case '@':
				// ���������� ����������
				$var = "\$this->global_vars".$this->varArray($matches[2]);
				break;
			case '#':
				// ���������
				$var = $matches[2];
				break;
			case '%':
				// Superglobal
				$matches[2] = preg_split("/\./", $matches[2], -1, PREG_SPLIT_NO_EMPTY);
				$var_name = array_shift($matches[2]);
				$var = "\$".$var_name.$this->varArray(implode('.', $matches[2]));
				break;
			default:
				// ������, ����� � ������ �����
				$var = $matches[2];
			break;
		}
		
		return $var;
	}
	
	/**
	* ��������� �������� ���� $foo.$i.bar � $foo[$i]['bar']
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
	* ������
	*/
	
	/**
	* ����������� �������� ����������, ������������� ������
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
	 * ������� �������� �� ������
	 * @access public
	 * @param string $section
	 * @return void
	 */
	public function cleanIterate($section) {
		unset($this->vars[$section]);
	}
	
	/**
	* �������� ��������
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
	 * ��������� ������� ��������. ������ ���� ���� ������ ���� ����� ��
	 * ����� ������� ����� DB::query(). ������� ���������� ������, � �������� ������
	 * �������� ������������ ����� ��������� $data, � � �������� �������� - ����������
	 * ������ ������.
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
	* ��������� � ����� ������� �� �����
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
		$__key = 0; // ��� ��������� �������� ��������
		require($this->compiled_file);
		error_reporting($error_reporting);
		return ob_get_clean();
	}
	
	
	
	/**
	* ��������� ������
	*/
	
	/**
	* ���������� �������
	* @access public
	* @param string $method
	* @param array $param
	* @return void
	*/
	public function __call($method, $param) {
		if ($method == 'set' && count($param) == 2) {
			// ��������� ����������
			$this->setLocalVar($param[0], $param[1]);
		} elseif ($method == 'set' && count($param) == 1) {
			// ��������� ������
			$this->setLocalArray($param[0]);
			
		} elseif ($method == 'setGlobal' && count($param) == 2) {
			// ���������� ����������
			$this->setGlobalVar($param[0], $param[1]);
			
		} elseif ($method == 'setGlobal' && count($param) == 1) {
			// ���������� ������
			$this->setGlobalArray($param[0]);
			
		} else {
			trigger_error(cms_message('CMS', '����������� ����� %s �� ���������.', $method), E_USER_ERROR);
			
		}
	}
	
	
	/**
	* �������� ������ �������������� �������
	* @param string $name
	* @return void
	*/
	public function __get($name) {
		trigger_error(cms_message('CMS', '�������� %s - �� ����������.', $name), E_USER_WARNING);
	}
	/**
	* �������� ������ �������������� �������
	* @param string $name
	* @return void
	*/
	public function __set($name, $var) {
		trigger_error(cms_message('CMS', '�������� %s - �� ����������.', $name), E_USER_WARNING);
	}

}
?>