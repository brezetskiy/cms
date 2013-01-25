<?php
/**
 * Удаление комментария
 * @package Pilot
 * @subpackage Comment
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$id = globalVar($_REQUEST['id'], 0);
Comment::delete($id);

?>