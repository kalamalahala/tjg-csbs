<?php

/** 
 * If this file is called directly, abort.
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use Vonage\Client\Credentials\Basic;
use Tjg_Csbs_Sendgrid as Tjg_Sendgrid;
class Tjg_Csbs_Settings {

    private $plugin_name;
    private $version;

	// Vonage settings
	// private $vonage_api_key;
	// private $vonage_api_secret;

    public function __construct( $plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Create settings fields
        $this->do_settings();
    }

    public function do_settings() {
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

		// Sendgrid From Email
		add_settings_field(
			'tjg_csbs_sendgrid_email_from',
			'SendGrid From Email',
			array($this, 'tjg_csbs_settings_field_sendgrid_email_from'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Sendgrid From Name
		add_settings_field(
			'tjg_csbs_sendgrid_email_from_name',
			'SendGrid From Name',
			array($this, 'tjg_csbs_settings_field_sendgrid_email_from_name'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Sendgrid Template ID
		add_settings_field(
			'tjg_csbs_sendgrid_template_id',
			'SendGrid Template ID',
			array($this, 'tjg_csbs_settings_field_sendgrid_template_id'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Sendgrid Voicemail Template ID
		add_settings_field(
			'tjg_csbs_sendgrid_voicemail_template_id',
			'SendGrid Voicemail Template ID',
			array($this, 'tjg_csbs_settings_field_sendgrid_voicemail_template_id'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Sendgrid API Verification Key
		add_settings_field(
			'tjg_csbs_sendgrid_api_verification_key',
			'SendGrid API Verification Key',
			array($this, 'tjg_csbs_settings_field_sendgrid_api_verification_key'),
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

		// Twilio SID
		add_settings_field(
			'tjg_csbs_twilio_sid',
			'Twilio SID',
			array($this, 'tjg_csbs_settings_field_twilio_SID'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Twilio Auth Token
		add_settings_field(
			'tjg_csbs_twilio_token',
			'Twilio Auth Token',
			array($this, 'tjg_csbs_settings_field_twilio_auth_token'),
			'tjg-csbs-admin-settings',
			'tjg_csbs_settings'
		);

		// Twilio Messaging Service SID
		add_settings_field(
			'tjg_csbs_twilio_msid',
			'Twilio Messaging Service SID',
			array($this, 'tjg_csbs_settings_field_twilio_messaging_service_sid'),
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
			'tjg_csbs_sendgrid_email_from'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_sendgrid_email_from_name'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_sendgrid_template_id'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_sendgrid_voicemail_template_id'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_sendgrid_api_verification_key'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_num_candidates'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_gravity_forms_id'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_twilio_sid'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_twilio_token'
		);
		register_setting(
			'tjg_csbs_option_group',
			'tjg_csbs_twilio_msid'
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
		echo '<input type="password" id="tjg_csbs_sendgrid_api_key" name="tjg_csbs_sendgrid_api_key" value="' . $api_key . '">';
		echo $api_key;
	}

	public function tjg_csbs_settings_field_sendgrid_email_from()
	{
		$email_from = get_option('tjg_csbs_sendgrid_email_from');
		echo '<input type="text" id="tjg_csbs_sendgrid_email_from" name="tjg_csbs_sendgrid_email_from" value="' . $email_from . '">';
	}

	public function tjg_csbs_settings_field_sendgrid_email_from_name()
	{
		$email_from_name = get_option('tjg_csbs_sendgrid_email_from_name');
		echo '<input type="text" id="tjg_csbs_sendgrid_email_from_name" name="tjg_csbs_sendgrid_email_from_name" value="' . $email_from_name . '">';
	}

	public function tjg_csbs_settings_field_sendgrid_template_id()
	{
		$template_id = get_option('tjg_csbs_sendgrid_template_id');
		echo '<input type="text" id="tjg_csbs_sendgrid_template_id" name="tjg_csbs_sendgrid_template_id" value="' . $template_id . '">';
	}

	public function tjg_csbs_settings_field_sendgrid_voicemail_template_id()
	{
		$template_id = get_option('tjg_csbs_sendgrid_voicemail_template_id');
		echo '<input type="text" id="tjg_csbs_sendgrid_voicemail_template_id" name="tjg_csbs_sendgrid_voicemail_template_id" value="' . $template_id . '">';
	}

	public static function tjg_csbs_settings_field_sendgrid_api_verification_key()
	{
		$api_verification_key = get_option('tjg_csbs_sendgrid_api_verification_key');
		echo '<input type="password" id="tjg_csbs_sendgrid_api_verification_key" name="tjg_csbs_sendgrid_api_verification_key" value="' . $api_verification_key . '">';
	}

	public function tjg_csbs_settings_field_num_candidates()
	{
		$num_candidates = get_option('tjg_csbs_num_candidates');
		echo '<input type="number" id="tjg_csbs_num_candidates" name="tjg_csbs_num_candidates" value="' . $num_candidates . '">';
	}

	public function tjg_csbs_settings_field_gravity_forms_id()
	{
		$gravity_forms_id = get_option('tjg_csbs_gravity_forms_id');
		echo '<input type="number" id="tjg_csbs_gravity_forms_id" name="tjg_csbs_gravity_forms_id" value="' . $gravity_forms_id . '">';
	}

	public function tjg_csbs_settings_field_twilio_SID() {
		$twilio_sid = get_option('tjg_csbs_twilio_sid');
		echo '<input type="text" id="tjg_csbs_twilio_sid" name="tjg_csbs_twilio_sid" value="' . $twilio_sid . '">';
	}

	public function tjg_csbs_settings_field_twilio_auth_token() {
		$twilio_token = get_option('tjg_csbs_twilio_token');
		echo '<input type="password" id="tjg_csbs_twilio_token" name="tjg_csbs_twilio_token" value="' . $twilio_token . '">';
	}

	public function tjg_csbs_settings_field_twilio_messaging_service_sid() {
		$twilio_msid = get_option('tjg_csbs_twilio_msid');
		echo '<input type="text" id="tjg_csbs_twilio_msid" name="tjg_csbs_twilio_msid" value="' . $twilio_msid . '">';
	}


}