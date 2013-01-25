<?php
/**
* ��������� �� ���� ����� ��� �������� ������
* @package Pilot
* @subpackage Editor
* @version 5.3
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2006
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

new Auth('admin');


$type = globalVar($_GET['type'], 'form');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/form/prop_'.$type);

$element = 'input';
if ($type=='textarea') {
	$element = 'textarea';
} elseif ($type == 'dropdown') {
	$element = 'select';
}

/**
 * ������� ������� ������, ������� ������������ �������������
 */
require_once(INC_ROOT.'editor/editor.inc.php');
$style = parse_css(SITE_ROOT.'css/site/content.css');
reset($style);
while(list(,$row) = each($style)) {
	if(strtolower($row['element']) != $element) continue;
	$TmplDesign->iterate('/style/', null, array('title' => $row['title'], 'class' => $row['class']));
}
unset($style);

echo $TmplDesign->display();

?>