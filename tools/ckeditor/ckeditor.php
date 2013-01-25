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
require_once('../../system/config.inc.php');

$DB = DB::factory('default');

// ������������ ���  ������ � ������������� ���������
new Auth(true);

$event = globalVar($_GET['event'], 'sw/content');
$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$css = globalVar($_GET['css'], '');

/**
* �������� ���� �������������� ������� �������������
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

$TmplDesign = new Template(SITE_ROOT.'templates/ckeditor/ckeditor');
$TmplDesign->setGlobal('event', $event);
$TmplDesign->setGlobal('id', $id);
$TmplDesign->setGlobal('table_name', $table_name);
$TmplDesign->setGlobal('field_name', $field_name);
$TmplDesign->setGlobal('css', $css);
$TmplDesign->set('title', '���������� ��������');

/**
* ���������� ��������� ��� ��������
*/
// ���������� �������� �������, ������� �������� �� �����������
$query = "
	SELECT 
		tb_field.name,
		tb_field._is_multilanguage,
		tb_interface.name AS interface
	FROM cms_table AS tb_table
	INNER JOIN cms_field AS tb_field ON tb_field.id = tb_table.fk_show_id
	INNER JOIN cms_interface AS tb_interface ON tb_interface.id=tb_table.interface_id
	WHERE tb_table.name='$table_name'
";
$data = $DB->query_row($query);
if ($DB->rows > 0) {
	$select_field_name = (!$data['_is_multilanguage']) ? $data['name'] : $data['name'].'_'.constant('LANGUAGE_'.$data['interface'].'_DEFAULT');
	$query = "
		SELECT `$select_field_name` AS title
		FROM `$table_name`
		WHERE id='$id'
	";
	$title = $DB->result($query);
	
	if ($DB->rows > 0 && !empty($title)) {
		$TmplDesign->set('title', $title);
	}
	unset($title);
}
unset($data);


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

/**
* ������� ������� ������, ������� ������������ �������������
*/
require_once(INC_ROOT.'editor/editor.inc.php');
$style = parse_css(SITE_ROOT.'css/site/content.css');

reset($style);
while(list(,$row) = each($style)) {
	if ($row['element'] == 'TABLE') continue;
	$TmplDesign->iterate('/style/', null, array(
			'title' => $row['title'], 
			'element' => (empty($row['class'])) ? $row['element'] : $row['element'].'.'.$row['class'], 
			'apply_to' => ($row['element'] == 'SPAN') ? '�' : '�'
		)
	);
}
unset($style);

/**
* ������� ������������ ������
*/
$query = "
	select
		tb_language.name_".LANGUAGE_CURRENT." as name,
		tb_language.code
	from cms_language as tb_language
	inner join cms_language_usage as tb_relation on tb_relation.language_id=tb_language.id
	inner join cms_interface as tb_interface on tb_interface.id=tb_relation.interface_id
	where tb_interface.name='SITE'
";
$data = $DB->query($query);

$no_language = preg_replace("/_".LANGUAGE_REGEXP."/", '', $field_name);
reset($data);
while(list(, $row) = each($data)) {
	$row['field'] = $no_language.'_'.$row['code'];
	if ($row['field'] == $field_name) {
		$TmplDesign->set('current_language', $row['code']);
	}
	
	$TmplDesign->iterate('/language/', null, $row);
}

// ������� �������
$query = "select `$field_name` from `$table_name` where id='$id'";
$content = $DB->result($query);
$TmplDesign->set('content', id2url($content, true));
//echo $TmplDesign->display();

echo mod_deflate($TmplDesign->display());
?>