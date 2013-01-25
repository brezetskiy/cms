<?php
/**
 * Вложения в письма (аттачменты)
 * @package Maillist
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2005
 */
$message_id = globalVar($_GET['message_id'], 0);

function prefilter($row) {
	$file = UPLOADS_ROOT.'maillist_attachment/file/'.Uploads::getIdFileDir($row['id']).'.'.$row['file'];
	if (is_file($file)) {
		$row['name'] .= '.'.Uploads::getFileExtension($file);
		$row['filesize'] = number_format(filesize($file)/1000, 0, '.', ' ').' Кб';
	} else {
		$row['name'] .= ' - Невозможной найти файл.';
		$row['filesize'] = '0 Кб';
	}
	return $row;
}

$query = "
	SELECT 
		id,
		file,
		name
	FROM maillist_attachment
	WHERE message_id = '$message_id'
	ORDER BY name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'prefilter');
$cmsTable->addColumn('name', '50%');
$cmsTable->addColumn('filesize', '20%', 'right', 'Размер');
echo $cmsTable->display();
unset($cmsTable);
?>