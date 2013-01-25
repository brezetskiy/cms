<?php
/**
* Модуль поиска ошибок 
* на страницах
*
* @package CMS
* @subpackage Search
* @version 3.0
* @author Markovskiy Dima <dima@delta-x.com.ua>
* @copyright Copyright 2010, Delta-X ltd.
*/

function form_param($row) {
	$row['url'] = "<a href=\"/Admin/Site/Spellcheck/Errorlist/?content_id=".$row['content_id']."&field_id=".$row['field_id']."\">http://".$row['site'].$row['url']."</a>";
	return $row;
}


$query = "
	select
		tb_spell.*,
		count(DISTINCT tb_spell.error) as counterror,
		tb_content.url,
		tb_structure.url as site
	from search_spell_check as tb_spell
	inner join search_content as tb_content on tb_content.id = tb_spell.content_id and tb_content.field_id = tb_spell.field_id
	inner join site_structure as tb_structure on tb_structure.id = tb_content.site_id
	group by 
		tb_spell.content_id,
		tb_spell.field_id
	order by counterror desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'form_param');
$cmsTable->setParam('title', 'Страницы с ошибками');
$cmsTable->setParam('add', false);
//$cmsTable->setParam('delete', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('url', '75%', 'left', 'Основной адрес');
$cmsTable->addColumn('counterror', '25%', 'left', 'Количество ошибок');
echo $cmsTable->display();
unset($cmsTable);