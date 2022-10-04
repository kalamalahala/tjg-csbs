<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/admin
 * @author     Tyler Karle <tyler.karle@icloud.com>
 */
class Tjg_Csbs_Admin
{

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tjg-csbs-admin.css', array(), $this->version, 'all');
		wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tjg-csbs-admin.js', array('jquery'), $this->version, false);
	}

	public function tjg_csbs_register_settings()
	{
		// Add section
		add_settings_section(
			'tjg_csbs_settings',
			'Cornerstone Settings',
			array($this, 'tjg_csbs_settings_header'),
			'tjg-csbs-admin-settings'
		);

		// Sendgrid API Key
		add_settings_field(
			'tjg_csbs_sendgrid_api_key',
			'SendGrid API Key',
			array($this, 'tjg_csbs_settings_field_sendgrid_API_key'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Number of candidates to display on main page
		add_settings_field(
			'tjg_csbs_num_candidates',
			'Number of Candidates to Display',
			array($this, 'tjg_csbs_settings_field_num_candidates'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Gravity Forms ID for Candidates
		add_settings_field(
			'tjg_csbs_gravity_forms_id',
			'Gravity Forms ID for Candidates',
			array($this, 'tjg_csbs_settings_field_gravity_forms_id'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Register setting
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_sendgrid_api_key'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_num_candidates'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_gravity_forms_id'
		);
	}

	public function tjg_csbs_settings_header()
	{
		if ($_GET['page'] !== 'tjg-csbs-admin-settings') {
			return;
		}

		// Handle $_GET requests here
		if (isset($_GET['settings-updated'])) {
			echo '<div class="row">';
			echo '<div class="notice notice-success is-dismissible" col-md-5"><p>Settings updated.</p></div>';
			echo '</div>';
		}
	}

	public function tjg_csbs_settings_field_sendgrid_API_key()
	{
		$api_key = get_option('tjg_csbs_sendgrid_api_key');
		// $sendgrid_api_key = $options['tjg_csbs_sendgrid_api_key'];
		echo '<input type="text" id="tjg_csbs_sendgrid_api_key" name="tjg_csbs_sendgrid_api_key" value="' . $api_key .'">';
	}

	public function tjg_csbs_settings_field_num_candidates()
	{
		$num_candidates = get_option('tjg_csbs_num_candidates');
		echo '<input type="number" id="tjg_csbs_num_candidates" name="tjg_csbs_num_candidates" value="' . $num_candidates .'">';
	}

	public function tjg_csbs_settings_field_gravity_forms_id()
	{
		$gravity_forms_id = get_option('tjg_csbs_gravity_forms_id');
		echo '<input type="number" id="tjg_csbs_gravity_forms_id" name="tjg_csbs_gravity_forms_id" value="' . $gravity_forms_id .'">';
	}
	

	public function tjg_csbs_create_admin_menu()
	{
		add_menu_page(
			'Cornerstone Business Solutions',
			'Cornerstone',
			'manage_options',
			'tjg-csbs-admin',
			array($this, 'tjg_csbs_admin_main_page'),
			'dashicons-menu-alt3',
			3
		);
		add_submenu_page(
			'tjg-csbs-admin',
			'CSBS Settings',
			'Settings',
			'manage_options',
			'tjg-csbs-admin-settings',
			array($this, 'tjg_csbs_admin_settings_page')
		);
	}

	public function tjg_csbs_admin_main_page()
	{
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'partials/tjg-csbs-admin-display.php';
		echo ob_get_clean();
	}

	public function tjg_csbs_admin_settings_page()
	{
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'partials/tjg-csbs-admin-settings.php';
		echo ob_get_clean();
	}
}
