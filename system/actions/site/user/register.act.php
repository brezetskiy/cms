<?php
/** 
 * Сохранение параметров пользователя 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

/**
 * Получаем данные
 */ 
$user_email 		   = strtolower(trim(globalVar($_POST['user_email'], '')));
$user_password 		   = trim(globalVar($_POST['user_password'], ''));
$user_password_confirm = trim(globalVar($_POST['user_password_confirm'], ''));
$user_group 		   = trim(globalVar($_POST['user_group'], 0));

$user_phone			   = globalVar($_POST['user_phone'], '');

$values 			   = globalVar($_POST['param'], array());
$current_params 	   = globalVar($_POST['current_params'], "");
$current_params 	   = explode(",", $current_params);

$auto_login 		   = globalEnum($_POST['auto_login'], array('true', 'false'));
$send_sms_notify 	   = globalEnum($_POST['send_sms_notify'], array('true', 'false'));
$send_confirmation     = globalEnum($_POST['send_confirmation'], array('true', 'false'));

$register_ip 		   = constant('HTTP_IP');
$register_local_ip     = constant('HTTP_LOCAL_IP');

$cookie_referer 	   = substr(globalVar($_COOKIE['referer'], ''), 0, 255);
$cookie_refered_page   = substr(globalVar($_COOKIE['refered_page'], ''), 0, 255);

/**
 * Проверяем правильность переданных данных
 */
if (!empty($user_group) && empty($current_params)) {
	// Передан пустой массив параметров группы
	Action::onError(cms_message('CMS', 'Передан пустой массив параметров группы.'));
} 
if (empty($user_email)) {
	// Не указан e-mail адрес
	Action::onError(cms_message('CMS', 'Не указан e-mail адрес'));
} 
if (!preg_match(VALID_EMAIL, $user_email) && !empty($user_email)) {
	// Неправильно указан e-mail адрес
	Action::onError(cms_message('CMS', 'Неправильно указан e-mail адрес'));
}  
if (!preg_match(VALID_PASSWD, $user_password)) {  
	// Неправильно указан пароль, можно использовать только латинские буквы, цифры, знак подчеркивания и символы +!@#$%^&*~()-
	Action::onError(cms_message('CMS', 'Неправильно указан пароль, можно использовать только латинские буквы, цифры, знак подчеркивания и символы +!@#$%^&*~()-'));	
} 
if ($user_password != $user_password_confirm) {
	// Введенные пароли - не совпадают
	Action::onError(cms_message('CMS', 'Введенные пароли - не совпадают'));
}

/**
 * Проверяем, нет ли такого пользователя
 */
$query = "LOCK TABLES auth_user WRITE, site_structure_site_alias WRITE, auth_user_group_param READ, auth_user_history WRITE, auth_user_phone WRITE"; 
$DB->query($query);

$query = "SELECT id FROM auth_user WHERE email='$user_email' OR login='$user_email'";
$DB->query($query);
if ($DB->rows > 0) {
	// Пользователь с таким электронным адресом уже зарегистрирован, воспользуйтесь формой напоминания пароля.
	Action::onError(cms_message('CMS', 'Пользователь с таким электронным адресом уже зарегистрирован, воспользуйтесь <a href="/User/Reminder/">формой напоминания пароля.</a>'));
}
  
 
/**
 * Перечень параметров
 */
$query = "select * from auth_user_group_param order by priority asc";
$params = $DB->query($query, 'uniq_name');


/**
 * Обрабатываем только параметры, что принадлежат соответсвующей группе пользователей
 */
$enabled_params = array();
$is_enabled     = false;

reset($params);
while(list($uniq_name, $row) = each($params)){
	if($row['data_type'] == "devider" && in_array($row['id'], $current_params)){
		$is_enabled = true;
		continue;
	} elseif($row['data_type'] == "devider" && !in_array($row['id'], $current_params)) {
		$is_enabled = false;
		continue;  
	}
	
	if($is_enabled){
		$enabled_params[$uniq_name] = $row;
	}
}

/**
 * Проверяем обязательные поля
 */
if(!empty($enabled_params)){
	
	reset($enabled_params);
	while (list(, $row) = each($enabled_params)) {
		$param_id = $row['id']; 
		
		/**
		 * Выдаём ошибку для обязательных полей и пропускаем необязательные для которых не переданы данные
		 */ 
		$values[$param_id] = (!empty($values[$param_id])) ? trim($values[$param_id]) : '';
		if (empty($values[$param_id]) && $row['required'] == 1) {
			// Выдаем ошибку
			Action::onError(cms_message('CMS', 'Не указано значение параметра "%s"', $row['name']));
		}
	}
}


/**
 * Проверяем CAPTCHA
 */
if (!Auth::isLoggedIn() && CMS_USE_CAPTCHA && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	Action::onError(cms_message('CMS', 'Неправильно введено число на картинке'));
}


/**
 * Подсистема автоматического Формирования имени
 */
if(!empty($enabled_params)){
	$auth_user_name = ""; 
	$auto_names 	= array();
	
	$defined_template = AUTH_USER_NAME;
	$auto_name_templates = explode("|", $defined_template);
	
	reset($auto_name_templates);
	while(list($name_id, $template) = each($auto_name_templates)){
		$auto_names[$name_id] = $template;
		
		reset($enabled_params);
		while (list(, $row) = each($enabled_params)) {
			$param_id = $row['id'];
			if(empty($values[$param_id])) continue;
			$auto_names[$name_id] = str_replace("%$row[uniq_name];", trim($values[$param_id]), $auto_names[$name_id]);
		}
		
		$auto_names[$name_id] = preg_replace("/%[a-zA-Z0-9_]+;/", "", $auto_names[$name_id]);
	} 
	
	reset($auto_names);
	while(list(, $name) = each($auto_names)){
		$name = trim($name);
		if(!empty($name)){
			$auth_user_name = $name;
			break;
		}
	}
	
	if(empty($auth_user_name)) Action::onError(cms_message('CMS', 'Не удалось сформировать имя пользователя.'));
} else {
	$auth_user_name = $user_email;
}


/**
 * Определяем сайт, на котором регистрируется пользователь
 */
$query = "
	select site_id from site_structure_site_alias
	where url = '".globalVar($_SERVER['HTTP_HOST'], '')."'
";
$site_id = $DB->result($query, 0);
$confirm_code = strtolower(Misc::randomKey(32));


/**
 * Определяем Ник на форуме
 */
$nickname = Auth::getNickname($user_email); 


/**
 * Сохраняем основные данные о пользователе
 */
$query = "
	INSERT INTO auth_user 
	SET login 				= '$user_email',
		user_group_id 		= '$user_group', 
		email 				= '$user_email',
		passwd 				= '".md5($user_password)."',  
		confirmation_code 	= '$confirm_code',
		site_id 			= '$site_id',
		registration_dtime  = NOW(),  
		register_ip 		= '$register_ip',
		register_local_ip 	= '$register_local_ip',
		referer 			= '".$DB->escape($cookie_referer)."',
		refered_page 		= '".$DB->escape($cookie_refered_page)."',
		name                = '$auth_user_name',
		nickname			= '".$DB->escape($nickname)."'
";
$user_id = $DB->insert($query);

 
/**
 * Сохраняем пользователя в истории 
 */
$query = "
	INSERT INTO auth_user_history 
	SET user_id 			= '$user_id',
		auth_code           = '$confirm_code', 
		ip 					= '$register_ip',
		local_ip 			= '$register_local_ip',
		name                = '$auth_user_name'
";   
$history_id = $DB->insert($query);


/** 
 * Сохраняем телефон, если он был указан
 */  
$user_phone_original = trim($user_phone);
$user_phone = parse_phone($user_phone_original);   
if(!empty($user_phone)) $phone_id = $DB->insert("
	INSERT IGNORE INTO auth_user_phone 
	SET user_id = '$user_id', 
		phone = '$user_phone', 
		phone_original = '$user_phone_original'
"); 
  

/**
 * Конец трансакции 
 */
$DB->query("UNLOCK TABLES");


/**
 * Добавляем свойства пользователя
 */
if(!empty($enabled_params)){
	reset($enabled_params);
	while (list(, $row) = each($enabled_params)) {
		$param_id = $row['id'];
		
			 
		/**
		 * Пропускаем пустые поля
		 */
		if (empty($values[$param_id]) && !in_array($row['data_type'], array('file', 'image'))) {
			continue;
		}  
		
		$value = $values[$param_id];
		
		    
		/**
		 * Обработка файлов
		 */
		if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
			// Закачиваем файлы
			$extension = Uploads::getFileExtension($_FILES['param']['name'][$param_id]);
			$extension = strtolower($extension);
			$file = UPLOADS_ROOT.'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$param_id.$extension";
			$url = substr($file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
			Uploads::moveUploadedFile($_FILES['param']['tmp_name'][$param_id], $file);
			$query = "
				insert into auth_user_data (`user_id`,`param_id`,`value_char`, `value_text`)
				values ('$user_id', '$param_id', '$extension', '$url')
			";
			$DB->insert($query);
		}
		
		
		/**
		 * Добавляем переданные данные
		 */
		if ($row['data_type'] == 'char') {
			
			// Текстовое поле
			$insert[] = "('$user_id', '$param_id', null, null, '$value', null, null)";
			$insert_history[] = "('$history_id', '$param_id', 'char', null, null, '$value', null)";
			
		} elseif ($row['data_type'] == 'decimal') {
			
			// Десятичное значение
			$insert[] = "('$user_id', '$param_id', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."', null)";
			$insert_history[] = "('$history_id', '$param_id', 'decimal', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."')";
			
		} elseif ($row['data_type'] == 'bool') {
			
			// Checkbox
			if(!empty($value)){
				$insert[] = "('$user_id', '$param_id', null, '".intval($value)."', null, null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'bool', null, '".intval($value)."', null, null)";
			} else {
				$insert[] = "('$user_id', '$param_id', null, '0', null, null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'bool', null, '0', null, null)";				
			}
			
		} elseif ($row['data_type'] == 'fkey') {
			
			// Внешний ключ
			if(!empty($value)){
				$query = "select group_concat(name) from auth_user_info_data where id='$value'";
				$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($DB->result($query))."', null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'fkey',  null, '$value', '".addslashes($DB->result($query))."', null)";
			} else {
				$insert[] = "('$user_id', '$param_id', null, '0', null, null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'fkey',  null, '0', null, null)";			
			}
			
		} elseif ($row['data_type'] == 'fkey_table') {
			
			// БД/таблица
			if(!empty($value)){ 
				$fkey_table_info = $DB->query_row("select UPPER(db_alias) as db_alias, table_name from cms_table_static where id='$value'");
				$fkey_table_value = db_config_constant("name", $fkey_table_info['db_alias']) . '.' . $fkey_table_info['table_name'];  
				  
				$insert[] = "('$user_id', '$param_id', null, '$value', '".addslashes($fkey_table_value)."', null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'fkey_table',  null, '$value', '".addslashes($DB->result($query))."', null)";
			} else {
				$insert[] = "('$user_id', '$param_id', null, '0', null, null, null)";
				$insert_history[] = "('$history_id', '$param_id', 'fkey_table',  null, '0', null, null)";
			}
			
		} elseif ($row['data_type'] == 'multiple') {  
			
			// Внешнее многозначное поле
			$query = "
				insert ignore into auth_user_multiple (`user_id`,`param_id`,`data_id`)
				values ('$user_id', '$param_id', '".implode("'), ('$user_id', '$param_id', '", $value)."')
			";
			$DB->insert($query);      
			
			$query = "select group_concat(name) from auth_user_info_data where id in (0".implode(",", $value).")";
			$insert[] = "('$user_id', '$param_id', '".addslashes($DB->result($query))."', null, null, null, null)";
			$insert_history[] = "('$history_id', '$param_id', 'multiple', '".addslashes($DB->result($query))."', null, null, null)";
			
		} elseif ($row['data_type'] == 'text') {
			
			// Текст
			$insert[] = "('$user_id', '$param_id', '$value', null, null, null, null)";
			$insert_history[] = "('$history_id', '$param_id', 'text', '$value', null, null, null)";
		} elseif ($row['data_type'] == 'html') {
			
			// HTML
			$insert[] = "('$user_id', '$param_id', '$value', null, null, null, null)";
			$insert_history[] = "('$history_id', '$param_id', 'html', '$value', null, null, null)";
		
		} elseif ($row['data_type'] == 'date') {
			// Дата
			$insert[] = "('$user_id', '$param_id', null, null, null, null,'$value')";
		}
	}

	if (!empty($insert)) {
		$query = "insert into auth_user_data (`user_id`,`param_id`,`value_text`,`value_int`,`value_char`,`value_decimal`,`value_date`) values ".implode(",", $insert);
		$DB->insert($query);
	}

	if(!empty($insert_history)){
		$query = "insert into auth_user_history_data (`history_id`, `param_id`, `data_type`, `value_text`,`value_int`,`value_char`,`value_decimal`) values ".implode(",", $insert_history);
		$DB->insert($query);
	}
}
 

/**
 * Высылаем администратору письмо, с указанием того, что пользователь изменил данные о себе
 */
if (CMS_NOTIFY_EMAIL!='') {
	$mailto = CMS_NOTIFY_EMAIL; 
	require_once(ACTIONS_ROOT.'site/user/notification.inc.php');
}
 
	
/**
 * Создание контрагента на основании введенных регистрационных данных
 */
if (is_module('billing') && BILLING_AUTO_CREATE_CONTRAGENT && !empty($enabled_params)) {
	
	$query = "SELECT uniq_name FROM auth_user_group WHERE id = '$user_group'";
	$group_name = $DB->result($query);
	
	$name 	 	= $auth_user_name;  
	$user_phone = (!empty($user_phone)) ? $user_phone : '';
	$country 	= (!empty($values[$enabled_params['country']['id']]))?$values[$enabled_params['country']['id']]:"";
	$region 	= (!empty($values[$enabled_params['region']['id']]))?$values[$enabled_params['region']['id']]:"";
	$city 		= (!empty($values[$enabled_params['city']['id']]))?$values[$enabled_params['city']['id']]:"";
	$index 		= (!empty($values[$enabled_params['post_index']['id']]))?$values[$enabled_params['post_index']['id']]:"";
	
	$street = (!empty($values[$enabled_params['street']['id']]))?$values[$enabled_params['street']['id']]:"";
	if(!empty($values[$enabled_params['house']['id']])) $street .= ', '.$values[$enabled_params['house']['id']];
	if(!empty($values[$enabled_params['room']['id']])) $street .= ', '.$values[$enabled_params['room']['id']]; 
	 
	// Физическое лицо
	if ($group_name == 'payer_phisical') {
		$query = "
			insert into billing_contragent set
				type = 'phisical',
				address_country = '$country',
				address_region  = '$region',
				address_city    = '$city',
				address_street  = '$street',
				address_index   = '$index',
				contact_person  = '$name',
				contact_phone   = '$user_phone',
				contact_email   = '$user_email'
		";
		$contragent_id = $DB->insert($query);	
	
	// Предприниматель 	
	} elseif ($group_name == 'payer_singletax') {
		$enabled_params['inn']['id'] = (!empty($enabled_params['inn']['id'])) ? $enabled_params['inn']['id'] : 0;
		$inn = (isset($values[$enabled_params['inn']['id']]))?$values[$enabled_params['inn']['id']]:"";
		
		$query = "
			insert into billing_contragent set
				type = 'singletax',
				address_country = '$country',
				address_region  = '$region',
				address_city 	= '$city',
				address_street 	= '$street',
				address_index 	= '$index',
				contact_person 	= '$name',
				contact_phone 	= '$user_phone',
				inn 			= '$inn',
				contact_email 	= '$user_email' 
		";
		$contragent_id = $DB->insert($query);	 
	
	// Юридическое лицо	
	} elseif ($group_name == 'payer_juridical') { 
		$inn 		  	 = (!empty($values[$enabled_params['inn']['id']])) ? $values[$enabled_params['inn']['id']] : ""; 
		$company_name 	 = (!empty($values[$enabled_params['company']['id']])) ? $values[$enabled_params['company']['id']] : "";
		$nds_certificate = (!empty($enabled_params['nds_certificate']['id']) && !empty($values[$enabled_params['nds_certificate']['id']])) ? $values[$enabled_params['nds_certificate']['id']] : "";
		$egrpou 		 = (!empty($values[$enabled_params['egrpou']['id']])) ? $values[$enabled_params['egrpou']['id']] : "";
		    
		$query = "
			INSERT INTO billing_contragent SET
				type = 'juridical',
				address_country = '$country',
				address_region 	= '$region',
				address_city 	= '$city',
				address_street 	= '$street',
				address_index 	= '$index',
				company_name 	= '$company_name',
				egrpou 			= '$egrpou',
				inn 			= '$inn',
				nds_certificate = '$nds_certificate',
				contact_phone 	= '$user_phone',
				contact_person 	= '$name',
				contact_email 	= '$user_email'
		";
		$contragent_id = $DB->insert($query);
	}
	
	/**
	 * Даем пользователю доступ к созданному контрагенту
	 */
	$query = "
		insert into billing_contragent_user
		set user_id = '$user_id',
			contragent_id = '$contragent_id'
	";
	$DB->insert($query);
	Billing::saveContragentVersion($contragent_id);
} 


/**
 * Высылаем SMS с поздравлениями, "что б пользователь не чувствовал себя в этом мире одиноким :)"
 */
if ($send_sms_notify == 'true') {
	Misc::userSmsNotify($user_id, "Рады видеть Вас среди клиентов хостинга www.ukraine.com.ua");
}

/**
 * Высылаем код подтверждения корректности телефонного номера
 */
if(!empty($phone_id)) {  
	Auth::sendPhoneConfirmationCode($phone_id, $user_id); 
}
 
/**
 * Автоматический логин
 */        
if ($auto_login == 'true' && AUTH_LOGIN_ON_REGISTER) {
	$logged_in = Auth::login($user_id, false, null);
	if (!$logged_in) {
		Auth::logLogin(0, time(), $user_email);
		Action::onError(cms_message('CMS', 'Доступ с IP заблокирован или Ваш аккаунт отключен администратором'));
	}
}

Action::setSuccess(cms_message('CMS', "Поздравляем, вы успешно зарегистрировались."));


?> 