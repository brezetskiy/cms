<?php
/** 
 * Класс для построения Gauge 
 * @package Pilot 
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

class FusionGadgetLinearGauge extends FusionChart {
	
	protected $color_ranges = array();
	protected $pointers = array();
	protected $trend_points = array();
	
	/**
	 * Отрисовывает специфические для графика данные
	 */
	protected function buildContent() {
		$xml  = $this->buildColorRanges();
		$xml .= $this->buildPointers();
		$xml .= $this->buildTrendLines();
		return $xml;
	}
	
	protected function buildTrendPoints() {
		$xml = '';
		if (count($this->trend_points) > 0) {
			$xml .= "<trendpoints>";
			reset($this->trend_points); 
			while (list(,$row) = each($this->trend_points)) { 
				$xml .= "<point ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</trendpoints>";
		}
		return $xml;
	}
	
	protected function buildColorRanges() {
		$xml = '';
		if (count($this->color_ranges) > 0) {
			$xml .= "<colorRange>";
			reset($this->color_ranges); 
			while (list(,$row) = each($this->color_ranges)) { 
				$xml .= "<color ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</colorRange>";
		}
		return $xml;
	}
	
	protected function buildPointers() {
		$xml = '';
		if (count($this->pointers) > 0) {
			$xml .= "<pointers>";
			reset($this->pointers); 
			while (list(,$row) = each($this->pointers)) { 
				$xml .= "<pointer ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</pointers>";
		}
		return $xml;
	}
	
	public function addColorRange($options) {
		$this->color_ranges[] = $options;
	}
	
	public function addPointer($options) {
		$this->pointers[] = $options;
	}
	
	public function addTrendPoint($options) {
		$this->trend_points[] = $options;
	}
	
	public function buildTrendLines() {
		return '';
	}
}

?>