<?php
/** 
 * Параметры настройки системы 
 * @package Pilot 
 * @subpackage Pilot 
 * @author Rudenko Ilya <rudenko@id.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */
echo '<div class="context_help">ВНИМАНИЕ! Обновление конфигурационного файла происходит только после того, как пользователь нажмёт сохранить на странице редактирования параметров. В этом разделе обновление конфигурации системы - не происходит.</div>';

$module_id = globalVar($_GET['module_id'], 0);


function row_filter($row){
	if($row['type'] == 'devider') {
		$row['name'] = "<b><div style=\"color:red;\">$row[name]</div></b>";
	}
	return $row;
}


$query = "
	SELECT
		tb_settings.id,
		tb_settings.name,
		tb_settings.type,
		if(
			tb_settings.unit='',
			tb_settings.value,
			concat(tb_settings.value, ' (', tb_settings.unit, ')')
		) as value,
		tb_settings.description_".LANGUAGE_CURRENT." AS description,
		tb_module.name as module,
		tb_settings.priority
	from cms_settings as tb_settings
	left join cms_module as tb_module on tb_module.id=tb_settings.module_id
	where tb_settings.module_id = $module_id 
	order by tb_settings.priority, tb_module.name, tb_settings.name
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'row_filter');
$cmsTable->addColumn('name', '15%');
$cmsTable->addColumn('type', '10%');
$cmsTable->addColumn('description', '50%');
echo $cmsTable->display();
unset($cmsTable);


?>


