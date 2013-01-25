<?php
/**
 * CKEditor
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
 
/**
* ���������� ��������� ��� ��������� �������������������
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* ���������������� ����
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

// ������������ ���  ������ � ������������� ���������
new Auth(true);
 


$id 		= globalVar($_GET['id'], 0);
$extention 	= globalVar($_GET['extention'], "php");
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$css        = globalVar($_GET['css'], '');
$is_file    = globalVar($_GET['file'], 0);
$is_saved   = globalVar($_SESSION['text_editor']['is_saved'], 0);
$save_dtime = globalVar($_SESSION['text_editor']['save_dtime'], ''); 
$content 	= "";  
 
$window_height = globalVar($_GET['height'], 730);
  
/**
 * �������� ���� ��������������
 */
if (!Auth::editContent($table_name, $id)) {
	$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
	$TmplDesign->set('message', '� ��� ��� ���� �� �������������� ���� ��������.');
	echo $TmplDesign->display();
	exit;
}


/**
 * ���������, �� ������������ �� ���� ������ �������������
 */
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	// ������ ��������� � ���, ��� �������� - �������������
	$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
	$TmplDesign->set('message', '
		��������, ������� �� ������ ������������� �������<br>
		������������� <b>'.$owner['login'].'</b>.<br><br>
		����� ��������: <b>'.$owner['datetime'].'</b>.<br><br>
		������������� ��������� ���������� �� �������� <br>����� �������������� - ����������.
	');
	echo $TmplDesign->display();
	exit;
}
unset($owner);

/**
 * ��������� ���������
 */
if (empty($id)) {
	echo '<SCRIPT>alert("� �������� �� �������� ������������\n �������� id!\n\n�������� ����� ������.");window.close();</SCRIPT>';
	exit;
} elseif (empty($table_name)) {
	echo '<SCRIPT>alert("� �������� �� �������� ������������\n �������� table_name!\n\n�������� ����� ������.");window.close();</SCRIPT>';
	exit;
} 
$TmplDesign = new Template(SITE_ROOT.'templates/cms/admin/editor');
$TmplDesign->setGlobal('extention', $extention);
$TmplDesign->setGlobal('id', $id);
$TmplDesign->setGlobal('is_file', $is_file);  
$TmplDesign->setGlobal('table_name', $table_name);
$TmplDesign->setGlobal('field_name', $field_name);
$TmplDesign->setGlobal('window_height', $window_height);
$TmplDesign->setGlobal('is_saved', $is_saved);
$TmplDesign->setGlobal('save_dtime', $save_dtime);
$TmplDesign->setGlobal('dtime', date("d.m.Y h:i:s"));
$TmplDesign->set('title', '���������� ��������');


if(!empty($is_file)){
	$query    = "SELECT url FROM site_structure WHERE id = '$id'";
	$site_url = $DB->result($query); 
	
	$file_url = strtolower(CONTENT_ROOT."site_structure/$site_url.".LANGUAGE_CURRENT.".".$extention);
	if(!is_writeable($file_url)){
		$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
		$TmplDesign->set('message', '��� ���� �� ������ �����.');
		echo $TmplDesign->display();
		exit;
	}
	
	$content = htmlspecialchars(file_get_contents($file_url));
	$TmplDesign->set('title', "<b>����:</b> <u>$file_url</u>");
} else {
	$query = "select `$field_name` from `$table_name` where id='$id'";
	$content = $DB->result($query);
	
	$content = stripslashes(id2url($content, true)); 
	$TmplDesign->set('title', "<b>����:</b> <u>$table_name.$field_name</u>");
}
 
$TmplDesign->set('content', $content);
echo $TmplDesign->display();

?>