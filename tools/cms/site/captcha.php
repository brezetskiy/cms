<?php
/** 
 * Выводит картинку CAPTCHA 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'SITE');

/**
* Конфигурация
*/
require_once('../../../system/config.inc.php');

$captcha_id = globalVar($_GET['uid'], '');
$refresh = globalVar($_GET['refresh'], 0);

if ($refresh > 0) {
	Captcha::refresh($captcha_id);
}

$image = Captcha::getImage($captcha_id);
header('Content-Type: image/png');
header('Content-Length: '.strlen($image));
echo $image;

?>