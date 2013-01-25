<?php
/**
 * ��������� �� �����
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

define('LOGS_ACTIONS_ROOT', LOGS_ROOT.'actions');


/**
 * ���������� ���� �� ���� ������� �����
 * @param string $path
 */
function get_parent_path($path){
	return substr($path, 0, strlen($path)-strlen(strrchr($path, "/"))); 
}


/**
 * ���������� ������ ��� �����
 * @param string $file
 * @param string $type
 */
function define_icon($file, $type){ 
	global $available;
			
	if($type == "") return "mime.gif";   
	if(in_array($type, array('tar', 'gz', 'bz', 'zip', 'rar'))) return "archive.gif";
	
	reset($available);
	while(list(, $row) = each($available)){
		if(strpos(strtolower(substr($row, 0, strpos($row, "."))), $type) !== FALSE) return $row;
	}
	
	return "mime.gif";
}


/**
 * �������� ������
 */
$page_start = globalVar($_REQUEST['page'], 0);   
$rows_per_page  = 25;

$path = globalVar($_REQUEST['path'], "");  
$is_zip = globalVar($_REQUEST['is_zip'], 0);  


/**
 * �������� �� ������������
 */
if(strpos($path, LOGS_ACTIONS_ROOT) === FALSE){
	echo "�� ������ ��� ������ � ������ ������!";
	exit;
}


/**
 * ���� ������ �����
 */
if(!empty($is_zip)){
	$tmp_path = LOGS_ACTIONS_ROOT.'/tmp__'.substr(basename($path), 0, strpos(basename($path), '.'));
	if(!file_exists($tmp_path)) mkdir($tmp_path, 0777); 
	
	exec("/bin/tar -xzvf $path -C $tmp_path");  
	$path = $tmp_path; 
}


/**
 * ���������� ���������� �� ������� ����
 */
$parent_path = get_parent_path($path);


/**
 * ���������� ������� �����
 */
$files_data = Filesystem::getDirContent($path, true, true, true, false); 


/**
 * ���� ��������� ������ ��������� ����� - ������� ��� ������� ��������� �����
 */
if($path == LOGS_ACTIONS_ROOT){
	reset($files_data);
	while(list($index, $file) = each($files_data)){
		if(empty($file) || trim(strtolower($file), '/') == trim(strtolower(SITE_ROOT), '/') || strlen($file) <= strlen(LOGS_ACTIONS_ROOT)) continue;
		if(strpos(basename($file), 'tmp_') !== FALSE){
			Filesystem::delete($file); 
			unset($files_data[$index]);
		}
	}
}


/**
 * ���������� ������ ������� �������� ��� ��������� �����
 */
$available = Filesystem::getDirContent(SITE_ROOT.'img/shared/ico/', false, false, true);


/**
 * ��������� ������ ������� ��� ����������� ����
 */
$TmplTable = new Template("cms/admin/logs_navi");
$TmplTable->setGlobal("current_path", $path); 
$TmplTable->setGlobal("parent_path", $parent_path); 


/**
 * ��������� ������ � �������
 */
$files = array();
$files_counter = 0;

reset($files_data);
while(list(, $file) = each($files_data)){
	if (strpos(PHP_OS, "WIN") !== FALSE) {
		$file = str_replace("\\", "/", $file);
	}
	
	$files[$files_counter]['filename'] = substr($file, strrpos($file, '/')+1);
	
	if(is_dir($file)){ 
		$files[$files_counter]['type']     = "�����";
		$files[$files_counter]['filesize'] = "4.00";
		$files[$files_counter]['filetype'] = "�����";
		$files[$files_counter]['is_dir']   = true;
	
	} elseif(is_file($file)) {
		$files[$files_counter]['type']     = "����";
		$files[$files_counter]['filetype'] = strtolower(Uploads::getFileExtension($files[$files_counter]['filename']));
		$files[$files_counter]['filesize'] = round(Filesystem::getSize($file)/1000, 2);
		$files[$files_counter]['is_dir']   = false; 
		$files[$files_counter]['icon']     = define_icon($files[$files_counter]['filename'], $files[$files_counter]['filetype']);  
	}

	$files_counter++;
}

$total_rows = count($files); 
$page_count = $total_rows;
$TmplTable->set("rows_count",  $total_rows);


/**
 * ���������� ���� �� ������� �������� 
 */
if ($total_rows - $rows_per_page < 0) {
	$rows_count = $total_rows;     
} elseif ($page_start + $rows_per_page > $total_rows) {
	$rows_count = $total_rows;
} else { 
	$rows_count = $page_start + $rows_per_page;
}
  

/**
 * ��������� ������
 */
order_structure($files, ($path == LOGS_ACTIONS_ROOT) ? "type asc, filename desc" : "type asc, filename asc");     


/**
 * ��������� ������ � ������
 */
$counter = 0;
for($i=$page_start; $i<$rows_count; $i++){
	if(!isset($files[$i])) continue;   
	$files[$i]['class'] = ($counter % 2 == 0) ? "even" : "odd";
	$files[$i]['count'] = $counter;
	
	if(empty($files[$i]['is_dir'])) $files[$i]['filesize'] = number_format($files[$i]['filesize'], 2, '.', ' ');
	$counter++;
	 
	$TmplTable->iterate('/files/', null, $files[$i]);  	
}


/**
 * ��������� �������� �� ���������
 */
$TmplTable->set('pages_list', Misc::pages($page_count, $rows_per_page, 10, 'logs_navi', false, false, '', 'load(\''.$path.'\', {$offset})', $page_start)); 
   

/**
 * ����� ����������
 */
$_SESSION['log_actions_current_path'] = $path; 
$_RESULT['content_navi'] = $TmplTable->display();


?>