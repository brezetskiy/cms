<?php
/** 
 * �������� ������� �� ������� 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$modules = globalVar($_POST['modules'], array());

// ������� ������ ���������, ��� ��� � ������� ���� ��� �� ������ ��������� ��-�� �������
// ������������ �� ������ �������
do {
	$start_count = count($modules);
	reset($modules);
	while (list($index,$id) = each($modules)) {
		$Module = new Module($id);
		if ($Module->delete()) {
			unset($modules[$index]);
		}
	}
} while(!empty($modules) && $start_count!=count($modules));


// ������� ������ ����������
Filesystem::deleteEmptyDirs(SITE_ROOT.'img/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'css/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'js/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'uploads/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'content/');
Filesystem::deleteEmptyDirs(TRIGGERS_ROOT);
Filesystem::deleteEmptyDirs(SITE_ROOT.'templates/');
Filesystem::deleteEmptyDirs(SITE_ROOT.'system/');

?>