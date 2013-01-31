<?php
/**
 * Работа с CVS
 * @package Pilot
 * @subpackage CVS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Работа с CVS
 * @package Pilot
 * @subpackage CVS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class CVS {
	
	/**
	* Проврка на наличие блокировки страницы, если страница заблокирована, то возвращает массив,
	* с указанием логина пользователя, который заблокировал страницу и временем автоматической разблокировки.
	* Если она не заблокирована, то блокирует ее. 
	* Если страница заблокирована этим же пользователем, то продлевает срок жизни блокировки
	* 
	* @param string $table_name
	* @param int $id
	* @return mixed
	*/
	static function isOwner($table_name, $field_name, $edit_id) {
		global $DB;
		
		$query = "
			LOCK TABLES
				auth_user AS tb_admin READ,
				auth_user READ,
				cvs_lock AS tb_lock WRITE,
				cvs_lock WRITE
		";
		$DB->query($query);

		$query = "
			SELECT 
				tb_admin.login, 
				UNIX_TIMESTAMP(tb_lock.dtime) AS dtime,
				CONCAT(
					DAYOFMONTH(tb_lock.dtime),
					' ',
					CASE MONTH(tb_lock.dtime) ".LANGUAGE_MONTH_GEN_SQL." END,
					' ',
					YEAR(tb_lock.dtime),
					' ',
					DATE_FORMAT(tb_lock.dtime, '%H:%i')
				) AS datetime
			FROM cvs_lock AS tb_lock
			INNER JOIN auth_user AS tb_admin ON tb_admin.id = tb_lock.admin_id
			WHERE 
				tb_lock.table_name='$table_name' 
				AND tb_lock.field_name='$field_name' 
				AND tb_lock.edit_id='$edit_id'
				AND UNIX_TIMESTAMP(tb_lock.dtime) + ".AUTH_TIMEOUT." > UNIX_TIMESTAMP()
				AND tb_lock.admin_id!='".$_SESSION['auth']['id']."'
			";
		$data = $DB->query_row($query);
		if ($DB->rows == 0) {
			self::lock($table_name, $field_name, $edit_id);
			$query = "UNLOCK TABLES";
			$DB->query($query);
			return true;
		} else {
			$query = "UNLOCK TABLES";
			$DB->query($query);
			return $data;
		}
	}
	
	/**
	* Обновление или добавление блокировки
	* @param string $table_name
	* @param int $edit_id
	* @return mixed
	*/
	public static function lock($table_name, $field_name, $edit_id) {
		global $DB;
		
		$query = "LOCK TABLES cvs_lock WRITE";
		$DB->query($query);
		
		/**
		 * Учитывая тот факт, что один пользователь может редактировать только одну страницу
		 * в таблице в один промежуток времени, то снимаем все остальные блокировки по этой
		 * таблице.
		 */
		$query = "
			DELETE FROM cvs_lock
			WHERE 
				(
					admin_id='".$_SESSION['auth']['id']."' 
					AND table_name='$table_name' 
					AND field_name='$field_name' 
					AND edit_id!='$edit_id'
				) OR UNIX_TIMESTAMP(dtime) + ".AUTH_TIMEOUT." < UNIX_TIMESTAMP()
		";
		$DB->delete($query);

		
		/**
		* Обновление блокировки CVS
		*/
		$query = "
			INSERT INTO cvs_lock (table_name, field_name, edit_id, admin_id) 
			VALUES ('$table_name', '$field_name', '$edit_id', '".$_SESSION['auth']['id']."')
			ON DUPLICATE KEY UPDATE dtime = NULL
		";
		$DB->insert($query);
		
		$query = "UNLOCK TABLES";
		$DB->query($query);
	}
	
	/**
	* Ведет лог изменений в системе и делает проверку, надо ли сохранять изменения
	* @param string $table_name
	* @param int $edit_id
	* @param string $content
	* @return bool
	*/
	public static function log($table_name, $field_name, $edit_id, $content) {
		global $DB;
		
		/**
		 * Определяем id текущей сессии (блокировки)
		 */
		$query = "
			SELECT id 
			FROM cvs_lock
			WHERE 
				table_name='$table_name'
				AND field_name='$field_name'
				AND admin_id='".$_SESSION['auth']['id']."'
				AND edit_id='$edit_id'
		";
		$lock_id = $DB->result($query, 0);
		
		/**
		 * Фиксируем новое изменение в CVS
		 */
		$query = "
			INSERT INTO cvs_log
			SET
				table_name='$table_name',
				field_name='$field_name',
				edit_id='$edit_id',
				admin_id='".Auth::getUserId()."',
				lock_id='$lock_id'
			ON DUPLICATE KEY UPDATE dtime=NULL, content=values(content)
		";
		$inserted_id = $DB->insert($query);
		
		/**
		 * Сохраняем текущую версию загруженных файлов
		 */
		$source_uploads = SITE_ROOT.'uploads/'.Uploads::getStorage($table_name, $field_name, $edit_id).'/';
		$destination_uploads = SITE_ROOT.'cvs/'.Uploads::getIdFileDir($inserted_id).'/';
		if (is_dir($source_uploads)) {
			
			// только файлы, подкаталоги не копируем - это дочерние разделы, до которых нам нет дела
			$source_uploads_files = Filesystem::getDirContent($source_uploads, true, false, true);
			if (is_dir($destination_uploads)) {
				Filesystem::delete($destination_uploads);
			}
			mkdir($destination_uploads, 0750, true);
			reset($source_uploads_files); 
			while (list(,$row) = each($source_uploads_files)) { 
				link($row, $destination_uploads.basename($row));
			}
			
			// Заменяем в контенте все ссылки на uploads
			$content = str_replace(Uploads::getURL($source_uploads), Uploads::getURL($destination_uploads), $content);
		}
		
		// Сохраняем контент после того, как в нем заменили ссылки на файлы в папке uploads.
		$query = "update cvs_log set content='".addcslashes($content, "'")."' where id='$inserted_id'";
		$DB->update($query);
		
		if (rand(0, 100) > -90) {
			self::clean();
		}
	}
	
	/**
	* Чистка cvs
	* @param void
	* @return void
	*/
	public static function clean() {
		global $DB;
		$query = "SELECT * FROM cvs_log WHERE dtime < NOW() - INTERVAL ".CMS_CVS_SAVE." SECOND";
		$cvs = $DB->query($query);
		
		reset($cvs);
		while(list(,$row) = each($cvs)) {
			$cvs_uploads_root = CVS_ROOT.Uploads::getStorage($row['table_name'], $row['field_name'], $row['edit_id'])."/$row[id]/";
			Filesystem::delete($cvs_uploads_root);
		}
		
		$query = "delete from cvs_log where dtime < NOW() - INTERVAL ".CMS_CVS_SAVE." SECOND";
		$DB->delete($query);
	}
	
	/**
	 * Провверка наличия блокировки на объекте
	 *
	 * @param string $table_name
	 * @param int $id
	 * @return bool
	 */
	public static function isLocked($table_name, $id) {
		global $DB;
		
		$query = "SELECT * FROM cvs_lock WHERE table_name='$table_name' AND edit_id='$id'";
		$DB->query($query);
		return ($DB->rows > 0) ? true : false;
	}
}
?>