<?php
/** 
 * Поля в формах на сайте
 * @package Pilot 
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$form_id = globalVar($_GET['form_id'], 0);

$field_types = $DB->fetch_column("
	select tb_enum.name, tb_enum.title_".LANGUAGE_CURRENT." as title
	from cms_field_enum as tb_enum
	inner join cms_field as tb_field on tb_enum.field_id = tb_field.id
	inner join cms_table as tb_table on tb_field.table_id = tb_table.id
	where 
		tb_table.name = 'form_field'
		and tb_field.name = 'type'
");

function cms_filter($row) {
	global $field_types;
	
	if ($row['type'] == 'devider') {
		$row['title'] = "<b>$row[title]</b>";
	}
	
	$row['type'] = $field_types[$row['field_type']];
	
	if (in_array($row['field_type'], array('enum','set','list'))) {
		$row['type'] = "<a href='./Values/?field_id=$row[id]'>$row[type]</a>";
	}
	
	return $row;
}

$query = "
	SELECT 
		id,
		uniq_name,
		concat(
			if(required, '<span style=\"color:red\">*</span>', ''),
			title_".LANGUAGE_SITE_DEFAULT.", '<br>',
			'<span class=\"comment\">', ifnull(comment_".LANGUAGE_SITE_DEFAULT.", ''), '</span>'
		) AS title,
		type,
		type as field_type,
		priority
	FROM form_field
	where form_id = '$form_id'
	ORDER BY priority
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('title', '40%');
$cmsTable->addColumn('type', '20%', 'left');
$cmsTable->addColumn('uniq_name', '20%');
echo $cmsTable->display();
unset($cmsTable);

?>