<?php
/** 
 * Перевод шаблона 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$template_id = globalVar($_GET['template_id'], 0);

$query = "
	select
		id,
		translate_ru,
		translate_en,
		translate_uk
	from cms_language_template_translate
	where template_id='$template_id'
	order by priority asc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('edit', true);
$cmsTable->setParam('delete', true);
$cmsTable->setParam('add', false);
$cmsTable->addColumn('translate_ru', '30%', 'left', 'Translate');
$cmsTable->addColumn('translate_en', '30%', 'left', 'Translate');
$cmsTable->addColumn('translate_uk', '30%', 'left', 'Переклад');
echo $cmsTable->display();
unset($cmsTable);


echo "<br><br><br><center><a href=\"javascript:void(0);\" onclick=\"CenterWindow('/tools/cms/admin/show_template.php?template_id=$template_id', 'template', 800, 600, 1, 0);return false;\"><b>Посмотреть шаблон</b></a></center>";
?>