<?php

/* things still to do
-remove the use themes templates inlue of per template css path link
-must beable to sort on optional items like tax/type etc
-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
-provide more areas to controll
-make the index
-create ruls for the bookmarking


*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_pages {
    public $dompdf = NULL;
    public $message = array();
    public $post = array();
    public $title = '';
    public $posts;
    function __construct() {
        if (is_admin()) {
            // Initailize admin
            add_action('admin_init', array( $this, 'admin_init' ));
            add_action('admin_menu', array( $this, 'admin_menu' ));
        }
        if (isset($_POST)) {
            $this->post = $_POST;
		
            // Check if option save is performed
            if (isset($this->post['catpdf_save_option'])) {
                // Add update option action hook
                add_action('init', array( $this, 'update_options' ));
            }
            // Check if pdf export is performed
            if (isset($this->post['catpdf_export'])) {
                // Add export hook
                add_action('init', array( $this, 'export' ));
            }
            // Check if template save is performed
            if (isset($this->post['catpdf_save'])) {
                if ($this->post['templateid'] == '') {
                    // Add save template action hook
                    add_action('init', array( $this, 'add_template' ));
                } else {
                    // Add update template action hook
                    add_action('init', array( $this, 'update_template' ));
                }
            }
        }
        // Check if post download is performed
        if (isset($_GET['catpdf_dl'])) {
            // Add download action hook
            add_action('init', array( $this, 'download_post' ));
        }
        // Check if single post download is performed
        if (isset($_GET['catpdf_post_dl'])) {
            // Add download action hook
            add_action('init', array( $this, 'download_posts' ));
        }
    }
    /*
     * Initailize plugin admin part
     */
    public function admin_init() {
		global $wp_scripts;
        // Enque style and script		
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker', CATPDF_PLUGIN_URL . 'js/ui/jquery.ui.datepicker.js', array(
            'jquery'
        ), '1.9.0', 'all');
		wp_enqueue_style('jquery-ui-datepicker', CATPDF_PLUGIN_URL . 'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
		
        wp_enqueue_script('jquery-ui-tabs', CATPDF_PLUGIN_URL . 'js/ui/jquery.ui.tabs.js', array(
            'jquery'
        ), '1.9.0', 'all');		
		wp_enqueue_style('jquery-ui-tabs', CATPDF_PLUGIN_URL . 'css/ui/jquery.ui.all.css', false, '1.9.0', 'all');
		// get registered script object for jquery-ui
		$ui = $wp_scripts->query('jquery-ui-core');
	 
		// tell WordPress to load the Smoothness theme from Google CDN
		$protocol = is_ssl() ? 'https' : 'http';
		$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);

		
		
        
        wp_enqueue_script('catpdf-js', CATPDF_PLUGIN_URL . 'js/catpdf.custom.js', array(
            'jquery'
        ), '', 'all');
        wp_enqueue_style('catpdfport-style', CATPDF_PLUGIN_URL . 'css/style.css', false, '1.9.0', 'all');
    }
    /*
     * Add plugin menu
     */
    public function admin_menu() {
        // Register menu
        add_menu_page(CATPDF_NAME, CATPDF_NAME, 'manage_options', CATPDF_BASE_NAME, array( &$this, 'option_page' ), CATPDF_PLUGIN_URL . 'images/nav-icon.png');
        // Register sub-menu
        add_submenu_page(CATPDF_BASE_NAME, _('Download PDF'), _('Download PDF'), 'manage_options', 'catpdf-download-pdf', array( $this, 'download_page' ));
        add_submenu_page(CATPDF_BASE_NAME, _('Template Manager'), _('Template Manager'), 'manage_options', 'catpdf-template-manager', array( $this, 'template_manager_page' ));
        add_submenu_page(CATPDF_BASE_NAME, _('Add Template'), _('Add Template'), 'manage_options', 'catpdf-add-template', array( $this, 'add_page' ));
    }
    /*
     * Display "Add" page
     */
    public function add_page() {
        $data            = array();
        $data['message'] = $this->get_message();
        $this->view(CATPDF_PLUGIN_PATH . '/includes/views/template.php', $data);
    }

	
    /*
     * Display "Template Manager" page
     */
    public function template_manager_page() {
        // Include list class
        include(CATPDF_PLUGIN_PATH . '/includes/list_class.php');
        $wp_list_table = new template_list();
        $wp_list_table->prepare_items();
		
		$body_templateShortCodes= shortcode::get_template_shortcodes('body');
		$data['body_templateShortCodes']=$body_templateShortCodes;
		$loop_templateShortCodes= shortcode::get_template_shortcodes('loop');
		$data['loop_templateShortCodes']=$loop_templateShortCodes;
        // Check if edit action is performed
        if (isset($_GET['catpdf_action']) && $_GET['catpdf_action'] == 'edit') {
            $data['on_edit'] = $this->get_template($_GET['template']);
            $data['message'] = $this->get_message();
            // Display template form
            $this->view(CATPDF_PLUGIN_PATH . '/includes/views/template.php', $data);
        } else {
            ob_start();
            $wp_list_table->display();
            $data['table']   = ob_get_clean();
            $data['message'] = $this->get_message();
            // Display template list
            $this->view(CATPDF_PLUGIN_PATH . '/includes/views/template_manager.php', $data);
        }
    }
	
	
	

	
	
    /*
     * Display "Download" page
     */
    public function download_page() {
		global $catpdf_templates;
        $data                  = array();
        $args                  = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hierarchical' => 1,
            'hide_empty' => '0'
        );
        $options               = get_option('catpdf_options');
        // Construct category dropdown
        $select_cats           = wp_dropdown_categories(array(
            'echo' => 0,
            'hierarchical' => 1
        ));
		
		$post_types      = get_post_types(array(
            'public'   => true,
                     //'_builtin' => false
        ),'names' , 'and' );
		$select_types= '<select name="type[]" multiple="multiple" class="postform" >';
		foreach ($post_types  as $post_type ) {
			$select_types.='<option value="'. $post_type.'"  class="level-0" >'. $post_type. '</option>';
		}
		$select_types.='</select>';
		
		
        $select_cats           = str_replace("name='cat' id=", "name='cat[]' multiple='multiple' id=", $select_cats);
        $select_cats           = str_replace("<option", '<option selected="selected"', $select_cats);
        // Construct user dropdown
        $select_author         = wp_dropdown_users(array(
            'id' => 'author',
            'echo' => false
        ));
        $select_author         = str_replace("name='user' ", "name='user[]' multiple='multiple' ", $select_author);
        $select_author         = str_replace("<option", '<option selected="selected"', $select_author);
		
		$data['select_types']  = $select_types;
        $data['select_cats']   = $select_cats;
        $data['select_author'] = $select_author;
        $data['select_sizes']  = array(
            'letter', '4a0', '2a0', 'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6', 'b7', 'b8', 'b9', 'b10', 'c0', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6c6', 'c7', 'c8', 'c9', 'c10', 'ra0', 'ra1', 'ra2', 'ra3', 'ra4', 'sra0', 'sra1', 'sra2', 'sra3', 'sra4', 'legal', 'ledger', 'tabloid', 'executive', 'folio', 'commerical #10 envelope', 'catalog #10 1/2 envelope', '8.5x11', '8.5x14', '11x17'
        );
        $data['select_ors']    = array(
            'portrait', 'landscape'
        );
        $data['option_url']    = "";//$tool_url;
        $data['templates']     = $catpdf_templates->get_template();
        $data['message']       = $this->get_message();
        // Display export form
        $this->view(CATPDF_PLUGIN_PATH . '/includes/views/export.php', $data);
    }
    /*
     * Display "Option" page
     */
    public function option_page() {
		global $catpdf_templates;
        // Get options
        $options           = get_option('catpdf_options');
        $data['options']   = $options;
        // Get templates
        $data['templates'] = $catpdf_templates->get_template();
        // Display option form
        $this->view(CATPDF_PLUGIN_PATH . '/includes/views/options.php', $data);
    }




    /*-------------------------------------------------------------------------*/
    /* -General- 															   */
    /*-------------------------------------------------------------------------*/
    /*
     * Return falsh message
     */
    public function get_message() {
        if (!empty($this->message)) {
            $arr = $this->message;
            return '<div id="message" class="' . $arr['type'] . '"><p>' . $arr['message'] . '</p></div>';
        }
    }
    /*
     * Return query filter
     * @file - string
     * @data - array
     * @return - boolean
     */
    public function view($file = '', $data = array(), $return = false) {
        if (count($data) > 0) {
            extract($data);
        }
        if ($return) {
            ob_start();
            include($file);
            return ob_get_clean();
        } else {
            include($file);
        }
    }
	

	
	
	
	
	
}
?>