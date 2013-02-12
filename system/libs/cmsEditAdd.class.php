<?php
/**
 * Класс обработки событий добавления рядов в БД
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Rudenko Ilya <rudenko@ukraine.com.ua>
 * @copyright Copyright 2004, Delta-X ltd.
 */

/**
 * Класс обработки событий добавления рядов в БД
 * @package CMS
 * @subpackage CMS
 */
Class cmsEditAdd {
	
	/**
	 * Тип события update или insert
	 * @var string
	 */
	public $action_type = '';
	
	/**
	 * В связи с тем, что система в свойство NEW при обновлении добавляет все 
	 * нехватающие поля из свойства OLD, для того, что б в запрос UPDATE поступали только те данные,
	 * которые реально пришли на обновление. Мы фиксируем поля, которые добавлены из свойства OLD в 
	 * этом массиве
	 *
	 * @var array
	 */
	private $got_from_old = array();
	
	/**
	 * Информация, которая поступает в скрипт / изменение информации
	 * @var array
	 */
	private $NEW = array();
	
	/**
	 * Значения, которые переданы как NULL
	 *
	 * @var array
	 */
	private $nulls = array();
	
	/**
	 * Старая информация о записи, которая редактируется
	 * @var array
	 */
	private $OLD = array();
	
	/**
	 * Информация о закачанных файлах
	 * @var array 
	 */
	private $uploads = array();
	
	/**
	 * Указывает, откуда пришёл запрос с edit или view класса
	 * Для класса view проверка правильности заполнения полей происходит только для тех полей, которые переданы на обновление
	 *
	 * @var string
	 */
	private $update_form = 'edit';
	
	/**
	 * Список полей, которые поступят в CVS
	 * @var array
	 */
	private $cvs = array();
	
	/**
	 * номер транзакции в CVS
	 *
	 * @var int
	 */
	private $cvs_transaction_id = 0;
	
	/**
	 * id ряда, который обновляется
	 * @var int
	 */
	private $row_id = 0;
	
	/**
	 * Соединение с БД
	 * @var object
	 */
	private $DBServer;
	
	/**
	 * Информация о колонках
	 * @var array
	 */
	private $fields = array();
	
	/**
	 * Информация о таблице
	 * @var array
	 */
	private $table = array();
	
	/**
	 * Массив через который передаются данные между add_pre и add_post
	 * или же для del_pre и del_post триггерами. Значения берутся и выставляются
	 * через $this->triggerGet() и $this->triggerSet()
	 * @var array
	 */
	private $trigger_data = array();
	
	/**
	 * Путь к файлам-триггерам
	 * @var string
	 */
	private $triggers_root = '';
	
	/**
	 * Поля типа UPLOAD
	 * @var array
	 */
	private $upload_fields = array();
	
	/**
	 * Путь к файлам, которые были временно сохранены, до того, как записи присвоен id
	 *
	 * @var string
	 */
	private $tmp_root = '';
	
	/**
	 * Значения полей ajax_select. Используется в том случае, когда пользователь вручную вводит имя не выбирая его из справочника
	 * и в скрипт передается пустой id
	 *
	 * @var array
	 */
	private $ajax_select = array();
	
	
	/**
	 * Конструктор класса
	 * 
	 * @param int $table_id уникальный номер таблицы в БД
	 * @param array $data информация, которую необходимо изменить
	 * @return object
	 */
	public function __construct($table_id, $data, $update_form, $tmp_dir, $ajax_select) {
		global $DB;
		
		$this->table = cmsTable::getInfoById($table_id);
		$this->triggers_root = TRIGGERS_ROOT . $this->table['triggers_dir'];
		$this->fields = cmsTable::getFields($table_id);
		$this->DBServer = db::factory($this->table['db_alias']);
		$this->tmp_root = is_dir(TMP_ROOT.$tmp_dir) ? TMP_ROOT.$tmp_dir : '';
		$this->ajax_select = $ajax_select;
				
		// Откуда поступило обновление
		$this->update_form = $update_form;
		
		// Устанавливаем id ряда, который поступил на обновление
		if (isset($data['id']) && !empty($data['id'])) {
			$this->row_id = $data['id'];
		}
		
		// Определяем тип события. Заменять это свойство нельзя на empty($this->row_id), так как после insert
		// в это свойство устанавливается значение last_inserted_id. После чего невозможно будет определить тип события 
		// insert или upodate
		$this->action_type = (!empty($this->row_id)) ? 'update' : 'insert';
		
		// Параметры, которые необходимо изменить в таблице
		$this->NEW = $this->buildNew($data);
		
		// Добавляет из OLD несуществующие в NEW поля
		if ($this->action_type['update']) {
			$this->OLD = $this->buildOld();
			$this->mergeOldNew();
		}
		
		/**
		 * Приведение типов данных в соответствие с типами данных в MSSQL
		 * 
		 * Не объеденять с нижней частью кода, так как если часть значений преобразована
		 * с массива в строку, а часть нет и при проверке regexp'ом произойдет ошибка,
		 * то данные будут неоднородными (часть массив, а часть строка), а при возврате нам нужны
		 * данные в такой форме, в которой они отсылались
		 * 
		 * 14/10/2008 Rudenko Ilya: Я всё-таки объеденил двацикла, в связи с тем, что данные возвращаются в скрипт с переменных
		 * $_POST и $_GET, а тут обрабатываются переменные уровня класса
		 * 
		 * 2. Проверка регулярным выражением введенных значений
		 * 3. Хеширование новых паролей
		 * 4. Проверка правильности указания дат
		 * 
		 */
		$regexp_error = false;
		reset($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			// Пропускаем виртуальные поля
			if (substr($field_name, 0, 1) == '_') {
				continue;
			}
			
			// Пропускаем hidden поля, для которых не всегда есть значения, например поле priority
			if (!isset($this->NEW[$field_name]) && !isset($this->nulls[$field_name])) {
				continue;
			}
			
			// Обрезаем пустые значения
			if (!is_array($this->NEW[$field_name])) {
				$this->NEW[$field_name] = trim($this->NEW[$field_name]);
			}
			
			// Проверка - пустое поле или нет
			if ($field['is_obligatory'] && empty($this->NEW[$field_name])) {
				// Передано пустое значение в поле, которое не должно быть пустым
				$_SESSION['cmsEditError'][$field['id']] = cms_message('CMS', 'Поле "%s" обязательное для заполнения.', $field['title']);
				$regexp_error = true;
			}
			
			 // Проверка регулярным выражением правильности ввода информации 
			if (!empty($this->NEW[$field_name])	&& !empty($field['regular_expression'])	&& !preg_match($field['regular_expression'], $this->NEW[$field_name])) {
				$query = "SELECT error_message_".LANGUAGE_CURRENT." FROM cms_regexp WHERE id='".$field['regexp_id']."'";
				$_SESSION['cmsEditError'][$field['id']] = str_replace('[[title]]', '<b>"'.$field['title'].'"</b>', $DB->result($query));
				$regexp_error = true;
			}
			
			// Не передаём на обработку NULL значения, должно идти после проверки на обязательность заполнения
			if (isset($this->nulls[$field_name]) && $field['is_nullable']) {
				continue;
			}
			
			if ($field['data_type'] == 'date') {
				
				// Преобразовываем формат даты в SQL
				$this->NEW[$field_name] = preg_replace("/^(\d+)\.(\d+)\.(\d+)$/", "\\3-\\2-\\1", $this->NEW[$field_name]); 
				
			} elseif ($field['data_type'] == 'datetime') {
				
				// Преобразовываем формат даты в SQL
				$this->NEW[$field_name] = preg_replace("/^(\d+)\.(\d+)\.(\d+)[\s\n\r\t]+(\d+):(\d+):(\d+)$/", "\\3-\\2-\\1 \\4:\\5:\\6", $this->NEW[$field_name]); 
				
			} elseif (is_array($this->NEW[$field_name]) && empty($field['fk_link_table_id'])) {
					
				// Преобразовываем массив в строку, если это не данные для внешнего ключа n:n
				$this->NEW[$field_name] = implode(',', $this->NEW[$field_name]);
					
			} elseif ($field['pilot_type'] == 'int' && empty($field['fk_link_table_id'])) {
				
				// Приведение типов к целочисленным значениям
				$this->NEW[$field_name] = (int)$this->NEW[$field_name];
				
			} elseif ($field['pilot_type'] == 'decimal') {

				// Для полей типа float, decimal, double, dec, numeric запятую заменяем на точку
				$this->NEW[$field_name] = str_replace(',', '.', $this->NEW[$field_name]);
			}

			/**
			 * Проверяем, был ли изменен пароль (для паролей md5)
			 */
			if (
				$field['field_type'] == 'passwd_md5'
				&& $this->NEW[$field_name] != ''
				&& $this->NEW[$field_name] == $this->NEW[$field_name.'_old_password']
			) {
				continue;
			}
			
			/**
			 * Хешируем новые пароли, захешированные пароли уже пропущены условием, которое находится сверху
			 */
			if ($field['field_type'] == 'passwd_md5') {
				$this->NEW[$field_name] = md5($this->NEW[$field_name]);
			}

		}
		
		/** 
		 * Если произошла ошибка при проверке данных регулярным выражением то 
		 * возвращаемся на страницу редактирования
		 */
		if ($this->update_form == 'view' && !empty($_SESSION['cmsEditError'])) {
			reset($_SESSION['cmsEditError']);
			while(list(,$row) = each($_SESSION['cmsEditError'])) {
				Action::setError($row);
			}
			unset($_SESSION['cmsEditError']);
		}
		
		if ($regexp_error == true) {
			Action::onError();
		}
		
	}
	
	/**
	 * Формирует значения для свойства $this->NEW 
	 *
	 */
	private function buildNew($data) {
		global $DB;
		
		/**
		 * Устанавливает значения для полей, которые не передают свои параметры,
		 * к примеру, если не ставить галочку, то ее значение - не передается
		 */
		if (isset($data['_dummie_fields_']) && is_array($data['_dummie_fields_'])) {
			$dummie = $data['_dummie_fields_'];
			reset($dummie);
			while(list($field, $value) = each($dummie)) {
				if (!isset($data[$field])) {
					$data[$field] = $value;
				}
			}
		}
		unset($data['_dummie_fields_']);
		
		/**
		 * Обработка полей ajax_select. Эта обработка нужна в тех случаях, когда пользователь напечатал вручную 
		 * данные и не кликнул по выпавшему окну
		 */
		reset($this->ajax_select);
		while (list($field,) = each($this->ajax_select)) {
			$value = reset($this->ajax_select[$field]);
			if (empty($data[$field])) {
				$query = "select table_name, fk_show_name from cms_table_static where id='".$this->fields[$field]['fk_table_id']."'";
				$fk_table = $DB->query_row($query);
				
				$query = "select id from `$fk_table[table_name]` where `$fk_table[fk_show_name]`='$value'";
				$data[$field] = $DB->result($query);
			}
		}
		
		/**
		 * Устанавливает значения типа NULL
		 */
		if (isset($data['_null_']) && is_array($data['_null_'])) {
			$this->nulls = $data['_null_'];
			reset($this->nulls); 
			while (list($field_name,) = each($this->nulls)) { 
				 $data[$field_name] = null;
			}
		}
		unset($data['_null_']);
		
		/**
		 * Обрабатывает поля таблицы, являющиеся формой для аплоада файлов
		 */
		$table_id = $this->table['id'];
		if (isset($_FILES[$table_id]['name']) && is_array($_FILES[$table_id]['name'])) {
			reset($_FILES[$table_id]['name']);
			while(list($field_name) = each($_FILES[$table_id]['name'])) {
				
				// Формируем массив uploads
				$this->uploads[$field_name] = array(
					'name' => $_FILES[$table_id]['name'][$field_name]['file'],
					'type' => $_FILES[$table_id]['type'][$field_name]['file'],
					'tmp_name' => $_FILES[$table_id]['tmp_name'][$field_name]['file'],
					'error' => $_FILES[$table_id]['error'][$field_name]['file'],
					'size' => $_FILES[$table_id]['size'][$field_name]['file'],
					'extension' => Uploads::getFileExtension($_FILES[$table_id]['name'][$field_name]['file'])
				);
				
				
				/**
				 * Определяем расширение файла, которое будет сохранено в БД
				 */
				if (isset($data[$field_name]['del']) && $data[$field_name]['del'] == 'true') {
					// Был установлен флаг - удалить файл
					$upload_file = Uploads::getFile($this->table['name'], $field_name, $this->row_id, $data[$field_name]['extension']);
					
					// Удаляем файл
					if (is_file($upload_file)) {
						unlink($upload_file);
						Action::setLog(cms_message('CMS', 'Удален привязанный к полю %s файл %s.', $field_name, Uploads::getURL($upload_file)));
					}
					$data[$field_name] = '';
				} elseif (!empty($this->uploads[$field_name]['extension'])) {
					// Закачан новый файл
					$data[$field_name] = $this->uploads[$field_name]['extension'];
				} else {
					// Никаких изменений, оставляем старое значение поля
					$data[$field_name] = $data[$field_name]['extension'];
				}


				/**
				 * При отправке пустых полей, данные из формы все равно передаются их надо удалять
				 * чтоб потом программа не пыталась закачать их
				 */
				if (empty($this->uploads[$field_name]['name'])) {
					unset($this->uploads[$field_name]);
				} elseif (!empty($this->uploads[$field_name]['error'])) {
					Action::setError(Uploads::check($this->uploads[$field_name]['error']));
				} else {
					Action::setLog(cms_message('CMS', 'Закачан файл %s.', $this->uploads[$field_name]['name']));
				}
			}
		}
		
		// Создает значение для пустых полей uniq_name
		if (isset($this->fields['uniq_name']) && empty($data['uniq_name']) && isset($data[$this->table['fk_show_name']]) && !empty($data[$this->table['fk_show_name']])) {
			$data['uniq_name'] = name2url($data[$this->table['fk_show_name']], $this->fields['uniq_name']['max_length']);
		}
		
		return $data;
	}
	
	/**
	 * Формирует значения для свойства $this->OLD
	 *
	 */
	private function buildOld() {
		global $DB;
		
		// Есть таблицы, в которых надо иметь возможность редактировать поле id
		// в таких таблицах при добавлении новой записи поле id является непустым
		// ососбенно это касается таблиц с БД или курсов валют, где id должен являтся специфическим ключом
		$query = "
			SELECT *
			FROM `".$this->table['db_name']."`.`".$this->table['name']."`
			WHERE id='".$this->row_id."'
		";
		$return = $this->DBServer->query_row($query);
		if ($this->DBServer->rows == 0) {
			$this->action_type = 'insert';
			return array();
		}
		
		/**
		 * Загружаем связи n:n
		 */
		reset($this->fields);
		while(list($field_name, $row) = each($this->fields)) {
			if (empty($row['fk_link_table_name'])) {
				continue;
			}
			
			// Определяем название колонок, которые содержат id записи в текущей таблице
			// и колонки, которая содержит значения
			$query = "
				SELECT tb_field.name
				FROM cms_field AS tb_field
				WHERE 
					tb_field.table_id='".$row['fk_link_table_id']."'
					AND tb_field.fk_table_id='".$this->table['id']."'
			";
			$where_field = $DB->result($query);
			$query = "
				SELECT tb_field.name
				FROM cms_field AS tb_field
				WHERE 
					tb_field.table_id='".$row['fk_link_table_id']."'
					AND tb_field.fk_table_id='".$row['fk_table_id']."'
			";
			$select_field = $DB->result($query);
			
			// Определяем имя БД, в которой находится таблица с внешними ключами
			$fk_link_db_alias = $DB->result("
				SELECT tb_db.alias
				FROM cms_db AS tb_db
				INNER JOIN cms_table AS tb_table ON tb_table.db_id=tb_db.id
				WHERE tb_table.id='".$row['fk_link_table_id']."'
			");
			
			$fk_link_db_name = db_config_constant("name", $fk_link_db_alias); 
			
			// Заправшиваем данные из таблицы, которая содержит связи
			$query = "
				SELECT `$select_field` AS id
				FROM `$fk_link_db_name`.`".$row['fk_link_table_name']."`
				WHERE `$where_field`='".$this->row_id."'
			";
			$return[$field_name] = $DB->fetch_column($query);
			
			unset($fk_link_db_name);
		}
		
		return $return;
	}
	
	/**
	 * Функция определяет добавляет в свойство NEW недостающие значения из свойства OLD,
	 * фиксируя при этом в свойстве $this->got_from_old поля, которые добавились в NEW
	 * из поля OLD
	 * 
	 * На обновление должны поступать все ряды, которые есть в таблице,
	 * так как если этого не сделать, то в триггерах, при групповом 
	 * обновлении, могут возникнуть ошибки
	 */
	private function mergeOldNew() {
		reset($this->OLD);
		while(list($field_name,$val) = each($this->OLD)) {
			if (!isset($this->NEW[$field_name]) && !isset($this->nulls[$field_name])) {
				$this->NEW[$field_name] = $val;
				$this->got_from_old[$field_name] = $field_name;
				// ставим флаг, который не даст повторно захешировать пароль
				if (isset($this->fields[$field_name]['field_type']) && $this->fields[$field_name]['field_type'] == 'passwd_md5') {
					$this->NEW[$field_name.'_old_password'] = $this->NEW[$field_name];
				}
			}
		}
	}
	
	/**
	 * Добавляет или изменяет поле в БД
	 * @param void
	 * @return int
	 */
	public function dbChange() {
		global $DB; // класс $DB используется в триггерах

		// Определяем, является ли редактируемая таблица рекурсивной
		$recursive = false;
		if ($this->table['id'] == $this->table['parent_table_id']) {
			$recursive = true;
		}
		
		/**
		 * До выполнения триггеров
		 * Определяем не пытается ли пользователь сделать редактируемый раздел собственным
		 * потомком
		 */
		if ($recursive && $this->action_type == 'update') {
			if (empty($this->table['relation_table_name'])) {
				trigger_error('Please define param "Optimisation table" for table `'.$this->table['name'].'`', E_USER_ERROR);
				exit;
			}
			$query = "
				SELECT id 
				FROM `".$this->table['relation_table_name']."`
				WHERE
					parent = '".$this->row_id."'
					AND id != '".$this->row_id."'
					AND id = '".$this->NEW[ $this->table['parent_field_name'] ]."'
			";
			$this->DBServer->query($query);
			if ($this->DBServer->rows > 0 || $this->row_id == $this->NEW[ $this->table['parent_field_name'] ]) {
				Action::onError(cms_message('CMS', 'Нельзя перемещать раздел в самого себя. Выберите другой родительский раздел.'));
			}
		}
		
		
		/**
		 * Триггеры предварительной обработки.
		 */
		if (is_file($this->triggers_root . $this->action_type . '_before.act.php')) {
			require($this->triggers_root . $this->action_type . '_before.act.php');
			Action::setLog('Выполняется триггер '.$this->action_type.' before');
		}
		 
		/**
		 * После выполнения триггеров.
		 * В случае, если были изменения в структуре, то удаляем старые связи
		 */
		if (
			$recursive &&
			$this->action_type == 'update' && 
			$this->NEW[ $this->table['parent_field_name'] ] != $this->OLD[ $this->table['parent_field_name'] ]
		) {
			$query = "CALL clean_relation('".$this->table['relation_table_name']."', '".$this->row_id."')";
			$DB->query($query);
		}
		
		/**
		 * Присваиваем полям таблицы их значения
		 */
		if ($this->action_type == 'insert') {
			$values = $this->buidInsertQuery();
		} else {
			$values = $this->buildUpdateQuery();
		}
		
		if (count($values) == 0) {
			return 0;
		}
		
		$this->checkDupFields();
		
		$query = "LOCK TABLES ".$this->table['name']." WRITE";
		$this->DBServer->query($query);
		
		if ($this->action_type == 'update') {
			
			$query = "UPDATE `".$this->table['db_name']."`.`".$this->table['name']."` SET ".implode(', ', $values)." WHERE id='".$this->row_id."'";
			$this->DBServer->update($query);
			Action::saveLog($query);
			if (IS_DEVELOPER) {
				Action::setLog(cms_message('CMS', '%s, обновлен ряд #%d', $this->table['title'], $this->row_id));
			}
			
		} else {
			
			$query = "INSERT INTO `".$this->table['db_name']."`.`".$this->table['name']."` (`".implode('`, `', array_keys($values))."`) VALUES (".implode(",", $values).")";
			$this->NEW['id'] = $this->row_id = $this->DBServer->insert($query);
			Action::saveLog($query.' insert_id='.$this->row_id);
			if (IS_DEVELOPER) {
				Action::setLog(cms_message('CMS', '%s, добавлен ряд #%d', $this->table['title'], $this->row_id));
			}
			
		}
		
		if (IS_DEVELOPER) {
			Action::setLog($query);
		}
		
		$this->DBServer->query("UNLOCK TABLES");
		
		/**
		 * При закачке файлов через swfUpload для новых записей, которых нет в таблице
		 * они размещаются в директории $this->tmp_root, после выполнения INSERT запроса
		 * мы получаем id и переносим их в соответствующую директорию
		 */
		if ($this->action_type == 'insert' && !empty($this->tmp_root)) {
			$fields = Filesystem::getDirContent($this->tmp_root, false, true, false);
			reset($fields);
			while (list(,$field) = each($fields)) {
				$files = Filesystem::getDirContent($this->tmp_root.$field, false, false, true);
				reset($files);
				while (list(,$file) = each($files)) {
					Filesystem::rename($this->tmp_root.$field.$file, UPLOADS_ROOT.strtolower($this->table['name'].".$field/".Uploads::getIdFileDir($this->NEW['id']).'/'.$file), true);
				}
			}
			Filesystem::delete($this->tmp_root);
		}
		
		/**
		 * Произошла ошибка при добавлении данных
		 */
		if ($this->DBServer->affected_rows == -1) {
			Action::setError(cms_message('CMS', 'Запрос не выполнен. SQL сервер вернул ошибку - %s.', $this->DBServer->error()));
			Action::onError();
		}
		
		/**
		 * Фиксируем изменения в CVS
		 */
		if ($this->table['use_cvs']) {
			// Создаём транзакцию
			$query = "
				insert into cvs_db_transaction (admin_id,table_id,event_type,row_id) 
				values ('".$_SESSION['auth']['id']."', '".$this->table['id']."', '".$this->action_type."', '".$this->row_id."')
			";
			$this->cvs_transaction_id = $DB->insert($query);
			reset($this->cvs); 
			while (list($field_name,$new_value) = each($this->cvs)) {
				// Определяем колонку, которая подойдёт для данного типа поля
				if (is_null($new_value)) {
					$pilot_type = 'null';
					$new_value = null;
				} else {
					$data_type = $this->fields[$field_name]['data_type'];
					$pilot_type = $this->fields[$field_name]['pilot_type'];
				}
				
				if ($this->action_type != 'insert' && $this->OLD[$field_name] == $new_value) {
					// Событие UPDATE фиксируем только те ряды, которые были изменены
					continue;
				}
				
				if (is_null($new_value)) {
					$new_value = 'true';
				}
				$field_language = (!empty($this->fields[ $field_name ]['field_language'])) ? "'".$this->fields[ $field_name ]['field_language']."'" : "NULL";
				
				$query = "
					insert into cvs_db_change (transaction_id, field_id, field_language, value_$pilot_type) 
					values ('".$this->cvs_transaction_id."', '".$this->fields[ $field_name ]['id']."', $field_language, '$new_value')
				";
				$DB->insert($query);
			}
		}
		
		/**
		 * Восстанавливаем структуру связей для рекурсивных таблиц
		 */
		if ($recursive) {
			if (empty($this->table['relation_table_name'])) {
				trigger_error("Please define in `cms_table` name of relation table for recursive table `".$this->table['name']."`", E_USER_ERROR);
			}
			do {
				$query = "CALL build_relation('".$this->table['name']."', '".$this->table['parent_field_name']."', '".$this->table['relation_table_name']."', @total_rows)";
				$DB->query($query);
				
				$query = "SELECT @total_rows";
				$total_rows = $DB->result($query);
			} while ($total_rows > 0);
		}
		
				
		/**
		 * В случае успешного добавления ряда в таблицу, перемещаем закачанные файлы и удаляем файлы из директории /i/
		 */
		if (is_array($this->uploads)) {
			reset($this->uploads);
			while(list($field, $val) = each($this->uploads)) {
				$extension = Uploads::getFileExtension($val['name']);
				$upload_file = Uploads::getFile($this->table['name'], $field, $this->row_id, $extension);
				
				// Перемещаем закачанный файл
				Uploads::moveUploadedFile($val['tmp_name'], $upload_file);
				
				// Удаляем пиктограммы из директории /i/
				$query = "select uniq_name from cms_image_size";
				$resized = $DB->fetch_column($query);
				reset($resized);
				while (list(,$uniq_name) = each($resized)) {
					$thumb = SITE_ROOT."i/$uniq_name/".$this->table['name']."/$field/".Uploads::getIdFileDir($this->row_id).'.'.$extension;
					if (is_file($thumb)) {
						unlink($thumb);
					}
				}
				
				
				// Указываем, новый путь к файлу, так как свойство может быть использовано в add_post скриптах
				$this->uploads[$field]['tmp_name'] = $upload_file;
			}
		}
		
		$this->updateFKey();
		
		// Обновляем значение поля URL
		if (isset($this->fields['uniq_name']) && isset($this->fields['url']) && !empty($this->table['relation_table_name'])) {
			$Structure = new Structure($this->table['table_name']);
			$parent_field_name = $this->table['parent_field_name'];
			if ($this->action_type == 'update' && ($this->NEW[$parent_field_name] != $this->OLD[$parent_field_name] || $this->NEW['uniq_name'] != $this->OLD['uniq_name'])) {
				$Structure->cleanURL($this->OLD['id']);
			}
			$Structure->updateURL();
			unset($Structure);
		}

		
		/**
		 * Триггеры POST обработки.
		 * Внимание! Особая обработка триггеров view! Если нет триггера view_after, 
		 * то будет выполнен триггер update_after, в ином случае выполняется view_after.
		 */
		if (is_file($this->triggers_root . $this->action_type . '_after.act.php')) {
			require($this->triggers_root . $this->action_type . '_after.act.php');
			Action::setLog('Выполняется триггер '.$this->action_type.' before');
		}
				

		// Обновляем поисковый индекс
		if (is_module('Search')) {
			Search::update($this->table['name'], $this->row_id);
		} 

		// Возвращаем номер последней вставленной записи
		return $this->row_id;
	}
	
	
	/**
	 * Определяем, какие поля необходимо вставлять в таблицу, это должны быть поля,
	 * для которых пришли данные, плюс поля следующего типа:
	 * 1. Которые не могут иметь DEFAULT_VALUE и им нельзя присвоить значение NULL (TEXT, BLOB)
	 * 2. Которые имеют тип timestamp
	 * 3. Которые содержат значение порядкового номера записи в данной категории (`priority`)
	 * @return array
	 */
	private function buidInsertQuery() {
		$value = array();   
		reset ($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			// Пропускаем не переданные поля, кроме тех, которые не имеют значения по умолчанию
			if (!isset($this->NEW[$field_name]) && $field['no_default_value']==0 && $field_name != 'priority') {
				continue;
			}
			
			// пропускаем спецполя
			if (!$field['is_real'] || $field['data_type'] == 'timestamp' || $field_name == 'id') {
				continue;
			}
			
			if ($field_name == 'priority') {
				// Значение для поля priority
				$value[$field_name] = $this->getNextPriorityId();
				$this->cvs[$field_name] = $value[$field_name];
				
			} elseif ((!isset($this->NEW[$field_name]) || empty($this->NEW[$field_name])) && !$field['is_nullable'] && $field['no_default_value']) {
				// Пустое значение для полей, которые не могут иметь default_value
				$value[$field_name] = "''";
				$this->cvs[$field_name] = '';
				
			} elseif ($field['is_nullable'] && isset($this->nulls[$field_name])) {
				// Поле передано как NULL
				$value[$field_name] = "NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif($field['data_type'] == 'timestamp') {
				// Значение для поля timestamp, взято будет значение по умолчанию (не всегда оно равно CURRENT_TIMESTAMP)
				$value[$field_name] = "NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif (isset($this->NEW[$field_name]) && (!empty($this->NEW[$field_name]) || strlen($this->NEW[$field_name]) != 0)) {
				// strlen добавлено из-за того, что при передаче значения 0 ставилось в таблице значение по умолчанию
				// Значение для непустого поля
				$value[$field_name] = "'".$this->NEW[$field_name]."'";
				$this->cvs[$field_name] = $this->NEW[$field_name];
				
			} elseif ($field['data_type'] == 'set') {
				// Значение для поля типа set
				$value[$field_name] = "''";
				$this->cvs[$field_name] = '';
				
			} else {
				// Для пустых значений используется значение в таблице по умолчанию, определённое в MySQL
				
			}
		}
		
		return $value;
	}
	
	/**
	 * Определяем, какие поля необходимо обновлять в таблице при update событии
	 * @return array
	 */
	private function buildUpdateQuery() {
		global $DB;
		$value = array();
		
		// Информация об индексах в таблице
		$query = "
			SELECT name, column_name, is_nullable, priority
			FROM cms_table_index
			WHERE table_id='".$this->table['id']."'
			ORDER BY name ASC, priority ASC
		";
		$keys = $DB->query($query);
		reset($keys); 
		while (list(,$row) = each($keys)) { 
			$uniq_key[$row['name']]['is_nullable'] = $row['is_nullable'];
			$uniq_key[$row['name']]['fields'][$row['priority']] = $row['column_name'];
			$uniq_column[$row['column_name']][] = $row['name'];
		}
		unset($keys);

		reset ($this->fields);
		while(list($field_name, $field) = each($this->fields)) {
			
			// Пропускаем поля, которые были добавлены из свойства $this->OLD
			// Пропускаем поле типа timestamp
			// Пропускаем поле id
			// Поле priority не обновляется
			// пропускаем поля, которые не существуют
			if (
				isset($this->got_from_old[$field_name]) || 
				$field['data_type'] == 'timestamp' ||
				$field['is_real'] == 0 || 
				$field_name == 'id' ||
				$field_name == 'priority' && $this->action_type == 'edit'
			) {
				continue;
			}
			
			if ($field['is_nullable'] && !empty($field['fk_table_id']) && empty($this->NEW[$field_name])) {
				// Для внешних ключей, которые поддерживают NULL, при передаче значения 0 ставим NULL
				$value[$field_name] = "`$field_name`=NULL";
				$this->cvs[$field_name] = NULL;
				
			} elseif ($field['is_nullable'] && isset($this->nulls[$field_name])) {
				// Поле передано как NULL
				$value[$field_name] = "`$field_name`=NULL";
				$this->cvs[$field_name] = NULL;
				
			} else if ($field['data_type'] == 'timestamp') {
				$value[$field_name] = "`$field_name`=CURRENT_TIMESTAMP";
				
			} else {
				$value[$field_name] = "`$field_name`='".$this->NEW[$field_name]."'";
				$this->cvs[$field_name] = $this->NEW[$field_name];
			}
		}
		return $value;
	}
	
	/**
	 * Определяем связи с другими таблицами через внешние ключи, 
	 * которые имеют соотноешение многие к многим (N:N)
	 * @return void
	 */
	private function updateFKey() {
		global $DB;
		reset($this->fields);
		while (list($field_name, $field) = each($this->fields)) {
			
			// пропускаем все колонки, которые не относятся к n:n ключам
			if (empty($field['fk_link_table_id'])) {
				continue;
			}
			
			// Пропускаем все колонки, которые не должны быть обновлены
			if (!isset($this->NEW[$field_name])) {
				continue;
			}
			
			$info = cmsTable::getFkeyNNInfo($field['table_id'], $field['fk_table_id'], $field['fk_link_table_id']);
			
			// удаляем старые значения
			$query = "DELETE FROM `$info[from_table]` WHERE `$info[where_field]`='$this->row_id'";
			$this->DBServer->delete($query);
			
			$fk = $cvs = array();
			if (is_array($this->NEW[$field_name])) {
				reset($this->NEW[$field_name]);
				while(list(,$val) = each($this->NEW[$field_name])) {
					$fk[] = "('".$this->row_id."', '$val')";
					$cvs[] = "('".$this->cvs_transaction_id."', '$field_name', '$val')";
				}
				if (!empty($fk)) {
					$query = "INSERT IGNORE INTO `$info[from_table]` (`$info[where_field]`, `$info[select_field]`) VALUES ".implode(",", $fk);
					$this->DBServer->insert($query);
					Action::setLog(cms_message('CMS', 'Обновлены внешние ключи для поля %s (%s)', $field_name, $this->DBServer->affected_rows));
				}
			}
			
			// Фиксируем изменения в CVS
			if (!empty($this->cvs_transaction_id) && empty($cvs)) {
				// Пользователь убрал все значения внешнего ключа
				$query = "insert into cvs_db_fkey (transaction_id, field_name, fkey_id) values ('".$this->cvs_transaction_id."', '$field_name', NULL)";
				$DB->insert($query);
			} elseif (!empty($this->cvs_transaction_id)) {
				$query = "insert into cvs_db_fkey (transaction_id, field_name, fkey_id) values ".implode(",", $cvs);
				$DB->insert($query);
			}
		}
	}
	
	/**
	* Осуществляет проверку на дублирование unique записей
	* @return array
	*/
	private function checkDupFields() {
		global $DB;
		
		$query = "
			select name, group_concat(column_name order by priority asc) as columns
			from cms_table_index as tb_index
			where tb_index.table_id='".$this->table['id']."'
			group by name
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$columns = preg_split("/,/", $row['columns'], -1, PREG_SPLIT_NO_EMPTY);
			$where = $error = array();
			reset($columns);
			while (list(,$column) = each($columns)) {
				$where[] = (isset($this->NEW[$column])) ? "`$column`='".$this->NEW[$column]."'": "`$column`=''";
				$error[] = $this->fields[$column]['title'];
			}
			
			$query = "
				SELECT * 
				FROM `".$this->table['db_name']."`.`".$this->table['name']."` 
				WHERE ".implode(" AND ", $where)."
			";
			$query .= ($this->action_type == 'update') ? " AND id!='".$this->row_id."'" : '';
			$result = $this->DBServer->query($query);
			if ($this->DBServer->rows > 0) {
				// Определяем названия колонок
				Action::setError(cms_message('CMS', 'Вы пытаетесь вставить дублирующуюся запись. Проверьте правильность заполнения полей "%s".', implode('", "', $error)));
				Action::onError();
			}
		}
	}
	
	/**
	* Определяет последний id в колонке priority в заданном разделе
	* @param void
	* @return int
	*/
	private function getNextPriorityId() {
		$query = "SELECT IFNULL(MAX(priority) + 1, 1) AS next_priority FROM `".$this->table['db_name']."`.`".$this->table['name']."`";
		if (!empty($this->table['parent_field_name']) && isset($this->NEW[$this->table['parent_field_name']])) {
			$query .= " WHERE ".$this->table['parent_field_name']."='".$this->NEW[$this->table['parent_field_name']]."'";
		}
		return $this->DBServer->result($query, 1);
	}
}

?>