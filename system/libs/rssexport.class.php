<?php
/** 
 * ����� ��� �������� ������ � ������ RSS 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

class RssExport {
	
	const RSS_2_0 = 1;
	
	/**
	 * ������ ������, ������� �������� �������
	 * @var array
	 */
	private $export_data = array();
	
	/**
	 * ���������� � ������
	 * @var array
	 */
	private $channel_info = array();
	
	/**
	 * ������ ������ ��� �������� ����� ��������. ������ ������� ������� ����� ��������� ��������� ����:
	 * title, description, link, publication_date, category, enclosure
	 * @param array $data
	 */
	public function setData($data) {
		reset($data); 
		while (list(,$row) = each($data)) { 
			@$this->addItem($row['title'], $row['description'], $row['link'], $row['publication_date'], $row['category'], $row['enclosure']); 
		}
	}
	
	/**
	 * �������� �������������� ������� (�������)
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $link
	 * @param string $publication_date
	 * @param string $category
	 * @param string $enclosure
	 */
	public function addItem($title, $description, $link, $publication_date = null, $category = null, $enclosure = null) {
		$this->export_data[] = array(
			'title' => $title,
			'description' => $description,
			'link' => $link,
			'pubdate' => $publication_date,
			'category' => $category,
			'enclosure' => $enclosure
		);
	}
	
	/**
	 * ������ ���������� � ������ RSS
	 *
	 * @param string $title
	 * @param string $link
	 * @param string $description
	 * @param string $publication_date
	 * @param string $category
	 * @param string $language
	 * @param string $image_url
	 */
	public function setChannelInfo($title, $description, $link, $publication_date = null, $category = null, $language = null, $image_url = null, $image_title = null, $image_link = null) {
		$this->channel_info = array(
			'title' => $title,
			'description' => $description,
			'link' => $link,
			'pubdate' => $publication_date,
			'category' => $category,
			'language' => $language,
			'image_url' => $image_url,
			'image_title' => $image_title,
			'image_link' => $image_link
		);
	}
	
	/**
	 * ������� ������ � ������ RSS
	 * @param int $rss_version
	 * @return string
	 */
	public function export($rss_version = RssExport::RSS_2_0) {
		
		if (count($this->channel_info) == 0) {
			trigger_error("���������� ������ ���������� � ������ ����� ���������", E_USER_WARNING);
			return;
		}
		
		switch ($rss_version) {
			case self::RSS_2_0:
			default: 
				return $this->export_rss_2_0();
		}
	}
	
	/**
	 * ���������� RSS Content-Type � ����������� �� ��������
	 * IE6 �� ����� rss ������ � �������� ��� ��������� application/rss+xml, 
	 * ������� ������ ��� text/xml
	 *
	 * @param string $user_agent
	 * @return string
	 */
	static public function getContentTypeFor($user_agent) {
		if (preg_match('~MSIE 6~', $user_agent) && !preg_match('~MSIE 7~', $user_agent)) {
			// IE7 ���� �������� � UA ������ MSIE 6, ���������� ���
			return 'text/xml';
		} elseif (preg_match('~Firefox/1\.~', $user_agent)) {
			// Firefox 1.x �� ����� ������, ���������� ��������� rss+xml
			return 'text/xml';
		} else {
			return 'application/rss+xml';
		}
	}
	
	/**
	 * ���������� ������� � ������� RSS 2.0
	 */
	private function export_rss_2_0() {
		/**
		 * ������� ���������� � ������
		 */
		$export = '<?xml version="1.0" encoding="windows-1251"?><rss version="2.0"><channel>';
		$export .= '<generator>Pilot CMS 6.0</generator>';
		$export .= '<title>'.$this->text($this->channel_info['title']).'</title>';
		$export .= '<description>'.$this->text($this->channel_info['description']).'</description>';
		$export .= '<link>'.$this->text($this->channel_info['link']).'</link>';
		$export .= '<pubDate>'.$this->text($this->channel_info['pubdate']).'</pubDate>';
		$export .= '<lastBuildDate>'.date('r').'</lastBuildDate>';
		if (!empty($this->channel_info['image_url'])) {
			$export .= '<image>'.'<url>'.$this->text($this->channel_info['image_url']).'</url>'.'<title>'.$this->text($this->channel_info['image_title']).'</title>'.'<link>'.$this->text($this->channel_info['image_link']).'</link>'.'</image>';
		}
		
		/**
		 * ������� ������
		 */
		reset($this->export_data); 
		while (list(,$row) = each($this->export_data)) { 
			$export .= "<item>\n"; 
			$export .= "<title>".$this->text($row['title'])."</title>\n"; 
			$export .= "<link>".$this->text($row['link'])."</link>\n"; 
			$export .= "<description><![CDATA[".$row['description']."]]></description>\n"; 
			//$export .= "<description><![CDATA[".$this->text($row['description'])."]]></description>\n"; 
			$export .= "<category>".$this->text($row['category'])."</category>\n"; 
			$export .= "<pubDate>".$this->text($row['pubdate'])."</pubDate>\n"; 
			if (!empty($row['enclosure'])) {
				$export .= "<enclosure url=\"".$this->text($row['enclosure'])."\"></enclosure>\n"; 
			}
			$export .= "</item>\n"; 
		}
		
		$export .= '</channel></rss>';
		return $export;
	}
	
	/**
	 * ����������� ����� ��� ����������� ������ XML
	 * @param string $text
	 */
	private function text($text) {
		return htmlspecialchars($text, ENT_QUOTES);
	}
}

?>