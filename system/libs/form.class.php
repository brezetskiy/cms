<?php
/** 
 * Класс для работы с формами 
 * @package Pilot 
 * @subpackage Form 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

require_once(LIBS_ROOT.'form/field.class.php');
require_once(LIBS_ROOT.'form/fillexception.class.php');
require_once(LIBS_ROOT.'form/genericexception.class.php');

class Form {
	
	/**
	 * Уникальное имя формы
	 * @var string
	 */
	protected $uniq_name = '';
	
	/**
	 * Заголовок формы
	 * @var string
	 */
	protected $title = '';
	
	/**
	 * Поля формы
	 * @var array
	 */
	protected $fields = array();
	
	/**
	 * Ошибки, которые произошли при валидации формы
	 * @var array
	 */
	protected $errors = array();
	
	/**
	 * Конструктор класса
	 */
	public function __construct() {}
	
	/**
	 * Создает объект формы на основании настроек,
	 * сохраненных в БД
	 * @param string $name
	 * @return Form
	 */
	public function loadForm($name) {
		$DB = DB::factory('default');
		
		$query = "
			select 
				*,
				title_".LANGUAGE_CURRENT." as title
			from form
			where uniq_name = '".$DB->escape($name)."'
		";
		$form = $DB->query_row($query);
		
		if ($DB->rows==0) {
			throw new FormGenericException("Unknown form: $name");
		}
		
		/**
		 * Задаем параметры формы
		 */
		$this->setUniqName($form['uniq_name'])->setTitle($form['title']);
		
		$query = "
			select 
				tb_field.*, 
				tb_field.title_".LANGUAGE_CURRENT." as title,
				tb_field.comment_".LANGUAGE_CURRENT." as comment,
				tb_regexp.regular_expression as `regexp`,
				group_concat(tb_value.uniq_name) as possible_values
			from form_field as tb_field
			left join cms_regexp as tb_regexp on tb_field.regexp_id = tb_regexp.id
			left join form_field_value as tb_value on tb_field.id = tb_value.field_id
			where tb_field.form_id = '$form[id]'
			group by tb_field.id
			order by tb_field.priority asc
		";
		$fields = $DB->query($query);
		
		/**
		 * Добавляем поля в форму
		 */
		reset($fields); 
		while (list(,$row) = each($fields)) { 
			$field = $this->addField(new FormField($row['type'], $row['uniq_name'], $row['title']));
			
			/**
			 * Добавляем ограничения на поле
			 */
			$field->setRegexp($row['regexp']);
			$field->setRequired($row['required']);
			$field->setMinValue($row['min_value'])->setMaxValue($row['max_value']);
			$field->setMinLength($row['min_length'])->setMaxLength($row['max_length']);
			$field->setDefaultValue($row['default_value']);
			$field->setPossibleValues($row['possible_values']);
			$field->setComment($row['comment']);
		}
		return $this;
	}
	
	/**
	 * Задает уникальное имя формы
	 * @param string $value
	 * @return Form
	 */
	public function setUniqName($value) {
		$this->uniq_name = $value;
		return $this;
	}
	
	/**
	 * Задает заголовок формы
	 * @param string $value
	 * @return Form
	 */
	public function setTitle($value) {
		$this->title = $value;
		return $this;
	}
	
	/**
	 * Задает значения, введенные в поля формы
	 * @param array $values
	 */
	public function populateValues($values) {
		reset($values); 
		while (list($field_name,$value) = each($values)) { 
			if ($this->hasField($field_name)) {
				$this->getField($field_name)->setValue($value);
			}
		}
	}
	
	/**
	 * Задает ошибку валидации значения поля формы
	 * @param string|FormField $field
	 * @param string $error
	 */
	protected function addError($field, $error) {
		$this->errors[$this->fieldUniqName($field)] = $error;
	}
	
	/**
	 * Возвращает список произошедших ошибок
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	public function getFieldError($field) {
		return $this->errors[$this->fieldUniqName($field)];
	}
	
	/**
	 * Производит валидацию введенных значений
	 * @return boolean
	 */
	public function validate() {
		reset($this->fields);
		while (list(,$field) = each($this->fields)) { 
			try {
				$field->validate();
			} catch (FormFillException $e) {
				$this->addError($field, $e->getMessage());
			}
		}
	}
	
	/**
	 * Возвращает очищенные значения полей формы
	 * @return array
	 */
	public function getCleanValues() {
		$return = array();
		
		reset($this->fields); 
		while (list(,$field) = each($this->fields)) { 
			$return[$field->getUniqName()] = $field->getCleanValue();
		}
		return $return;
	}
	
	/**
	 * Рендерит форму в шаблон
	 * @param string $template_file
	 */
	public function renderTemplate($template_file) {
		$Template = new Template($template_file);
		$Template->set('form', $this);
		
		reset($this->fields); 
		while (list(,$row) = each($this->fields)) { 
			$Template->iterate('/field/', null, array('field' => $row, 'form' => $this)); 
		}
		return $Template->display();
	}
	
	private $action = '';
	private $method = 'POST';
	private $return_path = '';
	private $error_path = '';
	
	/**
	 * Устанавливает action, обрабатывающий форму
	 * @param string $value
	 * @return Form
	 */
	public function setAction($value) {
		$this->action = $value;
		return $this;
	}
	
	/**
	 * Устанавливает метод отправки формы
	 * @param string $value
	 * @return Form
	 */
	public function setMethod($value) {
		$this->method = $value;
		return $this;
	}
	
	/**
	 * Устанавливает URL возврата для формы
	 * @param string $value
	 * @return Form
	 */
	public function setReturnPath($value) {
		$this->return_path = $value;
		return $this;
	}
	
	/**
	 * Устанавливает URL возврата по ошибке для формы
	 * @param string $value
	 * @return Form
	 */
	public function setErrorPath($value) {
		$this->error_path = $value;
		return $this;
	}
	
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function getReturnPath() {
		return $this->return_path;
	}
	
	public function getErrorPath() {
		return $this->error_path;
	}
	
	
	
	
/**
 * ====================================================================================================
 * Работа с полями
 * ====================================================================================================
 */

	/**
	 * Возвращает поле формы
	 * @param string $uniq_name
	 * @return FormField
	 * @throws FormGenericException
	 */
	public function getField($uniq_name) {
		if (isset($this->fields[$uniq_name])) {
			return $this->fields[$uniq_name];
		} else {
			throw new FormGenericException("Unknown field $uniq_name");
		}
	}
	
	/**
	 * Добавляет поле в форму
	 * @param FormField $field
	 * @return FormField
	 * @throws FormGenericException
	 */
	public function addField(FormField $field) {
		if ($this->hasField($field)) {
			throw new FormGenericException("Field {$field->getUniqName()} already exists in form");
		} else {
			$this->fields[$field->getUniqName()] = $field;
			$field->setForm($this);
		}
		return $field;
	}
	
	/**
	 * Удаляет поле из формы
	 * @param string|FormField $field
	 */
	public function removeField($field) {
		unset($this->fields[$this->fieldUniqName($field)]);
	}
	
	/**
	 * Содержит ли форма поле?
	 * @param string|FormField $field
	 * @return boolean
	 */
	public function hasField($field) {
		return array_key_exists($this->fieldUniqName($field), $this->fields);
	}
	
	/**
	 * Возвращает уникальное имя поля, для поддержки
	 * различных типов параметров фнукций класса
	 *
	 * @param string|FormField $field
	 * @return string
	 */
	protected function fieldUniqName($field) {
		if ($field instanceof FormField) {
			return $field->getUniqName();
		} else {
			return $field;
		}
	}
	
}


?>