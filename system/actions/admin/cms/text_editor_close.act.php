<?php
/**
* Очистка сессии
* @package Pilot
* @subpackage CMS
* @version 5.0
* @author Miha Barin <barin@delta-x.ua>
* @copyright Delta-X, 2010
*/

if(!empty($_SESSION['text_editor'])){
	unset($_SESSION['text_editor']);
}

?>