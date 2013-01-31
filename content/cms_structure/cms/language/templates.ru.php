<?php
/** 
 * Система перевода шаблонов
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */
$parent_id = globalVar($_GET['parent_id'], 0);

function cms_filter($row) {
	$row['filename'] = "<a href='$row[link]'>$row[filename]</a>";
	return $row;
}

$query = "
	select
		id,
		if(
			type='dir',
			concat('<img border=0 src=/design/cms/img/icons/folder.gif> ', filename),
			concat('<img border=0 src=/design/cms/img/icons/ie.gif> ', filename)
		) as filename,
		if (
			type='dir',
			concat('?parent_id=', id),
			concat('./Translate/?template_id=', id)
		) as link,
		(
			select count(t_template.id)
			from cms_language_template_relation as t_relation
			inner join cms_language_template as t_template on t_relation.id=t_template.id
			where 
				t_relation.parent=tb_template.id and 
				t_template.translate_en='false'
		) as translate_en,
		(
			select count(t_template.id)
			from cms_language_template_relation as t_relation
			inner join cms_language_template as t_template on t_relation.id=t_template.id
			where 
				t_relation.parent=tb_template.id and 
				t_template.translate_uk='false'
		) as translate_uk
	from cms_language_template as tb_template
	where parent_id='$parent_id'
	order by type asc, filename asc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->setParam('add', false);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('filename', '70%');
$cmsTable->addColumn('translate_en', '15%', 'right', 'Перевод [en]');
$cmsTable->addColumn('translate_uk', '15%', 'right', 'Перевод [uk]');
echo $cmsTable->display();
unset($cmsTable);

echo '<br><br><br><center><a href="/'.LANGUAGE_URL.'action/admin/sdk/template/?_return_path='.CURRENT_URL_LINK.'"><b>Обновить информацию</b></a></center>';
?>

