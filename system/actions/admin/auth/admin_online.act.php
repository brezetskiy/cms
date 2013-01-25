<?php
/** 
 * Система определения администраторов, которые находятся online на странице url
 * @package Pilot 
 * @subpackage Support 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010 
 */ 

$url = globalVar($_REQUEST['url'], '');
$current_date = date("Y-m-d H:i:s");

$user_id = Auth::getUserId();
//if (empty($user_id)) exit;

$query = "
	INSERT INTO auth_admin_online (user_id, url, over_time, current_dtime)
	VALUES ('$user_id', '".addslashes($url)."', ".AUTH_ONLINE_CHECK_INTERVAL.", '$current_date')    
	ON DUPLICATE KEY UPDATE  
		over_time = over_time + VALUES(over_time), 
		current_dtime = VALUES(current_dtime)
";
$DB->insert($query);

 
?> 