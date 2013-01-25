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

$content_id = globalVar($_GET['content_id'], 0);
$field_id = globalVar($_GET['field_id'], 0);

function set_links($row) {
	$row['error'] = "<a target=\"_blank\" href=\"http://".$row['site'].$row['url']."\">".$row['error']."</a>";
	return $row;
}


$query = "
	select
		tb_spellcheck.*,
		tb_content.url,
		tb_structure.url as site
	from search_spell_check as tb_spellcheck
	inner join search_content as tb_content on tb_content.id = tb_spellcheck.content_id and tb_content.field_id = tb_spellcheck.field_id 
	inner join site_structure as tb_structure on tb_structure.id = tb_content.site_id
	where tb_spellcheck.content_id = '".$content_id."' and  tb_spellcheck.field_id = '".$field_id."'
";

$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'set_links');
$cmsTable->setParam('title', 'Список ошибок');
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', '/Admin/Site/Spellcheck/?');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->addColumn('error', '50%', 'left', 'Ошибка');
$cmsTable->addColumn('fixed', '50%', 'left', 'Варианты исправления');
echo $cmsTable->display();
unset($cmsTable);



