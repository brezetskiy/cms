<?php
/**
 * Снятие блокировки с пользовательских аккаунтов
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

// Удаляем все стоп-записи о подписчике
$query = "delete from maillist_stoplist where email='{$this->OLD['email']}'";
$DB->delete($query);

?>