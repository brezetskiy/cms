<?php
/**
 * Событие, которое возникает после изменения водяного знака
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$query = "select uniq_name from cms_image_size where watermark_id='{$this->OLD['id']}'";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	Filesystem::delete(SITE_ROOT.'i/'.$row['uniq_name'].'/');
}