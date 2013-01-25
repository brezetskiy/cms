<?php
/** 
 * Класс, представляющий поле формы 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

class FormField {
	
	/**
	 * Имя поля (уникальное, id)
	 * @var string
	 */
	private $uniq_name = '';
	
	/**
	 * Заголовок поля (подпись, label)
	 * @var string
	 */
	private $title = '';
	
	/**
	 * Тип поля
	 * @var string
	 */
	private $type = '';
	
	/**
	 * Префикс для образования id поля. Используется, чтобы
	 * была возможность корректной работы на одной странице 
	 * нескольких форм, имеющих одинаковые id полей 
	 *
	 * @var string
	 */
	private $prefix = null;
	
	/**
	 * Constraints
	 */
	private $min_value = null;
	private $max_value = null;
	private $min_length = null;
	private $max_length = null;
	private $regexp = null;
	private $required = false;
	private $comment = '';
	
	/**
	 * Значение поля по умолчанию
	 * @var mixed
	 */
	private $default_value = null;
	
	/**
	 * Введенное значение поля
	 * @var mixed
	 */
	private $value = null;
	
	/**
	 * Для перечисляемых типов - содержит возможные значения поля
	 * @var array
	 */
	private $possible_values = array();
	
	/**
	 * Callbacks для валидации значения поля
	 * @var array
	 */
	private $validation_callbacks = array();
	
	/**
	 * Callback для очистки значения поля, заменяющий стандартную обработку
	 * @var callback
	 */
	private $cleaner_callback = null;
	
	/**
	 * Ссылка на форму, в которой расположено поле. Используется для того, чтобы
	 * не было необходимости ссылаться и на форму и на поле там, где можно использовать только поле
	 * @var Form
	 */
	private $form = null;
	
	/**
	 * Типы полей
	 */
	const TYPE_TEXT 		= 'text';
	const TYPE_PASSWD 		= 'passwd';
	const TYPE_FILE 		= 'file';
	const TYPE_SWF_UPLOAD 	= 'swf_upload';
	const TYPE_ENUM		 	= 'enum';
	const TYPE_SET		 	= 'set';
	const TYPE_TEXTAREA	 	= 'textarea';
	const TYPE_INTEGER	 	= 'integer';
	const TYPE_DECIMAL	 	= 'decimal';
	
	/**
	 * Конструктор класса
	 * @return FormField
	 */
	public function __construct($type, $uniq_name, $title) {
		$this->setUniqName($uniq_name);
		$this->setType($type);
		$this->setTitle($title);
	}
	
	/**
	 * Производит валидацию значения, введенного в поле
	 * @throws FormGenericException
	 * @throws FormFillException
	 */
	public function validate() {
		
		$value = $this->getCleanValue();
		
		/**
		 * Required?
		 */
		if ($this->required && empty($value)) {
			throw new FormFillException(cms_message('cms', 'Это поле необходимо заполнить'));
		}
		
		/**
		 * For Integers: check min/max value
		 */
		if (in_array($this->type, array(self::TYPE_DECIMAL, self::TYPE_INTEGER))) {
			if ($this->min_value !== null && $value < $this->min_value) {
				throw new FormFillException($this->formatIntervalErrorMessage());
			} elseif ($this->max_value !== null && $value > $this->max_value) {
				throw new FormFillException($this->formatIntervalErrorMessage());
			}
		}
		
		/**
		 * Check min/max length
		 */
		if (in_array($this->type, array(self::TYPE_DECIMAL, self::TYPE_INTEGER, self::TYPE_PASSWD, self::TYPE_TEXT, self::TYPE_TEXTAREA))) {
			if ($this->min_length !== null && strlen($value) < $this->min_length) {
				throw new FormFillException($this->formatLengthErrorMessage());
			} elseif ($this->max_length !== null && strlen($value) > $this->max_length) {
				throw new FormFillException($this->formatLengthErrorMessage());
			}
		}
		
		/**
		 * Check regexp
		 */
		if (!empty($this->regexp)) {
			$preg_result = @preg_match($this->regexp, $this->value);
			if ($preg_result === false) {
				throw new FormGenericException("Bad regular expression for field {$this->getUniqName()}");
			}
		}
		
		/**
		 * Проверка колбэками
		 */
		reset($this->validation_callbacks); 
		while (list(,$callback) = each($this->validation_callbacks)) { 
			$callback_result = call_user_func_array($callback, array($value));
			if (!empty($callback_result)) {
				throw new FormFillException($callback_result);
			}
		}
		
	}
	
	/**
	 * Добавляет callback для валидации значения поля
	 * Callback должен возвращать пустое значение, если проверка прошла успешно
	 * и строку с ошибкой, если она произошла
	 * 
	 * @param callback $callback
	 * @return FormField
	 * @throws FormGenericException
	 */
	public function addValidatorCallback($callback) {
		if (is_callable($callback)) {
			$this->validation_callbacks[] = $callback;
			return $this;
		} else {
			throw new FormGenericException("Bad validation callback: $callback");
		}
	}
	
	/**
	 * Формирует сообщение об ошибке - непопадание в интервал
	 * @return string
	 */
	protected function formatIntervalErrorMessage() {
		if ($this->min_value !== null) {
			if ($this->max_value !== null) {
				return cms_message('cms', "Введите число от %s до %s", $this->min_value, $this->max_value);
			} else {
				return cms_message('cms', "Введите число больше %s", $this->min_value);
			}
		} else {
			return cms_message('cms', "Введите число до %s", $this->max_value);
		}
	}
	
	/**
	 * Формирует сообщение об ошибке - некорретный размер данных
	 * @return string
	 */
	protected function formatLengthErrorMessage() {
		if ($this->min_length !== null) {
			if ($this->max_length !== null) {
				if ($this->min_length == $this->max_length) {
					return cms_message('cms', "Введите значение длиной %s символов", $this->min_length);
				} else {
					return cms_message('cms', "Введите значение от %s до %s символов", $this->min_length, $this->max_length);
				}
			} else {
				return cms_message('cms', "Введите не менее %s символов", $this->min_length);
			}
		} else {
			return cms_message('cms', "Введите не более %s символов", $this->max_length);
		}
	}
	
	/**
	 * Задает callback, который будет проводить очистку значения в поле
	 * @param callback $callback
	 * @return FormField
	 * @throws FormGenericException
	 */
	public function setCleanerCallback($callback) {
		if (is_callable($callback)) {
			$this->cleaner_callback = $callback;
			return $this;
		} else {
			throw new FormGenericException("Bad cleaner callback: $callback");
		}
	}
	
	/**
	 * Восстанавливает очистку поля по умолчанию
	 * @return FormField
	 */
	public function removeCleanerCallback() {
		$this->cleaner_callback = null;
		return $this;
	}
	
	/**
	 * Возвращает "очищенное" значение поля согласно его типу
	 * В случае неподходящего типа введенного значения возвращается
	 * значение по умолчанию
	 * 
	 * @return mixed
	 */
	public function getCleanValue() {
		
		/**
		 * Для полей можно назначать специфические функции очистки значения
		 */
		if (is_callable($this->cleaner_callback)) {
			return call_user_func_array($this->cleaner_callback, array($this->value));
		}
		
		if ($this->type == self::TYPE_DECIMAL) {
			/**
			 * 1. DECIMAL
			 */
			if (!is_scalar($this->value)) {
				return $this->default_value;
			}
			return floatval($this->value);
		} elseif ($this->type == self::TYPE_ENUM) {
			/**
			 * 2. ENUM
			 */
			if (!is_scalar($this->value)) {
				return $this->default_value;
			}
			if (!in_array($this->value, $this->possible_values)) {
				return $this->default_value;
			}
			return $this->value;
		} elseif ($this->type == self::TYPE_FILE) {
			/**
			 * 3. FILE (обычная загрузка файла)
			 */
			
			$default_file = array(
				'name' => '',
				'type' => '',
				'tmp_name' => '',
				'error' => UPLOAD_ERR_NO_FILE,
				'size' => 0
			);
			
			$value = globalVar($_FILES[$this->getUniqName()], array());
			
			reset($default_file); 
			while (list($key,) = each($default_file)) { 
				if (!array_key_exists($key, $value)) {
					return $default_file;
				}
			}
			
			if (!is_uploaded_file($value['tmp_name'])) {
				return $default_file;
			}
			
			return $value;
		} elseif ($this->type == self::TYPE_INTEGER) {
			/**
			 * 4. INTEGER
			 */
			$value = intval($this->value);
			if ($value != $this->value) {
				return $this->default_value;
			}
			return $value;
		} elseif ($this->type == self::TYPE_PASSWD) {
			/**
			 * 5. PASSWD
			 */
			return $this->value;
		} elseif ($this->type == self::TYPE_SET) {
			/**
			 * 6. SET
			 */
			if (!is_array($this->value)) {
				return $this->default_value;
			}
			
			$set_values = array();
			reset($this->value); 
			while (list(,$row) = each($this->value)) { 
				if (in_array($row, $this->possible_values)) {
					$set_values[] = $row;
				}
			}
			return $set_values;
		} elseif ($this->type == self::TYPE_SWF_UPLOAD) {
			/**
			 * 7. SWF UPLOAD
			 * todo: как обрабатывать swf uploads ???
			 */
			return $this->value;
		} elseif ($this->type == self::TYPE_TEXT) {
			/**
			 * 8. TEXT
			 */
			return $this->value;
		} elseif ($this->type == self::TYPE_TEXTAREA) {
			/**
			 * 9. TEXTAREA
			 */
			return $this->value;
		} else {
			throw new FormGenericException("Unknown field type: {$this->type}");
		}
	}
	
	/**
	 * Задает возможные значения для перечисляемых полей (enum, set)
	 * @param string|array $values Массив или строка, разделенная запятыми
	 */
	public function setPossibleValues($values) {
		if (!is_array($values)) {
			$values = preg_split('~[\s\t\n\r,]+~', $values, -1, PREG_SPLIT_NO_EMPTY);
		}
		$this->possible_values = $values;
	}
	
	/**
	 * Устанавливает минимальное значение для числового поля
	 * @param int $value
	 * @return FormField
	 */
	public function setMinValue($value) {
		$this->min_value = (int)$value;
		return $this;
	}
	
	/**
	 * Устанавливает максимальное значение для числового поля
	 * @param int $value
	 * @return FormField
	 */
	public function setMaxValue($value) {
		$this->max_value = (int)$value;
		return $this;
	}
	
	/**
	 * Устанавливает минимальную длину для текстового поля
	 * @param int $value
	 * @return FormField
	 */
	public function setMinLength($value) {
		$this->min_length = (int)$value;
		return $this;
	}
	
	/**
	 * Устанавливает максимальную длину для текстового поля
	 * @param int $value
	 * @return FormField
	 */
	public function setMaxLength($value) {
		$this->max_length = (int)$value;
		return $this;
	}
	
	/**
	 * Устанавливает ограничение в виде регулярного выражения
	 * @param string $value
	 * @return FormField
	 */
	public function setRegexp($value) {
		$this->regexp = $value;
		return $this;
	}
	
	/**
	 * Устанавливает обязательность заполнения поля
	 * @param bool $value
	 * @return FormField
	 */
	public function setRequired($value) {
		$this->required = (bool)$value;
		return $this;
	}
	
	/**
	 * Устанавливает уникальное имя поля
	 * @param string $value
	 * @return FormField
	 */
	protected function setUniqName($value) {
		$this->uniq_name = $value;
		return $this;
	}
	
	/**
	 * Устанавливает заголовок поля
	 * @param string $value
	 * @return FormField
	 */
	public function setTitle($value) {
		$this->title = $value;
		return $this;
	}
	
	/**
	 * Устанавливает тип поля
	 * @param string $value
	 * @return FormField
	 */
	public function setType($value) {
		$this->type = $value;
		return $this;
	}
	
	/**
	 * Задает значение по умолчанию для поля
	 * @param string $value
	 * @return FormField
	 */
	public function setDefaultValue($value) {
		$this->default_value = $value;
		return $this;
	}
	
	/**
	 * Задает введенное значение для поля
	 * @param mixed $value
	 * @return FormField
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * Задает префикс для формирования id поля
	 * @param string $value
	 * @return FormField
	 */
	public function setPrefix($value) {
		$this->prefix = $value;
		return $this;
	}
	
	public function setComment($value) {
		$this->comment = $value;
		return $this;
	}
	
	/**
	 * Привязывает элемент к форме
	 * @param Form $form
	 */
	public function setForm(Form $form) {
		$this->form = $form;
		return $this;
	}
	
	/**
	 * Возвращает id HTML элемента
	 * @return string
	 */
	public function getId() {
		if (empty($this->prefix)) {
			return $this->getUniqName();
		} else {
			return $this->prefix.'_'.$this->getUniqName();
		}
	}
	
	/**
	 * Возвращает имя поля (другими словами - title, label)
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	/**
	 * Возвращает флаг - обязательное ли для заполнения это поле?
	 * @return boolean
	 */
	public function isRequired() {
		return $this->required;
	}
	
	/**
	 * Возвращает уникальное имя элемента формы
	 * @return string
	 */
	public function getUniqName() {
		return $this->uniq_name;
	}
	
/**
 * ====================================================================================
 * Рендеринг полей в HTML
 * ====================================================================================
 */
	
	/**
	 * Возвращает HTML-представление элемента
	 * @return string
	 */
	public function render() {
		if ($this->type == self::TYPE_TEXT) {
			return $this->renderText();
		}
	}
	
	/**
	 * Рендеринт поля типа "text" (TYPE_TEXT)
	 * @return string
	 */
	protected function renderText() {
		return "<input type='text' name='{$this->uniq_name}' id='{$this->getId()}'>";
	}
}

?>