<?php

/** 
 * Фотогалерея, привязка к структуре сайта, без вывода групп 
 * @package Pilot 
 * @subpackage Hamalia 
 * @author Eugen Golubenko <eugen@delta-x.com.ua>
 * @author Yaschenko Yuriy <yura@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */ 
$url    = trim(globalVar($_GET['_GALLERY_URL'], ''), '/');
$offset = globalVar($_REQUEST['offset'], 0);
$group  = $DB->query_row("SELECT * FROM gallery_group WHERE url = '$url'");


/**
 * Получаем дочерние группы
 */
$query = (!empty($url))? "
	SELECT tb_group.id, tb_group.url, tb_group.name_".LANGUAGE_CURRENT." AS name , tb_photo.id as photo_id, tb_photo.photo
	FROM gallery_group AS tb_group 
	LEFT JOIN gallery_photo tb_photo ON tb_photo.group_id = tb_group.id 
	WHERE tb_group.group_id = '{$group['id']}'
	GROUP BY tb_group.id
":"SELECT id, url, name_".LANGUAGE_CURRENT." AS name, photo FROM gallery_group WHERE group_id = '0'";
$child_group = $DB->query($query);

reset($child_group);
while (list(, $row) = each($child_group)) {
	$row['photo_id'] = (!empty($row['photo_id'])) ? $row['photo_id'] : 0;
	$row['photo'] 	 = (!empty($row['photo'])) ? $row['photo'] : 'jpg';
	
	if(!empty($row['photo_id'])){
		$photo = Uploads::getFile('gallery_photo', 'photo', $row['photo_id'], $row['photo']);
		$row['photo'] = Uploads::getImageURL($photo);
	} else {
		$row['photo'] = "gallery_group/photo/" . Uploads::getIdFileDir($row['id']) . "." . $row['photo'];
	}
	
	$TmplContent->iterate('/child/', null, $row);	
}


/**
 * Усли есть родительская группа получаем ее
 */
if ($group['group_id']>0){
	$query = "SELECT url FROM gallery_group WHERE id = '{$group['group_id']}'";
	$parent_group = $DB->result($query);
	$TmplContent->set('parent', $parent_group);
}

if (!empty($url)){
	$TmplDesign->set('title', 'Фотогалерея - '.$group['name_'.LANGUAGE_CURRENT]);
	$TmplDesign->set('page_title', 'Фотогалерея - '.$group['name_'.LANGUAGE_CURRENT]);
	$TmplContent->set('gallery_url', $group['url']);
	$TmplDesign->set('headline', $group['name_'.LANGUAGE_CURRENT]);
}


/**
 * Получаем все фото группы
 */
$query = "
	SELECT SQL_CALC_FOUND_ROWS
		id, 
		description_".LANGUAGE_CURRENT." AS comment, 
		photo 
	FROM gallery_photo
	WHERE group_id='".$group['id']."' 
	ORDER BY priority ASC
". Misc::limit_mysql(GALLERY_PHOTO_PAGE, 0, $offset);
$photos = $DB->query($query);

$total_rows = $DB->result("SELECT FOUND_ROWS()");
$TmplContent->set('pages_list', Misc::pages($total_rows, GALLERY_PHOTO_PAGE, 10, '0', true, true));

$img_rows = ceil($DB->rows / 3);
$counter = 0;
$TmplContent->set('photocount', $total_rows);

reset($photos);
while (list($index, $row) = each($photos)) {
	$photo = Uploads::getFile('gallery_photo', 'photo', $row['id'], $row['photo']);
	$row['photo'] = Uploads::getImageURL($photo);
	$row['index'] = $index;
	$TmplContent->iterate('/photo/', null, $row);
	$counter++;
}
$index = count($photos);


/**
 * Выводим пустые клетки таблицы, если число изображений не кратно 3-м
 */
while ($counter < $img_rows*3) {
	$TmplContent->iterate('/photo/', null, array('index' => $index));
	$index++;
	$counter++;
}


/**
 * Определяем текущий раздел
 
$group = $DB->query_row("SELECT id, url, name_".LANGUAGE_CURRENT." AS name FROM site_structure WHERE url = '$url'");
if ($DB->rows == 0) {
	$group_id = 0;
} else {
	$group_id = $group['id'];
	$TmplDesign->set('title', 'Фотогалерея - '.$group['name']);
	$TmplDesign->set('page_title', 'Фотогалерея - '.$group['name']);
	$TmplContent->set('gallery_url', $group['url']);
}


 * Информация о текущем разделе галереи и путь к нему

$query = "
	SELECT 
		tb_structure.name_".LANGUAGE_CURRENT." AS name,
		CONCAT('/', tb_structure.url, '/') AS url
	FROM site_structure_relation  AS tb_relation
	INNER JOIN site_structure AS tb_structure ON tb_relation.parent = tb_structure.id
	WHERE tb_relation.id = '$group_id'
	ORDER BY tb_relation.priority
";
$path = $DB->query($query);

$TmplDesign->cleanIterate('/path/');
$TmplDesign->iterate('/path/', null, array('url' => '/', 'name'=>cms_message('cms', 'Главная')));
reset($path);
while (list(,$row)=each($path)) {
	$TmplDesign->iterate('/path/', null, $row);
}



 * Выводим фотографии

$query = "
	SELECT SQL_CALC_FOUND_ROWS
		id, 
		description_".LANGUAGE_CURRENT." AS comment, 
		photo 
	FROM gallery_photo
	WHERE structure_id='".$group_id."' 
	ORDER BY priority ASC
";
$query .= Misc::limit_mysql(9, 0, $offset);
$photos = $DB->query($query);

$total_rows = $DB->result("SELECT FOUND_ROWS()");
$TmplContent->set('pages_list', Misc::pages($total_rows, 9, 10, '0', true, true));

$img_rows = ceil($DB->rows / 3);
$counter = 0;


reset($photos);
while (list($index, $row) = each($photos)) {
	$photo = Uploads::getFile('gallery_photo', 'photo', $row['id'], $row['photo']);
	$row['photo'] = Uploads::htmlImage($photo);
	$row['index'] = $index;
	
	$TmplContent->iterate('/photo/', null, $row);
	
	$counter++;
}

$index = count($photos);

 * Выводим пустые клетки таблицы, если число изображений не кратно 3-м

while ($counter < $img_rows*3) {
	$TmplContent->iterate('/photo/', null, array('index' => $index));
	$index++;
	$counter++;
}
*/



?>