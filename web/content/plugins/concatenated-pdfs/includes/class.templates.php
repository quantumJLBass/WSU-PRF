<?php

/*
	Still needs a good refactor
	- sections should be abstracted to subplugin style
*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class catpdf_templates {
    public $title = '';
	public $current_template = NULL;
    function __construct() {
		global $_params;
        if (is_admin()) {
			if (isset($_params)) {
				// Check if template save is performed
				if (isset($_params['catpdf_save'])) {
					if ($_params['templateid'] == '') {
						// Add save template action hook
						add_action('init', array($this, 'add_template' ));
					} else {
						// Add update template action hook
						add_action('init', array( $this, 'update_template' ));
					}
				}
			}
		}  
    }


	public function get_default_template_sections(){
		$sections = array(
			'cover'=>"",
			'index'=>"",
			'content'=>"",
			'appendix'=>"",
		);
		return $sections;
	}

	/*
	* @todo set this up to accept inserted parts
	*/
	public function get_template_sections(){
		return $this->get_default_template_sections();
	}
	
	public function get_section_content(){	
		global $catpdf_output;
		$catpdf_output->_html_structure();
		$content 		= $catpdf_output->filter_shortcodes('body');
		$contentHtml	= "<div id='catpdf_content'>{$content}</div>";	
		return $content;
	}	
	public function get_section_cover(){
		$cover			= "<h1 class='CoverTitle'>Cover Letter</h1>";
		$coverHtml 		= "<div id='catpdf_cover'>{$cover}</div>";	
		return $coverHtml;
	}
	
	public function get_section_index(){
		global $posts;
		$index='<script type="text/php">$GLOBALS["indexpage"]=$pdf->get_page_number(); $GLOBALS["backside"]=$pdf->open_object();</script>';
		$index.= "<h2>Table of Contents</h2>";
		$index.= "";
		$c=1;
		foreach($posts as $post){
			$index.="<table class='indexed_chapter'>
  <tbody>
    <tr>
      <td class='chapter' width='15%' align='right' cellspacing='0' cellpadding='0' >{chapter{$c}}</td>
      <td class='text' width='25%' align='left' cellspacing='0' cellpadding='0' >{text{$c}}</td>
      <td class='segment' align='right' cellspacing='0' cellpadding='0' ></td>
      <td class='pagenumber' width='5%' align='left' cellspacing='0' cellpadding='0' >{page{$c}}</td>
    </tr>
  </tbody>
</table>
";
			$c++;
		}
		$index.= "";
		$index.="</div>";
		$index.='<script type="text/php"> $pdf->close_object(); </script>';
		$indexHtml="<div id='catpdf_index'>{$index}</div>";
		return $indexHtml;
	}	
	public function get_section_appendix(){	
		$appendix			= "<h1 class='CoverTitle'>appendix</h1>";
		$appendixHtml 		= "<div id='catpdf_appendix'>{$appendix}</div>";	
		return $appendixHtml;
	}
	
	/*
	* return template object
	*/
	public function get_current_tempate($type=NULL){
		if($this->current_template==NULL)$this->set_current_tempate($type);
		return $this->current_template;
	}
	/*
	* set template object
	*/
	public function set_current_tempate($type=NULL){
		global $_params,$catpdf_templates;
		$curr_temp = $_params['template'];
        if ($type == 'single') {
            $options   = get_option('catpdf_options');
            $curr_temp = $options['dltemplate'];
        }
        if ($curr_temp == 'def') {
            $template = $catpdf_templates->get_default_template();
        } else {
            $template = $catpdf_templates->get_template($curr_temp);
        }
		$this->current_template = $template;
	}



    /*
     * Return default template structure
     */
    public function custruct_default_template($type = 'all') {
        $temp         = array();
        $temp['name'] = 'Default';
		
		
		
		// Construct template loop
		$pageheadertemplate = '<div id="site_info_block">
<img src="http://images.wsu.edu/index-images/bg-header.jpg" id="logo" />
<div id="site_info"><span id="site_info_name">[site_title]</span><br/><span id="site_info_tag">[site_tagline]</span></div>
</div>
<div id="site_tag">[date_today]<br/>[site_url link=true]</div>';
		$pagefootertemplate = '[page_numbers label="PAGE" separator="/"]';
		
		
        if ($type == 'single') {
            // Construct template loop
            $looptemplate = '<div class="post single">';
            $looptemplate .= '<h2>[title]</h2>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '<p>[content]</p>';
            $looptemplate .= '<div class="taxonomy">[category label="Posted in:"] | [tags label="Tagged:"] | With [comments_count] comments</div>';
            $looptemplate .= '</div>';
            // Construct template body
            $bodytemplate = '<div class="content-wrapper">';
            $bodytemplate .= '[loop]';
            $bodytemplate .= '</div>';
        } else {
            // Construct template loop
            $looptemplate = '<div class="content-wrapper"><div class="post">';
            $looptemplate .= '<h1>[title]</h1>';
			$looptemplate .= '<h2>[category label="Posted in:"]</h2>';
			$looptemplate .= '<h3>[tags label="Tagged:"]</h3>';
			$looptemplate .= '<h4>version [version_count] <span>([revision_count] revisions)<span></h4>';
            $looptemplate .= '<div class="meta"><p>Posted on <strong>[date]</strong> by <strong>[author]</strong></p></div>';
            $looptemplate .= '[content]';
            $looptemplate .= '</div></div><i class="page-break"></i>';
            // Construct template body
            $bodytemplate = '';
            $bodytemplate .= '<!--<div class="pdf-header">';
            $bodytemplate .= '<h1>Post List</h1>';
            $bodytemplate .= '<h2>[site_title]</h2>';
            $bodytemplate .= '<h3>[site_tagline]</h3>';
            $bodytemplate .= '[from_date label="From:"] [to_date label="To:"]';
            $bodytemplate .= '</div>-->';
            $bodytemplate .= '<div>[loop]</div>';
            $bodytemplate .= '';
        }
        $temp['loop'] = $looptemplate;
        $temp['body'] = $bodytemplate;
		$temp['pageheader'] = $pageheadertemplate;
		$temp['pagefooter'] = $pagefootertemplate;
        return $temp;
    }
    /*
     * Return default template
     */
    public function get_default_template() {
        if (isset($_GET['catpdf_dl'])) {
            $default_template = $this->custruct_default_template('single');
        } else {
            $default_template = $this->custruct_default_template();
        }
        $arr = array();
        $arr = array(
            'template_name' => 'Default',
            'template_loop' => $default_template['loop'],
            'template_body' => $default_template['body'],
			'template_pageheader' => $default_template['pageheader'],
			'template_pagefooter' => $default_template['pagefooter']
        );
        return (object) $arr;
    }
    /*
     * Insert to template table
     * @arr - array
     */
    public function add_this($arr = array()) {
        global $wpdb, $current_user;
        // Get user info
        get_currentuserinfo();
        $user               = $current_user;
        // Insert data
        $arr['create_date'] = current_time('mysql');
        $arr['create_by']   = $user->ID;
        $table_name         = $wpdb->prefix . "catpdf_template";
        $rows_affected      = $wpdb->insert($table_name, $arr);
    }
    /*
     * Update entry in template table
     * @data - array
     */
    public function update_this($data = array()) {
        global $wpdb,$catpdf_core,$_params;
        $where         = array(
            'template_id' => $_params['templateid']
        );
        $table_name    = $wpdb->prefix . "catpdf_template";
        $rows_affected = $wpdb->update($table_name, $data, $where);
    }

    /*
     * Return template data
     * @id - string
     */
    public function get_template($id = NULL) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        if ($id !== NULL) {
            $sql      = $wpdb->prepare("SELECT * FROM " . $table_name . " WHERE template_id = %d;", $id);
            $template = $wpdb->get_row($sql);
        } else {
            $template = $wpdb->get_results("SELECT * FROM " . $table_name);
        }
        return $template;
    }
    /*
     * Add template
     */
    public function add_template() {
		global $catpdf_core,$_params;
        if ($_params['templatename'] != '') {
            $data = array(
                'template_name' => $_params['templatename'],
                'template_loop' => $_params['looptemplate'],
                'template_body' => $_params['bodytemplate'],
				'template_pageheader' => $_params['pageheadertemplate'],
				'template_pagefooter' => $_params['pagefootertemplate'],
                'template_description' => $_params['description']
            );
            // Insert template
            $this->add_this($data);
            $catpdf_core->message = array(
                'type' => 'updated',
                'message' => __('Template saved.')
            );
        } else {
            $catpdf_core->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Update template database entry
     */
    public function update_template() {
		global $catpdf_core,$_params;
        if ($_params['templatename'] != '') {
            $data = array(
                'template_name' => $_params['templatename'],
                'template_description' => $_params['description'],
                'template_body' => $_params['bodytemplate'],
				'template_pageheader' => $_params['pageheadertemplate'],
				'template_pagefooter' => $_params['pagefootertemplate'],
                'template_loop' => $_params['looptemplate']
            );
            $this->update_this($data);
            $catpdf_core->message = array(
                'type' => 'updated',
                'message' => __('Template updated.')
            );
        } else {
            $catpdf_core->message = array(
                'type' => 'error',
                'message' => __('Please provide template name.')
            );
        }
    }
    /*
     * Delete template entry
     */
    public function delete_template($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "catpdf_template";
        $wpdb->query("DELETE FROM " . $table_name . " WHERE template_id = " . $id);
    }

}
?>