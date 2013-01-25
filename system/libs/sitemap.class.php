<?php
/**
 * Класс для построения Sitemaps
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
class Sitemap {
	
	protected $urls = array();
	
	protected $sitemaps = array();
	
	protected $sitemap_count = 0;
	
	protected $content = '';
	
	/**
	 * Список файлов, которые созданы текущим экземпляром sitemap
	 * @var array
	 */
	protected $generated_files = array();
	
	protected $target_directory = '';
	
	protected $file_name = '';
	
	protected $host = '';
	
	protected $gzip = false;
	
	protected $max_file_urls = 9900;
	
	protected $max_file_size = 9000000;
	
	protected $urlset_params = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"';
	protected $index_urlset_params = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"';
	
	public function __construct() {}
	
	/**
	 * Добавление URL в Sitemap
	 *
	 * @param string $url
	 * @param string $last_modified
	 * @param string $change_frequency
	 * @param float $priority
	 */
	public function addUrl($url, $last_modified = null, $change_frequency = null, $priority = null) {
		
		// дата должна быть в формате 2008-01-01T10:10:10-02:00
		if (!empty($last_modified)) {
			$last_modified = str_replace(' ', 'T', $last_modified).'-02:00';
		}
		
		$this->urls[] = array(
			'url' => $url,
			'last_modified' => $last_modified,
			'change_frequency' => $change_frequency,
			'priority' => $priority
		);
	}
	
	/**
	 * Строит Sitemap на основании добавленных URL
	 *
	 * @param string $target_directiry
	 * @param string $file_name
	 * @param string $host Пример: http://host.com/sitemaps/
	 * @param boolean $gzip
	 */
	public function build($target_directory, $file_name, $host, $gzip) {
		
		if (!preg_match('~/$~', $target_directory)) {
			$target_directiry .= '/';
		}
		
		$this->target_directory = $target_directory;
		$this->file_name = $file_name;
		$this->host = $host;
		$this->gzip = $gzip;
		
		$this->newSitemap();
		
		$counter = 0;
		reset($this->urls);
		while (list(,$row) = each($this->urls)) {
			
			if ($counter >= $this->max_file_urls || strlen($this->content) >= $this->max_file_size) {
				$this->saveSitemap(true);
				$this->newSitemap();
				$counter = 0;
			}
			
			$this->content .= $this->buildUrl($row);
			
			$counter++;
		}
		
		$this->saveSitemap();
		$this->saveIndex();		
	}
	
	/**
	 * Строит строку для отображения одной страницы
	 *
	 * @param array $url
	 * @return string
	 */
	protected function buildUrl($url) {
		$content = '';
		$content .= "<url>\n";
		$content .= $this->addTag('loc', $url['url']);
		$content .= $this->addTag('lastmod', $url['last_modified']);
		$content .= $this->addTag('changefreq', $url['change_frequency']);
		$content .= $this->addTag('priority', $url['priority']);
		$content .= "</url>\n";
		return $content;
	}
	
	/**
	 * Escape данных для XML
	 *
	 * @param string $string
	 * @return string
	 */
	protected function escape($string) {
		return htmlspecialchars($string, ENT_QUOTES);
	}
	
	/**
	 * Инициализация новго файла Sitemap
	 *
	 */
	protected function newSitemap() {
		$this->sitemap_count++;
		$this->content = '<?xml version="1.0" encoding="UTF-8"?><urlset '.$this->urlset_params.'>'."\n";
	}
	
	/**
	 * Добавление опционального тега
	 *
	 * @param string $tag
	 * @param string $value
	 * @return string
	 */
	protected function addTag($tag, $value) {
		if (empty($value)) {
			return '';
		} else {
			return "<$tag>{$this->escape($value)}</$tag>\n";
		}
	}
	
	/**
	 * Сохранение файла Sitemap
	 *
	 * @param boolean $force_index
	 */
	protected function saveSitemap($force_index = false) {
		if ($force_index || $this->sitemap_count > 1) {
			$filename = $this->getFilename($this->file_name, $this->sitemap_count);
		} else {
			$filename = $this->file_name;
		}
		
		if (!is_dir($this->target_directory)) {
			mkdir($this->target_directory, 0755, true);
		}
		
		file_put_contents($this->target_directory.$filename, iconv(CMS_CHARSET, 'UTF-8', $this->content."</urlset>"));
		
		if ($this->gzip) {
			exec("gzip --force ".$this->target_directory.$filename);
			$this->sitemaps[] = $filename.'.gz';
			$this->generated_files[] = $filename.'.gz';
		} else {
			$this->sitemaps[] = $filename;
			$this->generated_files[] = $filename;
		}
	}
	
	/**
	 * Сохранение индексного файла
	 * 
	 */
	protected function saveIndex() {
		if (count($this->sitemaps) > 1) {
			$content = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex '.$this->index_urlset_params.'>'."\n";
			
			reset($this->sitemaps);
			while (list(,$row) = each($this->sitemaps)) {
				$content .= '<sitemap>'."\n";
				$content .= '	<loc>'.$this->host.$row.'</loc>'."\n";
				$content .= '	<lastmod>'.date('Y-m-d').'</lastmod>'."\n";
				$content .= '</sitemap>'."\n";
			}
			
			$content .= '</sitemapindex>';
			
			file_put_contents($this->target_directory.$this->file_name, iconv(CMS_CHARSET, 'UTF-8', $content));
			$this->generated_files[] = $this->file_name;
		}
	}
	
	/**
	 * Возвращает имя файла для сохранения текущего сайтмапа
	 *
	 * @param string $filename
	 * @param int $number
	 * @return string
	 */
	protected function getFilename($filename, $number) {
		if (strpos($filename, '.') === false) {
			return $filename.'-'.$index;
		} else {
			return preg_replace('~\.([^\.]+)$~', "-$number.\\1", $filename);
		}
	}
	
	/**
	 * Возвращает список созданных файлов Sitemap
	 * @return array
	 */
	public function getSitemaps() {
		return $this->generated_files;
	}
}

?>