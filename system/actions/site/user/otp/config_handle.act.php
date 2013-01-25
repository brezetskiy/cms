<?php 
/**
 * Обработчик настройки OTP
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


function otp_handle_error($message){
	global $_RESULT;
	
	$_RESULT['javascript'] .= "delta_error('".cms_message('User', $message)."');";
	exit;
}

  
$_RESULT['javascript'] = '';

 
/**
 * Проверка авторизации
 */ 
$user_id = Auth::isLoggedIn();
if(empty($user_id)){    
	otp_handle_error("Пожалуйста, авторизируйтесь");
}


/**
 * Входные параметры
 */
$user = Auth::getInfo();

$step  = globalVar($_REQUEST['step'], '');
$force = globalVar($_REQUEST['force'], '');
$type  = globalVar($_REQUEST['otp_type'], '');

$mobile_types = array('mobile', 'android', 'iphone', 'java');
$otp_type_titles = array(
	'etoken'  => "eToken PASS", 
	'android' => "Google Authenticator для Android", 
	'iphone'  => "Google Authenticator для iPhone", 
	'java' 	  => "Google Authenticator для J2ME",
	'mobile'  => "Google Authenticator", 
	'sms' 	  => "SMS авторизации"
);
	

/**
 * Проверяем, настроена ли OTP конфигурация
 */
$otp_data = $DB->query_row("
	SELECT 
		otp_type, 
		otp_type as type,  
		otp_enable as is_enabled, 
		IF(otp_type IS NOT NULL && TRIM(otp_type) != '', 1, 0) is_configured 
	FROM auth_user 
	WHERE id = '$user_id'
");
 
$otp_data['type_title'] = (!empty($otp_type_titles[$otp_data['otp_type']])) ? $otp_type_titles[$otp_data['otp_type']] : $otp_data['otp_type'];


/**
 * Страница отключения
 */
if($step == 2 && $type == 'disable'){ 
	$TmplDisable = new Template('user/otp/config_disabled');
	$TmplDisable->set('config', $otp_data); 
	
	/**
	 * Если OTP включено, нужно вывести форму для указания кода подтверждения
	 * Отдельно обрабатываем ситуацию с SMS авторизацией
	 */
	if(!empty($otp_data['is_enabled']) && $otp_data['type'] == 'sms'){
		
		/**
		 * Проверяем, был ли отправлен код 
		 */
		$code = AuthPhone::getLastCode('otp_delete', $user_id, true);
		$TmplDisable->set("code", $code);
		
		$phones = AuthPhone::getConfirmedPhones();
		$phones_count = count($phones);
		
		$TmplDisable->set("phones_count", $phones_count);
		if($phones_count == 1) $TmplDisable->set("phone", array_pop($phones));
		if($phones_count > 1) $TmplDisable->iterateArray("/phones/", null, $phones);
	}
	
	$_SESSION['otp_step'] = 2;   
	$_SESSION['otp_type'] = 'disable';
		
	$_RESULT['otp_content'] = $TmplDisable->display();
	$_RESULT['javascript'] = '$("#otp_content").show();';
	
	/**
	 * Вывод формы отключения OTP 
	 */ 
	if(!empty($_SESSION['otp_disable_form'])){ 
		if($_SESSION['otp_disable_form'] == "phone_disable") {
			$_RESULT['javascript'] .= "config_disable_sms_open();"; 
			
		} elseif($_SESSION['otp_disable_form'] == "reserve_disable") { 
			$_RESULT['javascript'] .= "config_disable_open();"; 
			$_RESULT['javascript'] .= "switch_code(0);";  
			
		} elseif($_SESSION['otp_disable_form'] == "submit_disable") { 
			$_RESULT['javascript'] .= "config_disable_open();"; 
		}
	}
	
	exit;
} 


/**
 * OTP уже настроена
 */   
if($DB->rows > 0 && !empty($otp_data['is_configured'])){
	$TmplConfigured = new Template("user/otp/config_configured"); 
    
	/**
	 * Генерирование резервных кодов доступа после успешной настройки
	 */ 
	if(!empty($_SESSION['otp_install'])){   
		$insert = array();
		$reserve_codes = array();
		
		for ($i=0; $i<12; $i++){
			$code = gen_password(8);
			$insert[] = "('$user_id', '$code')";
			
			if($i < 3) $reserve_codes['row_1'][]  = $code;
			if($i >= 3 && $i < 6) $reserve_codes['row_2'][] = $code;
			if($i >= 6 && $i < 9) $reserve_codes['row_3'][] = $code;
			if($i >= 9 && $i < 12) $reserve_codes['row_4'][] = $code;
		}  
		
		$DB->insert("INSERT INTO auth_user_otp_code (user_id, code) VALUES ".implode(',', $insert));
		 
		$TmplConfigured->set('reserve_codes_col_1', implode('<br/>', $reserve_codes['row_1']));
		$TmplConfigured->set('reserve_codes_col_2', implode('<br/>', $reserve_codes['row_2']));
		$TmplConfigured->set('reserve_codes_col_3', implode('<br/>', $reserve_codes['row_3']));
		$TmplConfigured->set('reserve_codes_col_4', implode('<br/>', $reserve_codes['row_4']));
		 
		$TmplConfigured->set('is_reserve_content', true);
		unset($_SESSION['otp_install']); 
	}

	
	$current_type = $type;
	$current_type_title = (!empty($otp_type_titles[$current_type])) ? $otp_type_titles[$current_type] : $otp_data['otp_type'];
	
	if(in_array($current_type, $mobile_types)) $current_type = "mobile";
	if(in_array($otp_data['type'], $mobile_types)) $otp_data['type'] = "mobile";
	
	$TmplConfigured->set('current_type', $current_type);
	$TmplConfigured->set('current_type_title', $current_type_title);
	$TmplConfigured->set('config', $otp_data); 
	
	/**
	 * Если OTP включено, нужно вывести форму для указания кода подтверждения
	 * Отдельно обрабатываем ситуацию с SMS авторизацией
	 */
	if(!empty($otp_data['is_enabled']) && $otp_data['type'] == 'sms'){
		  
		/**
		 * Проверяем, был ли отправлен код 
		 */
		$code = AuthPhone::getLastCode('otp_delete', $user_id, true);
		$TmplConfigured->set("code", $code);
		
		$phones = AuthPhone::getConfirmedPhones();
		$phones_count = count($phones);
		 
		$TmplConfigured->set("phones_count", $phones_count);
		if($phones_count == 1) $TmplConfigured->set("phone", array_pop($phones));
		if($phones_count > 1) $TmplConfigured->iterateArray("/phones/", null, $phones);
	}
	
	$_SESSION['otp_step'] = 2;
	$_SESSION['otp_type'] = $current_type;
	
	$_RESULT['otp_content'] = $TmplConfigured->display();   
	$_RESULT['javascript'] .= "$('#otp_content').show();"; 
	 
	$style_button = (!empty($otp_data['is_enabled']) && $current_type == $otp_data['type']) ? "green" : "active";
	$style_content = (!empty($otp_data['is_enabled']) && $current_type == $otp_data['type']) ? "green" : "gray";
	$_RESULT['javascript'] .= "config_style('$style_button', '$style_content', '$current_type');"; 
	
	
	/**
	 * Удаляем сессии остальных форм отключения OTP защиты
	 */ 
	if(!empty($_SESSION['otp_disable_form']) && strpos($_SESSION['otp_disable_form'], $current_type) === FALSE){
		unset($_SESSION['otp_disable_form']);
	}
	
	/**
	 * Вывод текущей формы отключения OTP 
	 */ 
	if(!empty($_SESSION['otp_disable_form'])){ 
		if($_SESSION['otp_disable_form'] == "phone_$current_type") {
			$_RESULT['javascript'] .= "config_disable_sms_open();"; 
			
		} elseif($_SESSION['otp_disable_form'] == "reserve_$current_type") { 
			$_RESULT['javascript'] .= "config_disable_open();"; 
			$_RESULT['javascript'] .= "switch_code(0);";  
			
		} elseif($_SESSION['otp_disable_form'] == "submit_$current_type") { 
			$_RESULT['javascript'] .= "config_disable_open();"; 
		}
	}
	  
	exit;   
}
 

/**
 * Выбор eToken, Mobile или SMS
 */
if($step == 2){
	if($type == 'etoken'){
		$TmplEtokenStep2 = new Template('user/otp/etoken_step_2');
		
		$_RESULT['otp_content'] = $TmplEtokenStep2->display();
		$_RESULT['javascript'] .= '$("#otp_content").show();';
	
	} elseif(in_array($type, $mobile_types)){
		$TmplMobileStep2 = new Template('user/otp/mobile_step_2'); 
		
		$_RESULT['otp_content'] = $TmplMobileStep2->display(); 
		$_RESULT['javascript'] .= '$("#otp_content").show();';
		
		if($force > $step) $_RESULT['javascript'] .= "config_step(3, '$type', $force);";
	
	} elseif($type == 'sms'){   
		if(!is_module("GSM")){ 
			otp_handle_error("Модуль GSM не найден. SMS авторизация не доступна");
		}
		
		$phones = AuthPhone::getConfirmedPhones();
		$phones_count = count($phones);
		
		$TmplSMSStep2 = new Template('user/otp/sms_step_2');
		$TmplSMSStep2->set("phones_count", $phones_count);
		
		if($phones_count == 1) $TmplSMSStep2->set("phone", array_pop($phones));
		if($phones_count > 1) $TmplSMSStep2->iterateArray("/phones/", null, $phones);
		  
		$_RESULT['otp_content'] = $TmplSMSStep2->display();
		$_RESULT['javascript'] .= '$("#otp_content").show();';
		
		if($force > $step) $_RESULT['javascript'] .= "config_step(3, '$type', $force);";
	}
}


/**  
 * Выбор типа телефона
 */
if($step == 3 && in_array($type, $mobile_types)){
	
	$TmplMobileStep3 = new Template('user/otp/mobile_step_3');
	$TmplMobileStep3->set('otp_type', $type);
	
	/**
	 * Создаем секретный ключ пользователя и кодируем его в QR-код
	 */
	$_SESSION['otp_sign'] = Base32::encode(gen_password(8), false);  
	$qr_url = str_replace(array('{$login}', '{$secret}'), array($user['login'], $_SESSION['otp_sign']), HTTP_SCHEME."://".AUTH_OTP_PROGRAM_QR_URL);
	$TmplMobileStep3->set('qr_url', $qr_url);
	
	if($type == 'android'){
		 $store = array(
		 	'name' => 'Android Market',   
		 	'url'  => AUTH_OTP_PROGRAM_ANDROID_URL, 
		 	'is_request' => true,
		 	'list' => '
		 		<li>Нажмите на значок "+".</li>
		 		<li>
		 			Чтобы привязать телефон к аккаунту, выберите <b>Сканировать штрихкод аккаунта</b>. 
		 			Вам потребуется загрузить и установить приложение для сканирования штрихкодов, 
		 			если приложение Google Authenticator не сможет обнаружить его на вашем телефоне. 
		 			Чтобы установить приложение для сканирования штрихкода и продолжить процесс настройки, 
		 			нажмите <b>Установить</b> и выполните все требуемые для установки действия. 
		 			Установив приложение, снова откройте приложение Google Authenticator и направьте камеру на QR-код, 
		 			отображенный в браузере на текущей странице в форме <b>QR-код</b>.
		 			<p style="color:red;">
		 				Если Вы не имеете возможности отсканировать QR-код, Вы всегда можете ввести секретный ключ вручную.<br/>
		 				<b>Секретный ключ:</b> '.$_SESSION['otp_sign'].' 
		 			</p>
		 		</li>
		 	'
		 );
		 
	} elseif($type == 'iphone'){
		 $store = array( 
		 	'name' => 'App Store', 
		 	'url'  => AUTH_OTP_PROGRAM_IPHONE_URL,
		 	'is_request' => true,
		 	'list' => ' 
		 		<li>Нажмите на значок "+".</li>
		 		<li>Нажмите <b>Временный</b>.</li> 
		 		<li>
		 			Нажмите кнопку <b>Сканировать штрихкод</b> и направьте камеру на QR-код, 
		 			отображенный в браузере на текущей странице в форме <b>QR-код</b>.
		 			<p style="color:red;">
		 				Если Вы не имеете возможности отсканировать QR-код, Вы всегда можете ввести секретный ключ вручную.<br/>
		 				<b>Секретный ключ:</b> '.$_SESSION['otp_sign'].' 
		 			</p>
		 		</li>
		 		<li> 
		 			В приложении Google Authenticator отобразится шестизначное число проверки целостности. 
		 			Не обращайте на него внимание: оно вам не потребуется. Чтобы продолжить, нажмите <b>Сохранить токен</b>.
		 		</li>
		 	'
		 ); 
		 
	} elseif($type == 'java'){ 
		 $store = array( 
		 	'name' => AUTH_OTP_PROGRAM_JAVA_URL, 
		 	'url'  => AUTH_OTP_PROGRAM_JAVA_URL,
		 	'list' => '
		 		<li>
		 			Введите новый <b>Секретный ключь</b>, который вы можете, при наличии соответствующего программного обеспечения, считать в виде QR-кода или
		 			ввести вручную (см. ниже).
		 			<p style="color:red;">
		 				Если Вы не имеете возможности отсканировать QR-код, введите секретный ключ вручную.<br/>
		 				<b>Секретный ключ:</b> '.$_SESSION['otp_sign'].' 
		 			</p>
		 		</li>
		 	'
		 );
	}  
	 
	if(empty($store)) $store = array('name' => 'спец. электронный магазин програмного обеспечения Вашего смартфона', 'url' => '#', 'list' => '');
	$TmplMobileStep3->set($store);
	
	$_RESULT['otp_mobile_content'] = $TmplMobileStep3->display(); 
	$_RESULT['javascript'] .= "$('#otp_mobile_content').show();";
	 
} elseif($step == 3 && $type == 'sms'){
		
		/**
		 * Проверка кода  
		 */ 
		$code = AuthPhone::getLastCode('otp_confirm', $user_id, true);
		if (empty($code)) {      
			otp_handle_error("Пожалуйста, отправьте новый код подтверждения");
		}
		        
		$phone = AuthPhone::getPhone($code['phone_send_id'], $user_id);    
		if (empty($phone)) { 
			otp_handle_error("Телефонный номер, на который было отправлено СМС с кодом подтверждения утерян. Пожалуйста, повторите все шаги настройки SMS авторизации");
		}
		      
		$TmplSMSStep3 = new Template('user/otp/sms_step_3');
		$TmplSMSStep3->set("phone", $phone);
		 
		$_RESULT['otp_sms_install_block'] = $TmplSMSStep3->display();   
}
 
 
/**
 * Форма подтверждения сгенерированного кода
 */
if($step == 4 && in_array($type, $mobile_types)){
	$TmplMobileStep4 = new Template('user/otp/mobile_step_4'); 
	$TmplMobileStep4->set('otp_type', $type);
	$_RESULT['otp_mobile_content'] = $TmplMobileStep4->display();
}
		

if(!empty($_SESSION['otp_disable_form'])) unset($_SESSION['otp_disable_form']); 
$_SESSION['otp_step'] = $step;
$_SESSION['otp_type'] = $type;


?>