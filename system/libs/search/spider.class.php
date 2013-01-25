<?php 
/**
* ���������� ������� �����
* @package Pilot
* @subpackage Search
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Copyright 2004, Delta-X ltd.
*/

/**
* ����� ���������� ������� �����
* @package Search
* @subpackage Libraries
*/
class SearchSpider {
	
	private $request_headers = array(
		'Accept' => 'text/html, text/plain, application/msword, application/vnd.ms-excel, application/x-msexcel',
		'Accept-Charset' => 'windows-1251, *;q=0.1',
		'Accept-Language' => 'ru;q=1.0, uk;q=0.9, en;q=0.8',
		'Cache-control' => 'no-cache'
	);
	
	/**
	 * ���������, ������� ������ �������������� � �������.
	 * @var array
	 */
	private $ignore_link_protocol = array('https?','ftps?','mailto','javascript','mms');
	
	/**
	 * ���������� � �����
	 * @var array
	 */
	private $site = array();
	
	/**
	 * ��������, ������� ��������� �����������
	 * ������������, ��� �������� ������� �� �������, ����� SQL ������
	 * @var array
	 */
	private $disallow = array();
	
	/**
	 * �����, ������� � �������� ��������� ������.
	 * ������ ��������� �� ���� ���� ���������� ��������.
	 * @var int
	 */
	private $update_time = 0;
	
	/**
	 * id ��������, ������� � ������ ������ ������������ ����
	 * @var int
	 */
	private $page_id = 0;
	
	/**
	 * URL ��������
	 * @var string
	 */
	private $page_url = '';
	
	/**
	 * ������� ����������� ������� ��������
	 * @var int
	 */
	private $depth = 0;
	
	/**
	 * ���������, ������� �� �������� � ����� �� ������, ������������
	 * @var array
	 */
	private $response_headers = array();
	
	/**
	 * ������ Download
	 * @var object
	 */
	private $Download;
	
	
	/**
	* ����������� ������
	* @param string $url
	* @param int $update_time
	* @return object
	*/
	public function __construct($url, $update_time) {
		global $DB;
		$this->update_time = $update_time;
		$this->Download = new Download();
		$this->Download->addHeaders($this->request_headers);
		
		// ��������� URL
		if (!preg_match(VALID_URL, $url)) {
			echo '[e] Check URL spelling '.$url."\n";
		}
		$url = parse_url($url);
		
		/**
		 * ��������� ������������ �� ��������� ���� ��������
		 */
		if (!preg_match("/^https?/i", $url['scheme'])) {
			$url = $url['scheme'].'://'.$url['host'].'/';
			echo '[e] Protocol not supported by spider '.$url."\n";
			exit;
		}
		
		// ��������� URL �����
		$url = $url['scheme'].'://'.$url['host'].'/';
		
		// ���� ����� �� ���������� � ��, �� ��������� ���
		$query = "INSERT IGNORE INTO search_site (url) VALUES ('".mysqli_real_escape_string($DB->link, $url)."')";
		$DB->insert($query);
		
		$query = "SELECT id, url FROM search_site WHERE url='$url'";
		$this->site = $DB->query_row($query);
		
		// ��������� � ������� ����������� ������ �� �������� ��������
		$query = "INSERT IGNORE INTO search_page (site_id, url, tstamp) VALUES ('".$this->site['id']."', '', 0)";
		$DB->insert($query);
		
		// ����������� ���� /robots.txt
		$this->updateRobots();

		// ��������� ������ ��� �����
		echo "[i] Start indexing\n";
		for ($i = 0; $i <= SEARCH_MAX_DEPTH; $i++) {
			$this->start();
		}

		// ������� ��������, �� ������� �� ����� ������
		$this->optimize();
		
		echo "[i] Done: ".$this->site['url']."\n";
	}
	
	/**
	* ��������� ����������, ������� ����������� ��� ��������, ������� ������������������
	* @access private
	* @param void
	* @return void
	*/
	private function start() {
		global $DB;
		
		// ������� �� ������� ��������, ����������� robots.txt
		echo "[i] Delete disallow page\n";
		reset($this->disallow);
		while (list(, $url) = each($this->disallow)) {
			$query = "DELETE FROM search_page WHERE site_id='".$this->site['id']."' AND url like '".mysqli_real_escape_string($DB->link, $url)."'";
			$DB->delete($query);
		}
		
		
		/**
		 * ���������� ��������, ���������� �� ������� ���� ��������
		 */
		$query = "
			SELECT
				tb_page.id,
				tb_page.depth,
				CONCAT(tb_site.url, tb_page.url) AS url
			FROM search_page AS tb_page
			INNER JOIN search_site AS tb_site ON tb_page.site_id= tb_site.id
			WHERE 
				UNIX_TIMESTAMP(tb_page.tstamp) < ".$this->update_time."
				AND tb_page.site_id='".$this->site['id']."'
			ORDER BY tb_page.tstamp ASC
		";
		$pages = $DB->query($query);
		if ($DB->rows == 0) return false;
		reset($pages);
		while (list(, $page) = each($pages)) {
			
			// ������������� ��������� ��������, ������� ��������������
			$this->page_id 	= $page['id'];
			$this->page_url	= $page['url'];
			$this->depth 	= $page['depth'];
			
			// ���������� ������� ����������� ������ ��������
			echo '[i] '.$this->page_id."\t".$this->page_url."\t ... ";
			if ($this->depth > SEARCH_MAX_DEPTH) {
				echo "skipped max depth\n";
				continue;
			}
			
			// ��������� �������
			$content = $this->Download->get($page['url']);
			$this->response_headers = $this->Download->getResponseHeaders();
			if (empty($this->response_headers)) {
				// �������� �� �������, ������� � �� �������
				$query = "DELETE FROM search_page WHERE id='".$this->page_id."'";
				$DB->delete($query);
				
				$query = "DELETE FROM search_referer WHERE page_id='".$this->page_id."'";
				$DB->delete($query);
				
				echo "404 Not Found \n";
				continue;
			}
			
			// ������������ 404 ������
			
			// ����������� ����� �� ����������
			if ($this->response_headers['content-type'] == 'application/msword') {
				$content = $this->parseMSWord($content);
			} elseif (in_array($this->response_headers['content-type'], array('application/vnd.ms-excel', 'application/x-msexcel'))) {
				$content = $this->parseExcel($content);
			}
			
			// ������������ ��������
			$content = $this->parse($content);
			
			// ��������� ������
			$query = "
				UPDATE search_page
				SET 
					depth='".$this->depth."',
					title='".mysqli_real_escape_string($DB->link, substr($content['title'],0,255))."',
					keywords='".mysqli_real_escape_string($DB->link, substr($content['keywords'],0,255))."',
					description='".mysqli_real_escape_string($DB->link, substr($content['description'],0,255))."',
					h1='".mysqli_real_escape_string($DB->link, substr($content['h1'],0,255))."',
					h2='".mysqli_real_escape_string($DB->link, substr($content['h2'],0,255))."',
					h3='".mysqli_real_escape_string($DB->link, substr($content['h3'],0,255))."',
					content='".mysqli_real_escape_string($DB->link, substr($content['content'],0,65000))."'
				WHERE id='".$this->page_id."'
			";
			$DB->update($query);
			
			echo "ok\n";

		}
	}
	
	/**
	* ������ ����� MS Word, ��� ����� ���������� ������������� ���������
	* catdoc http://www.45.free.net/~vitus/ice/catdoc/index.html
	* @access private
	* @param string $content
	* @return string
	*/
	private function parseMSWord($content) {
		$error = '';
		$content = Shell::exec_stdin("/usr/local/bin/catdoc -d".CMS_CHARSET." -w -", $content, $error);
		echo "\n[w]$this->page_url\n[w] $error";
		return $content;
	}
	
	/**
	* ������ ����� MS Excel, ��� ����� ���������� ������������� ���������
	* catdoc(xls2csv) http://www.45.free.net/~vitus/ice/catdoc/index.html
	* @param string $content
	* @return string
	*/
	private function parseExcel($content) {
		$tmp_filename = TMP_ROOT . crc32($content);
		
		$fp = fopen($tmp_filename, 'wb');
		fwrite($fp, $content);
		fclose($fp);
		
		$charset = CMS_CHARSET;
		$content = `/usr/local/bin/xls2csv -d$charset $tmp_filename`;
		unset($charset);
			
		unlink($tmp_filename);
		
		return $content;
	}
	
	/**
	* ������, ������� ��������� ������ ��� ��������
	* @param string $content
	* @return array
	*/
	private function parse($content) {
		
		$content = substr($content, 0, 65536);

		$return = array(
			'title' => '',
			'keywords' => '',
			'description' => '',
			'h1' => '',
			'h2' => '',
			'h3' => '',
			'content' => '',
		);
		
		// ���������� TITLE ��������
		preg_match("/<title>(.*)<\/title>/ismU", $content, $matches);
		if (isset($matches[1])) {
			$return['title'] = $matches[1];
		}
		
		// ���������� �������� �����
		preg_match("/<meta[^>]+name=[\"']?keywords[\"']?[^>]+content=([\"']?)(.*)\\1[^>]*>/ismU", $content, $matches);
		if (empty($matches)) preg_match("/<meta[^>]+content=([\"']?)(.*)\\1[^>]+name=[\"']?keywords[\"']?[^>]*>/ismU", $content, $matches);
		if (isset($matches[2])) {
			$return['keywords'] = strip_tags($matches[2]);
		}
		
		// ���������� ��������
		preg_match("/<meta[^>]+name=[\"']?description[\"']?[^>]+content=([\"']?)(.*)\\1[^>]*>/ismU", $content, $matches);
		if (empty($matches)) preg_match("/<meta[^>]+content=([\"']?)(.*)\\1[^>]+name=[\"']?description[\"']?[^>]*>/ismU", $content, $matches);
		if (isset($matches[2])) {
			$return['description'] = strip_tags($matches[2]);
		}
		
		// ���������� ��������� H1
		preg_match_all("/<h1>([^<]+)<\/h1>/ismU", $content, $matches);
		if (!empty($matches[1])) {
			$return['h1'] = implode("\n", $matches[1]);
		}
		
		
		// ���������� ��������� H2
		preg_match_all("/<h2>([^<]+)<\/h2>/ismU", $content, $matches);
		if (!empty($matches[1])) {
			$return['h2'] = implode("\n", $matches[1]);
		}
		
		// ���������� ��������� H3
		preg_match_all("/<h3>([^<]+)<\/h3>/ismU", $content, $matches);
		if (!empty($matches[1])) {
			$return['h3'] = implode("\n", $matches[1]);
		}
		
		// ������� ����������� JS � STYLE
		$content = preg_replace("/<style[^>]*>.*<\/style>/ismU", " ", $content);
		$content = preg_replace("/<script[^>]*>.*<\/script>/ismU", " ", $content);
		$content = preg_replace("/<noscript[^>]*>.*<\/noscript>/ismU", " ", $content);
		
		/**
		 * ���� ������� ����������� ������ �������� - �� �������� ������������, ��
		 * ������� ��� ������ � ���������� �� � ������, ����� ��������� ��, ���
		 * ������ ��������� ������ �� HTML ���������
		 */
		if ($this->depth < SEARCH_MAX_DEPTH) {
			preg_match_all("/<a[\s\r\n\t]+[^>]*href[^=]*=[\s'\"\n\r\t]*([^\s\"'>\r\n\t#]+)[^>]*>/is", $content, $matches);
			if (is_array($matches[1])) {
				$this->parseLinks($matches[1]);
			}
		}
		
		// ������ HTML
		$content = preg_replace("/<a[^>]*>.*<\/a>/ismU", " ", $content);
		$content = str_replace('<', ' <', $content);
		$content = strip_tags($content);
		$content = preg_replace("/&[a-z0-9]{2,5};/ismU", " ", $content);
		$content = preg_replace("/[^�-��-�A-Z\-]+/ismU", " ", $content);
		$content = preg_replace("/[\s\n\r\t]+/ism", " ", $content);
		
		$return['content'] = $content;
		
		return $return;
	}
	
	
	/**
	 * ������������ ������ � ��������� �� � �������
	 * @version 2006-01-12
	 * @param array $links
	 * @return void
	 */
	private function parseLinks($links) {
		global $DB;
		
		/**
		 * ���������, ��������� �� ������ � ��������� ��� � �������,
		 * ������� ������ �� ������ ����� �������� ������� �� �������
		 */
		reset($links);
		while (list($index, $link) = each($links)) {
			/** 
			 * ������������ ������ ��������� ������
			 */
			if (substr($link, 0, 1) == '/') {
				/**
				 * ������, ������� ���������� � /
				 */
				$link = substr($link, 1);
				
			} elseif (substr($link, 0, strlen($this->site['url'])) == $this->site['url']) {
				/**
				 * ������, ������� ���������� � ������ �������������� ����� http://www.site.com/test
				 */
				$link = substr($link, strlen($this->site['url']));
				
			} elseif (substr($link, 0, 3) == '../') {
				/**
				 * ������ ������� ���������� � ���� �����
				 */
				$prefix = substr($this->page_url, strlen($this->site['url']));
				$prefix = dirname($prefix);
				$prefix = preg_split("/\//", $prefix, -1, PREG_SPLIT_NO_EMPTY);
				$matches = preg_match("/^(\.\.\/)+/i", $link);
				for ($i = 0; $i < $matches; $i++) {
					array_pop($prefix);
				}
				$prefix = implode('/', $prefix);
				$link = $prefix . '/' . substr($link, 3 * $matches);
				
			} elseif (preg_match("/^(?:[a-z0-9_]|\.\/)/i", $link)) {
				/**
				 * ������ ������� ���������� � ����� ��� � ����� �����
				 */
				
				/**
				* ���������� mailto, javascript, http, https, ftp, ftps
				* �������� http ����������, ��� ��� ��� ������ �� ������� ����
				*/
				if (preg_match('/^(?:'.implode('|', $this->ignore_link_protocol).'):/i', $link)) {
					unset($links[$index]);
					continue;
				}
				
				/**
				 * ���� ����� ��������� � ����� �������, ����� � /, ����� ���� ������������ ��� ������� 
				 * ���-�����, ������������ � �����
				 */
				if (substr($link, 0, 2) == './') {
					$link = substr($link, 2);
				}
				
				/**
				 * �������� � ��������� ���������� ����� ������� ��������, ������������� �������, 
				 * ���� url ����� ����� ��� ��������� �����
				 */
				$prefix = preg_replace("/\/[^\/]*$/i", "/", $this->page_url);
				$link = $prefix . $link;
				if (strpos($link, $this->site['url']) === 0) {
					$link = substr($link, strlen($this->site['url']));
				}
				
			} elseif (substr($link, 0, 1) == '?') {
				/**
				 * ������ ���������� �� ����� �������
				 */
				$url = parse_url($this->page_url);
				$link = substr($url['path'], 1) . $link;
				
			} else {
				/**
				 * ����������� �� ������� ������� �����
				 */
				echo "\n[w]$this->page_url\n[w] Unknown URL $link\n";
				unset($links[$index]);
				continue;
			}
			
			$links[$index] = $link;
		}
		
		/**
		 * ������� ������������� ������
		 */
		$links = array_unique($links);
		
		/**
		 * ������� ���������� � ��������� �� ������� ��������� ������ ��������
		 */
		$query = "DELETE FROM search_referer WHERE referer_id='".$this->page_id."'";
		$DB->delete($query);
		
		/**
		 * ��������� ������ � �������
		 */
		reset($links);
		$counter = 0;
		while (list($index, $link) = each($links)) {
			
			$counter++;
			if ($counter >= 255) {
				/**
				 * ��������� ������ �� ���������� ������, �� ��������
				 */
				break;
			}
			
			$query = "SELECT id FROM search_page WHERE url='".mysqli_real_escape_string($DB->link, $link)."'";
			$id = $DB->result($query, false);

			/**
			 * ��������� �������� � �������, ���� �� ��� ��� � ���������� �� id
			 */
			if ($id == false) {
				$query = "
					INSERT INTO search_page (site_id, url, depth, tstamp) VALUES (
						'".$this->site['id']."',
						'".mysqli_real_escape_string($DB->link, $link)."',
						".intval($this->depth + 1).",
						0
					)
				";
				$id = $DB->insert($query);
			}
			
			/**
			* ��������� referer
			*/
			$query = "INSERT IGNORE INTO search_referer (referer_id, page_id) VALUES ('".$this->page_id."', '".$id."')";
			$DB->insert($query);
		}
	}
	
	/**
	* ������ ���� robots.txt � ��������� � ��� ���������� � �������
	* @access private
	* @param void
	* @return void
	*/
	private function updateRobots() {
		global $DB;
		
		echo "[i] Updating robots.txt\n";

		// ��������� ���� robots.txt
		$robots = $this->Download->get($this->site['url'].'robots.txt');
		$robots = strtolower($robots);
		$robots = preg_split("/\n/", $robots, -1, PREG_SPLIT_NO_EMPTY);
		
		// ���������� ����� �� ������� ��������� � ������ ������
		$active = false;
		reset($robots);
		while (list($index, $line) = each($robots)) {
			$key = trim(substr($line, 0, strpos($line, ':')));
			$val = trim(substr($line, strpos($line, ':') + 1));
			if ($key == 'user-agent') {
				$active = (in_array($val, array('*', 'deltaspider'))) ? true : false;
			} elseif ($active && $key == 'disallow') {
				if (substr($val, -2) == '*$') {
					$val = substr($val, -2).'*';
				} elseif (substr($val, -1) != '$') {
					$val = $val.'*';
				}
				if (substr($val, 0, 1) == '/') {
					$val = substr($val, 1);
				}
				$this->disallow[] = str_replace(array('$', '\%', '_', '*'), array('', '\%', '\_', '%'), $val);
			}
		}
	}
	

	/**
	 * �������� ������ ������ ������ � ��������, ������������ ��
	 * @param void
	 * @return bool
	 */
	private function optimize() {
		global $DB;
		
		echo "[i] Delete unlinked pages\n";
		
		// ������� ��������, �� ������� �� ����� ������
		$counter = 0;
		do {
			$counter++;
			$query = "
				SELECT tb_page.id
				FROM search_page AS tb_page
				LEFT JOIN search_referer AS tb_referer ON tb_referer.page_id=tb_page.id
				WHERE tb_referer.page_id IS NULL
				LIMIT 200
			";
			$data = $DB->fetch_column($query);
			if ($DB->rows == 0 || $counter > 100) {
				break;
			}
			$query = "DELETE FROM search_page WHERE id IN (0".implode(",", $data).")";
			$DB->delete($query);
		} while (1);
		
		echo "[i] Optimize tables\n";
		
		// ������������ ��� �������
		$query = "OPTIMIZE TABLE `search_page`, `search_referer`, `search_site`";
		$DB->query($query);
	}
	

}
?>