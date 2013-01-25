<?php
/** 
 * �������� ������ � ����� � ���������, ��� ������� ��� ������ � �� 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$files = globalVar($_POST['file'], array());
$dirs = globalVar($_POST['dir'], array());

$deleted_files = $deleted_dirs = 0;

reset($files); 
while (list($table,) = each($files)) { 
	reset($files[$table]);
	while (list(,$row) = each($files[$table])) {
		Filesystem::delete($row);
	}
}

Action::setSuccess("������� $deleted_files ������ � $deleted_dirs ���������");

?>