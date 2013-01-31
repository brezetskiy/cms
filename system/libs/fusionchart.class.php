<?php
/** 
 * Класс для работы с графиками FusionCharts 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

class FusionChart {
	
	/**
	 * Общие параметры графика
	 * @var array
	 */
	protected $chart_options = array();
	
	/**
	 * Подписи к оси Y (в FusionCharts - categories)
	 * @var array
	 */
	protected $labels = array();
	
	/**
	 * Параметры подписей к оси Y
	 * @var array
	 */
	protected $labels_options = array();
	
	/**
	 * Горизонтальные линии
	 * @var array
	 */
	protected $trendlines = array();
	
	/**
	 * Применение стилей отображения к объектам
	 * @var array
	 */
	protected $apply_styles = array();
	
	/**
	 * Стили отображения элементов
	 * @var array
	 */
	protected $styles = array();
	
	/**
	 * Результат - сформированный XML документ
	 * @var string
	 */
	protected $xml = '';
	
	/**
	 * Наборы данных для отображения
	 * @var array
	 */
	protected $data_sets = array();
	
	/**
	 * Создание нового графика
	 * @param array $chart_options
	 */
	public function __construct($chart_options = array()) {
		assert(is_array($chart_options));
		$this->chart_options = $chart_options;
	}
	
	/**
	 * Задает подписи по оси Y
	 * @param array $labels
	 */
	public function setLabels($labels, $options = array()) {
		if (!is_array($labels)) {
			trigger_error("Labels is not array", E_USER_WARNING);
			return;
		}
		$this->labels = $labels;
		$this->labels_options = $options;
	}
	
	/**
	 * Добавляет набор данных для отображения
	 * @param array $data_set
	 * @param array $options
	 */
	public function addDataSet($data_set, $options = array()) {
		$this->data_sets[] = array('data'=>$data_set, 'options'=>$options);
	}
	
	/**
	 * Возвращает XML документ, содержащий данные для построения графика
	 * @return string
	 */
	public function renderXml($charset = 'UTF-8') {
		$this->xml = "<?xml version='1.0' encoding='$charset'?>";
		$this->xml .= "<chart showFCMenuItem='0' ".$this->buildOptions($this->chart_options).">";
		
		/**
		 * Подписи по Y и данные (различный формат для single и multi mode)
		 */
		$this->xml .= $this->buildContent();
		
		/**
		 * TrendLines
		 */
		$this->xml .= $this->buildTrendLines();
		
		/**
		 * Styles
		 */
		$this->xml .= $this->buildStyles();
		
		$this->xml .= "</chart>";
		return iconv(CMS_CHARSET, $charset, $this->xml);
	}
	
	protected function buildContent() {
		$xml = '';
		if (count($this->data_sets) == 0) {
			trigger_error('Use addDataSet method to provide chart data', E_USER_ERROR);
		} elseif (count($this->data_sets) == 1) {
			$xml .= $this->buildSingleChartData();
		} else {
			$xml .= $this->buildMultiChartData();
		}
		return $xml;
	}
	
	protected function buildStyles() {
		$xml = '';
		if (count($this->styles) > 0) {
			$xml .= "<styles>";
			$xml .= "<definition>";
			reset($this->styles); 
			while (list(,$row) = each($this->styles)) { 
				$xml .= "<style ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</definition>";
			$xml .= "<application>";
			reset($this->apply_styles); 
			while (list(,$row) = each($this->apply_styles)) { 
				$xml .= "<apply ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</application>";
			$xml .= "</styles>";
		}
		return $xml;
	}
	
	protected function buildTrendLines() {
		$xml = '';
		if (count($this->trendlines) > 0) {
			$xml .= "<trendlines>";
			reset($this->trendlines); 
			while (list(,$row) = each($this->trendlines)) { 
				$xml .= "<line ".$this->buildOptions($row)." />"; 
			}
			$xml .= "</trendlines>";
		}
		return $xml;
	}
	
	/**
	 * Строит данные для SingleChart графиков
	 * @return string
	 */
	protected function buildSingleChartData() {
		$xml = '';
		/**
		 * DataSets
		 */
		reset($this->data_sets); 
		while (list(,$data_set) = each($this->data_sets)) { 
			/**
			 * Проходимся по лейблам, если в датасете нету такой позиции - ставим пустую строчку
			 * Это избавляет от необходимости в сортировке данных и контроле их заполненности
			 */
			//$xml .= "<dataset ".$this->buildOptions($data_set['options']).">";
			reset($this->labels); 
			while (list(,$index) = each($this->labels)) { 
				if (!isset($data_set['data'][$index])) {
					$xml .= "<set label='".$this->escapexml($index)."'/>";
				} elseif (is_array($data_set['data'][$index])) {
					$xml .= "<set label='".$this->escapexml($index)."' ".$this->buildOptions($data_set['data'][$index])."/>";
				} else {
					$xml .= "<set label='".$this->escapexml($index)."' value='{$data_set['data'][$index]}' ".$this->buildOptions($data_set['options'])." />";
				}
			}
			//$xml .= "</dataset>";
		}
		return $xml;
	}
	
	/**
	 * Строит данные для MultiChart графиков
	 * @return string
	 */
	protected function buildMultiChartData() {
		/**
		 * Категории (подписи по оси Y)
		 */
		$xml = '';
		$xml .= "<categories ".$this->buildOptions($this->labels_options).">";
		reset($this->labels); 
		while (list(,$row) = each($this->labels)) { 
			if (is_array($row)) {
				$xml .= "<category ".$this->buildOptions($row)." />";
			} else {
				$xml .= "<category label='".$this->escapexml($row)."' />";
			}
		}
		$xml .= "</categories>";
		
		/**
		 * DataSets
		 */
		reset($this->data_sets); 
		while (list(,$data_set) = each($this->data_sets)) { 
			/**
			 * Проходимся по лейблам, если в датасете нету такой позиции - ставим пустую строчку
			 * Это избавляет от необходимости в сортировке данных и контроле их заполненности
			 */
			$xml .= "<dataset ".$this->buildOptions($data_set['options']).">";
			reset($this->labels); 
			while (list(,$index) = each($this->labels)) { 
				if (!isset($data_set['data'][$index])) {
					$xml .= "<set />";
				} elseif (is_array($data_set['data'][$index])) {
					$xml .= "<set ".$this->buildOptions($data_set['data'][$index])."/>";
				} else {
					$xml .= "<set value='{$data_set['data'][$index]}' />";
				}
			}
			$xml .= "</dataset>";
		}
		return $xml;
	}
	
	/**
	 * Добавляет горизонтальную линию
	 * @param array $options
	 */
	public function addTrendLine($options) {
		$this->trendlines[] = $options;
	}
	
	/**
	 * Добавляет стиль отображения элементов
	 * @param array $options
	 */
	public function addStyle($options) {
		$this->styles[] = $options;
	}
	
	/**
	 * Променяет стили к объекту
	 * @param string $to_object
	 * @param string $styles
	 */
	public function applyStyle($to_object, $styles) {
		$this->apply_styles[] = array('toObject'=>$to_object, 'styles'=>$styles);
	}
	
	/**
	 * Строит строку XML-атрибутов из массива
	 * @param array $options
	 * @return string
	 */
	protected function buildOptions($options) {
		$options_str = '';
		reset($options); 
		while (list($key,$value) = each($options)) { 
			$options_str .= "$key='".$this->escapexml($value, ENT_QUOTES)."' ";
		}
		return $options_str;
	}
	
	protected function escapexml($str) {
		return htmlspecialchars($str, ENT_QUOTES);
	}
	
	/**
	 * Алиасы для удобной установки параметров графика
	 */
	
	/**
	 * Назначает заголовок графика
	 * @param string $value
	 */
	public function setCaption($value) {
		$this->chart_options['caption'] = $value;
	}
	
	/**
	 * Назначает подзаголовок графика
	 * @param string $value
	 */
	public function setSubCaption($value) {
		$this->chart_options['subCaption'] = $value;
	}
	
	/**
	 * Назначает надпись по оси Х
	 * @param string $value
	 */
	public function setXAxisName($value) {
		$this->chart_options['xAxisName'] = $value;
	}
	
	/**
	 * Назначает надпись по оси Y
	 * @param string $value
	 */
	public function setYAxisName($value) {
		$this->chart_options['yAxisName'] = $value;
	}
	
	/**
	 * Функции для предустановки параметров часто используемых графиков
	 */
	
	/**
	 * Создает структуру для построения графика по дням за месяц
	 */
	public function createDaysLabels($year, $month, $language) {
		$labels = array();
		$days_in_month = date('t', mktime(0,0,0,$month,1,$year));
		for ($i=1;$i<=$days_in_month;$i++) {
			$labels[] = date(LANGUAGE_DATE, mktime(0,0,0,$month,$i,$year));
		}
		$this->setLabels($labels);
	}
	
	/**
	 * Создает структуру для построения графика по часам за сутки
	 */
	public function createHoursLabels() {
		$labels = array();
		for ($i=0;$i<=23;$i++) {
			$labels[] = $i;
		}
		$this->setLabels($labels);
	}
	/**
	 * Создает структуру для построения графика за год
	 *
	 */
	public function createYearsLabels() {
		$labels = array();
		for ($i=1; $i<=12; $i++) {
			$labels[] = $i;
		}
		$this->setLabels($labels);
	}
	/**
	 * // Stepan Pokladov 08.07.11
	 * Создает структуру для построения графика за год по месяцам
	 *
	 */
	public function createMonthsLabels($year) {
		$labels = array();
		for ($i=1; $i<=12; $i++) {
			$labels[] = sprintf("%02d", $i).".$year";
		}
		$this->setLabels($labels);
	}
	
	/**
	 * Создает структуру для построения графика по неделям за год
	 */
	public function createWeeksLabels($language) {
		$count = 52;
		$labels = array();
		/**
		 * Первая отображаемая неделя
		 */
		$week = mktime(0, 0, 0, 1, 1, date('Y')) + date('W')*7*24*3600 - ($count+1)*7*24*3600;
		for ($i=0;$i<$count;$i++) {
			$labels[] = date(LANGUAGE_DATE, $week);
			/**
			 * Следующая неделя
			 */
			$week += 7*24*3600;
		}
		$this->setLabels($labels);
	}
	
	/**
	 * Функции подготовки данных для вывода на графиках
	 */
	public function prepareLimitedPie($chart_data, $max_pieces, $other_title) {
		arsort($chart_data);
		if (count($chart_data) > $max_pieces) {
			$tail = array_slice($chart_data, $max_pieces-1);
			$chart_data = array_slice($chart_data, 0, $max_pieces-1);
			
			$other = 0;
			reset($tail); 
			while (list(,$row) = each($tail)) { 
				$other += $row;
			}
			$chart_data[$other_title] = $other;
		}
		return $chart_data;
	}

}

?>