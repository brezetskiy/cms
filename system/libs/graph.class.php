<?php
/**
* ����� ���������� ��������
*
* @package Pilot
* @subpackage CMS
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

abstract class Graph {
	/**
	 * ��������� �����������, ���������� �������� setParam()
	 * @var array
	 */
	protected $params = array(
	/**
	 * ����� ��������� �����������
	 */
		'width' => 150,
		'height' => 150,
		'text_size' => 2,
		// �������� ����� ��������������� �������
		'y_interval' => 20,
		'max' => null,
		'min' => null,
		'show_legend' => false,
	/**
	 * �������� �������
	 */
		// ���� ���� ����� �����������
		'bgcolor' => 'FFFFFF',
		// ���� ���� ������� ��������� �������
		'grid_bgcolor' => 'FFFFFF',
		// ���� �������������� ����� �����
		'grid_x_color' => 'CCCCCC',
		// ���� ������������ ����� �����
		'grid_y_color' => 'CCCCCC',
		// ���� �������� �� �����������
		'label_x_color' => '5D5D5D',
		// ���� �������� �� ���������
		'label_y_color' => 'DE275B',
		// ���� ������ �� ���������
		'text_color' => '000000',
		// ���� ������ ���������
		'signature_color' => '999999',
		// ����� �������� �� ���������
		'graph_color' => array('3F8AD3', 'D84545', '79B860'),
	/**
	 * ���������/���������� ����������� ��������� �������
	 */
		// ���������� �������������� �����
		'show_x_grid' => true,
		// ����� �������� � ��� X ('center' => ����� �������, 'grid' - �� ������)
		'x_label_style' => 'center', 
		// ����� ������������ ����� ('all' - ��� �����, 'labeled' - ������ �����, � ������� ��������� �������)
		'x_grid_style' => 'all',
		// ���������� ������������ �����
		'show_y_grid' => true,
		// ���������� ������� � ��� X
		'show_x_labels' => true,
		// ���������� ������� � ��� Y
		'show_y_labels' => true,
	/**
	 * �������������� �����������
	 */
		// ������������� ������� ����������� �������, ��� ����
		// �������������� �������� ������ �������, ����� ����������� ������� �� Y ������������� �� �����
		'fixed_area' => false,
		// ���������������� ������� �� ��� Y � ����-����-����
		'kmg_labels' => false,
		// ���������� ���������� ������
		'dark_coeficient' => 20,
		// ������� ������ ������������ (������������ ������������� ����� ����)
		'symmetric' => false,
		// ���������� �������� � ������������ ��� (null ��� �����)
		'precision' => null,
		// ���������� �� ������ ��������������� ��������� ������ (���������� min/max etc)
		// ������ �����-������� ������������� �� ������ ����������
		'preprocessing' => true,
	/**
	 * ������ �������
	 */
		// ������������� ������ �������, ��� ��� ����������
		'break' => false,
		// ����������� ������������� ����� ������� ��� ����������� ������� (0.4 = 40%)
		'break_min_interval' => 0.4,
		// ����� ������� ������� ������� �������
		'break_shift_top' => 0.1,
		// ����� ������ ������� ������� �������
		'break_shift_bottom' => 0.2
	);
	
	/**
	 * ������������ ��� ������� ��������� ���������� ����� ������ ���������
	 * ������� (��������, ���� ������������ ��������� �������� �� ����� �������)
	 *
	 * @var unknown_type
	 */
	private $allow_set_param = true;
	
	/**
	 * ������ ��������, ������� ���������� ����������
	 * @var array
	 */
	protected $graphs = array();
	
	/**
	 * ������� �������
	 * @var array
	 */
	protected $legend = array();
	
	/**
	 * ������ �������� ����������, �������������� �������� prepare()
	 * @var array
	 */
	protected $prepared = array();
	
	/**
	 * ��� ������ �����������
	 * @var array
	 */
	protected $colors = array();
	
	/**
	 * ������ �����������
	 * @var resource
	 */
	protected $image;
	
	/**
	 * ��������� ������� ����������� �������
	 * @var array
	 */
	protected $area = array(
		'x1' => 0,
		'x2' => 0,
		'y1' => 0,
		'y2' => 0,
		'width' => 0,
		'height' => 0
	);
	
	/**
	 * �������� ������� �������
	 * @var array
	 */
	protected $break;
	
	/**
	 * ����������� ������
	 *
	 * @param int $width
	 * @param int $height
	 */
	public function __construct($width, $height, $bgcolor = null) {
		$this->params['width'] = $width;
		$this->params['height'] = $height;
		$this->setWorkArea(45, 5, 20, 5);
		
		if (!$this->image = imagecreatetruecolor($width, $height)) {
			trigger_error('Unable to initialize GD', E_USER_ERROR);
		}
		
		if ($bgcolor !== null) {
			$this->params['bgcolor'] = $bgcolor;
		}
	
		imagefilledrectangle($this->image, 0, 0, $width, $height, $this->color($this->params['bgcolor']));
	}
	
	
	/**
	 * ������ ���������� � ������� ������������
	 * @param string $signature
	 * @return void
	 */
	public function display($signature = null) {
		
		if (empty($this->graphs)) {
			//trigger_error('����� ������� ����������� ���������� �������� ������� ������� addGraph()', E_USER_ERROR);
			$this->text($this->params['width']/2, $this->params['height']/2, '��� ������ ��� �����������', 4, '999999', true, 'center');
			
			if (!headers_sent())  {
				imagepng ($this->image);
				imagedestroy($this->image);
			}
			
			return;
		}
		
		/**
		 * �� ��� ���� �������� ���������� ��������� ������� �������, ���, ���� �������� � �.�.
		 */
		if ($this->params['preprocessing']) {
			/**
			 * ��������� �������
			 */
			if ($this->params['show_legend']) {
				$legend = $this->drawLegend();
				$this->adjustWorkArea(0, ($legend['height']+5)*(-1));
			}
			
			/**
			 * ������������� ������, ���� � ���� ���� �������������
			 */
			$this->setBreak();
			
			
			/**
			 * ���������� ������ � ������� ������� ��� ������ �������
			 */
			$this->prepare();
			
			
			/**
			 * ��������� ����, ����� � �������� � ��� Y 
			 * (������� � � ��������� �������� draw, ������ ��� �� ��������� �������� ��� ������ ����� ��������)
			 */
			$this->drawTemplate();
		}
		
		/**
		 * ��������� �������� (����� draw ������ ���������� � ������� ��������)
		 */
		$this->draw();
		
		if ($this->params['preprocessing']) {
			/**
			 * ����� �������
			 */
			$this->drawBreak();
		}
		
		/**
		 * ��������� ���������
		 */
		if ($signature === null) {
			$signature = 'Generated: '.date('d M Y H:i').", Delta-X ltd.";
		}
		$this->text($this->params['width']/2, $this->params['height'] - 15 , $signature, null, $this->params['signature_color'], null, 'center');
		//$this->text(5, 5, $this->max);
		
		if (!headers_sent())  {
			imagepng ($this->image);
			imagedestroy($this->image);
		}
	}
	
	/**
	 * ��������� ������ �� �������
	 */
	abstract protected function draw();
	
	
	/**
	 * ������� ���������� �������
	 *
	 * @param string $param
	 * @param mixed $value
	 */
	public function setParam($param, $value) {
		if ($this->allow_set_param != true) {
			trigger_error('��� ��������� ���������� ������������� �� ������ ��������', E_USER_ERROR);
		}
		
		if (in_array($param, array('width', 'height', 'bgcolor'))) {
			trigger_error("�������� $param �������� ������ ��� ������");
		}
		
		if (array_key_exists($param, $this->params)) {
			$this->params[$param] = $value;
		} else {
			trigger_error("����������� ��������: $param", E_USER_ERROR);
		}
	}
	
	/**
	 * �������� ������ ���������, �������������� ��� ������-�������
	 *
	 * @param string $param
	 * @param mixed $default_value
	 */
	protected function addParam($param, $default_value = null) {
		if (isset($this->params[$param])) {
			trigger_error('��������� ��� ��������� ��� ������������', E_USER_ERROR);
		}
		$this->params[$param] = $default_value;
	}
	
	
	/**
	 * ������� ������� ������ ������� 
	 *
	 * @param int $top
	 * @param int $right
	 * @param int $bottom
	 * @param int $left
	 */
	public function setWorkArea($top, $right, $bottom, $left) {
		$this->area = array(
			'x1' => $left, 
			'y1' => $top, 
			'x2' => $this->params['width'] - $right, 
			'y2' => $this->params['height'] - $bottom,
			'width' => $this->params['width'] - $left - $right,
			'height' => $this->params['height'] - $top - $bottom
		);
	}
	
	/**
	 * �������� ����� ������ �� �������
	 *
	 * @param array $data
	 * @param string $color
	 */
	public function addGraph($data, $color = null, $legend = null) {
		/**
		 * ������ $data ����� ���� � ���������� ��������:
		 * 1. �������:		 $data['label'] = <value> ('label' - ������� �� X, <value> - �������� �� Y)
		 * 2. � �����������: $data['label'][<param>] = <value>
		 * 		���������:
		 * 			value - �������� �� ��� Y, ������������ ���� ��� ������������� ����� �������
		 * 			color - ���� �������
		 * 			label_color - ���� ������� � ��� X
		 */
		
		/**
		 * ����� ���������� ������� ��������� �������� ���������
		 */
		$this->allow_set_param = false;
		
		if (count($data) == 0) {
			return;
		}
		
		/**
		 * ������� � �������������� ��� �������� ������� ������� ������, ������� ���
		 * ������� � �������� ������� �������, ������� ��������� ������� ������ ������� � ������� �������
		 */
		if (count($this->graphs) > 0) {
			if (array_keys($this->graphs[0]['data']) != array_keys($data)) {
				//trigger_error('������� � �������������� ��� ��� ������� � �������� �������� �� ���������', E_USER_ERROR);
				return;
			}
		}
		
		/**
		 * ���� ���� ������� �� ������, �� ������ ��������������� ���� �� ���������
		 */
		if ($color === null) {
			$graphs_count = count($this->graphs);
			if (isset($this->params['graph_color'][$graphs_count])) {
				$color = $this->params['graph_color'][$graphs_count];
			} else {
				trigger_error('����� ���������������� ������ �������� ��������. ���������� ������� ���� ������� �������', E_USER_ERROR);
			}
		}
		
		$this->graphs[] = array(
			'data' => $data,
			'color' => $color
		);
		
		if ($legend !== null) {
			$this->legend[] = array('color' => $color, 'legend' => $legend);
		}
	}
	
	/**
     * ����� ���������
     *
     * @param string $title
     * @param string $color
     * @param int $x
     * @param int $y
     */
    public function title($title, $color = '205C96') {
   		$this->text($this->params['width']/2, 5, $title, 4, $color, null, 'center');
    }
	
	/**
	 * ����� ������ �� �������
	 *
	 * @param int $x
	 * @param int $y
	 * @param string $text
	 * @param int $size
	 * @param string $color
	 * @param bool $truetype �������������� ����� TrueType �������
	 * @param string $align ������������ ������ (left, right, center)
	 */
	public function text($x, $y, $text, $size = null, $color = null, $truetype = null, $align = null) {
		/**
		 * default ���������
		 */
		if ($color === null) {
			$color = $this->params['text_color'];
		}
		
		if ($size === null) {
			$size = $this->params['text_size'];
		}
		
		if (!in_array($align, array('left', 'right', 'center'))) {
			$align = 'left';
		}
		
		/**
		 * ���� ����� �������� ������� ��������� - �������� TrueType �������
		 */
		if (preg_match('/[�-�]/i', $text) || $truetype == true) {
			if (function_exists('imagettftext')) {
				/**
				 * ����������� ������� ������ - �� 0 �� 5
				 */
				if ($size < 0) {
					$size = 0;
				} elseif ($size > 5) {
					$size = 5;
				}
				
				/**
				 * ������ �������� �� ������� 2 ����������� ��������� �������
				 */
				if ($size % 2 == 0) {
					$font = SITE_ROOT.'system/fonts/trebuc.ttf';
				} else {
					$font = SITE_ROOT.'system/fonts/trebucbd.ttf';
				}
				
				/**
				 * ������������ �������� TrueType ������ �������� ������������ ������
				 */
				$sizes = array('6', '7', '8', '9', '10', '11');
				
				/**
				 * ����� ������� TrueType ������� ����� ���������� ��������� � ��������� UTF-8
				 */
				$text = iconv(LANGUAGE_CHARSET, 'UTF-8', $text);
				
				/**
				 * ����� ������ ������ �������� imagettftext ���������� �� imagestring:
				 * imagestring - x, y - ������ ���������� ������ �������� ���� ������
				 * imagettftext - x, y - ������ ���������� ������ ������� ���� ������ 
				 * ������� ���������� Y ���������� ��������� �� ������ ������� ������ ������
				 */
				$metrics = $this->text_metrics($text, $sizes[$size], $font, true);
				
				if ($align == 'right') {
					$x  -= $metrics['width'];
				} elseif ($align == 'center') {
					$x  -= $metrics['width']/2;
				}
				
				imagettftext($this->image, $sizes[$size], 0, $x, $y + $metrics['height'], $this->color($color), $font, $text);
				return;
			} else {
				/**
				 * ���� �� ����������� ���������� FreeType,
				 * ������ �������������� �������� ������ � ������� ����������� �������
				 */
				$text = Charset::translit($text);
			}
		} 
		
		$metrics = $this->text_metrics($text, $size);
				
		if ($align == 'right') {
			$x  -= $metrics['width'];
		} elseif ($align == 'center') {
			$x  -= $metrics['width']/2;
		}
		imagestring($this->image, $size, $x, $y, $text, $this->color($color));
	}
	
	/**
	 * ��������� ������ � ������� 
	 *
	 * @param int $graph_id
	 * @param string $point
	 * @return array
	 */
	protected function getVertex($graph_id, $point) {
		
		if (!isset($this->graphs[$graph_id]['data'])) {
			x($graph_id);
		}
		
		if (is_array($this->graphs[$graph_id]['data'][$point])) {
			return array(
				'value' => $this->graphs[$graph_id]['data'][$point]['value'],
				'color' => (isset($this->graphs[$graph_id]['data'][$point]['color'])) 
					? $this->graphs[$graph_id]['data'][$point]['color'] 
					: $this->graphs[$graph_id]['color'],
				'label_color' => (isset($this->graphs[$graph_id]['data'][$point]['label_color'])) 
					? $this->graphs[$graph_id]['data'][$point]['label_color'] 
					:$this->params['label_x_color']
			);
		} else {
			return array(
				'value' => $this->graphs[$graph_id]['data'][$point],
				'color' => $this->graphs[$graph_id]['color'],
				'label_color' => $this->params['label_x_color']
			);
		}
	}

	
	/**
	 * �������� ������� ������ �������
	 *
	 * @param int $dx
	 * @param int $dy
	 * @param bool $left_corner 
	 */
	protected function adjustWorkArea($dx, $dy, $left_corner = false) {
		if ($left_corner) {
			$this->setWorkArea($this->area['y1'] + $dy, $this->params['width'] - $this->area['x2'], $this->params['height'] - $this->area['y2'], $this->area['x1'] + $dx);
		} else {
			$this->setWorkArea($this->area['y1'], $this->params['width'] - $this->area['x2'] - $dx, $this->params['height'] - $this->area['y2'] - $dy, $this->area['x1']);			
		}
	}
	
	/**
	 * ������� ������ �������, ���� ��� ����������
	 */
	protected function setBreak() {
		if ($this->params['break'] != true) {
			return;
		}
		
		$values = array();
		
		reset($this->graphs);
		while (list($graph_id, $row)=each($this->graphs)) {
			reset($row['data']);
			while (list($point, $data)=each($row['data'])) {
				$vertex = $this->getVertex($graph_id, $point);
				$values[] = $vertex['value'];
			}
		}
		
		rsort($values);
		
		/**
		 * ���� ������������ �������� �� �������, � ������� ��� ��������
		 * � ���������� ����� ������ ���������� ���� ��������
		 */
		$max_interval = 0;
		$interval = array();
		for ($i=0; $i<count($values); $i++) {
			if (isset($values[$i+1]) && $values[$i] - $values[$i+1] > $max_interval && round($values[$i+1] * (1 + $this->params['break_shift_bottom'])) != 0) {
				$max_interval = $values[$i] - $values[$i+1];
				$interval = array('start' => round($values[$i+1] * (1 + $this->params['break_shift_bottom'])), 'end' => $values[$i] * (1 - $this->params['break_shift_top']));
			}
			
			if (!isset($max_value) || $max_value < $values[$i]) {
				$max_value = $values[$i];
			}
			
			if (!isset($min_value) || $min_value > $values[$i]) {
				$min_value = $values[$i];
			}
		}
		
		/**
		 * ���� ������������ �������� ������ ������������ �������� - ������ ������
		 */
		if ($max_interval > $this->params['break_min_interval'] * ($max_value - $min_value)) {
			$this->break = $interval;
			//x($max_value);
			//x($min_value);
		}
	}
	
	/**
	 * ��������� ����� ������� �������
	 */
	private function drawBreak() {
		if (!is_array($this->break)) {
			return;
		}
		
		$x = $this->area['x1']+1;
		$break_line = $this->translateCoord($this->break['end']);
		$counter = 0;
		$y = $break_line;
		while($x < $this->area['x2']) {
			$x2 = $x + 10;
			if ($x2 > $this->area['x2']) {
				$x2 = $this->area['x2'];
			}
			$y2 = ($counter%2 == 0)? $break_line+3:$break_line-3;
			
			$counter++;
			$this->line($x+1, $y-1, $x2, $y2-1, 'ffffff');
			$this->line($x+1, $y, $x2, $y2, 'ff0000');
			$this->line($x+1, $y+1, $x2, $y2+1, 'ffffff');
			$x = $x2;
			$y = $y2;
		}
	}
	
	/**
	 * ��������� "�������" ������� (��������, �� ������� ��������� ���, ����� etc)
	 */
	protected function drawTemplate() {
		/**
		 * 0. �������� ���� ��� �����
		 */
		imagefilledrectangle($this->image, $this->area['x1'], $this->area['y1'], $this->area['x2'], $this->area['y2'], $this->color($this->params['grid_bgcolor']));
		
		/**
		 * 1. ��������� �������������� ���� ����
		 */
		if ($this->params['show_y_grid']) {
			for ($pos = $this->area['y1']; $pos <= $this->area['y2']; $pos += $this->params['y_interval']) {
				$this->line($this->area['x1'], $pos, $this->area['x2'], $pos, $this->params['grid_y_color']);
			}
		}
		
		/**
		 * 2. ��������� ������������ ����
		 */
		if ($this->params['show_x_grid']) {
			/**
			 * ��������� ����� ����������� ����� - ��� ��� ������ ����� � ���������
			 */
			if ($this->params['x_grid_style'] == 'all') {
				$x_disp = $this->prepared['x_disp'];
			} elseif ($this->params['x_grid_style'] == 'labeled') {
				$x_disp = $this->prepared['x_disp'] * ($this->prepared['x_skip_count']+1);
			} else {
				trigger_error('����������� ����� ����������� ������������ �����: '.$this->params['x_grid_style'].". ���������� ��������: 'all', 'labeled'.", E_USER_ERROR);
			}
			
			for ($pos = $this->area['x1']; $pos <= $this->area['x2']; $pos += $x_disp) {
				$this->line($pos, $this->area['y1'], $pos, $this->area['y2'], $this->params['grid_x_color']);
			}
			
			/**
			 * ����������� ����� �� ������ ������� �������
			 */
			$this->line($this->area['x2'], $this->area['y1'], $this->area['x2'], $this->area['y2'], $this->params['grid_x_color']);
		}
		
		/**
		 * 3. ������� � ������������ ���
		 */
		if ($this->params['show_y_labels']) {
			reset($this->prepared['y_labels']);
			while (list(,$row)=each($this->prepared['y_labels'])) {
				$this->line($this->area['x1'], $row['y'], $this->area['x1']-2, $row['y'], $this->params['label_y_color']);
				$metrics = $this->text_metrics($row['value'], $this->params['text_size']);
				$this->text($this->area['x1'] - 4, $row['y'] - $metrics['height']/2, $row['value'], null, $this->params['label_y_color'], null, 'right');
			}
		}
		
		/**
		 * 4. ������� � �������������� ���
		 */
		if ($this->params['show_x_labels']) {
			if ($this->params['x_label_style'] == 'grid') {
				$start = $this->area['x1'] ;
				$disp = $this->prepared['x_disp'] * ($this->prepared['x_skip_count'] + 1);
			} elseif ($this->params['x_label_style'] == 'center') {
				$start = $this->area['x1'] + /*$this->prepared['x_disp'] * ($this->prepared['x_skip_count']) +*/ $this->prepared['x_disp']*0.5;
				$disp = $this->prepared['x_disp'] * ($this->prepared['x_skip_count'] + 1);
			} else {
				trigger_error('����������� ����� ����������� ��������: '.$this->params['x_label_style'].". ���������� ��������: 'center', 'grid'.");
			}
			
			$counter = $this->prepared['x_skip_count'];
			$pos = $start;
			reset($this->graphs[0]['data']);
			while (list($point, $value)=each($this->graphs[0]['data'])) {
//				if ($counter == $this->prepared['x_skip_count']) {
//					/**
//					 * ����� �������
//					 */
//					$vertex = $this->getVertex(0, $point);
//					$this->text($pos, $this->area['y2']+5, $point, null, $vertex['label_color'], null, 'center'); 
//					$this->line($pos, $this->area['y2'], $pos, $this->area['y2']+2, $this->params['label_x_color']);
//					$pos += $disp;
//					$counter = 0; 
//				} else {
//					$counter++;
//				}
				if ($counter == $this->prepared['x_skip_count']) {
					/**
					 * ����� �������
					 */
					$vertex = $this->getVertex(0, $point);
					$this->text($pos, $this->area['y2']+5, $point, null, $vertex['label_color'], null, 'center'); 
					$this->line($pos, $this->area['y2'], $pos, $this->area['y2']+2, $this->params['label_x_color']);
					$pos += $disp;
					$counter = 0; 
				} else {
					$counter++;
				}
				
				//x($this->prepared['x_skip_count']);
			}
		}
		
		//echo count($this->prepared['y_labels']);
		
		$this->drawAreaRect();
		//imagerectangle($this->image, 0, 0, $this->params['width']-1, $this->params['height']-1, $this->color('9999ff'));
	}
	
	/**
	 * �������������� �������� � ���������� �� ������������ ���
	 *
	 * @param int $value
	 * @return int
	 */
	protected function translateCoord($value) {
		if (!is_array($this->break)) {
			$interval = $this->params['max'] - $this->params['min'];
			$result = round($this->area['y2'] - ($value - $this->params['min'])*$this->area['height'] / ($this->params['max'] - $this->params['min']));		
			
			if ($result < $this->area['y1']) {
				//x($value);
				//x($this->area);
				//x($this->params);
			}
			
			return $result;
		} else {
			if ($value >= $this->break['end']) {
				return $this->area['y2'] - ($value - $this->params['min'])*$this->area['height'] / ($this->params['max'] - $this->params['min']);
			} else {
				return $this->area['y2'] - ($value - $this->params['min'])*($this->area['y2'] - $this->translateCoord($this->break['end'])) / ($this->break['start'] - $this->params['min']);
			}
		}
	}
	
	/**
	 * �������������� ����� � ������� HTML � ���� ��� GD
	 *
	 * @param string $color
	 * @return int
	 */
	protected function color($color) {
		if (!isset($this->colors[ $color ])) {
			sscanf($color, "%2x%2x%2x", $red, $green, $blue);
    		return imagecolorallocate($this->image, $red, $green, $blue);
		} else {
			return $this->colors[ $color ];
		}
	}
	
	 /**
     * ���������� ����������� ����
     * 
     * @param string $color
     * @param int $dark_coef
     * @return int
     */
    protected function darkcolor($color, $dark_coef = null) {
    	if ($dark_coef === null) {
    		$dark_coef = $this->params['dark_coeficient'];
    	}
    	
    	sscanf($color, "%2x%2x%2x", $red, $green, $blue);
    	$red   = ($red   > $dark_coef) ? $red   - $dark_coef : 0;
    	$green = ($green > $dark_coef) ? $green - $dark_coef : 0;
    	$blue  = ($blue  > $dark_coef) ? $blue  - $dark_coef : 0;
		
    	return $this->color(sprintf("%2x%2x%2x", $red, $green, $blue));
    } 
    
	
	/**
	 * ������ �����
	 *
	 * @param int $start_x
	 * @param int $start_y
	 * @param int $end_x
	 * @param int $end_y
	 * @param string $color
	 */
	protected function line($start_x, $start_y, $end_x, $end_y, $color) {
		imageline($this->image, $start_x, $start_y, $end_x, $end_y, $this->color($color));
	}
	
	
	/**
	 * ���������� ������� ��������������, � ������� ����� ������ �����
	 *
	 * @param string $text
	 * @param int $size
	 * @param string $font
	 * @param bool $truetype
	 * @return array
	 */
	protected function text_metrics($text, $size, $font = null, $truetype = null) {
		/**
		 * ���� ��� ������ ������ ������������ TrueType �����, �� �������
		 * ����������� �������������� ���������� ���������� �-���� imagettfbbox
		 */
		if ($truetype) {
			$text_box = imagettfbbox($size, 0, $font, $text);
			return array(
				'height' => $text_box[1]-$text_box[7], 
				'width' => $text_box[2]-$text_box[0]);
		} else {
			return array(
				'height' => imagefontheight($size),
				'width' => imagefontwidth($size)*strlen($text));
		}
	}
	
	/**
	 * ������� �������� �������������� ������ � ��������, ���������� ������� � ����,
	 * ������������� ������� ����������� ������� � ������ ���� ��������
	 */
	private function prepare() {
			
		$this->drawAreaRect('ff0000');
		
		/**
		 * 1. ����� ������������ � ������������� �������� �� �������
		 * 2. ����� ������� ��������
		 */
		reset($this->graphs);
		while (list($graph_id, $graph)=each($this->graphs)) {
			
			$graph_sum = 0;
			reset($graph);
			while (list($point, $row)=each($graph['data'])) {
				$vertex = $this->getVertex($graph_id, $point);
				$graph_sum += $vertex['value'];
				if (!isset($max) || $vertex['value'] > $max) {
					$max = $vertex['value'];
				}
				
				if (!isset($min) || $vertex['value'] < $min) {
					$min = $vertex['value'];
				}
				
				if (!isset($x_max_len) || strlen($point) > $x_max_len) {
					$x_max_len = strlen($point);
					$this->prepared['x_longest_label'] = $point;
				}
			}
			$this->graphs[$graph_id]['average'] = $graph_sum / count($this->graphs[$graph_id]['data']);
		}
		
		/**
		 * ������ ��������� min & max �������, ������ ���� ������������ �� ��������� �� �����
		 * ��������� ���� � 5%, ����� ������ �� �������� �� ���� �����������
		 */
		if ($this->params['min'] === null) {
			/**
			 * ���� ���������� ������ � �������� ����������� ������� ������� �� 5% ������������� ��������,
			 * �� ���� ������ ������ ������� �����, ������� ��������� �� 5% �� ������ ������� ������� 
			 */
			if (is_array($this->break)) {
				$this->params['min'] = $min - max(abs($this->break['start']), abs($min))*0.05;	
			} else {
				$this->params['min'] = $min - max(abs($max), abs($min))*0.05;
			}
		}
		
		if ($this->params['max'] === null) {
			$this->params['max'] = $max + max(abs($max), abs($min))*0.05;
		}
		
		/**
		 * ������ ������ ��� �������������� �����
		 */
		if ($this->params['min'] == $this->params['max']) {
			$this->params['max'] += 100;
			$this->params['precision'] = 0;
		}
		
		/**
		 * ������� ������ ������������, ���� ����� �������� symmetric
		 */
		if ($this->params['symmetric']) {
			$max = max(abs($this->params['min']), abs($this->params['max']));
			$this->params['max'] = $max;
			$this->params['min'] = $max * (-1);
			//x($this->params);
		}
		
		
		/**
		 * 3. ������� �������� � ��� Y 
		 */
		$this->prepared['y_labels'] = array();
		$y_labels_count = ceil($this->area['height'] / $this->params['y_interval']);
		
		if ($y_labels_count < 3) {
			trigger_error('������� ������� ����������� � ������� ������� ��� ��������� �������� ����� ��������������� �������', E_USER_ERROR);
		}
		
		//x($this->area['height']);
		//x($this->params['y_interval']);
		
		/**
		 * �������� ������ ������� �������, ����� ����������� ����� ����� �������������� �����
		 */
		$this->adjustWorkArea(0, ($this->area['y2'] - $this->area['y1'] - ($y_labels_count-1) * $this->params['y_interval'])*(-1), false);
		//x($this->area['y2']);
		//x($this->area['y1']);
		//x($y_labels_count);
		
		if (is_array($this->break)) {
			/**
			 * ������ ������� ����������
			 */
			$bottom_y_count = ceil(($this->area['y2'] - $this->translateCoord($this->break['start'])) / $this->params['y_interval']);
			$top_y_count = $y_labels_count - $bottom_y_count;
			
			//$tmp = $this->translateCoord($this->break['start']);
			//x($tmp);
			//x($this->area['y2']);
			
			if ($top_y_count == 1) {
				$top_y_count = 2;
				$bottom_y_count--;
			}
			
			$top_counter_disp = ($this->params['max'] - $this->params['min']) / ($y_labels_count - 1); 
			$bottom_counter_disp = ($this->break['start'] - $this->params['min']) / ($bottom_y_count - 0); 
			
			$label_sections = array(
				array('start' => 0, 'end' => $bottom_y_count, 'disp' => $bottom_counter_disp, 'start_value' => $this->params['min']),
				array('start' => $bottom_y_count, 'end' => $top_y_count + $bottom_y_count, 'disp' => $top_counter_disp, 'start_value' => $bottom_y_count * $top_counter_disp)
			);
		} else {
			/**
			 * ������ ������� �� ����������
			 */
			$counter_disp = ($this->params['max'] - $this->params['min']) / ($y_labels_count - 1);
			$label_sections = array(
				array('start' => 0, 'end' => $y_labels_count, 'disp' => $counter_disp, 'start_value' => $this->params['min'])
			);
		}
		
		$max_len = 0;
		$counter = 0;
		reset($label_sections);
		while (list(,$section)=each($label_sections)) {
			$value = $section['start_value'];
			$tmp_counter = 0;
			for ($i=$section['start']; $i<$section['end']; $i++) {
				$precision = $this->get_precision($value);
				
				/**
				 * ������ ������� k, M, G
				 */
				if ($this->params['kmg_labels']) {
					$disp_value = $this->make_kmg_label($value, $precision);
				} else {
					$disp_value = round($value, $precision);
				}
				
				/**
				 * Fix "-0" bug
				 */
				if ($disp_value == "-0") {
					$disp_value = 0;
				}
				
				$this->prepared['y_labels'][$counter] = array('value' => $disp_value, 'y' => $this->area['y2']-$counter * $this->params['y_interval']);
				$value += $section['disp'];
				if (strlen($disp_value) > $max_len) {
					$max_len = strlen($disp_value);
					$y_longest_label = $disp_value;
				}
				$counter++;
				$tmp_counter++;
			}
			//x($tmp_counter);
		}
		$this->prepared['y_max_length'] = $max_len;
		
		/**
		 * ������� ������ ������ �� ������ ������������ ������� �� ��� Y
		 */
		if ($this->params['fixed_area'] != true && $this->params['show_y_labels'] == true) {
			$y_longest = $this->text_metrics($y_longest_label, $this->params['text_size']);
			$this->adjustWorkArea($y_longest['width'] + 5, 0, true);
		}
		
		/**
		 * ����������, �� ������� ������ ������ ��� X � ����� �� ���������� 
		 * ������� �� ��� X (����� ���������, ����� �������� ����� ����� � ��������
		 * ������� ����� ����� ������������ ��������)
		 */

		$x_disp_float = ($this->area['x2'] - $this->area['x1'])/count($this->graphs[0]['data']);

		
		if ($x_disp_float < 1) {
			trigger_error('������� ����� ������ �� ������� ������', E_USER_ERROR);
		}
		
		$x_disp = floor($x_disp_float);
		$longest_label = $this->text_metrics($this->prepared['x_longest_label'], $this->params['text_size']);
		$skip_count = floor($longest_label['width']*1.2/$x_disp);
		$this->prepared['x_skip_count'] = $skip_count;
		$this->prepared['x_disp'] = $x_disp;
		
		//echo count($this->graphs[0]['data']);
		
		/**
		 * �������� ������ ������� �������, ����� ����������� ����� ����� ������������ �����
		 * ���� ������� ��������� �� ������ ������������ �����, �� ���������� ����� ����� ��������� �� ����
		 */
		if ($this->params['x_label_style'] == 'center') {
			$this->adjustWorkArea(($this->area['x2'] - $this->area['x1'] - (count($this->graphs[0]['data'])) * $x_disp)*(-1), 0, false); 
		} else {
			$this->adjustWorkArea(($this->area['x2'] - $this->area['x1'] - (count($this->graphs[0]['data'])-1) * $x_disp)*(-1), 0, false); 
		}
		//echo "count: ".count($this->graphs[0]['data'])."; disp: $x_disp";
		//x($this->area);
		
		

	}
	
	/**
	 * ����������� ����� � kMG �������
	 * @param float $value
	 * @return string
	 */
	private function make_kmg_label($value, $precision) {
		/**
		 * ���� �������� precision ����� �������, �� ��� ���������� ��������� � ����������
		 * ����� �������, ���� �� �� ��������� ������������� - �� �������
		 */
		if ($this->params['precision'] !== null) {
			if ($value >= 1000000000) {
				$disp_value = (round($value/1000000000, $precision))."G";
			} elseif ($value >= 1000000) {
				$disp_value = (round($value/1000000, $precision))."M";
			} elseif ($value >= 1000) {
				$disp_value = (round($value/1000, $precision))."k";
			} else {
				$disp_value = round($value, $precision);
			}
		} else {
			if ($value >= 1000000000) {
				$disp_value = (round($value/1000000000, $precision+9))."G";
			} elseif ($value >= 1000000) {
				$disp_value = (round($value/1000000, $precision+6))."M";
			} elseif ($value >= 1000) {
				$disp_value = (round($value/1000, $precision+3))."k";
			} else {
				$disp_value = round($value, $precision);
			}
		}
		
		return $disp_value;
	}
	
	/**
	 * ����������� ������� ���������� � ������������ � ��������� �����������
	 * @param float $value
	 * @return int
	 */
	private function get_precision($value) {
		if ($this->params['precision'] !== null) {
			$precision = $this->params['precision'];
		} else {
			$precision = floor(log10(abs($value)) - 2)*(-1);
			$precision = ($precision > 5) ? 5 : $precision;
		}
		return $precision;
	}
	
	/**
	 * ��������� ������� �������
	 * ���������� ������ � ������������ � ��������� ������� ��������� �������
	 * @return array
	 */
	protected function drawLegend() {
		$sx = 1;
		$sy = $this->params['height'] - count($this->legend)*20 - 11;
		/**
		 * �� ���� ������ �� ��������� � �������
		 */
		if (empty($this->legend)) {
			return array('height' => 0, 'width' => 0, 'x1' => 0, 'y1' => 0, 'x2' => 0, 'y2' => 0);
		}
		
		$max_width = 0;
		reset($this->legend);
		while (list(,$row)=each($this->legend)) {
			$metrics = $this->text_metrics($row['legend'], $this->params['text_size']);
			if ($metrics['width'] > $max_width) {
				$max_width = $metrics['width'];
			}
		}
		
		$max_width += 25;	
		
		imagefilledrectangle($this->image, $sx, $sy, $max_width + $sx, $sy + count($this->legend)*20 + 9, $this->color('ffffff'));
		imagerectangle($this->image, $sx, $sy, $max_width + $sx, $sy + count($this->legend)*20 + 9, $this->color('000000'));
		$y = $sy + 10;
		reset($this->legend);
		while (list($index, $row) = each($this->legend)) {
			imagefilledrectangle($this->image, $sx + 5, $y, $sx + 13, $y+8, $this->color($row['color']));
			imagerectangle($this->image, $sx + 5, $y, $sx + 13, $y+8, $this->color('000000'));
			$this->text($sx + 17, $y-2, $row['legend'], null, null, true);
			$y+=20; 
		}
		
		return array('height' => count($this->legend)*20 + 10, 'width' => $max_width, 'x1' => $sx, 'y1' => $sy, 'x2' => $sx+$max_width, 'y2' => $sy+count($this->legend)*20+10);
	}
	
	/**
	 * Debug - �������
	 */
	protected function drawAreaRect($color = 'ff9999') {
		//imagerectangle($this->image, $this->area['x1'], $this->area['y1'], $this->area['x2'], $this->area['y2'], $this->color($color));
	}
}

?>