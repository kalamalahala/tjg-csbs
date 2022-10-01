<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 * @author     Tyler Karle <tyler.karle@icloud.com>
 */
class Tjg_Csbs
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tjg_Csbs_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('TJG_CSBS_VERSION')) {
			$this->version = TJG_CSBS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tjg-csbs';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tjg_Csbs_Loader. Orchestrates the hooks of the plugin.
	 * - Tjg_Csbs_i18n. Defines internationalization functionality.
	 * - Tjg_Csbs_Admin. Defines all hooks for the admin area.
	 * - Tjg_Csbs_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tjg-csbs-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-tjg-csbs-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-tjg-csbs-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-tjg-csbs-public.php';

		/**
		 * Include composer vendor/autoload.php
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
		
		$this->loader = new Tjg_Csbs_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tjg_Csbs_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Tjg_Csbs_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		// Create new instance of Tjg_Csbs_Admin
		$plugin_admin = new Tjg_Csbs_Admin($this->get_plugin_name(), $this->get_version());

		// Action hook array
		$action_hooks = array(
			'create_menu' => array( // Main Menu Item
				'hook' => 'admin_menu',
				'callback' => 'tjg_csbs_create_admin_menu',
			),
			'plugin_settings' => array(
				'hook' => 'admin_init',
				'callback' => 'tjg_csbs_register_settings',
			),
			'admin_enqueue_styles' => array( // Enqueue scripts and styles
				'hook' => 'admin_enqueue_scripts',
				'callback' => 'enqueue_styles',
			),
			'admin_enqueue_scripts' => array( // Enqueue scripts and styles
				'hook' => 'admin_enqueue_scripts',
				'callback' => 'enqueue_scripts',
			),
		); // End action_hooks array

		// Loop through action hooks and add them to the loader
		foreach ($action_hooks as $action_hook) {
			$this->loader->add_action($action_hook['hook'], $plugin_admin, $action_hook['callback']);
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Tjg_Csbs_Public($this->get_plugin_name(), $this->get_version());

		// Array of hooks, filters, and shortcodes

		$action_hooks = array(
			'scripts' => array(
				'hook' => 'wp_enqueue_scripts',
				'function' => 'enqueue_scripts',
			),
			'styles' => array(
				'hook' => 'wp_enqueue_scripts',
				'function' => 'enqueue_styles',
			),
			'primary_ajax' => array(
				'hook' => 'wp_ajax_tjg_csbs_primary_ajax',
				'function' => 'tjg_csbs_ajax_primary',
			),
			'nopriv_ajax' => array(
				'hook' => 'wp_ajax_nopriv_tjg_csbs_primary_ajax',
				'function' => 'tjg_csbs_ajax_no_priv',
			),
		);

		$shortcode_hooks = array(
			'csbs_upload_new_candidates' => 'csbs_upload_new_candidates_shortcode'
		);

		// Loop through action hooks and add them to the loader
		foreach ($action_hooks as $name => $hook_and_function) {
			$this->loader->add_action($hook_and_function['hook'], $plugin_public, $hook_and_function['function']);
		}
		
		// Loop through shortcode hooks and add them to the loader
		foreach ($shortcode_hooks as $name => $function) {
			$this->loader->add_shortcode($name, $plugin_public, $function);
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tjg_Csbs_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
