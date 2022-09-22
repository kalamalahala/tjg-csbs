<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/public
 * @author     Tyler Karle <tyler.karle@icloud.com>
 */
class Tjg_Csbs_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tjg_Csbs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tjg_Csbs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Don't load stylesheets unless inside /csb/ URI path

		$current_URI = $_SERVER['REQUEST_URI'];
		$URI = explode('/', $current_URI);
		$csb = in_array('csb', $URI);

		if ($csb) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tjg-csbs-public.css', array(), $this->version, 'all' );
			// Add bootstrap CSS
			wp_enqueue_style( 'tjg-csbs-bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		} else {
			// do nothing
		}


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tjg_Csbs_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tjg_Csbs_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tjg-csbs-public.js', array( 'jquery' ), $this->version, false );
		
		// AJAX for New Candidates Upload
		wp_localize_script( $this->plugin_name, 'tjg_csbs_ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'tjg_csbs_nonce' )
		));

		// Add boostrap JS bundle
		// Exit script loading unless csb is inside the URL
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode('/', $uri);
		$uri = in_array('csb', $uri);
		
		if ($uri) {
			wp_enqueue_script( 'tjg-csbs-bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, false );
		} else {
			// do nothing
		}

	}

	/**
	 * AJAX handler/router for the TJG CSBS Plugin
	 * 
	 * csbs_ajax()
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function tjg_csbs_ajax_primary() {
		// Check nonce
		$verify = check_ajax_referer( 'tjg_csbs_nonce', 'nonce' );
		if ( $verify == false ) {
			wp_send_json_error( 'Nonce verification failed' );
		}
		
		if ( ! isset( $_POST['method'] ) ) {
			wp_send_json_error( 'No method specified' );
		}
		// Check for ajax method
		$method = $_POST['method'];
		$output = '';

		// log to qm/debug bar the contents of $_POST and $_FILES

		foreach ($_POST as $key => $value) {
			do_action('qm/debug', $key . ' => ' . $value);
		}

		foreach ($_FILES as $key => $value) {
			do_action('qm/debug', $key . ' => ' . $value);
		}
		// Send output
		wp_send_json_success( $output );
		die();
		
		// Switch on method
		switch ( $method ) {
			case 'upload_new_candidates':
				$output = $this->tjg_csbs_ajax_parse_spreadsheet($_POST['file']);
				break;
			default:
				wp_send_json_error( 'Invalid method' );
				break;
		}
		
	}

	/**
	 * Handle New Candidate Upload
	 */
	public function tjg_csbs_ajax_parse_spreadsheet($candidate_file) {
		wp_send_json_success( 'Method successfully called' );
		die();
	}

	// Begin Shortcode inclusions

	// Shortcode for new candidate form
	function csbs_upload_new_candidates_shortcode() {
		// Include the form

		include plugin_dir_path( dirname( __FILE__ ) ) . 'public/shortcodes/tjg-csbs-upload-new-candidates.php';
		$output = new_candidate_form();
		return $output;
	}

}
