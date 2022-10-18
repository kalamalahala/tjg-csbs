<?php

/**
 * Fired during plugin activation.
 * 
 * This class defines all code necessary to run during the plugin's activation.
 * 
 * php version 7.3.9
 * 
 * @category Core
 * @package  TJG_CSBS
 * @author   Tyler Karle <tyler.karle@icloud.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://thejohnson.group/
 * @since    1.0.0
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @category   Class
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 * @author     Tyler Karle <tyler.karle@icloud.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 * @link       https://thejohnson.group/
 * @since      1.0.0
 */
class Tjg_Csbs_Activator
{
    /**
     * Fired on plugin activation.
     *
     * Create database schema for CSBS candidate uploads.
     *
     * @since  1.0.0
     * @return void
     */
    public static function activate()
    {
        // Include upgrade.php to use dbDelta
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $candidate_table_name = $wpdb->prefix . 'tjg_csbs_candidates';
        $log_table_name = $wpdb->prefix . 'tjg_csbs_log';
        $candidate_notes_table_name = $wpdb->prefix . 'tjg_csbs_notes';
        $call_log_table_name = $wpdb->prefix . 'tjg_csbs_call_log';

        // Check if the table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$candidate_table_name'") != $candidate_table_name) {
            // Create CSBS Candidate table
            $candidate_table_query = "CREATE TABLE $candidate_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                first_name VARCHAR(255) DEFAULT NULL,
                last_name VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(10) NOT NULL,
                email VARCHAR(255) NOT NULL,
                city VARCHAR(255) DEFAULT NULL,
                state VARCHAR(255) DEFAULT NULL,
                date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                date_updated DATETIME DEFAULT NULL,
                date_worked DATETIME DEFAULT NULL,
                date_scheduled DATETIME DEFAULT NULL,
                phone_number_status VARCHAR(255) DEFAULT NULL,
                disposition VARCHAR(255) DEFAULT NULL,
                confirmed_date DATETIME DEFAULT NULL,
                rep_user_id mediumint(9) DEFAULT NULL,
                interview_date DATETIME DEFAULT NULL,
                merge_status VARCHAR(255) DEFAULT NULL,
                lead_source VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY  (id)
                ) $charset_collate;";
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") != $log_table_name) {
            // Create CSBS Log table
            $log_table = "CREATE TABLE $log_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                wp_user_id mediumint(9) NOT NULL,
                candidate_id mediumint(9) NOT NULL,
                action VARCHAR(255) NOT NULL,
                notes TEXT DEFAULT NULL,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY  (id)
                ) $charset_collate;";
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '$candidate_notes_table_name'") != $candidate_notes_table_name) {
            // Create CSBS Candidate Notes table
            $candidate_notes_table = "CREATE TABLE $candidate_notes_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                rep_user_id mediumint(9) NOT NULL,
                candidate_id mediumint(9) NOT NULL,
                notes TEXT DEFAULT NULL,
                date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY  (id)
                ) $charset_collate;";
        }

        if ($wpdb->get_var("SHOW TABLES LIKE '$call_log_table_name'") != $call_log_table_name) {
            // Create CSBS Candidate Call Log table
            $call_log_table = "CREATE TABLE $call_log_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                candidate_id mediumint(9) NOT NULL,
                rep_user_id mediumint(9) NOT NULL,
                direction VARCHAR(255) NOT NULL,
                start_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                end_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                PRIMARY KEY  (id)
                ) $charset_collate;";
        }

        // Create the table
        if (isset($candidate_table_query)) dbDelta($candidate_table_query);
        if (isset($log_table)) dbDelta($log_table);
        if (isset($candidate_notes_table)) dbDelta($candidate_notes_table);
        if (isset($call_log_table)) dbDelta($call_log_table);

        // do_action('qm/debug', 'Tjg_Csbs_Activator::activate()');
    }
}
