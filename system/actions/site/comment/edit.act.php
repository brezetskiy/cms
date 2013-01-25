<?php
/** 
 * Редактирование коментариев
 * @package Pilot 
 * @subpackage Comment
 * @author Dima Markovskiy <dima@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */

$id = globalVar($_POST['edit_comment_id'], 0);
$comment = globalVar($_POST['edit_comment'], '');
  
$DB->update("update comment set comment = '$comment' where id = '".$id."'");

?>