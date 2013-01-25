<?php
/**
* ���������� �������� ������� � cmsView
* @package Pilot
* @subpackage Actions_Admin
* @version 3.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

if (empty($_REQUEST[$table_id])) {
	Action::finish();
}

/**
 * ��������� ����� �� ��������� �������
 */
if (!Auth::updateTable($table_id)) {
	Action::onError(cms_message('CMS', '� ��� ��� ���� �� ���������� �������� � ������� %s', $table_id));
}

/**
 * ������������ ���������� ���������
 */
$start_row = globalVar($_REQUEST['_start_row'], 0);
$filter = globalVar($_REQUEST['filter'], array());
$ajax_select = globalVar($_REQUEST['ajax_select'], array());
$table_language = globalVar($_REQUEST['_table_language'], LANGUAGE_CURRENT);
if (empty($table_language) && IS_DEVELOPER) {
	$table_language = LANGUAGE_CURRENT;
}
$fields = cmsTable::getFields($table_id);

/**
 * ������� ������ � ���������� � ��������� ��
 */
$sort_data = array();
reset($_REQUEST[$table_id]);
while (list($id, $row) = each($_REQUEST[$table_id])) {
	// �� ���������� �� ���������� ������, ��� ������� �� ������ id
	if (empty($id)) continue;
	$row['id'] = $id;
	
	reset($row);
	while (list($field, $val) = each($row)) {
		if (
			!isset($fields[$field])
			&& isset($fields[$field.'_'.$table_language])
			&& $fields[$field.'_'.$table_language]['is_multilanguage']
		) {
			// �������� ������� �� �������������
			unset($row[$field]);
			$row[$field.'_'.$table_language] = $val;
		} elseif (!isset($fields[$field])) {
			// ����� ������� ��� � �� � ��� �� ������������
			unset($row[$field]);
		}
		
	}
	
	if (count($row) == 2 && isset($row['priority'])) {
		$sort_data[ $row['id'] ] = $row['priority'];
	} else {
		$cmsEditAdd = new cmsEditAdd($table_id, $row, 'view', '', $ajax_select);
		$cmsEditAdd->dbChange();
		unset($cmsEditAdd);
	}
	
}

?>