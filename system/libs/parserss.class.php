<?php
/**
 * ������ �������� � ������� RSS
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Andrey Tkachenko <andrey@tkachenko.kiev.ua>
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, 2006
 */

/**
 * ������ �������� � ������� RSS
 */
class ParseRSS {
	
    /**
     * ����� channel �������� ������ � ������ (��������. ��������, ���, ����. ����������, ���� �.�.�.)
     * @var array
     */
    private $channel_info = array();
    
    /**
     * ������ � ��� ������ ��������
     * @var string
     */    
    private $feedver = '';
    
    /**
     * ����� news �������� ������� � RSS ������
     * @var array
     */
    private $news = array();
    
    /**
     * ����� SimpleXML
     * @var object
     */
    private $SimpleXML;
	
 	/**
     * ���������� ���������� XML �����
     * @var string
     */
    private $xmlcontent = '';
    
    /**
     * �����������
     * ��������� �������� �������� (�� URL) ����������  $source_url;
     * 
     * @param string $xmlcontent
     * @return object
     */
	public function __construct($xmlcontent) {
		
		$this->xmlcontent = $xmlcontent;
		
		// ���������� ������ RSS
		$this->feedver = $this->detectFeedVer();
		
		if (extension_loaded('simlexml')) {
			trigger_error('PHP must be compiled with SimpleXML support', E_USER_ERROR);
		}
		
		// ������ ����� SimpleXML
		$this->SimpleXML = simplexml_load_string($this->xmlcontent);
			
		// ������ RSS ����� ��������
		switch ($this->feedver) {
			case 'rss_2_0':
				$this->channel_info = $this->getChannelInfoRSS2();
				$this->news = $this->parseRSS2();
			break;
			case 'rss_2':
				$this->channel_info = $this->getChannelInfoRSS2();
				$this->news = $this->parseRSS2();
			break;
			case 'rss_0_91':
				$this->channel_info = $this->getChannelInfoRSS2();
				$this->news = $this->parseRSS2();
			break;
			case 'atom':
				$this->channel_info = $this->getChannelInfoAtom();
				$this->news = $this->parseAtom();
			break;
			case 'rss_1':
				$this->channel_info = $this-> getChannelInfoRSS1_0();
				$this->news = $this-> parseRSS1_0();
			break;
			default:
				echo "<b>��������!</b> ���������� ������ ��� ��������� ������!<br> ������ ������: $this->feedver <br> �������� ������:  $xmlcontent";	
			
		}
		
		array_walk_recursive($this->channel_info, array(&$this, 'utf2default'));
		array_walk_recursive($this->news, array(&$this, 'utf2default'));
	}
	
	/**
	 * ���������� ������ � ���������
	 *
	 * @return array
	 */
	public function getNews() {
		return $this->news;
	}
	
	/**
	 * ���������� ������ � ����������� � ������
	 *
	 * @return array
	 */
	public function getChannelInfo() {
		return $this->channel_info;
	}
		
	/**
	 * ����������� ���� RSS ������ 
	 * 0.91; 1.0; 2.0; Atmon; livejournal; 
	 * 
	 * ��������� ��:
	 * 0.91 - <rss version="0.91">
	 * 2.0 	<rss version="2.0" 
	 * 1.0 (and livejournal) -  <rdf:RDF 
	 * Atom - <feed version="0.3"
	 */
	
	private function detectFeedVer() {
    	if (preg_match("/<rss[^>]+version=[\"\']([0-9\.]+)[\"\'][^>]*>/ismU", $this->xmlcontent, $matches)){
    		$feedver = 'rss_'.str_replace('.', '_', $matches[1]);
    	} elseif(strpos($this->xmlcontent, 'rdf:RDF')) {
    		$feedver = 'rss_1';
    	} elseif(strpos($this->xmlcontent, "<feed version")) {
    		$feedver = 'atom';
    	} else {
    		$feedver = "�� ��������!";
    	}
    	
    	return $feedver;
    }
    	
   	
	/**
	 * ��������� ���������� � ������
	 * 
	 * @param void
	 * @return array
	 */
	private function getChannelInfoRSS2() {
		$channel = array(
			'title' => (string)$this->SimpleXML->channel->title,
			'link' => (string)$this->SimpleXML->channel->link,
			'description' => (string)$this->SimpleXML->channel->description,
			'image' => (string)$this->SimpleXML->channel->image->url,
			'language' => (string)$this->SimpleXML->channel->language
		);
		
		return $channel;	
	}
	
	/**
	 * ��������� ���������� � ������
	 * 
	 * @param void
	 * @return array
	 */
	private function getChannelInfoRSS1_0() {
		$channel = array(
			'title' => (string)$this->SimpleXML->channel->title,
			'link' => (string)$this->SimpleXML->channel->link,
			'description' => (string)$this->SimpleXML->channel->description,
			'image' => (string)$this->SimpleXML->channel->image->url,
			'language' => (string)$this->SimpleXML->channel->language
		);
		
		return $channel;	
	}
	
	/**
	 * ��������� ���������� � ������
	 * 
	 * @param void
	 * @return array
	 */
	private function getChannelInfoAtom() {
		$channel = array(
			'title' => (string)$this->SimpleXML->title,
			'link' => (string)$this->SimpleXML->link,
			'autor' => (string)$this->SimpleXML->author->name,
			'description' => (string)$this->SimpleXML->author->email,
			'language' => (string)$this->SimpleXML->language
		);
		
		return $channel;	
	}
    	
	/**
	 * ��������� ������ ��� ������� RSS 2.0, � RSS 0.91
	 * 
	 * @param void
	 * @return array
	 */	
    private function parseRSS2() {
    	$news = array();
		foreach ($this->SimpleXML->channel->item as $newsitem) {
			
	        $news[] = array(
		        'title' => (string)$newsitem->title, 
		        'desc' => (string)$newsitem->description,
		        'link' => (string)$newsitem->link,
		        'autor' => (string)$newsitem->author,
		        'pubDate' => (string)$newsitem->pubDate,
		        'fulltext' => (string)$newsitem->fulltext,
		        'category' => (string)$newsitem->category,
		        'enclosure' => @(string)$newsitem->enclosure->attributes()->url
        	);
		}
		return $news; 
    }
    
    
    /**
	 * ��������� ������ ��� ������� RSS 1.0
	 * 
	 * @param void
	 * @return array
	 */	
    private function parseRSS1_0() {
    	$news = array();
    	
		foreach ($this->SimpleXML->item as $newsitem) {
	        $news[] = array(
		        'title' => (string)$newsitem->title, 
		        'desc' => (string)$newsitem->description,
		        'link' => (string)$newsitem->link,
		        'autor' => (string) $newsitem->dc,
		        'pubDate' => (string)$newsitem->pubDate,
		        'fulltext' => (string)$newsitem->fulltext,
		        'category' => (string)$newsitem->category,
		        'enclosure' => (string)$newsitem->enclosure
        	);
		}
		return $news; 
    }
    
    
    /**
	 * ��������� ������ ��� ������� Atom
	 * 
	 * @param void
	 * @return array
	 */	
    private function parseAtom() {
    	$news = array();
    	
		foreach ($this->SimpleXML->entry as $newsitem) {
	        $news[] = array(
		        'title' => (string)$newsitem->title, 
		        'desc' => (string)$newsitem->content,
		        'link' => (string)$newsitem->link->attributes()->href,
		        'autor' => (string) $newsitem->dc,
		        'pubDate' => (string)$newsitem->pubDate,
		        'fulltext' => (string)$newsitem->content,
		        'category' => (string)$newsitem->summary,
		        'enclosure' => (string)$newsitem->enclosure
        	);
		}
		return $news; 
    }
    
        
    /**
     * �������������� ������ �� UTF � �������� ����������� ��� ������
     * @param mixed
     * @return void
     */
    private function utf2default(&$item) {
    	if (!is_string($item)) {
    		return;
    	}
    	
    	$item = iconv('UTF-8', CMS_CHARSET.'//IGNORE', $item);
    } 
	
}
?>