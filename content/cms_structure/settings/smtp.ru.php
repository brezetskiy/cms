<?php
/**
 * Список SMTP frrfeynjd, через которые отправляется почта
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$query = "select id, name, concat(sender_name, ' &lt;', sender_email, '&gt;') as sender_name from cms_mail_account";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('sender_name', '90%');
echo $cmsTable->display();
unset($cmsTable);


?>