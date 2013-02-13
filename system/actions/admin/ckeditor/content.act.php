<?php
/**
* ���������� ��������� �����
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

/**
* ���������� ����������
*/
$id = globalVar($_POST['id'], 0);
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$content = stripslashes(globalVar($_POST['content'], ''));
$html_tidy = globalVar($_POST['html_tidy'], 0);
$html_auto_charset = globalVar($_POST['html_auto_charset'], 0);

/**
 * �������� ���� �������������� ������� �������������
 */
if (!Auth::editContent($table_name, $id)) {
	Action::setError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
	Action::onError();
}

/**
 * �������� ���������� ���� � �������
 */
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	Action::setError(cms_message('CMS', '�������� ������������� %s ������������� %s', $owner['datetime'], $owner['login']));
	Action::onError();
}


/**
 * ��������� �������
 */
if (is_file(ACTIONS_ROOT.'admin/html_editor/'.$table_name.'.inc.php')) {
	require_once(ACTIONS_ROOT.'admin/html_editor/'.$table_name.'.inc.php');
}

/**
* ���������� ���������� � ������� ��������� ���� � ���������
*/
$Content = new Content($content, $table_name, $field_name, $id, $html_tidy, $html_auto_charset);
$Content->uploadImages();
$Content->rmImages();
$Content->url2id();
$Content->prepare4diff();

/**
 * ��������� ��������� � CVS
 */
CVS::log($table_name, $field_name, $id, $Content->content);

$Content->save();
$Content->statistic();

/**
 * ����, ������� ��� ��������� ������� ����������� �������� � ���������
 */
if ($Content->remote_images === true) {
	$_RESULT['source_update'] = true;
}


/**
 * ���� ����������� ����� ������ ������������, �� ���������� ����� ����� ������ � ������
 * ������� ����.
 */
if (isset($_REQUEST['login']) && isset($_REQUEST['passwd'])) {
	echo '<script language="JavaScript">window.close();</script>';
}

unset($Content);

/**
 * ��� ������ ���������� ��� ������������� Ajax - ���������� ������� �� _return_path
 */
if (AJAX) {
	exit;
}

?>