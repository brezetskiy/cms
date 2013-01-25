<?php

/**
 * Загрузка блока телефонов
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * Все номера
 */
$phones = AuthPhone::getPhones(); 


$TmplPhones = new Template('user/phones_block'); 
$TmplPhones->setGlobal('phones_count', count($phones));


$is_main_exists = false; 

reset($phones);
while(list(, $phone) = each($phones)){
	if(!empty($phone['is_main'])) $is_main_exists = true;
}

$TmplPhones->setGlobal('is_main_exists', $is_main_exists);

reset($phones);
while(list(, $phone) = each($phones)){
	$TmplPhones->iterate('/phones/', null, $phone);
}


$_RESULT['phones_block'] = $TmplPhones->display();


?>