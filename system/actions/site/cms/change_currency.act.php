<?php

/**
 * Смена валюты
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Delta-X, 2010
 */

$currency_id = globalVar($_REQUEST['currency_id'], 0);  
$_SESSION['currency_current'] = $currency_id;

?>