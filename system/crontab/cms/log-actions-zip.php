<?php 
/**
 * ��������� ���������� �����
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * ���������� ���������
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');
chdir(dirname(__FILE__));


/**
 * ���������������� ����
 */
require_once('../../config.inc.php');


/**
 * �������� �������� ���
 */
if(date('d') != '01'){
	echo "[i] Time is not come yet. \n";	
	exit;
}

echo "[i] It is time to zip all action logs of previous month. \n";	
define('LOGS_ACTIONS_ROOT', LOGS_ROOT.'actions/');


$zipstack = array();
$zip_path = LOGS_ACTIONS_ROOT.date('Y').'-'.sprintf("%02d", date('m')-1).".tar.gz"; 


/**
 * ���������� ������� ����� �����
 */
$folders = Filesystem::getDirContent(LOGS_ACTIONS_ROOT, false, true, false, false);


/**
 * C������� ���� ������
 */
reset($folders);
while(list($index, $folder) = each($folders)){
	preg_match('/[0-9]{4}-([0-9]{2})-[0-9]{2}/', $folder, $matched);
	if(empty($matched[1]) || intval($matched[1]) >= intval(date('m'))) continue;
	 
	$zipstack[] = './'.$folder; 
}
 
   
/**
 * ��������� ���� ��������� �� $zipstack
 */
if(!empty($zipstack)){
	exec("tar -czvf $zip_path -C ".LOGS_ACTIONS_ROOT." ".implode(' ', $zipstack)); 
	`chmod -R 777 $zip_path`; 
}


/**
 * �������� ���������� ������
 */
reset($zipstack);
while(list($index, $folder) = each($zipstack)){
	$folder_path = LOGS_ACTIONS_ROOT.basename($folder);
	if(trim(strtolower($folder_path), '/') == trim(strtolower(SITE_ROOT), '/')) continue;
	Filesystem::delete($folder_path);      
}

 
?>