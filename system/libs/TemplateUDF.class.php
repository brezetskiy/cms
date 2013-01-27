<?php
/**
* Функции которые используются в шаблонах
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* Функции которые используются в шаблонах
* @package Template
* @subpackage Libraries
*/
class TemplateUDF {
	
	/**
	* Обработка входящих в UDF параметров, как в виде ассоциативного массива,
	* так и числового
	* @access public
	* @param array $default
	* @param array $param
	* @return array
	*/
	static function parseParam($default, $param) {
		return array_merge($default, $param);
	}
	
//	/**
//	* Если передан пустой параметр, то устанавливаем значение $default
//	* @param array(mixed $var, string default)
//	* @return mixed
//	*/
//	static function emptyness($param) {
//		$param = self::parseParam(array('var'=>'', 'default'=>''), $param);
//
//		if (empty($param['var'])) {
//			return $param['default'];
//		} else {
//			return $param['var'];
//		}
//	}
//	
	/**
	 * Преобразовывает nl2br
	 * @param array(string $text)
	 * @return string
	 */
	static function nl2br($param) {
		$param = self::parseParam(array('text'=>''), $param);
		
		return nl2br($param['text']);
	}
	
//	/**
//	* Форматирует дату
//	* @param array(string $format, int $timestamp)
//	* @return string
//	*/
//	static function date_format($param) {
//		$param = self::parseParam(array('format'=>LANGUAGE_DATE, 'timestamp'=>time()), $param);
//		
//		return date($param['format'], $param['timestamp']);
//	}
//	
	/**
	* Форматирует число
	* @param array(float $number, int $decimals, string $dec_point, string $thousands_sep)
	* @return string
	*/
	static function number_format($param) {
		$param = self::parseParam(array('number'=>0, 'decimals'=>0, 'dec_point'=>'.', 'thousands_sep'=>' '), $param);
		
		return number_format($param['number'], $param['decimals'], $param['dec_point'], $param['thousands_sep']);
	}
	
	/**
	* Форматирует Дату
	* @param array(string format, int $tstamp)
	* @return string
	*/
	static function date_format($param) {
		$param = self::parseParam(array('tstamp'=>time(), 'format' => LANGUAGE_DATE), $param);
		return date($param['format'], $param['tstamp']);
	}
	
	/**
	* Экранирование строки
	* @param array(string $text, enum $type(html, htmlall, url, quotes))
	* @return string
	*/
	static function escape($param) {
		$param = self::parseParam(array('text'=>'', 'type'=>'html'), $param);
		
		switch ($param['type']) {
			case 'htmlall':
				return htmlentities($param['text'], ENT_QUOTES, LANGUAGE_CHARSET);
				break;
			case 'url':
				return urlencode($param['text']);
				break;
			case 'quotes':
				return addslashes($param['text']);
				break;
			default :
				return htmlspecialchars($param['text'], ENT_QUOTES, LANGUAGE_CHARSET);
				break;
		}
	}
	
	/**
	* установка флага checked и selected
	* @param array(string $value, string $selected)
	* @return string
	*/
	static function checked($param) {
		$param = self::parseParam(array('value'=>'', 'selected'=>''), $param);
		if ($param['value'] == $param['selected']) {
			return ' checked selected ';
		} else {
			return '';
		}
	}
	
	/**
//	* Обрезание строки до нужного размера
//	* @param array(string $text, int $length, string $break, bool $cut)
//	* @return string
//	*/
//	static function truncate($param) {
//		$param = self::parseParam(array('text'=>'', 'length'=>50, 'break'=>'...', 'cut'=>true), $param);
//		
//		if (strlen($param['text']) <= $param['length']) {
//			return $param['text'];
//		}
//		$cut_pos = strpos(wordwrap($param['text'], $param['length'], '@#!', $param['true']), '@#!');
//		return substr($param['text'], 0, $cut_pos).$param['break'];
//	}
	
	/**
	* Выпадающий список опций
	* @param array(array $options, mixed $selected) $param
	* @return string
	*/
	static function html_options($param) {
		$param = self::parseParam(array('options'=>array(), 'selected'=>array()), $param);
		$checked = (!is_array($param['selected'])) ? array($param['selected']) : $param['selected'];
		$return = '';
		
		reset($param['options']);
		while(list($key,$val) = each($param['options'])) {
			$selected = (in_array($key, $checked)) ? 'selected' : '';
			$return .= '<option '.$selected.' value="'.$key.'">'.$val.'</option>';
		}
		
		return $return;
	}
	
	/**
	* Вывод даты
	* @param array(string $name) $param
	* @return string
	*/
	static function html_select_date($param) {
		$param = self::parseParam(array('onchange' => '', 'name'=>'', 'day' => date('d'), 'month' => date('m'), 'year' => date('Y'), 'calendar'=>'true'), $param);
		
		
		if (isset($param['tstamp'])) {
			$param['day'] = date('d', $param['tstamp']);
			$param['month'] = date('m', $param['tstamp']);
			$param['year'] = date('Y', $param['tstamp']);
		}
		
		// Дни
		$html['d'] = '<input type="text" onkeyup="'.$param['onchange'].'" name="'.$param['name'].'[day]" value="'.$param['day'].'" size="2" maxlength="2" id="'.str_replace(array('[', ']'), '_', $param['name']).'_day"> ';
		
		// Список месяцев
		$html['m'] = '<select onchange="'.$param['onchange'].'" size="1" name="'.$param['name'].'[month]" id="'.str_replace(array('[', ']'), '_', $param['name']).'_month">';
		for ($i=1; $i<=12; $i++) {
			$selected = ($i == $param['month']) ? 'selected' : '';
			$html['m'] .= '<option '.$selected.' value="'.$i.'">'.constant('LANGUAGE_MONTH_GEN_'.$i).'</OPTION>'."\n";
		}
		$html['m'] .= '</select> ';
		
		// Год
		$html['y'] = '<input onkeyup="'.$param['onchange'].'" type="text" name="'.$param['name'].'[year]" value="'.$param['year'].'" size="4" maxlength="4" id="'.str_replace(array('[', ']'), '_', $param['name']).'_year"> ';
		
		// Календарь
		if ($param['calendar'] == 'true') {
			$html_calendar = ' <a href="javascript: void(0);" onclick="g_Calendar.show(event, \''.str_replace(array('[', ']'), '_', $param['name']).'\', false, \'dd/mm/yyyy\', new Date(1900, 0, 1, 0, 0, 0), new Date(2030, 11, 31, 0, 0, 0), 1900); return false;"><img src="/design/cms/img/js/calendar/calendar.gif" width="34" height="21" alt="Выберите дату" border="0" align="absmiddle"></a>';
		}
		
		$format = preg_split("/[^a-z]+/i", strtolower(LANGUAGE_DATE), -1, PREG_SPLIT_NO_EMPTY);
		$result = '';
		reset($format);
		while(list(,$period) = each($format)) {
			$result .= $html[$period];
		}
		return $result.$html_calendar;
	}
	
	/**
	* Вывод даты
	* @param array(string $name) $param
	* @return string
	*/
	static function html_short_date($param) {
		$param = self::parseParam(array('onchange' => '', 'name'=>'', 'day' => date('d'), 'month' => date('m'), 'year' => date('Y'), 'calendar'=>'true'), $param);
		
		// Если указан параметр tstamp, то дату определяем по нему
		if (isset($param['tstamp'])) {
			$param['day'] = date('d', $param['tstamp']);
			$param['month'] = date('m', $param['tstamp']);
			$param['year'] = date('Y', $param['tstamp']);
		}
		
		// Дни
		$html['d'] = '<input type="text" onkeyup="'.$param['onchange'].'" name="'.$param['name'].'[day]" value="'.$param['day'].'" style="width:25px;" maxlength="2" id="'.str_replace(array('[', ']'), '_', $param['name']).'_day">';
		
		// Список месяцев
		$html['m'] = '<input type="text" onkeyup="'.$param['onchange'].'" name="'.$param['name'].'[month]" value="'.$param['month'].'"style="width:25px;" maxlength="2" id="'.str_replace(array('[', ']'), '_', $param['name']).'_month">';
		
		// Год
		$html['y'] = '<input onkeyup="'.$param['onchange'].'" type="text" name="'.$param['name'].'[year]" value="'.$param['year'].'" style="width:35px;" maxlength="4" id="'.str_replace(array('[', ']'), '_', $param['name']).'_year">';
		
		// Календарь
		if ($param['calendar'] == 'true') {
			$html_calendar = '<a href="javascript: void(0);" onclick="g_Calendar.show(event, \''.str_replace(array('[', ']'), '_', $param['name']).'\', false, \'dd/mm/yyyy\', new Date(1900, 0, 1, 0, 0, 0), new Date(2030, 11, 31, 0, 0, 0), 1900); return false;"><img src="/design/cms/img/js/calendar/calendar.gif" width="34" height="21" alt="Выберите дату" border="0" align="absmiddle"></a>';
		}
		
		$format = preg_split("/[^a-z]+/i", strtolower(LANGUAGE_DATE), -1, PREG_SPLIT_NO_EMPTY);
		$result = '';
		reset($format);
		while(list(,$period) = each($format)) {
			$result .= $html[$period];
		}
		return $result.$html_calendar;
	}
	
	
	/**
	* Вывод времени
	* @param array(string $name, int $hour, int $minute, int $second, bool $show_seconds) $param
	* @return string
	*/
	static function html_text_time($param) {
		$param = self::parseParam(array('name'=>'', 'hour'=>date('H'), 'minute'=>date('i'), 'second'=>date('s'), 'show_seconds'=>true), $param);
		
		$hour = (empty($hour)) ? date('H', $param['timestamp']) : date('H');
		$minute = (empty($minute)) ? date('i', $param['timestamp']) : date('i');
		$second = (empty($second)) ? date('s', $param['timestamp']) : date('s');
		
		$html = '
			<input type="text" size="2" maxlength="3" name="'.$param['name'].'[hour]" id="'.str_replace(array('[', ']'), '_', $param['name']).'_hour" value="'.$param['hour'].'">:<input 
			type="text" size="2" maxlength="2" name="'.$param['name'].'[minute]" id="'.str_replace(array('[', ']'), '_', $param['name']).'_minute" value="'.$param['minute'].'">';
		if (!empty($param['show_seconds'])) {
			$html .= ':<input type="text" size="2" maxlength="2" name="'.$param['name'].'[second]" id="'.str_replace(array('[', ']'), '_', $param['name']).'_second" value="'.$param['second'].'">
				<a href="javascript: void(0);" onclick="javascript:DateTime = new Date();document.getElementById(\''.str_replace(array('[', ']'), '_', $param['name']).'_hour\').value=DateTime.getHours();document.getElementById(\''.str_replace(array('[', ']'), '_', $param['name']).'_minute\').value=DateTime.getMinutes();document.getElementById(\''.str_replace(array('[', ']'), '_', $param['name']).'_second\').value=DateTime.getSeconds();"><img src="/design/cms/img/button/time_now.gif" align="top" width="22" height="21" border="0" alt="Установить текущее время"></a>
			';
		}
		return $html;
	}
	
	/**
	 * Выводит форму, которая описана в системе
	 *
	 * @param array $param
	 * @return string
	 */
	static function form($param) {
		global $DB;
		
		$param = self::parseParam(array('name' => '', 'template'=> 'form/default'), $param);
		$Template = new Template($param['template']);
		
		// Captcha
		if (FORM_CAPTCHA) $Template->set('captcha_html', Captcha::createHtml());
		
		// Загружаем данные формы
		$Form = new FormLight($param['name']);
		$Template->set('form_id', $Form->form_id);
		$Template->set('title', $Form->title);
		$Template->set('button', $Form->button);
		$Template->set('image_button', $Form->image_button);
		$Template->set('uniq_name', $param['name']);
		
		$data = $Form->loadParam();
		if (empty($data)) {
			return cms_message('Form', 'Невозможно найти форму %s', $param['name']);
		}
		
		reset($data);
		
		while (list(,$row) = each($data)) {
			if (isset($_REQUEST[$row['uniq_name']])) {
				$row['default_value'] = $_REQUEST[$row['uniq_name']];
			} elseif (substr($row['uniq_name'], 0, 7) != 'passwd_' && isset($_SESSION['auth'][$row['uniq_name']])) {
				$row['default_value'] = $_SESSION['auth'][$row['uniq_name']];
			}
			if ($row['type'] == 'hidden') {
				$Template->iterate('/hidden/', null, $row);
			} else {
				$tmpl_row = $Template->iterate('/row/', null, $row);
				
				reset($row['info']);
				while (list($key, $val) = each($row['info'])) {
					$Template->iterate('/row/info/', $tmpl_row, array('key' => $key, 'value' => $val, 'uniq_name' => $row['uniq_name']));
				}
			}
		}
		
		return $Template->display();
	}
	
	
	/**
	 * Выводит блок, который описан в системе
	 *
	 * @param array $param
	 * @return string
	 */
	static function block($param) {
		global $DB, $Site;
		
		$param = self::parseParam(array('name' => '', 'template'=> 'block/default'), $param);
//		x($param);
		// Определение контента url блока 
		$query = "
			SELECT id, content_".LANGUAGE_CURRENT." as content, area
			FROM block 
			WHERE uniq_name = '{$param['name']}' AND area = 'url'  AND url = '".CURRENT_URL_FORM."'
		";
		$info = $DB->query_row($query);
		
		// url блок не обнаружен, определение контента page блока 
		if($DB->rows == 0){
			$query = "
				SELECT id, content_".LANGUAGE_CURRENT." as content, area
				FROM block 
				WHERE uniq_name = '{$param['name']}' AND area = 'page' AND structure_id = '".$Site->structure_id."'
			";
			$info = $DB->query_row($query);
			
			// page блок не обнаружен, определение контента общего блока 
			if($DB->rows == 0){
				$query = "
					SELECT id, content_".LANGUAGE_CURRENT." as content, area 
					FROM block 
					WHERE uniq_name = '{$param['name']}' AND area = 'site'
				";
				$info = $DB->query_row($query);
				
				// общий блок не обнаружен, создание нового общего блока 
				if($DB->rows == 0){
					$info['id'] 	 = $DB->insert("INSERT INTO block SET uniq_name = '{$param['name']}', title_".LANGUAGE_CURRENT." = '{$param['name']}'");
					$info['content'] = "<div style='margin:10px; color:#999; text-align:center; font-size:10px;'>Новый блок.<br/>Пожалуйста, добавьте контент.</div>";
					$info['area']    = "site";
				} 
			}
		}
		
		// Блок обнаружен, но контент пуст
		if(empty($info['content'])){
			$info['content'] = "<div style='margin:10px; color:#999; text-align:center; font-size:10px;'>Пустой блок.<br/>Пожалуйста, добавьте контент.</div>";
		}
		 
		// Загрузка контента блока на сайт
		$Template = new Template($param['template']);
		$Template->set('id', (isset($info['id'])) ? $info['id'] : 0);
		$Template->set('name', $param['name']);
		$Template->set('content', id2url($info['content']));
		$Template->set('area', $info['area']);
		$Template->set('structure_id', $Site->structure_id);
		$Template->set('style', (!empty($param['style'])) ? $param['style'] : '');
		
		return $Template->display();
	}
	
	
	/**
	 * Вывод формы авторизации по OpenID
	 */
	static function oid_widget($param){
		$param = self::parseParam(array('name' => '', 'template' => 'inline', 'return_path' => CURRENT_URL_FORM, 'subtemplate' => '', 'providers' => ''), $param);
		return AuthOID::displayWidget($param['name'], $param['template'], $param['return_path'], $param['subtemplate'], $param['providers']);
	}
	
	
	/**
	 * Формирование ссылки, доступной только авторизованным пользователям
	 * Для неавторизованных - сначала показывается окно авторизации
	 *
	 * @param array(string $href) $param
	 * @return string
	 */
	static function auth_link($param) {
		$param = self::parseParam(array('href'=>''));
		return auth_link($param['href']);
	}
	
	/**
	 * Метод формирования интерактивных карт 
	 * Mapia.ua на сайтах системы 
	 *
	 * @param array $param
	 * @return string
	 */
	static function map($param){
		global $DB;
		
		$param = self::parseParam(array('name' => '', 'template'=> 'map/mapia'), $param);
		$Template = new Template($param['template']);

		$query = "select * from map where uniq_name='$param[name]'";
		$data = $DB->query_row($query);	
		
		$Template->set($data);
		
		$query = "select * from map_marker where map_id='$data[id]'";
		$data = $DB->query($query);
		reset($data);
		while(list(,$row) = each($data)) {
			$row['mark_name'] = $param['name']."_".$row['id'];
			$logo_file = Uploads::getFile('map_marker', 'logo', $row['id'], $row['logo']);
			if(is_file($logo_file) && is_readable($logo_file)) {
				$row['logo'] = CMS_URL.Uploads::getUrl($logo_file);
			}
			
			$icon_file = Uploads::getFile('map_marker', 'icon', $row['id'], $row['icon']);
			if(is_file($icon_file) && is_readable($icon_file)) {
				$row['icon'] = CMS_URL.Uploads::getUrl($icon_file);
			}
			
			$Template->iterate('/marker/', null, $row);								
		}
		
		return $Template->display();
	}
	
	/**
	 * Метод формирования интерактивных карт 
	 * Mapia.ua на сайтах системы 
	 *
	 * @param array $param
	 * @return string
	 */
	static function static_mapia($param){
		global $DB;
		
		$param = self::parseParam(array('name' => '', 'template'=> 'map/mapia'), $param);
		$Template = new Template($param['template']);

		$query = "select * from map where uniq_name='$param[name]'";
		$info = $DB->query_row($query);
		
		return '<img src="http://mapia.ua/static?address='.$info['address'].'&marker_title='.urlencode($info['name_ru']).'&size='.ceil($info['div_width']).'x'.ceil($info['div_height']).'&zoom='.$info['zoom'].'&lang=ru&city=Киев">';
	}
	
	/**
	 * Вывод формы для голосования
	 *
	 * @param array $param
	 * @return string
	 */
	static function vote($param) {
		$param = self::parseParam(array('structure_id' => (defined('SITE_STRUCTURE_ID')) ? SITE_STRUCTURE_ID : 0, 'template'=> 'vote/voting', 'ajax' => false), $param);
		if (!is_module('Vote')) return '';
		
		$Vote = new Vote($param['structure_id']);
		$info = $Vote->getInfo();
		
		// Нет голосования для данной страницы
		if (empty($info)) return '';
		if ($Vote->isBlocked($info['id'])) $Template = new Template("vote/result");
		else $Template = new Template($param['template']);
		$answers = $Vote->getAnswers();
		$Template->set($info);
		$Template->set($param);
		$Template->iterateArray('/answer/', null, $answers);
		$Template->set('total', $answers[0]['total']);
		return $Template->display();
	}
	
	/**
	 * Вывод баннеров
	 *
	 * @param array $param
	 * @return string
	 */
	static function banner($param) {
		global $Site;
		
		
		$param = self::parseParam(array('name' => '', 'template'=> 'banner/default'), $param);
		if ($param['name'] == '') return '';
		if (!is_module('Banner')) return '';
		
		
		$Template = new Template($param['template']);
		$Banner = new Banner($param['name'], $Site->structure_id, $Site->parents);
		$banners = $Banner->select();
		reset($banners);
		while (list(,$row) = each($banners)) {
			$Template->iterate('/banner/', null, $row);
		}
		
		return $Template->display();
	}
	 
	
	/**
	 * Комментарии
	 *
	 * @param array $param
	 * @return string
	 */
	static function comment($param) {
		global $Site;
		
		$captha = Captcha::createHtml();
//		x($param);
		$param = self::parseParam(array('table_name' => 'news_message', 'object_id'=> 0), $param);
		if (empty($param['object_id'])) return '';
		if (!is_module('Comment')) return '';
				
		$return = '';
		$Comment = new Comment($param['table_name'], $param['object_id']);
		$return .= $Comment->getComments(0, $captha);
		$TmplComment = new Template(($Site->site_id==75105 ? 'comment/default_delta' :'comment/default_form'));
		$TmplComment->set('table_name', $param['table_name']);
		$TmplComment->set('object_id', $param['object_id']);
		$TmplComment->set('captcha_html', $captha);
		if (isset($_SESSION['ActionError']['id']) && $_SESSION['ActionError']['id'] == 0){
			$TmplComment->set('display', 1);
			$TmplComment->set('new_comment', $_SESSION['ActionError']['comment']);
			if (!Auth::isLoggedIn()) {
				$TmplComment->set('new_user_name', $_SESSION['ActionError']['user_name']);
				$TmplComment->set('new_user_email', $_SESSION['ActionError']['user_email']);
			}
		}

		$return .= $TmplComment->display();
		return $return;
	}
	
	/**
	 * Вывод клаендаря на сайте
	 * 
	 * @param array $param
	 * @return string
	 */
	static function calendar($param){
		$param = self::parseParam(array('links' => array(), 'current_date'=> time(), 'show_month' => time(), 'type' => ''), $param);
		$current_day = (date('Y-m', $param['current_date']) == date('Y-m', $param['show_month'])) ? date('j', $param['current_date']) : 0;
		$show_month = date('n', $param['show_month']);
		$show_year = date('Y', $param['show_month']);
		$number_of_days = date('t', mktime(0, 0, 0, $show_month, 1, $show_year));
		$name_month = constant('LANGUAGE_MONTH_NOM_'.$show_month);
		
		// определяем первый день месяца как день недели (пн либо вт ...) 
		$first_weekday = date('w', mktime(0, 0, 0, $show_month, 1, $show_year));
		$first_weekday--;
		if($first_weekday == -1) {
			$first_weekday = 6;
		}
		
		$html = '
			<table>
				<thead>
					<tr id="adjust">
						<td valign="middle">
							<a onclick="updateCalendar('.intval($show_month - 1).', '.$show_year.', '.$param['current_date'].', \''.$param['type'].'\'); return false;" href="#"><img align="middle" src="/img/news/calendar_left.png" /></a>
						</td>
						<td class="month" colspan="5"><div>'.$name_month.' '.$show_year.'</div></td>
						<td valign="middle">
							<a onclick="updateCalendar('.intval($show_month + 1).', '.$show_year.', '.$param['current_date'].', \''.$param['type'].'\'); return false;" href="#"><img align="middle" src="/img/news/calendar_right.png" /></a>
						</td>
					</tr>
					<tr id="day"><td>пн</td><td>вт</td><td>ср</td><td>чт</td><td>пт</td><td class="weekend">сб</td><td class="weekend">вс</td></tr>
				</thead>	
				<tr>';
		
		// проходим по всем дням месяца и заполняем их
		for ($i = 0; $i < $number_of_days + $first_weekday ; $i++) {
			if ($i % 7 == 0 && $i != 0) {
				$html .= '</tr><tr>';
			}
			
			if ($i < $first_weekday) {
				$html .= '<td>&nbsp;</td>';	
			} else {
				
				$iterateday = $i - $first_weekday + 1;
				$today = ($iterateday == $current_day) ? 'today' : '';
				$weekend = (date('w', mktime(0, 0, 0, $show_month, $iterateday, $show_year)) == 0 || date('w', mktime(0, 0, 0, $show_month, $iterateday, $show_year)) == 6) ? 'weekend' : '';
				
				// ставим ссылку с даты
				$html .= (isset($param['links'][$iterateday])) ?
					'<td class="'.$today.' '.$weekend.'"><a href="'.$param['links'][$iterateday].'">'.$iterateday.'</a></td>':
					'<td class="'.$today.' '.$weekend.'">'.$iterateday.'</td>';
			}
		}
		
		$html .= '</tr></table>';
		return $html;	
	}

	
	/**
	 * Вывод калькулятора на сайте
	 * @param array $param
	 */
	
	static function calc($param) {
		$param = self::parseParam(array('name' => '', 'template'=> 'calc/calc'), $param);
		
		$TmplContent = new Template('calc/calc');
		
		$calc = new Calc($param['name']);
		$object = $calc->getOptionList();
		
		reset($object);
		while (list(,$row) = each($object)) {
			$info = $calc->getOptionList($row['id']);
			$row['suboption'] = $calc->buildTree($row['id'], $row['type']);
			$tmpl = $TmplContent->iterate('/option/', null, $row);
			$need = $calc->getDependencyList($row['id']);
			if(!empty($need)) {
				$TmplContent->iterateArray('/option/modneed/', $tmpl, $need);
			}
		}
		
		return $TmplContent->display();
	}
}

?>