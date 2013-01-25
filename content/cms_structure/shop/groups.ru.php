<?php
/** 
 * Каталог электронного магазина 
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 
$group_id = globalVar($_GET['group_id'], 0);
$TmplContent->set('group_id', $group_id);

ShopEdit::reloadParamGroupId();

function cms_filter($row) {
	$row['name'] = "<a href='./?group_id=$row[id]'>$row[name]</a>";
	return $row;
}

function group_level($id, $level)
{
	global $DB;

	$query_level = "
		SELECT 
		group_id
		FROM shop_group
		WHERE id='$id'
	";
	$query_level_row = $DB->query($query_level);
	
	$id = !isset($query_level_row[0]["group_id"]) ? 0 : $query_level_row[0]["group_id"];
	if ($id==0) return $level;
	else return group_level($id, $level+1);
};

// Группы
$query = "
	SELECT 
		id,
		group_id,
		uniq_name,
		if(active=0, concat('<font color=gray>', name, '</font>'), name) as name,
		html_editor(id, 'shop_group', 'content_".LANGUAGE_ADMIN_DEFAULT."', 'Описание') as content,
		priority
	FROM shop_group
	WHERE group_id='$group_id'
	ORDER BY priority ASC
";


$group_level_id = group_level($group_id,1);

$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
if ($group_level_id==3)
{
	$cmsTable->setParam('excel_price_download', true);
	$cmsTable->setParam('excel_price_upload', true);
};
$cmsTable->addColumn('name', '70%');
$cmsTable->addColumn('content', '20%', 'center');
$TmplContent->set('cms_groups', $cmsTable->display());
unset($cmsTable);

if (!empty($group_id)) {
	function cms_filter_product($row) {
		$row['name'] = "<a href='./Info/?product_id=$row[id]'>$row[name]</a><br><span class=comment>$row[_description]</span>";
		return $row;
	}
	
	// Товары
	$query = "
		select
			tb_product.id,
			tb_product.name,
			tb_product._description,
			tb_product.price,
			tb_product.priority,
			tb_product.available
		from shop_product as tb_product
		 
		where tb_product.group_id='$group_id' 
		order by priority ASC
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('prefilter', 'cms_filter_product');
	$cmsTable->setParam('show_path', false);
	$cmsTable->setParam('add', false);
	$cmsTable->setParam('edit', false);
	$cmsTable->setParam('show_parent_link', false);
	$cmsTable->addEvent('add', './Info/?group_id='.$group_id, true, false, false, '/design/cms/img/event/table/new.gif', '/design/cms/img/event/table/new_over.gif', 'Добавить товар', null);
	$cmsTable->addColumn('name', '75%');
	$cmsTable->addColumn('price', '5%');
	$cmsTable->addColumn('available', '5%');
	$cmsTable->setColumnParam('price', 'editable', true);
	$cmsTable->setColumnParam('available', 'editable', true);
	$TmplContent->set('cms_products', $cmsTable->display());
	
	unset($cmsTable);
	
	function cms_filter_param($row) {
		if ($row['is_search']) {
			$row['name'] .= ' <img src="/img/shop/search.png" align="absmiddle" title="Используется при поиске">';
		} 
		if ($row['is_description']) {
			$row['name'] .= ' <img src="/img/shop/info.png" align="absmiddle" title="Используется при формировании краткого описания">';
		} 
		if ($row['required']) {
			$row['name'] .= ' <img src="/img/shop/star.png" align="absmiddle" title="Обязательное для заполнения поле">';
		} 
		if ($row['is_filter']) {
			$row['name'] .= ' <img src="/img/shop/filter.png" align="absmiddle" title="Используется для фильтрации">';
		} 
		return $row;
	}
	
	// свойства товаров
	$query = "
		select
			id,
			if(data_type='devider', concat('<b>', name, '</b>'), name) as name,
			is_description,
			is_filter,
			required,
			is_search,
			concat(uniq_name, ' (', data_type, ')') as uniq_name,
			description,
			priority
		from shop_group_param
		where group_id='$group_id'
		order by priority asc
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('show_parent_link', false);
	$cmsTable->setParam('prefilter', 'cms_filter_param');
	$cmsTable->setParam('show_path', false);
	$cmsTable->addColumn('name', '20%');
	$cmsTable->addColumn('uniq_name', '20%');
	$cmsTable->addColumn('description', '50%');
	$TmplContent->set('cms_params', $cmsTable->display());
	unset($cmsTable);
}
?>