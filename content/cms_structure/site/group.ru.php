<?php
/**
 * группы авторизации
 * @package Pilot
 * @subpackage Site
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */


$cmsTable = new cmsShowViewInfo($DB, 'site_auth_group');
echo $cmsTable->display();

?>