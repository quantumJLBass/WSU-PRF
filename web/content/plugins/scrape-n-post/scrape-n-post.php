<?php
/*
Plugin Name: Scrape-N-Post
Version: 0.1
Plugin URI: #
Description: Import content form your old site with easy
Author: Jeremy Bass
Author URI: #
*/
set_time_limit(300);
ini_set('memory_limit', '-1');
define('SCRAPE_NAME', 'Scrape-N-Post');
define('SCRAPE_BASE_NAME', 'scrape-n-post');
define('SCRAPE_VERSION', '0.1');
define('SCRAPE_URL', plugin_dir_url(__FILE__));
define('SCRAPE_PATH', plugin_dir_path(__FILE__));
define('SCRAPE_CACHE_PATH', SCRAPE_PATH . 'cache/');
define('SCRAPE_CACHE_URL', SCRAPE_URL . 'cache/');

/* things still to do
[ ]-remove the use themes templates inlue of per template css path link
[ ]-must be able to sort on optional items like tax/type etc
[•]-cache the pdfs on md5 of (tmp-ops)+(lastpost-date)+(query)
[•]-provide more areas to controll
[x]-make the index
[ ]-create ruls for the bookmarking
[ ]-create log/debug page
*/
if ( ! class_exists( 'scrapeNpostLoad' ) ) {
	$scrape_core = NULL;
	class scrapeNpostLoad {
		public function __construct() {
			global $scrape_core;
			include(SCRAPE_PATH . '/includes/class.core.php');// Include core
			$scrape_core = new scrape_core();// Instantiate core class
		}
	}
	/*
	 * Initiate the plug-in.
	 */
	register_activation_hook(__FILE__,  'scrape_N_post_initializer');
	register_deactivation_hook(__FILE__,  'scrape_N_post_remove');
	// Set option values
	function scrape_N_post_initializer() {
		global $scrape_core;
		$scrape_core->install_init();		// Call plugin initializer
	}
	// Unset option values
	function scrape_N_post_remove() {
		//delete_option('scrape_options');	// Delete plugin options
	}	 
	 
	global $scrapeNpostLoad;
	$scrapeNpostLoad = new scrapeNpostLoad();
}

?>