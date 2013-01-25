<?php

/**
 * Страница изменения ника на форуме
 * 
 * @package Pilot
 * @subpackage Forum
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */

$nickname = $DB->result("SELECT nickname FROM auth_user WHERE id = '".Auth::getUserId()."'");
$TmplContent->set('nickname', (!empty($_SESSION['ActionError']['nickname'])) ? $_SESSION['ActionError']['nickname'] : $nickname);


?>