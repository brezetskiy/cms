<?php
/**
 * используется чтобы формировать транслитерацию
 * имени файла если установлеи модуль SEO   
 */
if(!empty($this->NEW["headline_".LANGUAGE_CURRENT])) {
	$tmppath = preg_replace(array('/\s+/', '/\'/', '/\?/'), array('-', '', ''), $this->NEW["headline_".LANGUAGE_CURRENT]);
	$tmppath = Charset::translit($tmppath);
	$tmppath = preg_replace(array('/\s+/', '/[^a-zA-Z0-9-_]/'), array('-', ''), $tmppath);
	
	/**
	 * Делаем проверку нету ли такого имени уже в базе 
	 */
	if(!empty($tmppath)) {
		if ($this->action_type == 'update') {
			$query = "select count(id) from news_message where path = '".$DB->escape($tmppath)."' and id <> '".$this->OLD['id']."'";
		} else {
			$query = "select count(id) from news_message where path = '".$DB->escape($tmppath)."'";
		}
		$result = $DB->result($query);
		if(!empty($result)) {
			$tmppath .= $this->NEW['id'];
		} 
		$DB->update("UPDATE news_message SET `path` = '".$DB->escape($tmppath)."' WHERE id = '".$this->NEW['id']."'");
	}
}

/**
 * Формирование ключевых слов страницы
 */
//if (empty($this->NEW["keywords_".LANGUAGE_CURRENT]) && !empty($this->NEW["content_".LANGUAGE_CURRENT])) {
//
//	$seo = new SEO($this->table['name'], $this->NEW['id']);
//	$seo->contentFromString($this->NEW["content_".LANGUAGE_CURRENT]);
//	
//	$query = "
//		update news_message 
//		SET
//			keywords_".LANGUAGE_CURRENT." = '".$DB->escape($seo->updateKeywords())."',
//			description_".LANGUAGE_CURRENT." = '".$DB->escape($seo->updateDescription())."'
//		where id = '".$this->NEW['id']."'	
//	";
//	$DB->update($query); 
//}


/**
 * Определяем URL
 */	
$query = "	
	SELECT GROUP_CONCAT(tb_type.uniq_name  SEPARATOR '/') as family
    FROM news_type_relation tb_relation 
    INNER JOIN news_type as tb_type ON (tb_relation.id = tb_type.id)
    WHERE tb_relation.parent = '".$this->NEW['type_id']."'
"; 
$family = $DB->result($query);

$query = "UPDATE `news_message` SET url='$family/{$this->NEW['uniq_name']}' WHERE id='{$this->NEW['id']}'"; 
$DB->update($query);
if ($this->NEW['type_id'] == 247){
$query = "INSERT INTO maillist_message (subject, show_in_archive,create_dtime,send_dtime,content_ru) 
	VALUES ('{$this->NEW['headline_ru']}', 1,'{$this->NEW['dtime']}', '{$this->NEW['dtime']}', '{$this->NEW['content_ru']}')";
$mess_id = $DB->insert($query);
$DB->insert("INSERT INTO maillist_message_category (message_id,category_id) VALUES ($mess_id, 38)");
}
if ($this->NEW['type_id'] == 248){
$query = "INSERT INTO maillist_message (subject, show_in_archive,create_dtime,send_dtime,content_ru) 
	VALUES ('{$this->NEW['headline_ru']}', 1,'{$this->NEW['dtime']}', '{$this->NEW['dtime']}', '{$this->NEW['content_ru']}')";
$mess_id = $DB->insert($query);
$DB->insert("INSERT INTO maillist_message_category (message_id,category_id) VALUES ($mess_id, 39)");
}
?>