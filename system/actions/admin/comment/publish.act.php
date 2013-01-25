<?php
/**
 * Подтверждение публикации комментария администратором
 * @package Pilot
 * @subpackage Comment
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$id = globalVar($_REQUEST['id'], 0);
$active = globalVar($_REQUEST['active'], 0);

$query = "update comment set active='$active' where id='$id'";
$DB->update($query);
if ($active) {
	Comment::notify($id);
}

$_RESULT['comment_publish_'.$id] = ($active) ?
	'<a href="#" onclick="Comment.publish('.$id.', 0);return false;" style="color:brown;">Запретить</a>':
	'<a href="#" onclick="Comment.publish('.$id.', 1);return false;" style="color:green;">Опубликовать</a>';
exit;
?>