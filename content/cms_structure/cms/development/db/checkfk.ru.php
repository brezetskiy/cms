<?php
/**
 * Проверка целостности внешшних ключей
 * @package CMS
 * @subpackage Content_Admin
 * @author Ilya Rudenko <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

//  Определяем внешние ключи (родителей) и поля, на которые они ссылаются
$query = "
	SELECT
		tb_table.name AS child_table,
		tb_field.name AS child_field,
		tb_parent.name AS parent_table
	FROM cms_field AS tb_field
	INNER JOIN cms_table AS tb_table ON tb_table.id=tb_field.table_id
	INNER JOIN cms_table AS tb_parent ON tb_parent.id=tb_field.fk_table_id
	WHERE 
		tb_field.fk_table_id!=0
		AND tb_field.name NOT LIKE '\_%'
";
$parents = $DB->query($query);

$query = "SET SQL_MODE=''";
$DB->query($query);

reset($parents);
while(list(,$row) = each($parents)) {
	$query = "
		SELECT tb_child.`$row[child_field]` AS id
		FROM `$row[child_table]` AS tb_child
		LEFT JOIN `$row[parent_table]` AS tb_parent ON tb_parent.id=tb_child.`$row[child_field]`
		WHERE 
			tb_parent.id IS NULL
			AND tb_child.`".$row['child_field']."`!=0
			AND tb_child.`".$row['child_field']."` IS NOT NULL
		GROUP BY tb_child.`$row[child_field]`
		LIMIT 1
	";
	$id_list = $DB->fetch_column($query, 'id', 'id');
	if (empty($id_list)) {
		continue;
	}
	
	$row['id'] = implode(", ", $id_list);
	
	$TmplContent->iterate('/error/', null, $row);
	
}
?>