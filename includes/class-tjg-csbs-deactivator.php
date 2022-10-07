<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/kalamalahala
 * @since      1.0.0
 *
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Tjg_Csbs
 * @subpackage Tjg_Csbs/includes
 * @author     Tyler Karle <tyler.karle@icloud.com>
 */
class Tjg_Csbs_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		global $wpdb;
		$candidate_table_name = $wpdb->prefix . 'tjg_csbs_candidates';
		$log_table_name = $wpdb->prefix . 'tjg_csbs_log';

		// Drop table if exists
		$wpdb->query("DROP TABLE IF EXISTS $candidate_table_name");
		$wpdb->query("DROP TABLE IF EXISTS $log_table_name");

		// do_action('qm/debug', 'Tjg_Csbs_Deactivator::deactivate()');

	}

}
