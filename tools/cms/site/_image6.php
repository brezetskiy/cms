<?php
/**
* Окно, которое показывает картинку
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
define('CMS_INTERFACE', 'SITE');

/**
* Configuration
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$url = globalVar($_GET['url'], '');

/**
 * Безопасность
 */
if ('/uploads/' != substr($url, 0, strlen('/uploads/'))) {
	trigger_error('File must be within allowed path /uploads/', E_USER_ERROR);
}


/**
 * Определяем заголовок для страницы с картинкой
 */
$filename = substr($url, strlen('/uploads/'));
$table_name = substr($filename, 0, strpos($filename, DIRECTORY_SEPARATOR));
$field_name = substr($filename, strlen($table_name) + 1, strpos($filename, DIRECTORY_SEPARATOR, strlen($table_name) + 1) - strlen($table_name) - 1);
$filename = substr($filename, 0, strrpos($filename, '.'));
$id = intval(str_replace(DIRECTORY_SEPARATOR, '', substr($filename, strlen($table_name) + strlen($field_name) + 2)));

/**
 * Определяем имя родительского поля. это необходимо для того, что б пролистывание работало
 * только в пределах одной ветки. При пролистывании фотографий из фотогаллереи
 */
$query = "
	SELECT 
		tb_parent.name AS parent,
		tb_show.name AS `show`,
		tb_show._is_multilanguage as is_multilanguage,
		(SELECT name FROM cms_field WHERE table_id=tb_table.id AND name='priority') AS priority
	FROM cms_table AS tb_table
	INNER JOIN cms_field AS tb_field ON tb_table.id=tb_field.table_id
	INNER JOIN cms_field AS tb_parent ON tb_parent.id=tb_table.parent_field_id
	INNER JOIN cms_field AS tb_show ON tb_show.id=tb_table.fk_show_id
	WHERE 
		tb_field.name='$field_name'
		AND tb_table.name='$table_name'
		AND tb_field.field_type='file'
";
$table_info = $DB->query_row($query);
if (!empty($table_info)) {
	/**
	 * ВНИМАНИЕ!!! По соображениям безопасности модуль заблокирован только для
	 * вывода информации из таблицы gallery_photo. Так как любой пользователь интернета
	 * сможет получить доступ к информации отображаемого поля (fk_field) любой таблицы, 
	 * которая имеет картинки.
	 */
	$table_name = 'gallery_photo';
	$query = "
		SELECT 
			id,
			`$field_name` AS extension
		FROM `$table_name`
		WHERE `$table_info[parent]`=(SELECT `$table_info[parent]` FROM `$table_name` WHERE id='$id')
	";
	if (!empty($table_info['priority'])) {
		$query .= "ORDER BY priority ASC";
	}
	$files = $DB->query($query);
	reset($files);
	while(list($index,$row) = each($files)) {
		$files[$index] = UPLOADS_ROOT."$table_name/$field_name/".Uploads::getIdFileDir($row['id']).'.'.$row['extension'];
	}
	// Определяем подпись к картинке
	if ($table_info['is_multilanguage']) {
		$table_info['show'] = $table_info['show'] . '_' . LANGUAGE_CURRENT;
	}
	$query = "SELECT `$table_info[show]` FROM `$table_name` WHERE id='$id'";
	// 214. Картинка с сайта 
	$alt = $DB->result($query, cms_message('CMS', 'Картинка с сайта'));
} else {
	$files = Filesystem::getDirContent(SITE_ROOT.substr(dirname($url), 1), true, false, true);
	$query = "SELECT title FROM cms_image WHERE url='$filename'";
	// 214. Картинка с сайта 
	$alt = $DB->result($query, cms_message('CMS', 'Картинка с сайта'));
}


$back = $forward = $first = $last = $prev = $next_image = $first_image = '';
$current = false;

reset($files);
while(list($index,$row) = each($files)) {
	if (substr($row, strrpos($row, '.') - 6, -4) == '_thumb') {
		continue;
	}
	
	if ($first == '') {
		$first_image = '/tools/cms/site/image6.php?url='.substr($row, strlen(SITE_ROOT) - 1);
		$first = '<a href="/tools/cms/site/image6.php?url='.substr($row, strlen(SITE_ROOT) - 1).'"><img src="/design/cms/img/button/first.gif" border="0"></a>';
	}
	if ($current == false && $row == SITE_ROOT.substr($url, 1)) {
		// стрелка назад
		$back = (empty($last)) ? '' : '<a href="/tools/cms/site/image6.php?url='.substr($prev, strlen(SITE_ROOT) - 1).'"><img src="/design/cms/img/button/previous.gif" border="0"></a>';
		$current = true;
	}
	if ($current == true && $row != SITE_ROOT.substr($url, 1) && $forward == '') {
		// стрелка вперёд
		$forward = '<a href="/tools/cms/site/image6.php?url='.substr($row, strlen(SITE_ROOT) - 1).'"><img src="/design/cms/img/button/next.gif" border="0"></a>';
		$next_image = '/tools/cms/site/image6.php?url='.substr($row, strlen(SITE_ROOT) - 1);
	}
	
	$last = '<a href="/tools/cms/site/image6.php?url='.substr($row, strlen(SITE_ROOT) - 1).'"><img src="/design/cms/img/button/last.gif" border="0"></a>';
	$prev = $row;
}

$image_click = (empty($next_image)) ? $first_image : $next_image;

echo '<HTML>
<HEAD>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
	<SCRIPT language="JavaScript" src="/js/shared/global.js"></SCRIPT>
	<TITLE>'.$alt.'</TITLE>
	<STYLE>
		BODY {
			font-family : Verdana,Geneva,Arial;
			font-size : 11px;
			margin: 0px 0px 0px 0px;
			background-color : white;
		}
		TD {
			font-family : Verdana,Geneva,Arial;
			font-size : 11px;
		}
		TR.control TD {
			border-top: 1px solid gray;
			border-bottom: 1px solid gray;
			background-color: #DDDDDD;
			padding: 1px 3px 1px 3px;
		}
		TD.content {
			text-align:center;
			vertical-align:middle;
			padding: 30px 0 5px 0;
		}
	</STYLE>
</HEAD>
<BODY onContextMenu="return contextMenu();" onLoad="resizeImageDialog(\'image\');" onKeyPress="EnterEsc(event);">
<div style="position:absolute;width:100%;">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
<tr class="control">
	<td width="15%"><nobr>'.$first.' '.$back.'&nbsp;</nobr></td>
	<td width="70%" align="center">'.$alt.'&nbsp;</td>
	<td width="15%" align="right"><nobr>&nbsp;'.$forward.' '.$last.'</nobr></td>
</tr>
</table>
</div>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="content" height="100%"><img onrightclick="alert(123)" onclick="document.location.href=\''.$image_click.'\'" src="'.$url.'" border="0" id="image"></td>
</tr>
</table>
</BODY>
</HTML>';

?>