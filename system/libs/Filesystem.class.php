<?php
/**
 * Класс работы с файловой системой
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Класс работы с файловой системой
 * @package Pilot
 * @subpackage CVS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class Filesystem {
	
	/**
	 * Превращает 8битное значение прав доступа к файлу в массив с текстовыми значениями
	 * 
	 * @param int $mode 764 или значение возвращаемое stat().['mode']
	 * @return array
	 */
	static public function mode($mode) {
		$result = array();
		$type = array('user', 'group', 'other');
		
		// Значение с функции stat.['mode']
		if ($mode > 777) {
			$mode = substr(strval(decoct($mode)), -3, 3);
		}
		
		for($i = 0; $i < 3; $i++) {
			$bin = decbin($mode[$i]);
			$result[ $type[$i] ] = ($bin & 100) ? 'r' : '-';
			$result[ $type[$i] ] .= ($bin & 010) ? 'w' : '-';
			$result[ $type[$i] ] .= ($bin & 001) ? 'x' : '-';
		}
		return $result;
	}
	
	
	/**
	* Определяет MAX id файла в директории
	* 
	* @param string $dir
	* @return string - Директория и имя файла без расширения
	*/
	static public function getMaxFileId($dir) {
		
		$files = self::getDirContent($dir, false, false, true);
		reset($files);
		while (list($index, $file) = each($files)) {
			$files[$index] = intval($file);
		}
		
		if (count($files) > 0) {
			return $dir.sprintf("%02d", intval(max($files) + 1));
		} else {
			return $dir.sprintf("%02d", 0);
		}
	}
	
	/**
	 * Удаляет пустые директории в дереве
	 *
	 * @param string $path
	 */
	static public function deleteEmptyDirs($path) {
		$dirs = self::getDirContent($path, true, true, false);
		$files = self::getDirContent($path, true, false, true);
		
		if (empty($files) && empty($dirs) && is_dir($path)) {
			rmdir($path);
			return;
		}
		
		reset($dirs); 
		while (list(,$row) = each($dirs)) {
			self::deleteEmptyDirs($row);
		}
		
		// После того как удалили в директории все пустые поддиректории
		// текущая директория может быть пустой, удаляем её
		if (empty($files)) {
			$dirs = self::getDirContent($path, true, true, false);
			if (empty($dirs) && is_dir($path)) {
				rmdir($path);
				return;
			}
		}
	}

	
	/**
	* Возвращает список файлов в директории и/лии поддиректорий
	* @param string $dir
	* @param bool $full_path
	* @param bool $show_dirs
	* @param bool $show_files
	* @param bool $final_slash
	* @return array
	*/
	static public function getDirContent($dir, $full_path, $show_dirs, $show_files, $final_slash = true) {
		if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
			$dir .= DIRECTORY_SEPARATOR;
		}
		
		if (!is_dir($dir)) {
			return array();
		}
		
		clearstatcache();
		
		// Определяем переменные
		$return_files = array();
		$return_dirs = array();
		
		if (is_readable($dir)) {
			$dir_content = scandir($dir);
		} else {
			$dir_content = array();
		}
		
		reset($dir_content);
		while(list(,$filename) = each($dir_content)) {
			
			// Определяем, каким будет имя файла при выводе
			$file = (true === $full_path) ? $dir . $filename : $filename;

			if ($filename == '.' || $filename == '..') {
				continue;
			} elseif ($final_slash && $show_dirs && is_dir($dir . $filename)) {
				$return_dirs[] = $file . DIRECTORY_SEPARATOR;
			} elseif (!$final_slash && $show_dirs && is_dir($dir . $filename)) {
				$return_dirs[] = $file;
			} elseif ($show_files && is_file($dir . $filename)) {
				$return_files[] = $file;
			} elseif ($show_files && is_link($dir . $filename)) {
				$return_files[] = $file;
			}
		}
		
		/**
		* Сортируем директории и файлы
		*/
		sort($return_dirs);
		sort($return_files);
		
		return array_merge($return_dirs, $return_files);
	}
		
	/**
	* Возвращает список файлов в директории
	* @param mixed $dir
	* @param bool $files_only выводить только файлы
	* @return array
	*/
	static public function getAllSubdirsContent($dir, $files_only, $skip_logs = false) {
		clearstatcache();
		
		if (!is_array($dir)) {
			$dir = array($dir);
		}
		
		reset($dir);
		while(list($index, $current_dir) = each($dir)) {
			
			if (!is_dir($current_dir)) continue;
				
			// Читаем содержимое директории
			$dir_files = self::getDirContent($current_dir, true, true, true);
			
			/**
			* Добавляем найденные элементы к существующему массиву
			* push используется вместо merge для того, чтоб не сбивать internal point
			*/ 
			reset($dir_files);
			while(list(,$dir_file) = each($dir_files)) {
				if($skip_logs && $dir_file == SITE_ROOT.'system/logs/') continue;
				array_push($dir, $dir_file);
			}
			
			/**
			* Если указано находить только файлы, то удаляем имена директорий,
			* после того, как эти директории обработаны
			*/
			if ($files_only) unset($dir[$index]);
			
		}
		return $dir;
	}
	
	/**
	 * Создаёт пустой файл
	 * @param string $file
	 * @return bool
	 */
	static public function touch($file) {
		if (is_file($file)) {
			return true;
		}
		
		if (!is_dir(dirname($file))) {
			mkdir(dirname($file), 0777, true);
		}
		
		return touch($file);
	}
	
	/**
	* Рекурсивно удаляет директорию со всем содержимым
	* @param string $dir
	* @return bool
	*/
	static private function delDir($dir){
		clearstatcache();
		 
		if (!is_dir($dir)) return true;
		   
		// Блокировка удаления всей системы
		$dir = preg_replace("~/+~", "/", $dir);
		if (trim(strtolower($dir), '/') == trim(strtolower(SITE_ROOT), '/')) {
			trigger_error('You try destroy system', E_USER_ERROR);
			exit;
		}
		  
		$files = self::getDirContent($dir, true, true, true);
		
		reset($files);
		while (list(, $file) = each($files)) {
			if (is_dir($file)) {
				self::delDir($file);
			} elseif (is_writable($file)) { // проверка прав доступа
				unlink($file);
			}
		}
		
		rmdir($dir);
		return (is_dir($dir)) ? false : true;
	}
	
	/**
	 * Удаление файлов и директорий
	 * @param string $dir
	 * @return bool
	 */
	static public function delete($file){
		if (!is_writable($file)) {
			return false;
		}
		
		if (is_dir($file)) {
			return self::delDir($file);
		} else {
			return unlink($file);
		}
	}
	
	/**
	 * Переименовывает или перемещает директорию со всем ее содержимым
	 * $replace = true - замещаем копируемые файлы
	 * 
	 * @param string $source
	 * @param string $destination
	 * @param bool $replace
	 * @return bool
	 */
	static private function copyDir($source, $destination, $replace) {
		clearstatcache();
		
		if (!is_dir($source)) {
			// Исходная директория - не существует
			return true;
		}
		
		$source_files = self::getAllSubdirsContent($source, false);
		
		/**
		* Создаем структуру директорий
		*/
		reset($source_files);
		while(list(,$file) = each($source_files)) {
			
			if (!is_dir($file)) continue;
			
			$destination_dir = $destination . substr($file, strlen($source));
			
			
			if (is_file($destination_dir)) {
				return false;
			}
			
			if (!is_dir($destination_dir)) {
				makedir($destination_dir, 0777, true);
			}
		}
		
		/**
		* Копируем файлы
		*/
		reset($source_files);
		while(list(,$file) = each($source_files)) {
			
			if (!is_file($file)) continue;
			
			$destination_file = $destination.substr($file, strlen($source));
			
			// Замещаяем файл
			if ($replace && is_file($destination_file)) {
				unlink($destination_file);
			}
			
			// Копируем файл
			if (is_file($destination_file) || !copy($file, $destination_file)) {
				return false;
			}
			
		}
		return true;
	}
	
	/**
	* Переименовывает файл или директорию
	* @param string $source
	* @param string $destination
	* @param bool $replace - замещайть файлы
	* @return bool
	*/
	static public function rename($source, $destination, $replace = false) {
		if (is_file($source)) {
			if ($replace && is_file($destination)) {
				unlink($destination);
			}
			if (!file_exists(dirname($destination))) {
				makedir(dirname($destination), 0777, true);
			}
			if (!is_file($destination)) {
				return rename($source, $destination);
			}
			return false;
		} elseif (is_dir($source)) {
			$result = self::copyDir($source, $destination, $replace);
			return ($result === true && self::delDir($source)) ? true : false;
		}
		return true;
	}
	
	/**
	* Копирует файл или директорию
	* @param string $source
	* @param string $destination
	* @param bool $replace - замещайть файлы
	* @return bool
	*/
	static public function copy($source, $destination, $replace = false) {
		if ($source == $destination) {
			return false;
		} elseif (is_file($source)) {
			if ($replace && is_file($destination)) {
				unlink($destination);
			}
			if (!file_exists(dirname($destination))) {
				makedir(dirname($destination), 0777, true);
			}
			if (!is_file($destination)) {
				return copy($source, $destination);
			}
			return false;
		} elseif (is_dir($source)) {
			return self::copyDir($source, $destination, $replace);
		}
		return true;
	}
	
	/**
	 * Сравнивает содержимое 2-х каталогов. Возвращает true, если
	 * каталоги и файлы в этих каталогах полностью идентичны, иначе false.
	 *
	 * @param string $source
	 * @param string $destination
	 */
	static public function isEqualDirs($source, $destination) {
		$different = false;
		$listing_destination = self::getAllSubdirsContent($destination, false);
		$listing_source = self::getAllSubdirsContent($source, false);
		
		reset($listing_source);
		while (list($index,$row)=each($listing_source)) {
			$listing_source[ $index ] = preg_replace("~^".preg_quote($source)."~", '', $row);
		}
		
		reset($listing_destination);
		while (list($index,$row)=each($listing_destination)) {
			$listing_destination[ $index ] = preg_replace("~^".preg_quote($destination)."~", '', $row);
		}
		
		if (serialize($listing_destination) != serialize($listing_source)) {
			/**
			 * Список файлов в каталогах не совпадает
			 */
			$different = true;
		} else {
			/**
			 * Проверяем содержимое каждого файла
			 */
			reset($listing_destination);
			while (list($index,$item)=each($listing_destination)) {
				if (is_file($source.$listing_source[ $index ])) {
					if (md5_file($source.$listing_source[ $index ]) != md5_file($destination.$listing_destination[ $index ])) {
						$different = true;
						break;
					}
				}
			}
		}
		
		return !$different;
	}
	
	
	/**
	 * Возвращает размер файла или директории, в байтайх
	 *
	 * @param string $file
	 * @return int
	 */
	static public function getSize($file) {
		if (is_file($file)) {
			return filesize($file);
		} elseif (is_dir($file)) {
			$files = self::getAllSubdirsContent($file, true);
			$size = 0;
			reset($files);
			while (list(,$file) = each($files)) {
				$size += filesize($file);
			}
			return $size;
		} else {
			return 0;
		}
	}
	
}
?>