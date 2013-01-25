<?php
/**
 * Класс, который отвечает за вывод баннеров
 * @package Pilot
 * @subpackage Banner
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


class Banner {
	
	/**
	 * Информация о доступных к показу баннерах
	 *
	 * @var array
	 */
	private $banner = array();
	
	/**
	 * Количество баннеров, которые выводятся в группе
	 *
	 * @var int
	 */
	private $banner_count = 0;
	
	/**
	 * Конструктор класса
	 *
	 * @param int $group_id - группа баннеров
	 * @param int $structure_id - текущий раздел
	 * @param array $structure_parents - родительские разделы
	 */
	public function __construct($group_name, $structure_id, $structure_parents) {
		global $DB;
		
		// Отбираем профили, которые можно использовать на данной странице
		$query = "
			select is_recursive, structure_id, banner_id, banner_count
			from banner_profile_cache
			where
				`date`=current_date()
				and `hour`=hour(now())
				and group_name='$group_name'
		";
		$profile = $DB->query($query);
		reset($profile);
		while (list($index,$row) = each($profile)) {
			$row['structure_id'] = preg_split("/,/", $row['structure_id'], -1, PREG_SPLIT_NO_EMPTY);
			if ($row['is_recursive'] == 0 && in_array($structure_id, $row['structure_id'])) {
				continue;
			} elseif ($row['is_recursive'] && !array_intersect($row['structure_id'], $structure_parents)) {
				continue;
			} else {
				$this->banner[$row['banner_id']] = $row['banner_id'];
				$this->banner_count = $row['banner_count'];
			}
		}
	}
	
	/**
	 * Делает выборку баннеров для показа на странице
	 *
	 * @param int $structure_id
	 * @param int $structure_parents
	 * @return array
	 */
	public function select() {
		global $DB;
		
		$stat = array();
		$time = time();
		
		$query = "SELECT * FROM banner_banner WHERE id in (0".implode(",", $this->banner).")";
		$banners = $DB->query($query);
		if ($DB->rows == 0) return array();
		
		$banners = $this->shuffle($banners);
		
		reset($banners);
		while (list($index,$row)=each($banners)) {
			
			$banner_file = Uploads::getFile('banner_banner', 'image', $row['id'], $row['image']);
			$image_size = (is_file($banner_file)) ? getimagesize($banner_file) : array();
			$row['link'] = '/tools/banner/click.php?id='.$row['id'];
			if (!empty($row['html'])) {
				// HTML баннер
				$row['type'] = 'html';
				$row['html'] = str_replace('[[link]]', $row['link'], $row['html']);
			} elseif (empty($image_size)) {
				// Нет картинки для баннера
				unset($banners[$index]);
				continue;
			} elseif ($image_size[2] == IMAGETYPE_SWF || $image_size[2] == IMAGETYPE_SWC) {
				// Flash
				$row['type'] = 'flash';
				$row['flash_vars'] = str_replace('[[link]]', urlencode($row['link']), $row['flash_vars']);
				$row['tag_attr'] = $image_size[3];
				$row['image_url'] = Uploads::getURL($banner_file);
			} else {
				// Обычный баннер
				$row['type'] = 'image';
				$row['tag_attr'] = $image_size[3];
				$row['image_url'] = Uploads::getURL($banner_file);
			}
			
			$banners[$index] = $row;
			$stat[] = date('Y-m-d H:i:s')."\t".HTTP_IP."\t".HTTP_LOCAL_IP."\t$row[id]\t".Auth::getUserId()."\n";
		}
		if (rand(0,1000) > 950) {
			// Чистка статистики
			$this->cleanup();
			
			// Обновление статистики
			$this->saveStat();
		}
		
		// Сохраняем статистику
		$fp = fopen(LOGS_ROOT.'banner_view.log', 'a');
		flock($fp, LOCK_EX);
		fwrite($fp, implode("\n", $stat));
		flock($fp, LOCK_UN);
		fclose($fp);
		
		
		return $banners;
	}
	
	/**
	 * Перемешивает баннеры, выозращает только $this->group[banner_count] баннеров
	 * При отборе баннеров учитываются их weight
	 * @param array $banners
	 * @return array
	 */
	protected function shuffle($banners) {
		$p = $ret = array(); // $p - коэфициэнты для баннеров (например, 0-1000, 1001-1200, 1201-2000)
		$interval = 100000;
		$start    = $sum = 0;
		
		// Узнаем суммарный вес всех баннеров
		reset($banners); 
		while (list(,$row) = each($banners)) { 
			$sum += $row['weight']; 
		}
		
		// Просчитываем начальные точки в распределении для каждого баннера
		reset($banners); 
		while (list($index,$row) = each($banners)) { 
			$start += $row['weight']/$sum * $interval;
			$p[$index] = $start;
		}
		
		$counter = 1000;
		while (count($ret) < count($this->banner_count) && $counter > 0) {
			$counter--;
			$r = rand(0,$interval);
			reset($p); 
			while (list($index,$row) = each($p)) {
				if (!isset($banners[$index])) continue;
				if (count($banners)==0) break;
				if ($r <= $row) {
					$ret[] = $banners[$index];
					unset($banners[$index]);
					continue 2;
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 * Загружает данные с файла в таблицу
	 *
	 */
	private function loadFile($type) {
		global $DB;
		
		$tmp_file = TMP_ROOT.uniqid('banner_'.$type);
		$insert = array();
		$banner_stat = array();
		
		if (!is_file(LOGS_ROOT.'banner_'.$type.'.log')) return array();
		Filesystem::rename(LOGS_ROOT.'banner_'.$type.'.log', $tmp_file);
		
		$fp = fopen($tmp_file, 'r');
		if (!$fp) return; // исключение для тех случаев, когда нет папки tmp или временный файл был удален
		while (!feof($fp)) {
			$line = fgets($fp);
			$line = preg_split("/\t/", $line, -1, PREG_SPLIT_NO_EMPTY);
			if (empty($line)) continue;
			$insert[] = "('$line[3]', inet_aton('$line[1]'), inet_aton('$line[2]'), '$line[0]', '$line[4]')";
			$banner_stat[$line[3]] = (isset($banner_stat[$line[3]])) ? $banner_stat[$line[3]] + 1 : 1;
			if (count($insert) > 500) {
				$query = "insert into banner_{$type}_raw (banner_id, ip, local_ip, tstamp, user_id) values ".implode(",", $insert);
				$DB->insert($query);
				$insert = array();
			}
		}
		if (!empty($insert)) {
			$query = "insert into banner_{$type}_raw (banner_id, ip, local_ip, tstamp, user_id) values ".implode(",", $insert);
			$DB->insert($query);
		}
		
		unlink($tmp_file);
		
		return $banner_stat;
	}
	
	/**
	 * Формирует статистику по показам баннеров
	 *
	 */
	protected function saveStat() {
		global $DB;
		
		// статистика кликов
		$banner_stat = $this->loadFile('click');
		reset($banner_stat);
		while (list($banner_id, $row) = each($banner_stat)) {
			$query = "update banner_banner set stat_click=stat_click+$row where id='$banner_id'";
			$DB->update($query);
		}
		
		// статистика просмотров
		$banner_stat = $this->loadFile('view');
		reset($banner_stat);
		while (list($banner_id, $row) = each($banner_stat)) {
			$query = "update banner_banner set stat_view=stat_view+$row where id='$banner_id'";
			$DB->update($query);
		}
				
		// Обновляем статистику
		$query = "
			replace into banner_stat (banner_id, date, view)
			select banner_id, date_format(tstamp, '%Y-%m-%d'), count(*)
			from banner_view_raw
			where tstamp > current_date() - interval 1 day
			group by banner_id, year(tstamp), month(tstamp), dayofmonth(tstamp)
		";
		$DB->insert($query);
		
		$query = "
			insert into banner_stat (banner_id, date, click)
			select banner_id, date_format(tstamp, '%Y-%m-%d'), count(*)
			from banner_click_raw
			where tstamp > current_date() - interval 1 day
			group by banner_id, year(tstamp), month(tstamp), dayofmonth(tstamp)
			on duplicate key update click=values(click)
		";
		$DB->insert($query);
	}
	
	
	/**
	 * Формирует очередь баннеров, которые необходимо показывать
	 *
	 * @param int $duration
	 */
	public static function buldCache($duration = 7) {
		global $DB;
		
		$query = "truncate table banner_profile_cache";
		$DB->delete($query);
		
		for ($i = 0; $i < $duration; $i++) {
			$query = "
				insert into banner_profile_cache (date, hour, group_name, banner_count, profile_id, is_recursive, structure_id, banner_id)
				SELECT 
						current_date() + interval $i day,
						tb_hour.`hour`,
						tb_group.uniq_name,
						tb_group.banner_count,
				        tb_profile.id,
						tb_profile.is_recursive,
						group_concat(distinct tb_structure.structure_id),
						group_concat(distinct tb_banner.id)
					FROM banner_profile_structure tb_structure
					INNER JOIN banner_profile tb_profile ON tb_structure.profile_id = tb_profile.id
					INNER JOIN banner_profile_hour tb_hour ON tb_profile.id = tb_hour.profile_id
					INNER JOIN banner_banner as tb_banner ON tb_banner.profile_id = tb_profile.id
					INNER JOIN banner_group as tb_group ON tb_group.id = tb_banner.group_id
					WHERE
						tb_profile.date_from <= current_date() + interval $i day
						and tb_profile.date_to >= current_date() + interval $i day
				        and find_in_set(dayofweek(current_date() + interval $i day), tb_profile.weekdays)
				        and tb_banner.active=1
				group by tb_profile.is_recursive, tb_profile.id, tb_hour.`hour`, tb_banner.group_id
			";
			$DB->insert($query);
		}
	}
	

	/**
	 * Удаление устаревших данных статистики
	 * 
	 * @param boolean $force
	 */
	protected function cleanup($force = false) {
		global $DB;
		
		$query = "DELETE FROM banner_click_raw WHERE tstamp < CURRENT_DATE() - INTERVAL 120 DAY";
		$DB->delete($query);
		
		$query = "DELETE FROM banner_stat WHERE `date` < CURRENT_DATE() - INTERVAL 360 DAY";
		$DB->delete($query);

		$query = "DELETE FROM banner_view_raw WHERE `tstamp` < NOW() - INTERVAL 3 DAY; /* raw view stat is actual only one day */";
		$DB->delete($query);
		
	}
	
	/**
	 * Получение всех слайдов категории
	 */
	 
	static function getSliders($uniq_name){
		global $DB;
		$slider = array();
		
		$query = "SELECT tb_slider.* FROM `banner_slider` as tb_slider
					INNER JOIN banner_slidergroup as tb_group ON tb_group.`id`=tb_slider.`group_id`
					WHERE tb_slider.active=1 AND tb_group.uniq_name = '{$uniq_name}'
					ORDER BY tb_slider.priority
				";
		$sliders = $DB->query($query);
		
		reset($sliders);
		while (list($index,$row)=each($sliders)) {
			
			$file = Uploads::getFile('banner_slider', 'image', $row['id'], $row['image']);
			$row['image'] = Uploads::getURL($file);
			$slider[]=$row;
		}
		return $slider;
		
	}
	
	/* 
	* Функция возвращает шаблон слайдера
	*/
	static function getSlidersTmpl($uniq_name){
		global $DB;
		$slider = array();
		
		$query = "SELECT tb_group.template FROM banner_slidergroup as tb_group 
					WHERE tb_group.uniq_name = '{$uniq_name}'
				";
		$tmpl = $DB->result($query);
		return $tmpl;
		
	}
}

?>