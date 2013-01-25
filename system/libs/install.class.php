<?php
/** 
 * Класс конфигурирования и установки системы
 * @package Pilot
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

class Install {
	
	/**
	 * Смена базы данных install.php
	 *
	 */
	public static function changeDB($db_host, $db_login, $db_password, $db_name, &$error_message) {
		global $DB;  
		
		$link = mysqli_connect($db_host, $db_login, $db_password, $db_name);
		$error_message = mysqli_connect_error();
		
		if (!is_object($link) || !empty($error_message)) {
			// Ошибка подключения к БД
			return false;
		}
		
		// Соединение с БД прошло успешно, вносим изменения
//		$query = "
//			update cms_db
//			set
//				host='$db_host',
//				name='$db_name',
//				login='$db_login', 
//				passwd='$db_password'
//			where alias='default'
//		";
//		$result = mysqli_query($link, $query);
//		if ($result != true) {
//			// Ошибка выполнения запроса
//			$error_message = 'Невозможно обновить информацию';
//			return false;
//		}
		
		// Обновляем конфигурационные файлы
		define('DB_NEWINSTALL_HOST', $db_host);
		define('DB_NEWINSTALL_NAME', $db_name);
		define('DB_NEWINSTALL_LOGIN', $db_login);
		define('DB_NEWINSTALL_PASSWORD', $db_password);
		define('DB_NEWINSTALL_TYPE', 'mysqli');
		$DB = DB::factory('newinstall');
		
		// Очистить таблицы cms_schema_* и построить новую структуру БД
		$DB->query("truncate cms_table_static");
		$DB->query("truncate cms_field_static");
		
		$cmsDB = new cmsDB(array('id' => 1, 'alias' => 'newinstall', 'name' => $db_name));
		$cmsDB->updateDB();
		$cmsDB->buildTableStatic();
		$cmsDB->buildFieldStatic();
		$cmsDB->checkAllTables();
		return true;
	}
	
	
	/**
	 * После формирования дистрибутива программы необходимо удалить ключи и пароли.
	 * После выполнения метода необходимо выполнить либо updateMyConfig или buildConfig
	 *
	 */
	public static function cleanConfig() {
		global $DB;
		
		// Перед созданием конфигурации - генерируем уникальный ключ для шифрования кроссдоменной авторизации
		$key = Misc::randomKey(16, 'qwertyuiop[]\;lkjhgfdsazxcvbnm,./`1234567890-=+_)(*&^%$#@!~QWERTYUIOP{}|:LKJHGFDSAZXCVBNM<>?');
		$DB->update("update cms_settings set value = '".$DB->escape($key)."' where name = 'auth_cross_domain_auth_key'");
		
		// Генерируем уникальный ключ для доступа к экспорту биллинга
		$key = Misc::randomKey(16);
		$DB->update("update cms_settings set value = '".$DB->escape($key)."' where name = 'billing_export_access_key'");
		
		// Очищаем параметры системы
		$DB->update("update cms_settings set value = '' where name in ('gsm_gateway_access_code', 'gsm_gateway_client_id')");
	}
	
	
	/**
	 * Формируем конфигурационныый PHP файл cache/config.inc.php
	 * 
	 * @return string
	 */
	public static function buildConfig() {
		global $DB;
		
		// Общие параметры
		$query = "
			select
				tb_settings.name as name,
				tb_settings.value
			from cms_settings as tb_settings
			inner join cms_module as tb_module on tb_module.id=tb_settings.module_id
			where type != 'devider'
			order by tb_module.name asc, tb_settings.name asc
		";
		$config = $DB->fetch_column($query);
		
		// Регулярные выражения
		$query = "select concat('valid_', uniq_name), regular_expression from cms_regexp";
		$data = $DB->fetch_column($query);
		$config = array_merge($config, $data);
		
		// Параметры БД
//		$query = "select *, upper(alias) as alias from cms_db order by id asc";
//		$data = $DB->query($query);
//		 
//		reset($data); 
//		while (list(,$row) = each($data)) {
//			$config["DB_$row[alias]_NAME"] = $row['name'];
//			$config["DB_$row[alias]_HOST"] = $row['host'];
//			$config["DB_$row[alias]_LOGIN"] = $row['login'];
//			$config["DB_$row[alias]_PASSWORD"] = $row['passwd'];
//			$config["DB_$row[alias]_TYPE"] = $row['type'];
//		}
		
		// 3. Языки
		$query = "select code from cms_language";
		$data = $DB->fetch_column($query);
		$config["LANGUAGE_AVAILABLE"] = implode(",", $data);
		$config["LANGUAGE_REGEXP"] = "(?:".implode("|", $data).")";
		$config["LANGUAGE_REGEXP_SQL"] = "(".implode("|", $data).")";
		
		// 4. Интерфейсы
		$query = "
			select
				tb_interface.name as interface,
				tb_language.code as default_language,
				group_concat(tb_all.code separator ',') as all_languages
			from cms_interface as tb_interface
			inner join cms_language as tb_language on tb_language.id=tb_interface.default_language
			inner join cms_language_usage as tb_usage on tb_usage.interface_id=tb_interface.id
			inner join cms_language as tb_all on tb_all.id=tb_usage.language_id
			group by tb_interface.id
		";
		$data = $DB->query($query);
		reset($data); 
		while (list(,$row) = each($data)) { 
			$config["LANGUAGE_$row[interface]_DEFAULT"] = $row['default_language'];
			$config["LANGUAGE_$row[interface]_AVAILABLE"] = $row['all_languages'];
			$config["LANGUAGE_$row[interface]_REGEXP"] = "(?:".str_replace(",", "|", $row['all_languages']).")";
			$config["LANGUAGE_$row[interface]_REGEXP_SQL"] = "(".str_replace(",", "|", $row['all_languages']).")";
		}
		
		// 5. Платежные системы модуля Бухгалтерия
		if (is_module('billing')) {
			$query = "select concat('BILLING_SYSTEM_', uniq_name), id from billing_payment_system";
			$data = $DB->fetch_column($query);
			$config = array_merge($config, $data);
		}
		
		$text = "<?php\n";
		
		// 6. Список доменов и их алиасов, которые есть в системе
		$query = "
			select tb_alias.url, tb_site.auth_group_id
			from site_structure_site_alias as tb_alias
			inner join site_structure_site as tb_site on tb_site.id=tb_alias.site_id
		";
		$data = $DB->fetch_column($query);
		$text .= '$_cms_auth_group = array(';
		reset($data);
		while (list($key, $value) = each($data)) {
			$text .= "'$key' => '$value',";
		}
		$text .= ');';

		reset($config); 
		while (list($key, $val) = each($config)) {
			$text .= (is_numeric($val)) ? "define('".strtoupper($key)."', ".intval($val).");\n": "define('".strtoupper($key)."', '".addcslashes($val, "'")."');\n";
		}
		$text .= "?>";
		return $text;
	}
	
	/**
	 * Формирует конфигурационный файл для каждого языка
	 *
	 * @param string $language
	 * @return string
	 */
	public static function buildLanguageConfig($language) {
		global $DB;
		
		$query = "SELECT * FROM cms_language WHERE code='$language'";
		$info = $DB->query_row($query);
		
		$month_nom = preg_split("/[\s\n\r\t,]+/", $info['month_nom'], -1, PREG_SPLIT_NO_EMPTY);
		$month_gen = preg_split("/[\s\n\r\t,]+/", $info['month_gen'], -1, PREG_SPLIT_NO_EMPTY);
		$weekday_full = preg_split("/[\s\n\r\t,]+/", $info['weekday_full'], -1, PREG_SPLIT_NO_EMPTY);
		$weekday_short = preg_split("/[\s\n\r\t,]+/", $info['weekday_short'], -1, PREG_SPLIT_NO_EMPTY);
		
		$config = array();
		$config['LANGUAGE_CHARSET'] = $info['charset'];
		$config['LANGUAGE_TIME'] = $info['time_php'];
		$config['LANGUAGE_TIME_SQL'] = $info['time_sql'];
		$config['LANGUAGE_DATE'] = $info['date_php'];
		$config['LANGUAGE_DATE_SQL'] = $info['date_sql'];
		$config['LANGUAGE_DATETIME'] = $info['datetime_php'];
		$config['LANGUAGE_DATETIME_SQL'] = $info['datetime_sql'];
		$config['LANGUAGE_YEAR_NOM'] = $info['year_nom'];
		$config['LANGUAGE_YEAR_GEN'] = $info['year_gen'];
		
		// Названия месяцев в именительном и родительном падеже
		$config['LANGUAGE_MONTH_NOM_SQL'] = '';
		$config['LANGUAGE_MONTH_GEN_SQL'] = '';
		reset($month_nom);
		while (list($index, ) = each($month_nom)) {
			$month_index = $index + 1;
			$config['LANGUAGE_MONTH_NOM_'.$month_index] = $month_nom[$index];
			$config['LANGUAGE_MONTH_GEN_'.$month_index] = $month_gen[$index];
			$config['LANGUAGE_MONTH_NOM_SQL'] .= " WHEN $month_index THEN '".$month_nom[$index]."' ";
			$config['LANGUAGE_MONTH_GEN_SQL'] .= " WHEN $month_index THEN '".$month_gen[$index]."' ";
		}
		
		// Дни недели
		$config['LANGUAGE_WEEKDAY_SQL'] = '';
		$config['LANGUAGE_DAYOFWEEK_SQL'] = '';
		$config['LANGUAGE_WEEKDAY_SQL_SHORT'] = '';
		$config['LANGUAGE_DAYOFWEEK_SQL_SHORT'] = '';
		reset($weekday_full); 
		while (list($index,) = each($weekday_full)) {
			$config['LANGUAGE_WEEKDAY_'.$index] = $weekday_full[$index];
			$config['LANGUAGE_WEEKDAY_SHORT_'.$index] = $weekday_short[$index];
			$config['LANGUAGE_DAYOFWEEK_SQL'] .= " WHEN $index THEN '".$weekday_full[$index]."' ";
			$config['LANGUAGE_DAYOFWEEK_SQL_SHORT'] .= " WHEN $index THEN '".$weekday_short[$index]."' ";
			
			if ($index == 0) {
				$index = 6;
			} else {
				$index--;
			}
			$config['LANGUAGE_WEEKDAY_SQL'] .= " WHEN $index THEN '".$weekday_full[$index]."' ";
			$config['LANGUAGE_WEEKDAY_SQL_SHORT'] .= " WHEN $index THEN '".$weekday_short[$index]."' ";
		}
		
		$text = "<?php\n";
		reset($config); 
		while (list($key, $val) = each($config)) { 
			 $text .= "define('$key', '".addcslashes($val, "'")."');\n";
		}
		$text .= "?>";
		return $text;
	}
	
	/**
	 * Обновляет конфигурацию системы, My - обозначает что файлы будут сохранены в CACHE,
	 * иногда используются методы данного класса для построения конфига дистрибутива программы
	 *
	 */
	public static function updateMyConfig() {
		global $DB;
		
		// Переносим картинки в административный интерфейс
		$query = "SELECT id, code, file FROM cms_language";
		$language = $DB->query($query);
		reset($language);
		while(list(,$row) = each($language)) {
			$file = Uploads::getFile('cms_language', 'file', $row['id'], $row['file']);
			if (!is_file($file)) continue;
			copy($file, SITE_ROOT.'design/cms/img/language/'.$row['code'].'.gif');
		}
		
		// Формируем конфигурационныый PHP файл
		$text = self::buildConfig();
		file_put_contents(CACHE_ROOT.'config.inc.php', $text);
		
		
		// Формируем языковые данные
		$query = "SELECT code FROM cms_language";
		$data = $DB->fetch_column($query);
		reset($data); 
		while (list(,$language) = each($data)) {
			$text = self::buildLanguageConfig($language);
			file_put_contents(CACHE_ROOT.'language.'.$language.'.php', $text);
		}

	}
	
}