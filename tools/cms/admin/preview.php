<?php
/**
* Основная страница
* @package Pilot
* @subpackage Executables
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');


/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

/**
* Определяем поступившие в скрипт переменные
*/
$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$extension = globalVar($_GET['extension'], '');

if (empty($table_name) || empty($field_name) || empty($id) || empty($extension)) {
	trigger_error(cms_message('CMS', 'Неправильно указано имя стиля. Допускаются только английские буквы, цифры и знак подчеркивания.'), E_USER_ERROR);
}

$file = Uploads::getFile($table_name, $field_name, $id, $extension);
if (!is_file($file)) {
	trigger_error(cms_message('CMS', 'Не найден файл с картинкой %s', $file), E_USER_ERROR);
}


/**
 * Файлы, которые не являются картинками - предлагаем пользователю сохранить
 */
$imagetype = getimagesize($file);
if (empty($imagetype) || !in_array($imagetype[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_SWF, IMAGETYPE_PSD, IMAGETYPE_BMP, IMAGETYPE_WBMP, IMAGETYPE_XBM, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM, IMAGETYPE_IFF, IMAGETYPE_JB2, IMAGETYPE_JPC, IMAGETYPE_JP2, IMAGETYPE_JPX, IMAGETYPE_SWC))) {
	header('Content-Type: application/x-zip-compressed'); 
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	echo file_get_contents($file);
	exit;
}

$html_img = Uploads::htmlImage($file);
$html_img = str_replace('src="', 'src="'.CMS_URL, $html_img);

$TmplDesign = new Template(SITE_ROOT.'templates/cms/admin/preview');
$TmplDesign->set('html_img', $html_img);
echo $TmplDesign->display();
?>