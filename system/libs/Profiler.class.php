<?php
/**
 * Класс для профилирования работы скриптов
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Profiler {
	
	protected $sections = array();
	
	/**
	 * Начало секции профилирования
	 * @param string $section
	 */
	public function startSection($section) {
		if (isset($this->sections[$section])) {
			if ($this->sections[$section]['last_start'] != null) {
				trigger_error("Section $section already started but not finished", E_USER_NOTICE);
				return;
			}
			$this->sections[$section]['calls']++;
		} else {
			$this->sections[$section] = array('total_time' => 0, 'calls' => 1, 'min_time' => null, 'max_time' => 0);
		}
		
		$this->sections[$section]['last_start'] = microtime(true);
	}
	
	/**
	 * Конец секции профилирования
	 * @param string $section
	 */
	public function endSection($section) {
		if (empty($this->sections[$section]['last_start'])) {
			trigger_error("Section $section has not been started", E_USER_NOTICE);
			return;
		}
		$mt = microtime(true);
		$section_time = ($mt - $this->sections[$section]['last_start']);
		
		$this->sections[$section]['total_time'] += $section_time;
		if ($section_time > $this->sections[$section]['max_time']) {
			$this->sections[$section]['max_time'] = $section_time;
		}
		if ($section_time < $this->sections[$section]['min_time'] || $this->sections[$section]['min_time'] == null) {
			$this->sections[$section]['min_time'] = $section_time;
		}
		$this->sections[$section]['last_start'] = null;
	}
	
	
	public function dumpStat() {
		$r = array();
		
		reset($this->sections);
		while (list($s,$row) = each($this->sections)) {
			$r[$s] = array(
				'total_time' => number_format($row['total_time'], 4, '.', ' '),
				'min_time' => number_format($row['min_time'], 4, '.', ' '),
				'max_time' => number_format($row['max_time'], 4, '.', ' '),
				'calls' => $row['calls'],
				'avg_time_per_call' => number_format($row['total_time'] / $row['calls'], 8, '.', ' '),
			);
		}
		x($r);
	}
	
}
