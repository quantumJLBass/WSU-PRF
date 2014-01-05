<?php

/* lists helpers.. rethink this */
class crawl_list extends WP_List_Table {
    function __construct() {
        global $status, $page;
        parent::__construct(array(
            'singular' => 'wp_list_text_link',
            'plural' => 'wp_list_test_links',
            'ajax' => false
        ));
    }
    /*
    * Return no result copy
    */
    function no_items() {
        _e('No template found.');
    }
    /*
    * Return column default
    */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'post_id':
				if($item->post_id==NULL)$item->post_id="--NA--";
                return stripslashes($item->post_id);
                break;
            case 'url':
                return stripslashes($item->url);
                break;
            case 'added_date':
                return date('d-m-Y', strtotime($item->added_date));
                break;
            default:
                return print_r($item, true);
        }
    }
    /*
    * Set table column
    */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'post_id' => __('Post ID', 'mylisttable'),
            'url' => __('URL', 'mylisttable'),
            'added_date' => __('Added Date', 'mylisttable')
        );
        return $columns;
    }
    /*
    * Set sortable columns
    */
    public function get_sortable_columns() {
        return $sortable = array(
            'target_id' => array( 'target_id', true ),
            'post_id' => array( 'post_id', true ),
            'added_date' => array( 'added_date', true )
        );
    }
    /*
    * Set template name column structure
    @item - object
    */
    function column_url($item) {
        $arr_params = array( 'url' => $item->target_id, 'scrape_action' => 'topost' );
        $topostlink   = add_query_arg($arr_params);
		
        $arr_params = array( 'url' => $item->target_id, 'scrape_action' => 'ignore' );
        $ignorelink = add_query_arg($arr_params);

        $arr_params = array( 'url' => $item->target_id, 'scrape_action' => 'crawlhere' );
        $crawlherelink = add_query_arg($arr_params);
		
        $actions    = array(
            'topost' => '<a href="' . $topostlink . '">Make Post</a>',
            'ignore' => '<a href="' . $ignorelink . '">Ignore</a>',
            'crawlhere' => '<a href="' . $crawlherelink . '">Crawl</a>',
			'view' => '<a href="' . $item->url . '" target="_blank">View</a>'
        );
        return sprintf('<strong>%1$s</strong> %2$s', $item->url, $this->row_actions($actions));
    }
    /*
    * Set table bulk action
    */
    function get_bulk_actions() {
        $actions = array(
            'ignore' => 'Ignore',
			'topost' => 'Make Post'
        );
        return $actions;
    }
    /*
    * Set culumn checkbox
    * @item - object
    */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="url[]" value="%s" />', $item->target_id);
    }
    /*
    * Process action performed
	* @todo post for _params
    */
    function process_bulk_action() {
        global $scrape_data,$_param;
        if ('ignore' === $this->current_action()) {
            if (count($_param['url']) > 0) {
                foreach ($_param['url'] as $url) {
					//add ignore flag
                    //$scrape_data->update_queue($url);
                }
            }
        }
        if ('topost' === $this->current_action()) {
            if (count($_param['url']) > 0) {
                foreach ($_param['url'] as $url) {
					//$scrape_data->make_post($url);
					
					//add change import data
                    //$scrape_data->update_queue($url);
                }
            }
        }
		
    }
    /*
    * Process action performed
    */
    function process_link_action() {
        global $scrape_data;
        if (isset($_GET['scrape_action']) && $_GET['scrape_action'] == 'ignore') {
			//add ignore flag
            //$scrape_data->update_queue($_GET['url'])
        }
        if (isset($_GET['scrape_action']) && $_GET['scrape_action'] == 'topost') {
			//$scrape_data->make_post($_GET['url']);
			
			//add change import data
			//$scrape_data->update_queue($_GET['url']);
        }
        if (isset($_GET['scrape_action']) && $_GET['scrape_action'] == 'crawlhere') {
			//$scrape_data->make_post($_GET['url']);
			
			//add change import data
			//$scrape_data->update_queue($_GET['url']);
        }
    }
	
    /*
    * Prepage table items
	* @todo reduce sql
    */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();
        $this->process_bulk_action();
        $this->process_link_action();
        $query   = "SELECT * FROM " . $wpdb->prefix . "scrape_n_post_queue";
        $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'ASC';
        $order   = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : '';
        if (!empty($orderby) & !empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }
        $totalitems = $wpdb->query($query);
        $perpage    = 50;
        $paged      = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
        if (empty($paged) || !is_numeric($paged) || $paged <= 0) {
            $paged = 1;
        }
        $totalpages = ceil($totalitems / $perpage);
        if (!empty($paged) && !empty($perpage)) {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }
        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage
        ));
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array(
            $columns,
            $hidden,
            $sortable
        );
        $this->items           = $wpdb->get_results($query);
    }
}
?>