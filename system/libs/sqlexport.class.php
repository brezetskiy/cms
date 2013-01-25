<?php
/**
* ����� �������� ������ �� ������
* @package Pilot
* @subpackage SDK
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X ltd, 2005
*/

/**
* ����� �������� ������ �� ������
* @package Database
* @subpackage SDK
*/
class SQLExport {
	
	/**
	 * ������� ������
	 *
	 * @param int $table_id
	 * @param int $parent_id
	 * @param array $export_id
	 */
	static function export2xml($table_id, $parent_id, $export_tables = array(), $export_id = 0) {
		global $DB;
		
		// �������������� ������ �� �������, ������� ���� � export_tables,
		// ���� ���� ������ �� ������
		if (!empty($export_tables) && !in_array($table_id, $export_tables)) {
			return;
		}
		
		// ���������� ��� ������� �� �� id
		$query = "
			SELECT 
				tb_table.name AS table_name,
				(SELECT name FROM cms_field WHERE id=parent_field_id) AS parent_field
			FROM cms_table AS tb_table
			WHERE tb_table.id='".$table_id."'
		";
		$table_info = $DB->query_row($query);
		if ($DB->rows == 0) {
			Action::onError(cms_message('CMS', '������� #%d - �� ����������.', $table_id));
		}
		
		// ���������� ������
		$query = "
			SELECT * 
			FROM ".$table_info['table_name'];
		
		// � ������� ���� ������������ ����
		if (!empty($table_info['parent_field']) && empty($export_id)) {
			$query .= " WHERE ".$table_info['parent_field']."='".$parent_id."'";
		
		// ������� id, ������� ���������� �������������� (������ ��� ������ ��������)
		} elseif (!empty($export_id)) {
			$query .= " WHERE id IN (0".implode(",", $export_id).")";
		}
		
		$data = $DB->query($query);
		
		// � ������ ������� ��� �����������
		if ($DB->rows == 0) return;
		
		// ������������ ����������
		reset($data);
		while(list(,$row) = each($data)) {
			echo "\n<".$table_info['table_name']." ";
			
			reset($row);
			while(list($key, $val) = each($row)) {
				if ($key == $table_info['parent_field'] || empty($val)) {
					continue;
				} else {
					echo $key.'="'.iconv(CMS_CHARSET, 'UTF-8', htmlspecialchars($val, ENT_COMPAT, CMS_CHARSET)).'" ';
				}
			}

			echo '>';
			
			// ���������� �������� �������
			$query = "
				SELECT tb_table.id, tb_table.name AS table_name
				FROM cms_table AS tb_table
				WHERE tb_table.parent_field_id IN (
					SELECT id
					FROM cms_field
					WHERE fk_table_id='".$table_id."'
				)
			";
			$parent_tables = $DB->query($query);
			
			// ������������ ������ �� �������� ������
			reset($parent_tables);
			while(list(, $table) = each($parent_tables)) {
				self::export2xml($table['id'], $row['id'], $export_tables);
			}
			
			echo '</'.$table_info['table_name'].'>';
		}
	}
}