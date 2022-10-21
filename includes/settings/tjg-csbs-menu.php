<?php

/**
 * If this file is called directly, abort.
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Tjg_Csbs_Menu class.
 * 
 * Handles admin menu construction and routing.
 * 
 * @since       1.0.0
 * @package     tjg-csbs
 * @subpackage  tjg-csbs/includes
 * @author      Tyler Karle <tyler.karle@icloud.com>
 * 
 * @see         Tjg_Csbs_Admin
 */

class Tjg_Csbs_Menu
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->admin_menus();
    }

    public function admin_menus()
    {
        global $submenu;
        add_menu_page(
            'Cornerstone Business Solutions',
            'Cornerstone',
            'tjg_csbs_admin',
            'tjg-csbs-admin',
            array($this, 'tjg_csbs_admin_main_page'),
            'dashicons-menu-alt3',
            3
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'Upload Candidates',
            'Upload Candidates',
            'tjg_csbs_admin',
            'tjg-csbs-admin-upload',
            array($this, 'tjg_csbs_admin_upload_page')
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'Create Candidate',
            'Create Candidate',
            'tjg_csbs_admin',
            'tjg-csbs-admin-create',
            array($this, 'tjg_csbs_admin_create_page')
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'View Candidate',
            'View Candidate',
            'tjg_csbs_admin',
            'tjg-csbs-admin-view-candidate',
            array($this, 'tjg_csbs_admin_view_candidate_page')
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'CSBS Settings',
            'Settings',
            'tjg_csbs_admin',
            'tjg-csbs-admin-settings',
            array($this, 'tjg_csbs_admin_settings_page')
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'Plugin Playground', 
            'Scratch Pad', 
            'tjg_csbs_admin', 
            'tjg-csbs-scratch', 
            array($this, 'scratch_pad')
        );
        add_submenu_page(
            'tjg-csbs-admin',
            'Bulk Message',
            'Bulk Message',
            'tjg_csbs_admin',
            'tjg-csbs-admin-bulk-message',
            array($this, 'tjg_csbs_admin_bulk_message_page')
        );
        $submenu['tjg-csbs-admin'][0][0] = 'Candidate List';
    }

    public function tjg_csbs_admin_main_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/tjg-csbs-admin-display.php';
        echo ob_get_clean();
    }

    public function tjg_csbs_admin_settings_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/tjg-csbs-admin-settings.php';
        echo ob_get_clean();
    }

    public function tjg_csbs_admin_upload_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/layout-new-candidates-upload.php';
        echo ob_get_clean();
    }

    public function tjg_csbs_admin_create_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/CRUD/create-candidate.php';
        echo ob_get_clean();
    }

    public function tjg_csbs_admin_view_candidate_page()
    {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/CRUD/view-candidate.php';
        echo ob_get_clean();
    }

    public function scratch_pad() {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/layout-data-scratch.php';
        echo ob_get_clean();
    }

    public function tjg_csbs_admin_bulk_message_page() {
        ob_start();
        include_once plugin_dir_path(__FILE__) . 'layouts/bulk-message.php';
        echo ob_get_clean();
    }
}
