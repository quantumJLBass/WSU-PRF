<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'scrape_data' ) ) {
	class scrape_data extends scrape_core {
		public $seen_urls = array();
		public $found_urls = array();
		public $limit = 0;
		public $depth = 0;
		public $rootUrl = '';
		public $currentUrl = '';

		function __construct() {
			
			
			}



    /*
     * Insert to template table
     * @arr - array
     */
    public function add_queue($arr = array()) {
        global $wpdb;
		$arr['added_date'] = current_time('mysql');
		$table_name         = $wpdb->prefix . "scrape_n_post_queue";
		$rows_affected      = $wpdb->insert($table_name, $arr);
    }
    /*
     * Update entry in template table
     * @data - array
     */
    public function update_queue($arr = array()) {
        global $wpdb,$catpdf_core,$_params;
        $where         = array(
            'target_id' => $_params['target_id']
        );
		$arr['last_checked'] = current_time('mysql');
        $table_name    = $wpdb->prefix . "scrape_n_post_queue";
        $rows_affected = $wpdb->update($table_name, $arr, $where);
    }
/*				$_params['target_id']=$target_id;
				$this->update_queue(array(
					'url'=>$href,
					'type'=>$obj['type'],
					'http_status'=>200
				));
				*/






	
		public function get_options(){
			$plugin_option = get_option('scrape_options',array(
				'sc_posts' => 1,
				'sc_widgets' => 1,
				'on_error' => 'error_hide',
				'custom_error' => 'Unable to fetch data',
				'useragent' => "Scrape-N-Post bot (".get_bloginfo('url').")",
				'timeout' => 2,
				'cache' => 60
			));	
			return $plugin_option;
		}
	
	
		public function build_link_object(){
	
			return array();
		}
	
	/**
	 * Wrapper function to fetch content, select / query it and parse it
	 * @param string $url
	 * @param string $selector (optional) Selector
	 * @param string $xpath (optional) XPath
	 * @param array $scrapeopt Options
	 * @return string
	 */
	function scrape_get_content($url, $selector = '', $xpath = '', $scrapeopt = '') {
		$scrape_options = get_option('scrape_options');
		$scrape_options = $scrape_options['scrape_options'];
		$default_scrapeopt = array(
				'postargs' => '',
				'cache' => $scrape_options['cache'],
				'user_agent' => $scrape_options['useragent'],
				'timeout' => $scrape_options['timeout'],
				'on_error' => $scrape_options['on_error'],
				'output' => 'html',
				'clear_regex' => '',
				'clear_selector' => '',
				'replace_regex' => '',
				'replace_selector' => '',
				'replace_with' => '',
				'replace_selector_with' => '',
				'basehref' => '',
				'striptags' => '',
				'removetags' => '',
				'callback' => '',
				'debug' => '1',
				'htmldecode' => ''
		);
		$scrapeopt = wp_parse_args( $scrapeopt, $default_scrapeopt );
		unset($scrapeopt['url']);
		unset($scrapeopt['selector']);
		unset($scrapeopt['xpath']);
		//print('getting content for <h5>'.$url.'</h5>');
		if(!isset($scrapeopt['request_mt']))$scrapeopt['request_mt']=microtime(true);

		if(empty($url)) {
			//on error
		}
	
		if( strstr($url, '___QUERY_STRING___') ) {
			$url = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $url);
		} else {
			$url = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $url);
		}
	
		if( strstr($scrapeopt['postargs'], '___QUERY_STRING___') ) {
			$scrapeopt['postargs'] = str_replace('___QUERY_STRING___', $_SERVER['QUERY_STRING'], $scrapeopt['postargs']);
		} else {
			$scrapeopt['postargs'] = preg_replace_callback('/___(.*?)___/', create_function('$matches','return $_REQUEST[$matches[1]];'), $scrapeopt['postargs']);
		}
	
		$cache_args['cache'] = $scrapeopt['cache'];
	
		if ( !empty($scrapeopt['postargs']) ) {
			$http_args['headers'] = $scrapeopt['postargs'];
			$cache_args['headers'] = $scrapeopt['postargs'];
		}
		$http_args['user-agent'] = $scrapeopt['user_agent'];
		$http_args['timeout'] = $scrapeopt['timeout'];
		//print('making request content for <h5>'.$url.'</h5>');
		$response = $this->scrape_remote_request($url, $cache_args, $http_args);
		//var_dump($response);
		if( !is_wp_error( $response ) ) {
			$raw_html = $response['body'];
			if( !empty($selector) ) {
				$raw_html = $this->scrape_get_html_by_selector($raw_html, $selector, $scrapeopt['output']);
				 if( !is_wp_error( $raw_html ) ) {
					 $filtered_html = $raw_html;
				 } else {
					 $err_str = $raw_html->get_error_message();
				 }
			}
			if( !empty($err_str) ) {
				//log error
			}
			return $raw_html;
		} else {
			return "ERROR::".$response['response']['code'];
		}
	
	}
	
	/**
	 * Retrieve the raw response from the HTTP request (or its cached version).
	 * Wrapper function to wp_remote_request()
	 * @param string $url Site URL to retrieve.
	 * @param array $cache_args Optional. Override the defaults.
	 * @param array $http_args Optional. Override the defaults.
	 * @return WP_Error|array The response or WP_Error on failure.
	 */
	function scrape_remote_request($url, $cache_args = array(), $http_args = array(),$retry_limit=3) {
		//print('starting request <h5>'.$url.'</h5>');
		//var_dump($http_args);
		$default_cache_args = array(
			'cache' => 60,
			'on-error' => 'cache'
		);
		$default_http_args = array(
			'user-agent' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)'
		);
		$cache_args = wp_parse_args( $cache_args, $default_cache_args );
		$http_args = wp_parse_args( $http_args, $default_http_args );
		if(isset($cache_args['headers']) && $cache_args['headers']) {
			$transient = md5($url.serialize($cache_args['headers']));
		} else {
			$transient = md5($url);
		}
	
		
		//print('doing request <h5>'.$url.'</h5>');
		//var_dump($http_args);
		$response = wp_remote_request($url, $http_args);
		if( !is_wp_error( $response ) ) {
			if($cache_args['cache'] != 0)
				set_transient($transient, $response, $cache_args['cache'] * 60 );
			@$response['headers']['source'] = 'WP_Http';
			return $response;
		} else {
			var_dump($response);
			if($retry_limit>0){
				sleep(2);
				print('retrying');
				return $this->scrape_remote_request($url,$cache_args,$http_args,$retry_limit-1);	
			}
			die();
			return $response['response']['code'];
		}
	}

	/**
	 * Get HTML from a web page using selector
	 * @param string $raw_html Raw HTML
	 * @param string $selector Selector
	 * @param string $output html or text
	 * @return string
	 */
	function scrape_get_html_by_selector($raw_html, $selector, $output = 'html'){
		// Parsing request using phpQuery
		$currcharset = get_bloginfo('charset');
		require_once 'phpQuery.php';
		$phpquery = phpQuery::newDocumentHTML($raw_html, $currcharset);
		phpQuery::selectDocument($phpquery);
		if($output == 'text')
			return pq($selector)->text();
		if($output == 'html')
			return pq($selector)->html();
		if( empty($output) )
			return new WP_Error('scrape_get_html_by_selector_failed', "Error parsing selector: $selector");
	}

	
	public $seen = array();
	public $wanted = array();	
	function get_all_urls($url, $depth = 5) {
		$this->traverse_all_urls($url,$depth);
		//var_dump($this->wanted);
		return $this->wanted;
	}
	
	function traverse_all_urls($url,  $depth = 5) {
		global $_params,$scrape_core;
		
		if ( isset($this->seen["{$url}"]) 
			 || $depth === 0 
			 || strpos($url,'javascript:newWindow') !== false
			 ) {
			return;
		}
		//print('MARKING AS SEEN ==== '.$url);
		$this->seen["{$url}"] = true;
	
		$urls = $this->get_urls($url);
		//var_dump($urls);
		foreach($urls as $href=>$obj ) {
			if($obj['type']=='page'){
				//print('<h3>ready with=>'.$depth.'::'.$href.'</h3>');
				if (0 !== strpos($href, 'http')) {
					$relative=false;
					if(substr($href,0,1)!='/'){
						$relative=true;
					}
					$path = '/' . ltrim($href, '/');

					$parts = parse_url($url);
					//var_dump($parts);
					$href = $parts['scheme'] . '://';
					if (isset($parts['user']) && isset($parts['pass'])) {
						$href .= $parts['user'] . ':' . $parts['pass'] . '@';
					}
					$href .= $parts['host'];
					if (isset($parts['port'])) {
						$href .= ':' . $parts['port'];
					}
					if($relative){
						$pathparts=explode('/',$parts['path']);
						$last=end($pathparts);
						if(strpos($last,'.')!==false){
							array_pop($pathparts);
						}
						$urlpath=implode('/',$pathparts);
						$href .= '/'.trim($urlpath, '/').'/'.trim($path, '/');
						//print('<h3>relative built=>'.$href.'</h3>');
					}else{
						$href .= '/'.trim($path, '/');	
						//print('<h3>non--relative built=>'.$href.'</h3>');
					}
					//print('<h3>built=>'.$href.'</h3>');
				}
			}
			if(strpos($href,'.htm/')!==false){
				die($href);
			}
			
			
			//$o_href=$href;
			//print('<h4>here=>'.$depth.'::'.$href.'</h4>');
			if (isset($this->seen["{$href}"]) && $this->seen["{$href}"]) {
				//print('<h4>SAW=>'.$depth.'::'.$href.'</h4>');
				continue;
			}
			
			//var_dump($href);
			//var_dump($obj);
			if( $obj['type'] == "page" ){
				$exist=$scrape_core->_is_exist('url',$href);
				if(!$exist){
					$this->add_queue(array(
						'url'=>$href,
						'type'=>$obj['type'],
						'http_status'=>200
					));
				}
				$this->wanted[$href]=$obj;
				$this->traverse_all_urls($href,$depth - 1);
			}
		}
		sleep( 1 );
		echo $url;
	}

	public function get_urls($url){
		global $scrape_data,$_params;

		$page=$scrape_data->scrape_get_content($url, 'body');
		if($page=="ERROR::404"){
			var_dump($url);
			die();
		}
		$doc = phpQuery::newDocument($page);
		$as = pq('a');
		$urls=array();
		foreach($as as $a) {
			$link_url=pq($a)->attr('href');
			//$link_url=$this->normalize_url_format($link_url);
			if(!empty($link_url)){
				$type='page';
				if(!$this->is_localurl($link_url)){
					$type='external';
				}elseif($this->is_fileurl($link_url)){
					$type='file';
				}elseif($this->is_email($link_url)){
					$type='email';
				}elseif($this->is_anchor($link_url)){
					$type='anchor';
				}
				//$link_url=$this->normalize_url($link_url);
				$urls["{$link_url}"]=array('type'=>$type);
			}
		}
		return $urls;
		//if(!empty($page)){}die('had nothing');
	}
	
	/*
	* take @param $url and insure it's a fully qualified URL 
	* NOTE: this only does local domains
	*/	
	public function normalize_url($url){
		if(!$this->is_localurl($url))return $url;
		if($this->is_email($url))return $url;
		if($this->is_anchor($url))return $url;
		$baseurl=str_replace('http://'.$this->rootUrl,'',$url);
		if(substr($baseurl,0,1)!='/'){
			if(strpos($baseurl,'/')!==false ){
				 $baseparts=explode('/',$baseurl); 
				 $file = end ($baseparts);
			}else{
				$file =$baseurl;
			}
			$newbaseurl='http://'.$this->rootUrl;
			if($file){
				//$fileparts=explode( $file, $this->currentUrl[$depth]);
				//$newbaseurl = $fileparts[0];
			}
			$url=trim($newbaseurl,'/').'/'.trim($file,'/');
		}else{
			$url='http://'.$this->rootUrl.$baseurl;
		}
		$url=$this->normalize_url_format($url);
		return $url;	
	}
	/*
	* corrects any oddities of @param $url 
	* EX: 'HtTp://User:Pass@www.ExAmPle.com:80/Blah' becomes
	* 'http://User:Pass@www.example.com:80/Blah'
	*/	
	public function normalize_url_format($url){
		$url=preg_replace(
		  '#(^[a-z]+://)(.+@)?([^/]+)(.*)$#ei',
		  "strtolower('\\1').'\\2'.strtolower('\\3').'\\4'",
		  $url);	
		return $url;
	}
	/*
	* test if @param $url is not a file or email
	*/
	public function is_page($url=false){
		if(	$url === false
			|| is_email($url)!==false 
			|| is_fileurl($url)!==false 
			)
			return false;
		return true;
	}
	/*
	* test if @param $url is a file
	*/
	public function is_fileurl($url=false){
		if(	$url === false
			|| (strpos($url,'.pdf')===false 
				&& strpos($url,'.jpg')===false 
				&& strpos($url,'.gif')===false
				&& strpos($url,'.png')===false
				&& strpos($url,'.css')===false
				&& strpos($url,'.js')===false)
			)
			return false;
		return true;
	}
	/*
	* test if @param $url is an email
	*/
	public function is_email($url=false){
		if( $url === false
			|| strpos($url,'mailto:')===false 
			)
			return false;
		return true;
	}
	/*
	* test if @param $url is an email
	*/
	public function is_anchor($url=false){
		if(substr($url,0,1) == '#')return true;
		return false;
	}
	/*
	* test if @param $url is an email
	*/
	public function is_localurl($url){
		if( substr($url,0,4) == 'http'
			&& strpos($url,$this->rootUrl)===false
			)
			return false;
		return true;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		/*
		 * Get post data
		 * @id - int
		 * It's worth noting that any out put here will print into the pdf.  If the PDF can't be 
		 * read then look at it in a text editor like Notepad, where you will see the php errors
		 */
		public function query_posts($id = NULL) {
			global $_params;
			$type = isset($_params['type'])?$_params['type']:"post";
			$args = array(
				'post_type' => $type,
				'posts_per_page' => -1,
				'order' => 'DESC'
			);
			if ($id !== NULL) {
				$args['p'] = $id;
			}
			if (isset($_params['user']) && count($_params['user']) > 0) {
				$args['author'] = implode(',',$_params['user']);
			}
			if (isset($_params['status']) && count($_params['status']) > 0) {
				$args['post_status'] = implode(',',$_params['status']);
			}
			if (isset($_params['cat']) && count($_params['cat']) > 0) {
				$args['cat'] = implode(',',$_params['cat']);
			}
			add_filter('posts_where', array( $this, 'filter_where' ));
			$result = new WP_Query($args);
			
			return $result->posts;
		}
		/*
		 * Return query filter
		 * @where - string
		 */
		public function filter_where($where = '') {
			global $_params;
			if (isset($_params['from']) && $_params['from'] != '') {
				$from = date('Y-m-d', strtotime($_params['from']));
				$where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) >= "' . $from . '"';
			}
			if (isset($_params['to']) && $_params['to'] != '') {
				$to = date('Y-m-d', strtotime($_params['to']));
				$where .= ' AND DATE_FORMAT( post_date , "%Y-%m-%d" ) <= "' . $to . '"';
			}
			return $where;
		}
	
	
	}
	global $scrape_data;
	$scrape_data = new scrape_data();
}
?>