<?php
/**
 * Класс для работы с шаблонами, которые передаются в виде строки
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

class TemplateString extends Template {
	
	public function __construct($string) {
		global $DB;
		$this->initRegularExpressions();
		$this->compiled_file = CACHE_ROOT."template/content/".uniqid('template_string_').".php";
		$this->saveCompiledTemplate($this->compiled_file, $string);
		register_shutdown_function(array($this, 'deleteTemplate'));
	}
	
	public function deleteTemplate() {
		unlink($this->compiled_file);
	}
	
}
?>