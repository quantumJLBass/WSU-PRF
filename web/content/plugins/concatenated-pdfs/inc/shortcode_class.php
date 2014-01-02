<?php
class shortcode {
    public $single;
    function __construct() {
        if (is_admin() || isset($_GET['catpdf_dl']) || isset($_GET['catpdf_post_dl'])) {
            $this->register_tempalte_shortcodes();
        } else {
            add_shortcode('catpdf', array( $this, 'apply_download_button' ));
        }
    }
	
	/*
    * Return array
	* @attr
	*/
	public static function build_shortcodes(){
		$shortcodes = array(
			'loop'=> array('dis'=>__('Loop'),'function'=>'loop_func'),
			'site_title'=> array('dis'=>__('Site Title'),'function'=>'site_title_func'),
			'site_tagline'=>array('dis'=> __('Site Tagline'),'function'=>'site_tagline_func'),
			'site_url'=> array('dis'=>__('Site URL'),'function'=>'site_url_func'),
			'date_today'=> array('dis'=>__('Date Today'),'function'=>'date_today_func'),
			'from_date'=> array('dis'=>__('Date(From)'),'function'=>'from_date_func'),
			'to_date'=> array('dis'=>__('Date(To)'),'function'=>'to_date_func'),
			'categories'=> array('dis'=>__('Categories'),'function'=>'categories_func'),
			'post_count'=>array('dis'=> __('Post Count'),'function'=>'post_count_func'),
			'title'=> array('dis'=>__('Title'),'function'=>'title_func'),
			'excerpt'=> array('dis'=>__('Excerpt'),'function'=>'excerpt_func'),
			'content'=> array('dis'=>__('Content'),'function'=>'content_func'),
			'permalink'=> array('dis'=>__('Permalink'),'function'=>'permalink_func'),
			'date'=> array('dis'=>__('Date'),'function'=>'date_func'),
			'author'=> array('dis'=>__('Author'),'function'=>'author_func'),
			'author_photo'=> array('dis'=>__('Author Photo'),'function'=>'author_photo_func'),
			'author_description'=> array('dis'=>__('Author Description'),'function'=>'author_description_func'),
			'status'=> array('dis'=>__('Status'),'function'=>'status_func'),
			'featured_image'=> array('dis'=>__('Featured Image'),'function'=>'featured_image_func'),
			'category'=> array('dis'=>__('Category'),'function'=>'category_func'),
			'tags'=> array('dis'=>__('Tags'),'function'=>'tags_func'),
			'comments_count'=> array('dis'=>__('Comments Count'),'function'=>'comments_count_func')
		);
		return $shortcodes;
	}

    /*
    * Register template shortcodes
    */
    public function register_tempalte_shortcodes() {
        $shortcodes = shortcode::build_shortcodes();
		foreach($shortcodes as $code=>$props){
			add_shortcode($code, array( $this, $props['function'] ));
		}
    }
	
	public static function get_template_shortcodes($template='body'){
		switch($template){
			case 'body':
				$shortcodes = shortcode::build_shortcodes();
				$usingCodes = array(
					'loop','site_title','site_tagline','site_url','date_today',
					'from_date','to_date','categories','post_count'
				);
				$returning = array();
				foreach($shortcodes as $code=>$props){
					if(in_array($code,$usingCodes)){
						$returning[$code]= $props['dis'];
					}
				}
				return $returning;
				break;
			case 'loop':
				$shortcodes = shortcode::build_shortcodes();
				$usingCodes = array(
					'title','excerpt','content','permalink',
					'date','author','author_photo','author_description',
					'status','featured_image','category','tags','comments_count'
				);
				$returning = array();
				foreach($shortcodes as $code=>$props){
					if(in_array($code,$usingCodes)){
						$returning[$code]= $props['dis'];
					}
				}
				return $returning;
				break;
		}
	}
	
	
	
	
	
    /*
    * Display download button
    */
    public function apply_download_button($atts) {
        $link                  = '';
        $text                  = (isset($atts['text'])) ? $atts['text'] : 'Download';
        $atts['catpdf_post_dl'] = 'true';
        if (count($atts) > 0) {
            foreach ($atts as $key => $att) {
                $atts[$key] = urlencode($att);
            }
        }
        if (isset($atts['text'])) {
            unset($atts['text']);
        }
        $dllink = add_query_arg($atts);
        $link   = sprintf('<a href="%1$s">%2$s</a>', $dllink, $text);
        return $link;
    }
    /*
    * Return post content
    */
    public function content_func() {
        global $post;
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_content();
        return $item;
    }
    /*
    * Return post excerpt
    */
    public function excerpt_func() {
        global $post;
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_excerpt();
        return $item;
    }
    /*
    * Return post tags list
    * @atts - array
    */
    public function tags_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $posttags = get_the_tags();
        if ($posttags) {
            foreach ($posttags as $tag) {
                $item .= ucwords($tag->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
        return $item;
    }
    /*
    * Return post category list
    * @atts - array
    */
    public function category_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'delimiter' => ',',
            'label' => ''
        ), $atts));
        $item = '';
        $post = $this->single;
        setup_postdata($post);
        $cat_arr = (array) get_the_category(get_the_ID());
        if (count($cat_arr) > 0) {
            foreach ($cat_arr as $arr) {
                $item .= ucwords($arr->name) . $delimiter;
            }
            $item = substr($item, 0, -strlen($delimiter));
            $item = $label . $item;
        }
        return $item;
    }
    /*
    * Return post featured image
    * @atts - array
    */
    public function featured_image_func($atts) {
        global $post;
        $post = $this->single;
        extract(shortcode_atts(array(
            'size' => 'thumbnail'
        ), $atts));
        $item = '';
        setup_postdata($post);
        $item = get_the_post_thumbnail(get_the_ID(), $size);
        return $item;
    }
    /*
    * Return post status
    */
    public function status_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_post_status(get_the_ID());
        return $item;
    }
    /*
    * Return post author description
    */
    public function author_description_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_author_description();
        return $item;
    }
    /*
    * Return post author photo
    * @atts - array
    */
    public function author_photo_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'size' => '96'
        ), $atts));
        $post = $this->single;
        setup_postdata($post);
        $item = get_avatar(get_the_author_ID(), $size);
        return $item;
    }
    /*
    * Return post author
	*/
    public function author_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_author();
        return $item;
    }
    /*
    * Return post date
    * @atts - array
    */
    public function date_func($atts) {
        global $post;
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
        $post = $this->single;
        setup_postdata($post);
        $item = date($format, strtotime(get_the_date()));
        return $item;
    }
    /*
    * Return post permalink
    */
    public function permalink_func() {
        $post = $this->single;
        $item = get_permalink(get_the_ID());
        return $item;
    }
    /*
    * Return post title
    */
    public function title_func() {
        global $post;
        $post = $this->single;
        setup_postdata($post);
        $item = get_the_title();
        return $item;
    }
    /*
    * Return comment count
    */
    public function comments_count_func() {
        global $post, $structure;
        $post = $this->single;
        setup_postdata($post);
        $num = get_comments_number(0, 1, '%');
        return $num;
    }
    /*
    * Return loop html
    */
    public function loop_func() {
        global $catpdf_core;
        $item = '';
        if (count($catpdf_core->posts) > 0) {
            foreach ($catpdf_core->posts as $post) {
                $this->single = $post;
                $item .= $catpdf_core->filter_shortcodes('loop');
            }
        }
        return $item;
    }
    /*
    * Return found post count
    */
    public function post_count_func() {
        global $catpdf_core;
        $item = count($catpdf_core->posts);
        return $item;
    }
    /*
    * Return active categories
    * @atts - array
    */
    public function categories_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'delimiter' => ','
        ), $atts));
        $item = '';
        if (isset($structure->post['cat']) && ($structure->post['cat']) > 0) {
            foreach ($structure->post['cat'] as $row) {
                $cat_arr = get_category($row);
                $item .= $cat_arr->cat_name . $delimiter;
            }
        }
        return substr($item, 0, -strlen($delimiter));
    }
    /*
    * Return site title
    */
    public function site_title_func() {
        $item = get_bloginfo('name');
        return $item;
    }
    /*
    * Return site tagline
    */
    public function site_tagline_func() {
        $item = get_bloginfo('description');
        return $item;
    }
    /*
    * Return site url
    */
    public function site_url_func() {
        $item = get_bloginfo('url');
        return $item;
    }
    /*
    * Return today's date
    */
    public function date_today_func($atts) {
        extract(shortcode_atts(array(
            'format' => 'F d,Y'
        ), $atts));
        $item = date($format);
        return $item;
    }
    /*
    * Return filter from date
    */
    public function from_date_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['from']) && $structure->post['from'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['from']));
        }
        return $item;
    }
    /*
    * Return filter to date
    */
    public function to_date_func($atts) {
        global $structure;
        extract(shortcode_atts(array(
            'format' => 'F d,Y',
            'label' => ''
        ), $atts));
        $item = '';
        if (isset($structure->post['to']) && $structure->post['to'] != '') {
            $item = $label . ' ' . date($format, strtotime($structure->post['to']));
        }
        return $item;
    }
}
?>