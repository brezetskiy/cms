<?php

/**
 * Включить двухтапную авторизацию
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

$user_id = Auth::isLoggedIn();
if(empty($user_id)){
	Action::onError("Пожалуйста, авторизируйтесь");
}

$otp_type_titles = array(
	'etoken'  => "eToken PASS", 
	'android' => "Google Authenticator для Android", 
	'iphone'  => "Google Authenticator для iPhone", 
	'java' 	  => "Google Authenticator для J2ME",
	'mobile'  => "Google Authenticator", 
	'sms' 	  => "SMS авторизации"
);


$otp_config = $DB->query_row("SELECT otp_enable, otp_type, otp_cnt, otp_sign FROM auth_user WHERE id = '$user_id'");
$otp_type_title = (!empty($otp_type_titles[$otp_config['otp_type']])) ? $otp_type_titles[$otp_config['otp_type']] : $otp_config['otp_type'];

if(!empty($otp_config['otp_enable'])){
	Action::onError("Для Вашей учетной записи двухэтапная авторизация уже включена на генерацию кодов посредством $otp_type_title");
}


/**
 * Активация двухтапной проверки
 */
if(empty($otp_config['otp_type'])){
	Action::onError("Метод двухэтапной авторизации не определен. Активация невозможна");
	
} elseif($otp_config['otp_type'] != 'sms' && empty($otp_config['otp_cnt'])){
	Action::onError("Счетчик не установлен. Активация невозможна");
	
} elseif($otp_config['otp_type'] != 'sms' && empty($otp_config['otp_sign'])){
	Action::onError("Секретный ключ не найден. Активация невозможна");
}
	
	  
/**
 * Генерирование резервных кодов доступа
 */
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

$DB->delete("DELETE FROM auth_user_otp_code WHERE user_id = '$user_id'");
$DB->insert("INSERT INTO auth_user_otp_code (user_id, code) VALUES ".implode(',', $insert));
$DB->update("UPDATE auth_user SET otp_enable = 1 WHERE id = '$user_id'");
  
Action::setSuccess("
	Двухэтапаня авторизация успешно активирована.
	<p>
		Важно! В случае сбоя работы или потери устройства генерации кодов, воспользуйтесь резервными кодами доступа, что представлены ниже.
		<br/><span style='color:red;'>Обязательно создайте копию резервных кодов доступа, так как вы больше не сможете их просмотреть где-либо на сайте.</span> 
	</p>
	<h4>Резервные коды доступа:</h4>
	<div style='float:left; margin:10px;'>".implode('<br/>', $reserve_codes['row_1'])."</div> 
	<div style='float:left; margin:10px;'>".implode('<br/>', $reserve_codes['row_2'])."</div> 
	<div style='float:left; margin:10px;'>".implode('<br/>', $reserve_codes['row_3'])."</div> 
	<div style='float:left; margin:10px;'>".implode('<br/>', $reserve_codes['row_4'])."</div> 
	<div style='clear:both;'></div> 
	<a style='margin:10px;' href='/action/user/otp/config_reserve/'>Сохранить в файл</a>
");
  

	
?>