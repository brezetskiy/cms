<?php
$cmsShowEdit = new cmsShowEdit($table_id, $id, $copy);

// parent_field_id
$query = "
	SELECT
		id,
		id AS real_id,
		0 AS parent,
		name
	FROM cms_field
	WHERE 
		table_id=$id
	ORDER BY priority
";
$data = $DB->query($query, 'id');
$cmsShowEdit->setFKeyData('parent_field_id', $data);

// fk_show_id, fk_order_id
$query = "
	SELECT
		id,
		id AS real_id,
		0 AS parent,
		name
	FROM cms_field
	WHERE 
		table_id=$id and 
		fk_table_id=0
	ORDER BY priority
";
$data = $DB->query($query, 'id');
$cmsShowEdit->setFKeyData('fk_show_id', $data);
$cmsShowEdit->setFKeyData('fk_order_id', $data);

// relation_table_id
$query = "
	SELECT
		tb_relation.id,
		tb_relation.id AS real_id,
		0 AS parent,
		tb_relation.name
	FROM cms_table as tb_relation
	inner join cms_table as tb_table on tb_table.module_id=tb_relation.module_id
	WHERE tb_table.id='$id'
	ORDER BY tb_relation.name
";
$data = $DB->query($query, 'id');
$cmsShowEdit->setFKeyData('relation_table_id', $data);


$cmsShowEdit->parseFields();
echo $cmsShowEdit->show();
?>