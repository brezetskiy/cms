<?php
/**
* Класс Uploads
* @package Pilot
* @subpackage CMS
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X ltd, 2004
*/

/**
* Класс по работе с закачаными файлами
* @package Pilot
* @subpackage CMS
*/
class Uploads {
	
	/**
	 * Определяет путь к файлу, который сохраняется через редактор или систему в UPLOADS_ROOT и CONTENT_ROOT
	 *
	 * @param string $table_name
	 * @param string $field_name
	 * @param int $id
	 * @return string
	 */
	static function getStorage($table_name, $field_name, $id) {
		return strtolower($table_name.'/'.$field_name.'/'.self::getIdFileDir($id));
	}
		
	/**
	 * По имени файла определяет его URL адрес
	 * @param string $file
	 * @return string
	 */
	static function getURL($file) {
		if (false === strpos($file, SITE_ROOT)) {
			return '';
		} else {
			return substr($file, strlen(SITE_ROOT) - 1);
		}
	}
	
	/**
	 * По имени файла определяет адрес картинки без префикса /uploads/
	 * @param string $file
	 * @return string
	 */
	static function getImageURL($file) {
		if (false === strpos($file, UPLOADS_ROOT)) {
			return '';
		} else {
			return substr($file, strlen(UPLOADS_ROOT));
		}
	}
	
	/**
	 * Определяет расширение файла
	 * @param string $file
	 * @return mixed
	 */
	static function getFileExtension($file) {
		if (false === ($start = strrpos($file, '.'))) {
			return false;
		} else {
			return substr($file, $start + 1);
		}
	}
	
	/**
	* Определяет группирующую директорию картинки
	* 
	* Когда закачивается большое количество картинок, например несколько тысяч,
	* то с ними тяжело работать. Для этого мы разбиваем их по сотням в директории
	* 
	* @param int $id
	* @return string
	*/
	static function getIdFileDir($id) {
		return sprintf("%04d/%02d", intval($id / 100), intval($id % 100));
	}
	
	/**
	* Определяет имя файла
	* @param string $table_name
	* @param string $field_name
	* @param int $id
	* @param string $extension
	* @return string
	*/
	static function getFile($table_name, $field_name, $id, $extension) {
		return UPLOADS_ROOT . $table_name .'/'. $field_name .'/'. self::getIdFileDir($id) .'.'. $extension;
	}
	
	/**
	 * Создает HTML для картинки или Flash
	 * Раньше была функция Uploads::getHTML($image, $big_image = '', $attrib = ' border="0"', $alt = '')
	 *
	 * @param string $image_file
	 * @param string $attrib
	 */
	static public function htmlImage($image_file, $attrib = ' border="0"') {
		$image_url = self::getURL($image_file);
		
		// Проверка наличия картинки
		if (!is_file($image_file)) {
			return '<img src="/img/shared/1x1.gif" alt="'.cms_message('CMS', 'Не найден файл с картинкой %s', $image_url).'">';
		}
		
		$thumbnail_file = self::getThumb($image_file);
		$image_type = getimagesize($image_file);
		if ($image_type[2] == IMAGETYPE_SWF || $image_type[2] == IMAGETYPE_SWC) {
			// Flash
			return '
				<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" '.$image_type[3].' id="map">
				<param name="allowScriptAccess" value="sameDomain" />
				<param name="movie" value="'.$image_url.'" />
				<param name="menu" value="false" />
				<param name="quality" value="high" />
				<param name="bgcolor" value="#FFFFFF" />
				<embed src="'.$image_url.'" menu="false" quality="high" bgcolor="#FFFFFF" '.$image_type[3].' name="map" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
				</object>
			';
		} elseif (is_file($thumbnail_file)) {
			// У картинки есть пиктограмма
			$thumbnail_type = getimagesize($thumbnail_file);
			$thumbnail_url = self::getURL($thumbnail_file);
			return '<a class="image" href="javascript:void(0);" onclick="showImage(\''.$image_url.'\');"><img src="'.$thumbnail_url.'" '.$thumbnail_type[3].' '.$attrib.'></a>';
		} else {
			// У картинки нет пиктограммы
			return '<img src="'.$image_url.'" '.$image_type[3].' '.$attrib.'>';
		}
	}
	
	
	/**
	 * Возвращает путь к файлу с пиктограммой не зависимо от того есть он или нет
	 *
	 * @param string $file
	 * @return string
	 */
	static function getThumb($file) {
		return substr($file, 0, strrpos($file, '.')) . '_thumb.jpg';
	}
	
	/**
	 * Возвращает пиктограмму к картинке
	 *
	 * @param string $image_file
	 * @param string $attrib
	 */
	static public function thumbImage($image_file, $attrib = ' border="0"') {
		// Проверка наличия картинки
		if (!is_file($image_file)) {
			return '<img src="/img/shared/1x1.gif" alt="'.cms_message('CMS', 'Не найден файл с картинкой %s', $image_file).'">';
		}
		
		$thumbnail_file = self::getThumb($image_file);
		$file = (is_file($thumbnail_file)) ? $thumbnail_file : $image_file;
		$type = getimagesize($file);
		return '<img src="'.substr($file, strlen(SITE_ROOT)-1).'" '.$type[3].' '.$attrib.'>';
	}
		
	/**
	 * Создает HTML для картинки, которая увеличивется через JqueryLightbox
	 *
	 * @param string $image_file
	 * @param string $attrib
	 */
	static public function lightboxImage($image_file, $title, $group = 'group', $attrib = ' border="0"') {
		// Проверка наличия картинки
		if (!is_file($image_file)) {
			return '<img src="/img/shared/1x1.gif" alt="'.cms_message('CMS', 'Не найден файл с картинкой %s', $image_file).'">';
		}
		
		$image_url = self::getURL($image_file);
		$thumbnail_file = self::getThumb($image_file);
		
		if (is_file($thumbnail_file)) {
			// У картинки есть пиктограмма
			$thumbnail_type = getimagesize($thumbnail_file);
			return '<a target="_blank" rel="lightbox-'.$group.'" href="'.$image_url.'" title="'.addcslashes($title, '"').'"><img src="'.substr($thumbnail_file, strlen(SITE_ROOT)-1).'" '.$thumbnail_type[3].' '.$attrib.'></a>';
		} else {
			// У картинки нет пиктограммы
			$image_type = getimagesize($image_file);
			return '<img src="'.$image_url.'" '.$image_type[3].' '.$attrib.' />';
		}
	}
	
	/**
	* Перемещает закачанный файл в новую директорию, если директория не существует, то создает ее
	* @param $uploaded_file
	* @param $new_file
	* @return bool
	*/
	static public function moveUploadedFile($uploaded_file, $new_file) {
		if (!is_dir(dirname($new_file))) {
			makedir(dirname($new_file), 0777, true);
		}
		
		$return = move_uploaded_file($uploaded_file, $new_file);
		if ($return === true) {
			chmod($new_file, 0640);
		}
		
		return $return;
	}
	
	/**
	* Возврящает сообщение об ошибе в случае если такова имела место.
	* @static 
	* @param int $errno
	*/
	static public function check($errno) {
		$return = '';
		switch ($errno) {
			case UPLOAD_ERR_OK:
				$return = '';
				break;
			case UPLOAD_ERR_INI_SIZE:
				$return = cms_message('CMS', 'The uploaded file exceeds the upload_max_filesize directive in php.ini.');
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$return = cms_message('CMS', 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
				break;
			case UPLOAD_ERR_PARTIAL:
				$return = cms_message('CMS', 'The uploaded file was only partially uploaded.');
				break;
			case UPLOAD_ERR_NO_FILE:
				$return = cms_message('CMS', 'No file was uploaded.');
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$return = cms_message('CMS', 'Missing a temporary folder.');
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$return = cms_message('CMS', 'Failed to write file to disk.');
				break;
		}
		return $return;
	}
	
	/**
	 * Выводит ссылку на скачивание аттача
	 *
	 * @param string $url
	 * @param string $title
	 * @return string
	 */
	static function htmlAttach($url, $title = '') {
		if (substr($url, 0, strlen(UPLOADS_DIR) + 1) != '/'.UPLOADS_DIR) {
			return '';
		}
		return '<a href="/tools/cms/site/download.php?url='.$url.'">'.$title.'</a>';
	}
	
	
	/**
	 * Иконка к файлу, которая соответсвует его типу
	 *
	 * @param string $file
	 * @return string
	 */
	static function getIcon($file) {
		$extension = strtolower(self::getFileExtension($file));
		if (is_file(SITE_ROOT.'img/shared/ico/'.$extension.'.gif')) {
			$img = '<img src="/img/shared/ico/'.$extension.'.gif" border="0">';
		} else {
			$img = '';
		}
		return $img;
	}
	
	/**
	 * Удаляет файл с картинкой
	 *
	 * @param string $file
	 */
	static public function deleteImage($file) {
		global $DB;
		
		$url = substr($file, strlen(UPLOADS_ROOT));
		$delete[] = $file;
		$delete[] = substr($file, 0, strrpos($file, '.')).'_thumb.jpg';
		
		$query = "select uniq_name from cms_image_size";
		$parser = $DB->fetch_column($query);
		reset($parser);
		while (list(,$row) = each($parser)) {
			$delete[] = SITE_ROOT.'i/'.$row.'/'.$url;
		}
		
		reset($delete);
		while (list(,$row) = each($delete)) {
			if (CMS_CHARSET != CMS_CHARSET_FS) {
				$row = iconv('cp1251', 'utf-8', $row);
			}
			if (is_file($row) && is_writable($row)) {
				unlink($row);
			}
			
		}

	}
	
}


?>