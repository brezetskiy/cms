<?php
/**
 * Список новостей
 * @package News
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$type_id = globalVar($_REQUEST['type_id'], 0);

// Рубрики
$query = "
	select 
		tb_type.id,
		html_editor(id, 'news_type', 'content_".LANGUAGE_SITE_DEFAULT."', 'Описание') as about,
		concat('<a href=\"./?type_id=', id, '\">', tb_type.name_".LANGUAGE_CURRENT.", '</a>') as name,
		tb_type.title_".LANGUAGE_CURRENT." as title,
		tb_type.uniq_name,
		(
			select count(distinct t_message.id) 
			from news_type_relation as t_relation
			inner join news_message as t_message on t_message.type_id=t_relation.id
			where t_relation.parent=tb_type.id
		) as count_news,
		tb_type.priority
	from news_type as tb_type
	where type_id='$type_id'
	order by tb_type.priority
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('uniq_name', '10%');
$cmsTable->addColumn('about', '10%', 'center', 'Описание');
$cmsTable->addColumn('count_news', '10%', 'right', 'Новостей');
echo $cmsTable->display();
unset($cmsTable);


// Новости
if (!empty($type_id)) {
	$query = "
		SELECT 
			tb_message.id,
			CONCAT(html_editor(tb_message.id, 'news_message', 'content_".LANGUAGE_SITE_DEFAULT."', tb_message.headline_".LANGUAGE_SITE_DEFAULT."),
				'<br><span class=comment>', 
				IFNULL(REPLACE(tb_message.announcement_".LANGUAGE_SITE_DEFAULT.", '\n', '<br>'), ''),
				'</span>'
			) AS headline,
			case
				when date <> date_to and date_to <> '0000-00-00' and date_to is not null then concat(date_format(date, '".LANGUAGE_DATE_SQL."'),'-',date_format(date_to, '".LANGUAGE_DATE_SQL."'))
				else date_format(date, '".LANGUAGE_DATE_SQL."')
			end as date,
			tb_message.date as system_date,
			tb_message.active,
			tb_message.priority
		FROM news_message as tb_message
		WHERE tb_message.type_id='$type_id'
		ORDER BY tb_message.priority ASC
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->addColumn('date', '15%', 'center', 'Период');
	$cmsTable->setColumnParam('date', 'order', 'system_date');
	$cmsTable->addColumn('headline', '60%');
	$cmsTable->addColumn('active', '10%', 'center');
	$cmsTable->setColumnParam('active', 'editable', true);
	echo $cmsTable->display();
	unset($cmsTable);
}
?>