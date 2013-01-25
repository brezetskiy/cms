<?php
/**
 * Чтение новостей в формате RSS
 * @package Pilot
 * @subpackage CMS
 * @version 3.0
 * @author Andrey Tkachenko <andrey@tkachenko.kiev.ua>
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, 2006
 */

/**
 * Чтение новостей в формате RSS
 */
class ParseRSS {
	
    /**
     * Масив channel содержит данные о канале (название. описание, урл, посл. обновление, лого и.т.д.)
     * @var array
     */
    private $channel_info = array();
    
    /**
     * Версия и тип потока новостей
     * @var string
     */    
    private $feedver = '';
    
    /**
     * Масив news содержит новости с RSS потока
     * @var array
     */
    private $news = array();
    
    /**
     * Класс SimpleXML
     * @var object
     */
    private $SimpleXML;
	
 	/**
     * переменная содержимое XML файла
     * @var string
     */
    private $xmlcontent = '';
    
    /**
     * Конструктор
     * загружает источник новостей (по URL) переменная  $source_url;
     * 
     * @param string $xmlcontent
     * @return object
     */
	public function __construct($xmlcontent) {
		
		$this->xmlcontent = $xmlcontent;
		
		// Определяем версию RSS
		$this->feedver = $this->detectFeedVer();
		
		if (extension_loaded('simlexml')) {
			trigger_error('PHP must be compiled with SimpleXML support', E_USER_ERROR);
		}
		
		// Создаём класс SimpleXML
		$this->SimpleXML = simplexml_load_string($this->xmlcontent);
			
		// Разбор RSS ленты новостей
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
				echo "<b>Внимание!</b> Обнаружена ошибка при обработке канала!<br> версия канала: $this->feedver <br> полченые данные:  $xmlcontent";	
			
		}
		
		array_walk_recursive($this->channel_info, array(&$this, 'utf2default'));
		array_walk_recursive($this->news, array(&$this, 'utf2default'));
	}
	
	/**
	 * Возвращает массив с новостями
	 *
	 * @return array
	 */
	public function getNews() {
		return $this->news;
	}
	
	/**
	 * Возвращает массив с информацией о канале
	 *
	 * @return array
	 */
	public function getChannelInfo() {
		return $this->channel_info;
	}
		
	/**
	 * Определение типа RSS потока 
	 * 0.91; 1.0; 2.0; Atmon; livejournal; 
	 * 
	 * Определям по:
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
    		$feedver = "Не опознана!";
    	}
    	
    	return $feedver;
    }
    	
   	
	/**
	 * Получение информации о канале
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
	 * Получение информации о канале
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
	 * Получение информации о канале
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
	 * Обработка данных для формата RSS 2.0, и RSS 0.91
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
	 * Обработка данных для формата RSS 1.0
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
	 * Обработка данных для формата Atom
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
     * Преобразование текста из UTF в кодироку настроенную для выдачи
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