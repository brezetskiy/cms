<?php
/**
 * �������� � ������ (����������)
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
		$row['filesize'] = number_format(filesize($file)/1000, 0, '.', ' ').' ��';
	} else {
		$row['name'] .= ' - ����������� ����� ����.';
		$row['filesize'] = '0 ��';
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
$cmsTable->addColumn('filesize', '20%', 'right', '������');
echo $cmsTable->display();
unset($cmsTable);
?>