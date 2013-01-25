<?php
/**
 * Редактирование описания картинки
 * @package Pilot
 * @subpackage Gallery
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$id = globalVar($_REQUEST['id'], 0);
$table_id = globalVar($_REQUEST['table_id'], 0);
$language = globalVar($_REQUEST['language'], '');
$description = globalVar($_REQUEST['description'], '');

// Проверка прав редактирования таблицы пользователем
if (!Auth::updateTable($table_id)) {
	Action::onError(cms_message('CMS', 'У Вас нет прав на редактирование таблицы %s.', $table_name));
}

$table = cmsTable::getInfoById($table_id);

if (!in_array($language, $table['languages'])) {
	Action::onError(cms_message('CMS', 'Таблица не поддерживает указанный язык'));
}

$query = "
	update $table[name] set
	description_$language = '".$DB->escape($description)."'
	where id = '$id'
";
$DB->update($query);

Action::setSuccess('Описание картинки сохранено', 'Gallery');

$show_description = CoolGallery::formatDescription($description);
$description = addslashes($description);

$_RESULT['javascript'] = "
	$('#gallery_descr_{$table_id}_{$id}').html('$show_description')
	$('#gallery_descr_{$table_id}_{$id}').attr('title', '$description')
";