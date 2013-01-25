<?php
/**
 * Класс для построения Sitemap новостей
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
class SitemapNews extends Sitemap {
	
	protected $max_file_urls = 900;
	protected $urlset_params = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"';
	
	/**
	 * Добавление новости в Sitemap
	 *
	 * @param string $url
	 * @param string $publication_date
	 * @param string $keywords
	 */
	public function addUrl($url, $publication_date = null, $keywords = null, $stock_tickers = null) {
		
		if (!empty($publication_date)) {
			$publication_date = str_replace(' ', 'T', $publication_date).'-02:00';
		}
		
		$this->urls[] = array(
			'url' => $url,
			'publication_date' => $publication_date,
			'keywords' => $keywords,
			'stock_tickers' => $stock_tickers
		);
	}
	
	/**
	 * Построение строки, отображающее URL
	 *
	 * @param array $url
	 * @return string
	 */
	protected function buildUrl($url) {
		$content = '';
		$content .= "<url>\n";
		$content .= $this->addTag('loc', $url['url']);
		$content .= '	<news:news>'."\n";
		$content .= $this->addTag('news:publication_date', $url['publication_date']);
		$content .= $this->addTag('news:keywords', $url['keywords']);
		$content .= $this->addTag('news:stock_tickers', $url['stock_tickers']);
		$content .= '	</news:news>'."\n";
		$content .= "</url>\n";
		return $content;
	}
}

?>