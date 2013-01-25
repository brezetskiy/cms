<?php
/**
 * Отмена авторизации
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */
 
if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);

?>