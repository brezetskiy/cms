<?php
/**
 * Класс, который выводит формы на сайте
 * @package Pilot
 * @subpackage Form
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class FormLight {
	
	/**
	 * id формы
	 *
	 * @var int
	 */
	public $form_id = 0;
	
	/**
	 * E-mail адреса, на которые надо отправлять данные
	 *
	 * @var array
	 */
	public $email = array();
	
	/**
	 * Название формы
	 *
	 * @var string
	 */
	public $title = '';
	
	/**
	 * Адрес, на который будет переброшен пользователь в случае успешного заполнения формы
	 *
	 * @var string
	 */
	public $destination_url = '';
	
	/**
	 * Текст, который выводится на кнопке "Отправить"
	 *
	 * @var string
	 */
	public $button = '';
	
	/**
	 * Путь к картинке которая будет использована вместо кнопки
	 *
	 * @var string
	 */
	public $image_button = '';
	
	
	/**
	 * Конструктор
	 *
	 * @param string $uniq_name - уникальное имя формы
	 */
	public function __construct($uniq_name) {
		global $DB;
		
		$query = "
			select 
				id,
				title_".LANGUAGE_CURRENT." as title,
				email,
				button,
				image_button,
				destination_url,
				result_text
			from form 
			where uniq_name='$uniq_name'
		";
		$info = $DB->query_row($query);
		if ($DB->rows > 0) {
			$this->form_id = $info['id'];
			$this->destination_url = $info['destination_url'];
			$this->result_text = $info['result_text'];
			$this->title = $info['title'];
			$this->button = (empty($info['button'])) ? cms_message('Form', 'Отправить') : $info['button'];
			$info['image_button'] = Uploads::getFile('form', 'image_button', $info['id'], $info['image_button']);
			$this->image_button = (file_exists($info['image_button']) && is_readable($info['image_button'])) ? Uploads::getURL($info['image_button']): '';
			$this->email = preg_split("/[\s\n\r\t,]+/", $info['email'], -1, PREG_SPLIT_NO_EMPTY);
		}
	}
	
	/**
	 * Загружает поля, которые есть в форме
	 *
	 * @return array
	 */
	public function loadParam() {
		global $DB;
		
		// Информация о полях
		$query = "
			select 
				tb_field.id,
				tb_field.form_id,
				tb_field.uniq_name,
				tb_field.title_".LANGUAGE_CURRENT." as title,
				tb_field.comment_".LANGUAGE_CURRENT." as comment,
				tb_field.type,
				tb_field.required,
				tb_regexp.regular_expression as `regexp`,
				tb_field.default_value
			from form_field as tb_field
			left join cms_regexp as tb_regexp on tb_regexp.id=tb_field.regexp_id
			where tb_field.form_id='$this->form_id'
			order by tb_field.priority
		";
		$data = $DB->query($query, 'id');
		reset($data);
		while (list($index,) = each($data)) {
			$data[$index]['info'] = array();
		}
		
		// Справочники для полей
		$query = "
			select
				id,
				field_id,
				uniq_name,
				title_".LANGUAGE_CURRENT." as title
			from form_field_value
			where field_id in (0".implode(",", array_keys($data)).")
			order by priority
		";
//		x($query);
		$info = $DB->query($query);
		reset($info);
//		x($info);
		while (list(,$row) = each($info)) {
			$data[$row['field_id']]['info'][$row['uniq_name']] = $row['title'];
		}			
//		x($data);
		return $data;
	}
	
}

?>