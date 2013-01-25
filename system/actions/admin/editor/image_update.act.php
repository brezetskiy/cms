<?php
/**
 * Обновление описания картинки в БД
* @package Pilot
* @subpackage Editor
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$alt = globalVar($_REQUEST['alt'], '');
$url = globalVar($_REQUEST['src'], '');
$url = substr($url, strlen(CMS_URL.'uploads/'));

// Удаляем _thumb и раширение файла
if (substr($url, strrpos($url, '.') - 6, 6) == '_thumb') {
	$url = substr($url, 0, strrpos($url, '.') - 6);
} else {
	$url = substr($url, 0, strrpos($url, '.'));
}

if (empty($alt)) {
	
	// Удаляем запись с информацией о картинке
	$query = "DELETE FROM cms_image WHERE url='$url' LIMIT 1";
	$DB->delete($query);
	
} else {
	
	// Изменяем запись о картинке
	$query = "REPLACE INTO cms_image (url, title) VALUES ('$url', '$alt')";
	$DB->insert($query);
	
}

echo '<html>
<body>
<script language="JavaScript">
window.close();
</script>
</body>
</html>';

exit;
?>