<?php
/**
 * Обновляет данные в поле для выбора внешнего ключа
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$fk_table_id = globalVar($_REQUEST['fk_table_id'], 0);
$filter = globalVar($_REQUEST['filter'], array());
$offset = globalVar($_REQUEST['offset'], 0);
$_RESULT['table'] = Misc::cmsFKeyReference($fk_table_id, 0, $filter, $offset);
?>