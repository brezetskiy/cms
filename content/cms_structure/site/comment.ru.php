<?php
/**
 * Список страниц, на которых есть непроверенные комментарии
 * @package Pilot
 * @subpackage Comment
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */


$query = "select concat('<a target=_blank href=\"', url, '\">', url, '</a>') as url from comment where active=0";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('title', 'Страницы, на которых есть непроверенные комментарии');
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('url', '80%', 'left');
echo $cmsTable->display();
unset($cmsTable);

?>