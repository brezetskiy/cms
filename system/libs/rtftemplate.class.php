<?php
/** 
 * ����� ��� ������ � ��������� ���������� � ������� RTF 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

/**
 * ����� ��� ������ � RTF ���������
 *
 */
class RtfTemplate {
	
	/**
	 * ������� �������
	 * @var string
	 */
	private $rtf = '';
	
	/**
	 * ������ ����������, ������� ����� �������� � �������
	 * @var array
	 */
	private $variables = array();
	
	/**
	 * ������ �������� ������ ���� ������, �������� � Color Table ���������
	 * ������������ ��� ���������� ����� ������
	 * ������: ['color_index(0+)'] => UPPER('#ffffff')
	 * @var array
	 */
	private $colors = array();
	
	/**
	 * ������ �������� ������� ������� ���������
	 * ������������ ��� ����������� ��������� ����� ������
	 * ������ ['font_index'] => 'Font name'
	 * @var array
	 */
	private $fonts = array();
	
	/**
	 * ������������ ������ ������������� � ��������� ������
	 * ������������ ��� ���������� ������ ������
	 * @var int
	 */
	private $max_font_index = 0;
	
	/**
	 * ��������� ����������� ��������
	 * @var array
	 */
	private $iterations = array();
	
	/**
	 * ���������, ������������ ��� �������������� ������ HTML � RTF
	 */
	// regexp, ������������ �����
	private $units_regexp = '(px|%|cm)?';
	// ������ �������
	private $table_width = '';
	// �����: 0-Null, 2-Percent, 3-Twips
	private $table_width_unit = null;
	// ������ ����� ������� (������� � ���� TABLE)
	private $table_border = null;
	// !������! ����� ����� �������
	private $table_border_color = null;
	// !������! ����� ���� �������
	private $table_bg_color = null;
	// ������ �����, ������������ ��� ������ TR
	private $row_border = null;
	// !������! ����� ����� c����� �������
	private $row_border_color = null;
	// !������! ����� ���� c����� �������
	private $row_bg_color = null;
	// ������ �����, ������������ ��� ������ TD
	private $cell_border = null;
	// !������! ����� ����� ������ �������
	private $cell_border_color = null;
	// !������! ����� ���� ������ �������
	private $cell_bg_color = null;
	// ���������� �����, ������� ������������
	private $cell_colspan = 0;
	
	/**
	 * ����������� ������. $filename - ������ ��� ����� � ��������
	 * @param string $filename
	 */
	public function __construct($filename) {
		if (!file_exists($filename) || !is_readable($filename)) {
			trigger_error(cms_message('CMS', '���� � �������� %s �� ���������� ��� �� ����� ���� ��������', $filename),  E_USER_ERROR);
		}
		
		$this->rtf = file_get_contents($filename);
		
		/**
		 * ������ ������� ������ ���������
		 */
		if (preg_match("~\{\s*\\\\colortbl\;([^\}]+)\}~im", $this->rtf, $colortable)) {
			if (preg_match_all("~\\\\red([0-9]+)\\\\green([0-9]+)\\\\blue([0-9]+)~im", $colortable[1], $colors)) {
				reset($colors[1]); 
				while (list($index,$row) = each($colors[1])) { 
					$this->colors[$index] = $this->colorRgbToHtml($colors[1][$index], $colors[2][$index], $colors[3][$index]);
				}
			}
		}
		
		/**
		 * ������ ������� ������� ���������
		 */
		if (preg_match_all("~\\\\f([0-9]+)\\\\f(swiss|nil|roman|modern|script|decor|tech|bidi)\\\\fcharset([0-9]+)\\\\fprq([0-9]+)\s*([^{};]+);~im", $this->rtf, $fonttable)) {
			reset($fonttable[1]); 
			while (list($index,$row) = each($fonttable[1])) { 
				if ($fonttable[1][$index] > $this->max_font_index) {
					$this->max_font_index = $fonttable[1][$index];
				}
				/**
				 * ���������� ��-������������� ������, ��� ��� �� �����������
				 * ���� �� ������ ��, �� �������� ����� �������� ������ ��-������������� �������
				 */
				if ($fonttable[3][$index] != 204) {
					continue;
				}
				$this->fonts[$fonttable[1][$index]] = strtolower(trim($fonttable[5][$index]));
			}
		}
	}
	
	/**
	 * �������� ����� �������
	 * @param string $template
	 * @param array $data
	 */
	public function iterate($template, $data) {
		$this->iterations[$template][] = $data;
	}
	
	/**
	 * ������ �������� ����������. �������� ����� ��������� ������� HTML-���,
	 * ������� ����� ������������ � ������ RTF 
	 *
	 * @param string $variable
	 * @param mixed $value
	 */
	public function set($variable, $value = null) {
		if (is_array($variable)) {
			reset($variable); 
			while (list($name,$data) = each($variable)) { 
				 $this->variables[$name] = (string) $data;
			}
			return;
		} elseif (empty($variable)) {
			return;
		}
		
		$this->variables[$variable] = (string) $value;
	}
	
	/**
	 * ���������� ���������� RTF ���������, ���������� �� �������
	 * @return string
	 */
	public function display() {
		// �������� ����� � RTF ��������� ������������, ������� �������� ��, ����� �� ������ ���������� ����������
		$this->rtf = preg_replace("~[\n\r]~", '', $this->rtf);
		
		/**
		 * ������������ ����������� �������
		 */
		reset($this->iterations); 
		while (list($template,$iterations) = each($this->iterations)) { 
			if(preg_match("~<tmpl:$template>~", $this->rtf, $start_match, PREG_OFFSET_CAPTURE) && preg_match("~</tmpl:$template>~", $this->rtf, $end_match, PREG_OFFSET_CAPTURE)) {
				$tmpl_start = $start_match[0][1];
				$tmpl_end = $end_match[0][1] + strlen("</tmpl:$template>");
				
				$tmpl_content = substr($this->rtf, $tmpl_start, $tmpl_end - $tmpl_start);
				$tmpl_content = substr($tmpl_content, strlen("<tmpl:$template>"), -strlen("</tmpl:$template>"));
				
				$tmpl_rtf_before = substr($this->rtf, 0, $tmpl_start);
				$tmpl_rtf_after = substr($this->rtf, $tmpl_end);
				$tmpl_iterated = '';
				
				reset($iterations); 
				while (list(,$tmpl_variables) = each($iterations)) { 
					$tmpl_current_iteration = $tmpl_content;
					krsort($tmpl_variables);
					reset($tmpl_variables); 
					while (list($var,$row) = each($tmpl_variables)) { 
						$row = $this->htmlToRtf($row);
						$tmpl_current_iteration = preg_replace('~\$'.$var.'~i', $row, $tmpl_current_iteration);
						$tmpl_current_iteration = preg_replace('~\$}{[a-z0-9\\\]+\s+'.$var.'~is', $row, $tmpl_current_iteration);
					}
					$tmpl_iterated .= $tmpl_current_iteration;					 
				}
				
				$this->rtf = $tmpl_rtf_before . $tmpl_iterated . $tmpl_rtf_after;
			} else {
				echo "Unknown iterable part: $template";
				exit;
			}
		}
		
		/**
		 * �������� ���������� �� �� ��������
		 */
		krsort($this->variables);
		reset($this->variables); 
		while (list($var,$row) = each($this->variables)) { 
			$row = $this->htmlToRtf($row);
			$this->rtf = preg_replace('~\$'.$var.'~i', $row, $this->rtf);
			/**
			 * ���������� ���������� ���� �����, ����� �� ��������� ������� �����
			 * ������� ��������������
			 * $}{
\b0\i0\fs20\ul\lang1033\langfe255\langnp1033\insrsid6817888 datefrom}
			 */
			$this->rtf = preg_replace('~\$}{[a-z0-9\\\]+\s+'.$var.'~is', $row, $this->rtf);
		}
		
		/**
		 * ������ ������� RTF
		 */
		return $this->rtf;
	}
	
	/**
	 * ��������������� ���������� HTML ��� � ������ RTF
	 *
	 * @param string $html
	 * @return string
	 */
	private function htmlToRtf($html) {
		
		$html = html_entity_decode($html);
		// workaround
		//$html = preg_replace("~[\r\n]~", "", $html);
		
		$doc_buffer = $this->specialCharacters($html);
		unset($html);
		
		/**
		 * ������
		 */
		$doc_buffer = str_ireplace("<UL>", "", $doc_buffer);
		$doc_buffer = str_ireplace("</UL>", "", $doc_buffer);
		$doc_buffer = preg_replace("/<LI>(.*?)<\/LI>/mi", "{\\f3\\'B7\\tab} \\1\\par", $doc_buffer);
		
		/**
		 * ������� ���� ��������������
		 */
		$doc_buffer = preg_replace("/<P>(.*?)<\/P>/mi", "\\1\\par ", $doc_buffer);
		$doc_buffer = preg_replace("/<STRONG>(.*?)<\/STRONG>/mi", "\\b \\1\\b0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<B>(.*?)<\/B>/mi", "\\b \\1\\b0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<EM>(.*?)<\/EM>/mi", "\\i \\1\\i0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<I>(.*?)<\/I>/mi", "\\i \\1\\i0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<U>(.*?)<\/U>/mi", "\\ul \\1\\ul0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<STRIKE>(.*?)<\/STRIKE>/mi", "\\strike \\1\\strike0 ", $doc_buffer);
		$doc_buffer = preg_replace("/<SUB>(.*?)<\/SUB>/mi", "{\\sub \\1}", $doc_buffer);
		$doc_buffer = preg_replace("/<SUP>(.*?)<\/SUP>/mi", "{\\super \\1}", $doc_buffer);
		
		
		/**
		 * ���������
		 */
		$doc_buffer = preg_replace("/<H1>(.*?)<\/H1>/mi", "{\\fs48\\b \\1\\b0\\par}", $doc_buffer);
		$doc_buffer = preg_replace("/<H2>(.*?)<\/H2>/mi", "{\\fs36\\b \\1\\b0\\par}", $doc_buffer);
		$doc_buffer = preg_replace("/<H3>(.*?)<\/H3>/mi", "{\\fs27\\b \\1\\b0\\par}", $doc_buffer);
		
		/**
		 * ������
		 */
		$doc_buffer = preg_replace_callback("/<FONT([^>]*)>(.*?)<\/FONT>/mis", array($this, 'parserFontCallback'), $doc_buffer);
		
		/**
		 * �������...
		 * ������� ���������� ����� ������ TD, TR, TABLE - RTF ����-�� �� �����
		 * ����� � ������� ����������� �������� ������, ���� � ������������ �������
		 * ��� ����� �������� ������ ������ �������������� %)
		 */
		$doc_buffer = preg_replace("~(>)[\s\r\n]+(</?(?:td|tr|table))~mis", "$1$2", $doc_buffer);
		$doc_buffer = preg_replace_callback("/<TABLE([^>]*)>(.*?)<\/TABLE>/mis", array($this, 'parserTableCallback'), $doc_buffer);
		
		/**
		 * ������ ����
		 */
		$doc_buffer = str_ireplace("<BR>", "\\line ", $doc_buffer);
		$doc_buffer = str_ireplace("<PAGEBREAK>", "\\page ", $doc_buffer);
		$doc_buffer = str_ireplace("<TAB>", "\\tab ", $doc_buffer);
		
//		x($doc_buffer);
		
		/**
		 * �������� �������� ����� �� ������ 
		 * ��������, ����� �������� �� \line, � �� \par ...
		 */
		$doc_buffer = str_replace("\n", " \\par ", $doc_buffer);
		$doc_buffer = preg_replace("~(0x0D|0x0A)~", '', $doc_buffer);
		
//		x($doc_buffer);
		
		return $doc_buffer;
	}
	
	/**
	 * Callback ��� ������ ����� FONT
	 * $font[1] => ��������� ���� FONT
	 * $font[2] => ���������� ���� FONT
	 * @param array $table
	 */
	private function parserFontCallback($font) {
		/**
		 * ������������ ���������
		 * \f36\fs40\cf6\
		 * \�����_������\������\����
		 */
		$font_spec = '';
		if (preg_match("~size=[\"']?([0-9]+)[\"']?~i", $font[1], $match)) {
			$font_spec .= '\\fs'.$match[1]*2;
		}
		if (preg_match("~color=[\"']?(#[0-9a-f]+)[\"']?~i", $font[1], $match)) {
			$font_spec .= "\\cf".$this->colorToIndex($match[1]);
		}
		if (preg_match("~face=[\"']?([a-z\s]+)[\"']?~i", $font[1], $match)) {
			$font_spec .= "\\f".$this->fontToIndex($match[1]);
		}
		
		if (!empty($font_spec)) {
			return '{'.$font_spec.' '.$font[2].'}';
		} else {
			return $font[2];
		}
	}
	
	/**
	 * Callback ��� ������ HTML ������ � RTF �������
	 * $table[1] => ��������� ���� TABLE
	 * $table[2] => ���������� ���� TABLE
	 * @param array $table
	 */
	private function parserTableCallback($table) {
		$table_content = '\\par ';
		
		/**
		 * ��������� ����������, �������� �� ������ �������
		 */
		// 1. WIDTH
		$this->table_width = null;
		if (preg_match("~width=[\"']?([0-9]+)".$this->units_regexp."[\"']?~i", $table[1], $match)) {
			$this->table_width = $this->toTwips($match[1], $match[2]);
			$this->table_width_unit = $this->getUnits($match[2]);
		}
		
		// 2. BORDER
		$this->table_border = null;
		if (preg_match("~border-width:\s*([0-9]+)~i", $table[1], $match)) {
			$this->table_border = $this->toTwips($match[1], 'px');
		}
		
		// 3. BORDER-COLOR
		$this->table_border_color = null;
		if (preg_match("~border-color:\s*(#[0-9a-f]+)~i", $table[1], $match)) {
			$this->table_border_color = $this->colorToIndex($match[1]);
		}
		
		// 4. BACKGROUND-COLOR
		$this->table_bg_color = null;
		if (preg_match("~background-color:\s*(#[0-9a-f]+)~i", $table[1], $match)) {
			$this->table_bg_color = $this->colorToIndex($match[1]);
		}
		
		$table_content .= preg_replace_callback("/<TR([^>]*)>(.*?)<\/TR>/mis", array($this, 'parserTableRowCallback'), $table[2]);
		
		$table_content .= "\\pard ";
		return $table_content;
	}
	
	/**
	 * Callback ��� ������ ����� HTML ������ � ������ RTF �������
	 * $row[1] => ��������� ���� TR
	 * $row[2] => ���������� ���� TR
	 * @param array $row
	 */
	private function parserTableRowCallback($row) {
		/**
		 * ���������� �������� colspan 
		 */
		$this->cell_colspan = 0;
		
		/**
		 * ���������� ��������� �����������
		 */
		// 1. WIDTH
		$tr_width = ($this->table_width === null) ? '' : '\\trftsWidth'.$this->table_width_unit.'\\trwWidth'.$this->table_width;
		
		// 2. BORDER
		$this->row_border = null;
		if (preg_match("~border-width:\s*([0-9]+)~i", $row[1], $match)) {
			$this->row_border = $this->toTwips($match[1], 'px');
		}
		
		// 3. BORDER-COLOR
		$this->row_border_color = null;
		if (preg_match("~border-color:\s*(#[0-9a-f]+)~i", $row[1], $match)) {
			$this->row_border_color = $this->colorToIndex($match[1]);
		}
		
		// 4. BACKGROUND-COLOR
		$this->row_bg_color = null;
		if (preg_match("~background-color:\s*(#[0-9a-f]+)~i", $row[1], $match)) {
			$this->row_bg_color = $this->colorToIndex($match[1]);
		}
		
		$row_content = "\\trowd \\trqc\\trgaph108\\trrh280".$tr_width." ";
		
		$row_content .= preg_replace_callback("/<TD([^>]*)>(.*?)<\/TD>/mis", array($this, 'parserTableCellCallback'), $row[2]);
		
		$row_content .= "\\pard \\intbl \\row ";
		return $row_content;
	}
	
	/**
	 * Callback ��� ������ ����� HTML ������ � ������ RTF �������
	 * $cell[1] => ��������� ���� TD
	 * $cell[2] => ���������� ���� TD
	 * @param array $cell
	 */
	private function parserTableCellCallback($cell) {
		/**
		 * ���������� ��������� �����������
		 */
		// 1. WIDTH
		// BUG: �������� ������ ��������� �������� ������ � ���������. �����, ����� ������������ ������ ��� ���� �����???
		$cell_width = '';
		if (preg_match("~width=[\"']?([0-9]+)".$this->units_regexp."[\"']?~", $cell[1], $match)) {
			$cell_width = '\\clwWidth'.$this->toTwips($match[1], $match[2]).'\\clftsWidth'.$this->getUnits($match[2]);
		}
		
		// 2. BORDER-COLOR (������ �� BORDER)
		if ($this->table_border_color !== null) {
			$cell_border_bottom_color = $cell_border_top_color = $cell_border_left_color = $cell_border_right_color = $this->table_border_color;
		}
		if ($this->row_border_color !== null) {
			$cell_border_bottom_color = $cell_border_top_color = $cell_border_left_color = $cell_border_right_color = $this->row_border_color;
		}
		if (preg_match("~border-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) {
			$cell_border_top_color = $cell_border_bottom_color = $cell_border_left_color = $cell_border_right_color = $this->colorToIndex($match[1]);
		}
		
		// 2.1 SIDE-SPECIFIED BORDERS
		if (preg_match("~border-bottom-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) $cell_border_bottom_color = $this->colorToIndex($match[1]);
		if (preg_match("~border-top-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) $cell_border_top_color = $this->colorToIndex($match[1]);
		if (preg_match("~border-left-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) $cell_border_left_color = $this->colorToIndex($match[1]);
		if (preg_match("~border-right-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) $cell_border_right_color = $this->colorToIndex($match[1]);
		
		// 2.2 FORMAT BORDER COLORS TO RTF FORMAT
		$border_colors = array('bottom'=>'', 'top' => '', 'left' => '', 'right' => '');
		if (isset($cell_border_bottom_color) && $cell_border_bottom_color !== null) $border_colors['bottom'] = "\\brdrcf$cell_border_bottom_color";
		if (isset($cell_border_top_color) && $cell_border_top_color !== null) $border_colors['top'] = "\\brdrcf$cell_border_top_color";
		if (isset($cell_border_left_color) && $cell_border_left_color !== null) $border_colors['left'] = "\\brdrcf$cell_border_left_color";
		if (isset($cell_border_right_color) && $cell_border_right_color !== null) $border_colors['right'] = "\\brdrcf$cell_border_right_color";
		
		// 3. BORDER
		if ($this->table_border !== null) {
			$cell_border_top = $cell_border_bottom = $cell_border_left = $cell_border_right = $this->table_border;
		}
		if ($this->row_border !== null) {
			$cell_border_top = $cell_border_bottom = $cell_border_left = $cell_border_right = $this->row_border;
		}
		if (preg_match("~border-width:\s*([0-9]+)~", $cell[1], $match)) {
			$cell_border_top = $cell_border_bottom = $cell_border_left = $cell_border_right = $this->toTwips($match[1], 'px');
		}
		
		// 3.1 SIDE-SPECIFIED BORDERS
		if (preg_match("~border-bottom-width:\s*([0-9]+)~", $cell[1], $match)) $cell_border_bottom = $this->toTwips($match[1], 'px');
		if (preg_match("~border-top-width:\s*([0-9]+)~", $cell[1], $match)) $cell_border_top = $this->toTwips($match[1], 'px');
		if (preg_match("~border-left-width:\s*([0-9]+)~", $cell[1], $match)) $cell_border_left = $this->toTwips($match[1], 'px');
		if (preg_match("~border-right-width:\s*([0-9]+)~", $cell[1], $match)) $cell_border_right = $this->toTwips($match[1], 'px');
		
		// 3.2 FORMAT BORDERS
		$borders = '';
		if (isset($cell_border_bottom) && $cell_border_bottom > 0) $borders .= "\\clbrdrb\\brdrs$border_colors[bottom]\\brdrw".$cell_border_bottom;
		if (isset($cell_border_top) && $cell_border_top > 0) 	$borders .= "\\clbrdrt\\brdrs$border_colors[top]\\brdrw".$cell_border_top;
		if (isset($cell_border_left) && $cell_border_left > 0) 	$borders .= "\\clbrdrl\\brdrs$border_colors[left]\\brdrw".$cell_border_left;
		if (isset($cell_border_right) && $cell_border_right > 0) 	$borders .= "\\clbrdrr\\brdrs$border_colors[right]\\brdrw".$cell_border_right;
		
		// 4. BACKGROUND-COLOR
		if ($this->table_bg_color !== null) {
			$cell_bg_color = $this->table_bg_color;
		}
		if ($this->row_bg_color !== null) {
			$cell_bg_color = $this->row_bg_color;
		}
		if (preg_match("~background-color:\s*(#[0-9a-f]+)~i", $cell[1], $match)) {
			$cell_bg_color = $this->colorToIndex($match[1]);
		}
		
		if (isset($cell_bg_color)) {
			$cell_bg_color = '\\clcbpat'.$cell_bg_color;
		} else {
			$cell_bg_color = '';
		}
		
		// 5. PADDING
		if (preg_match("~padding:\s*([0-9]+)~", $cell[1], $match)) {
			$cell_padding_bottom = $cell_padding_left = $cell_padding_right = $cell_padding_top = $this->toTwips($match[1], 'px');
		}
		// 5.1 SIDE-SPECIFIED PADDING
		if (preg_match("~padding-bottom:\s*([0-9]+)~i", $cell[1], $match)) $cell_padding_bottom = $this->toTwips($match[1], 'px');
		if (preg_match("~padding-top:\s*([0-9]+)~i", $cell[1], $match)) $cell_padding_top = $this->toTwips($match[1], 'px');
		if (preg_match("~padding-left:\s*([0-9]+)~i", $cell[1], $match)) $cell_padding_left = $this->toTwips($match[1], 'px');
		if (preg_match("~padding-right:\s*([0-9]+)~i", $cell[1], $match)) $cell_padding_right = $this->toTwips($match[1], 'px');
		
		// 5.2 FORMAT PADDING
		/**
		 * \\clpadfb3 - ������ ������� ��������� (3=Twips)
		 */
		$padding = '';
		if (isset($cell_padding_bottom) /*&& $cell_padding_bottom > 0*/) $padding .= "\\clpadb$cell_padding_bottom\\clpadfb3";
		if (isset($cell_padding_top) /*&& $cell_padding_top > 0*/) 	$padding .= "\\clpadt$cell_padding_top\\clpadft3";
		if (isset($cell_padding_left) /*&& $cell_padding_left > 0*/) 	$padding .= "\\clpadl$cell_padding_left\\clpadfl3";
		if (isset($cell_padding_right) /*&& $cell_padding_right > 0*/) 	$padding .= "\\clpadr$cell_padding_right\\clpadfr3";
		
		/**
		 * 5.3 Alignment support in table cells
		 * @since 2007-11-28
		 */
		$horizontal_align = '';
		if (preg_match("~horizontal-align:\s*(left|right|center|justify)~", $cell[1], $match)) {
			switch ($match[1]) {
				case 'left' : $horizontal_align = '\ql'; break;
				case 'right' : $horizontal_align = '\qr'; break;
				case 'center' : $horizontal_align = '\qc'; break;
				case 'justify' : $horizontal_align = '\qj'; break;

			}
		}
		
		$vertical_align = '';
		if (preg_match("~vertical-align:\s*(top|bottom|center|middle)~", $cell[1], $match)) {
			switch ($match[1]) {
				case 'top' : $vertical_align = '\clvertalt'; break;
				case 'bottom' : $vertical_align = '\clvertalb'; break;
				case 'middle' :
				case 'center' : $vertical_align = '\clvertalc'; break;
			}
		}
		
		// 6. COLSPAN support
		/**
		 * � ������� RTF ������ �� ������������ ����� ������� ��� \clmgf, ��� �����������,
		 * ����������� � ����������� - \clmrg
		 */
		if (preg_match("~colspan=[\"']?([0-9]+)~", $cell[1], $match)) {
			$cell_colspan = $match[1]-1;
			$cell_content = $vertical_align.$borders.$cell_width.$cell_bg_color.$padding.'\\clmgf\\cellx\\pard'.$horizontal_align.' \\intbl ';
		} else {
			$cell_colspan = 0;
			$cell_content = $vertical_align.$borders.$cell_width.$cell_bg_color.$padding.'\\cellx\\pard'.$horizontal_align.' \\intbl ';
		}
		
		$cell_content .= $cell[2];
		$cell_content .= "\\cell ";
		
		/**
		 * ���������� ������, ����������� � colspan (������)
		 */
		for ($i=0; $i<$cell_colspan; $i++) {
			$cell_content .= '\\clmrg\\cellx\\pard'.$horizontal_align.' \\intbl \\cell';
		}
		
		return $cell_content;
	}
	
	/**
	 * ������� ������ ��������� HTML � Twips'� (1/20 of point)
	 *
	 * @param int $number
	 * @param string $unit
	 * @return int
	 */
	private function toTwips($number, $unit) {
		switch ($unit) {
			case 'cm':
				return 567*$number;
			case 'px':
			default:
				return 20*$number;
			case '%':
				return 50*$number;
		}
	}
	
	/**
	 * ��������� ��������� HTML (px, %, etc) � ��� ��������� RTF
	 * @param string $unit
	 */
	private function getUnits($unit) {
		if ($unit == '%') return 2;
		if (empty($unit)) return 0;
		return 3;
	}
	
	/**
	 * ������������ 8bit ������� � ������������������ \'hh
	 * @param string $text
	 * @return string
	 */
	private function specialCharacters($text) {
		$text_buffer = "";
		
		for ($i = 0; $i < strlen($text); $i++) {
			$text_buffer .= $this->escapeCharacter($text[$i]);
		}
		
		return $text_buffer;
	}
	
	/**
	 * ���������� ������, �������������� ��� ��������� ������� � RTF � �������� ���������� �������
	 * @param char $character
	 * @return char
	 */
	private function escapeCharacter($character) {
		if (ord($character)==0x0D) return '';
		$escaped = "";
		if(ord($character) >= 0x00 && ord($character) < 0x20)
			$escaped = "\\'".dechex(ord($character));
		
		if ((ord($character) >= 0x20 && ord($character) < 0x80) || ord($character) == 0x09 || ord($character) == 0x0A)
			$escaped = $character;
		
		if (ord($character) >= 0x80 and ord($character) <= 0xFF)
			$escaped = "\\'".dechex(ord($character));

		switch(ord($character)) {
			case 0x5C:
			case 0x7B:
			case 0x7D:
				$escaped = "\\".$character;
				break;
		}
		
		return $escaped;
	}
	
	/**
	 * ��������� ��������� ����� � Font Table ��������� � ���������� ��� ����� � ���� �������
	 * @param string $font
	 * @return int
	 */
	private function fontToIndex($font) {
		$font = strtolower(trim($font));
		$key = array_search($font, $this->fonts);
		if ($key !== false) {
			return $key;
		}
		
		/**
		 * ����� �� ���������� � Font Table, ����� ��� ��������
		 */
		$this->max_font_index++;
		$this->fonts[$this->max_font_index] = $font;
		
		$this->rtf = preg_replace("~\\\\fonttbl\s*\{~i", "\\fonttbl {\\f".$this->max_font_index."\\f".$this->fontToFamily($font)."\\fcharset204\\fprq2 $font;} {", $this->rtf);
		
		return $this->max_font_index;
	}
	
	/**
	 * ����������� ������ ������ �� ��� �����. ���� ������ ������ - ������������ ��� ������������
	 * ������� ������� � ��������� RTF ���������, ������� ����� �� �����...
	 * @param string $font
	 */
	private function fontToFamily($font) {
		if (in_array($font, array('times new roman', 'palatino'))) {
			return 'roman';
		} elseif (in_array($font, array('arial', 'verdana', 'tahoma'))) {
			return 'swiss';
		} elseif (in_array($font, array('courier', 'courier new', 'pica'))) {
			return 'modern';
		} else {
			return 'nil';
		}
	}
	
	/**
	 * ��������� ����, �������� � ������� #FFFFFF � ������� ������ ��������� � ���������� ����� ����������� �����
	 * @param string $color
	 * @return int
	 */
	private function colorToIndex($color) {
		$color = strtoupper($color);
		$key = array_search($color, $this->colors);
		if ($key !== false) {
			return $key+1;
		}
		
		/**
		 * ���� ��� �� ���������� � color table, ���������� ��� ��������
		 */
		$this->colors[] = $color;
		
		$rgb_color = $this->colorHtmlToRgb($color);
		$color_table_update = "\\red$rgb_color[0]\\green$rgb_color[1]\\blue$rgb_color[2];";
		
		/**
		 * ������������ Color Table ���������
		 */
		$this->rtf = preg_replace("~\{\s*\\\\colortbl\;([^\}]+)\}~im", "{\\colortbl;\\1$color_table_update}", $this->rtf);
		
		/**
		 * ���������� ������ ���������� �����
		 */
		return count($this->colors);
	}
	
	/**
	 * �������������� ����� RGB -> HTML ( 0, 0, 255 => #0000FF  )
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @return string
	 */
	private function colorRgbToHtml($red, $green, $blue) {
		return strtoupper('#'.str_pad(dechex($red), 2, '0', STR_PAD_LEFT).str_pad(dechex($green), 2, '0', STR_PAD_LEFT).str_pad(dechex($blue), 2, '0', STR_PAD_LEFT));
	}
	
	/**
	 * �������������� HTML ����� � ������������ RGB
	 * ���������� ������ Array(red, green, blue)
	 * @param string $html_color
	 * @return array
	 */
	private function colorHtmlToRgb($html_color) {
		if (substr($html_color, 0, 1) == '#') {
			$html_color = substr($html_color, 1);
		}
		
		$return[0] = hexdec(substr($html_color, 0, 2));
		$return[1] = hexdec(substr($html_color, 2, 2));
		$return[2] = hexdec(substr($html_color, 4, 2));
		return $return;
	}
}

?>