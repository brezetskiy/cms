<?php
$column_name = 'image';

//
//// ������� ����������� � �������
//if (!empty($this->uploads) && is_file($this->uploads[ $column_name ]['tmp_name'])) {
//	$thumb = Uploads::getImageLimits('news_type', $this->NEW['type_id']);
//	$path = Uploads::getStorage('news_message', 'image', $this->NEW['id']);
//	$Image = new Image($this->uploads[ $column_name ]['tmp_name']);
//	$Image->thumb(SITE_ROOT.'uploads/'.$path.'.jpg', $thumb['thumb_width'], $thumb['thumb_height']);
//	
//	// ��������� ����������, ��� ��� � ���������� ������ jpg
//	$DB->update("UPDATE news_message SET `image` = 'jpg' WHERE id = '".$this->NEW['id']."'");
//}
//

/**
 * ������������ ����� ����������� ��������������
 * ����� ����� ���� ���������� ������ SEO   
 */

if(!empty($this->NEW["headline_".LANGUAGE_CURRENT])) {
	$tmppath = preg_replace(array('/\s+/', '/\'/', '/\?/'), array('-', '', ''), $this->NEW["headline_".LANGUAGE_CURRENT]);
	$tmppath = Charset::translit($tmppath);
	$tmppath = preg_replace(array('/\s+/', '/[^a-zA-Z0-9-_]/'), array('-', ''), $tmppath);
	
	/**
	 * ������ �������� ���� �� ������ ����� ��� � ���� 
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
 * ������������ �������� ���� ��������
 */
 
if (empty($this->NEW["keywords_".LANGUAGE_CURRENT]) && !empty($this->NEW["content_".LANGUAGE_CURRENT])) {

	$seo = new SEO($this->table['name'], $this->NEW['id']);
	$seo->contentFromString($this->NEW["content_".LANGUAGE_CURRENT]);
	
	$query = "
		update news_message 
		SET
			keywords_".LANGUAGE_CURRENT." = '".$DB->escape($seo->updateKeywords())."',
			description_".LANGUAGE_CURRENT." = '".$DB->escape($seo->updateDescription())."'
		where id = '".$this->NEW['id']."'	
	";
	$DB->update($query); 
}
?>