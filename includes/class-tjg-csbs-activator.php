<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 * @author     Tyler Karle <tyler.karle@icloud.com>
 */
class Tjg_Csbs_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'tjg_csbs_candidates';

		// Check if the table exists
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

			// Create CSBS Candidate table
			$query_sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				first_name VARCHAR(255) DEFAULT NULL,
				last_name VARCHAR(255) DEFAULT NULL,
				phone VARCHAR(10) NOT NULL,
				email VARCHAR(255) NOT NULL,
				city VARCHAR(255) DEFAULT NULL,
				state VARCHAR(255) DEFAULT NULL,
				date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				date_worked DATETIME DEFAULT NULL,
				date_scheduled DATETIME DEFAULT NULL,
				phone_number_status VARCHAR(255) DEFAULT NULL,
				disposition VARCHAR(255) DEFAULT NULL,
				confirmed_date DATETIME DEFAULT NULL,
				rep_user_id mediumint(9) DEFAULT NULL,
				interview_date DATETIME DEFAULT NULL,
				merge_status VARCHAR(255) DEFAULT NULL,
				PRIMARY KEY  (id)
				) $charset_collate;";

			// Include upgrade.php to use dbDelta
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			// Create the table
			dbDelta($query_sql);
		}

		do_action('qm/debug', 'Tjg_Csbs_Activator::activate()');
	}
}
