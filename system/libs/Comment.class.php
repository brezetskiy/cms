<?php
/**
 * Модуль комментариев
 * 
 * Позволяет добавлять комментарии на страницах сайта.
 * Комментарии привязываются к любой записи в любой таблице.
 * 
 * @package Pilot
 * @subpackage Comment
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */


class  Comment {
	
	/**
	 * Таблица к которой будем добавлять комментарии
	 *
	 * @var unknown_type
	 */
	private $table_name = '';
	
	/**
	 * id таблицы к которой привязывают коментарии 
	 *
	 * @var int
	 */
	private $table_id = 0;
	
	/**
	 * Комментарии, которые есть на странице
	 *
	 * @var array
	 */
	private $comments = array();
	
	/**
	 * id объекта для которого выводится комментарий. Фактически это id ряда в таблице $this->table_name
	 *
	 * @var int
	 */
	private $object_id = 0;
	
	
	/**
	 * Конструктор класса
	 *
	 * @param string $table_name
	 * @return object
	 */
	public function __construct($table_name, $object_id) {
		global $DB;
		
		$this->table_name = $table_name;
		$this->object_id = $object_id;
		$this->table_id = $DB->result("select id from cms_table where name='$this->table_name'");
		
		// Загрузка комментариев
		$active = (Auth::isAdmin()) ? 0 : 1;
		$where = where_clause('tb_comment.active', $active);
		if(Auth::isLoggedIn() && !Auth::isAdmin()) {
			$user_id = Auth::getUserId();
			if($user_id != 0) {
				$where = " and tb_comment.user_id=$user_id";
			}
		}
		$query = "
			select 
				tb_comment.*,
				ifnull(tb_user.nickname, tb_comment.user_name) as login,
			    tb_rel.priority as mainpos,
				date_format(tb_comment.tstamp, '".LANGUAGE_DATE_SQL."') as date,
				date_format(tb_comment.tstamp, '%H:%i') as time
			from comment_relation as tb_rel
			inner join comment as tb_comment on tb_comment.id = tb_rel.id
			left join auth_user as tb_user on tb_user.id = tb_comment.user_id
			where 
				tb_comment.table_id='$this->table_id' and 
				tb_comment.object_id='$this->object_id'
				$where
			group by tb_rel.id 
			order by tb_rel.id, tb_rel.priority
		";
		$this->comments = $DB->query($query);
	}
	
	
	/**
	 * Дерево комментариев
	 *
	 * @param int $id
	 * @param bool $flag
	 * @return mixed
	 */
	public function getComments($id, $captha = '') {
		global $submenu;
		
		$Template = new Template(SITE_ROOT.'templates/comment/default');
		foreach ($this->comments as $index => $row) {
			if ($row['comment_id'] != $id) {
				continue;
			}
			
			if (isset($_SESSION['ActionError']['id']) && ($row['id'] == $_SESSION['ActionError']['id'])){
				$row['display'] = 1;
				$row['new_comment'] = $_SESSION['ActionError']['comment'];
				if (!Auth::isLoggedIn()) {
					$row['new_user_name'] = $_SESSION['ActionError']['user_name'];
					$row['new_user_email'] = $_SESSION['ActionError']['user_email'];
				}
			}
			$row['comment'] = nl2br($row['comment']);
			if(isset($captha) && !empty($captha)) {
				$row['captcha_html'] = $captha;
			}
			$row['table_name'] = $this->table_name;
			$row['object_id'] = $this->object_id;
			$row['subcomment'] = $this->getComments($row['id'], $captha);
			$Template->iterate('/comment/', null, $row);
		}
		return $Template->display();
	}
	
	/**
	 * Добавление комментария, возвращает id добавленного комментария
	 *
	 * @param int $type_id
	 * @param int $comment_id
	 * @param int $object_id
	 * @param string $comment
	 * @param string $url
	 * @param int $user_id
	 * @return mixed
	 */
	static function add($table_name, $comment_id, $object_id, $comment, $url, $user_name = '', $user_email = '') {
		global $DB;
		
		if (!COMMENT_NOT_REGISTER && !Auth::isLoggedIn()) {
			return false;
		}
		

		$query = "select id from cms_table where name='$table_name'"; 
		$table_id = $DB->result($query);
		if (empty($table_id)) {
			$table_id = 0;
		}
		
		$active = (COMMENT_PRE_MODERATION) ? 0 : 1;
		if(Auth::isAdmin()){
			$active = 1;
		}
		
		$query = "
			insert into comment
			set
				table_id = '$table_id',
				comment_id = '$comment_id',
				object_id = '$object_id',
				comment = '$comment',
				url = '$url',
				user_id = '".Auth::getUserId()."',
				active = '$active',
				user_name = '$user_name',
				user_email = '$user_email',
				ip = '".HTTP_IP."',
				local_ip = '".HTTP_LOCAL_IP."',
				tstamp=NOW()	
		";
		return $DB->insert($query);
	}
	
	/**
	 * Удаление комментария
	 * 
	 * @param int $id
	 */
	static function delete($id) {
		global $DB;
		
		// Находим все дочерние комментарии
		$query = "select distinct id from comment_relation where parent='$id'";
		$childrens = $DB->fetch_column($query);
		
		$query = "delete from comment where id in (0".implode(",", $childrens).")";
		$DB->query($query);
		
		$query = "delete from comment_relation where parent in (0".implode(",", $childrens).")";
		$DB->delete($query);
	}
	
	
	/**
	 * Уведомление всех пользователей, на которые было дано сообщение
	 *
	 * @param int $id
	 * @return void
	 */
	public static function notify($id) {
		global $DB;
		
		$query = "
			select
				tb_comment.url,
				tb_comment.comment,
				tb_comment.active,
				ifnull(tb_user.name, tb_comment.user_name) as user_name,
				ifnull(tb_user.email, tb_comment.user_email) as email
			from comment as tb_comment
			inner join comment_relation as tb_relation on tb_relation.parent=tb_comment.id
			left join auth_user as tb_user on tb_user.id = tb_comment.user_id
			where tb_relation.id='$id' and tb_comment.active=1
			group by ifnull(tb_comment.user_email, tb_user.email)
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$Template = new TemplateDB('cms_mail_template', 'Comment', 'response');
			$row['comment'] = nl2br($row['comment']);
			$Template->set($row);
			$Sendmail = new Sendmail(CMS_MAIL_ID, $Template->title, $Template->display());
			$Sendmail->send($row['email']);
		}
	}
	
}
