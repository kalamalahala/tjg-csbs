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

require_once plugin_dir_path(dirname(__FILE__)) . '/includes/class-tjg-csbs-methods.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/includes/class-tjg-csbs-sendgrid.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/includes/settings/tjg-csbs-settings.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/includes/settings/tjg-csbs-menu.php';
require_once plugin_dir_path(dirname(__FILE__)) . '/vendor/autoload.php';

use Tjg_Csbs_Common as Common;
use Tjg_Csbs_Settings as Settings;
use Tjg_Csbs_Menu as Menu;
use Tjg_Csbs_Sendgrid as Sendgrid_Handler;
use EllipticCurve\ECDSA as ECDSA;
use EllipticCurve\PublicKey as PublicKey;
use EllipticCurve\Signature as Signature;
// use Vonage\Client\Credentials\Basic as Basic;


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

		// Check for the page we want to load the styles on
		if (!isset($_GET['page'])) return;
		global $pagenow;
		$csbs_admin = str_contains($_GET['page'], 'tjg-csbs');
		if ($pagenow == 'admin.php' && $csbs_admin) {
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tjg-csbs-admin.css', array(), $this->version, 'all');
			wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
			wp_enqueue_style('dataTables', plugin_dir_url(__FILE__) . 'datatables/datatables.min.css', array(), $this->version, 'all');
			wp_enqueue_style('fontawesome4', plugin_dir_url(__FILE__) . '../includes/css/font-awesome.css', array(), $this->version, 'all');
			wp_enqueue_style('busy-app', plugin_dir_url(__FILE__) . 'css/busy-app.min.css', array(), $this->version, 'all');
		}
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

		// Only load resources if in a plugin page containing tjg-csbs
		if (!isset($_GET['page'])) return;
		global $pagenow;
		$csbs_admin = str_contains($_GET['page'], 'tjg-csbs');
		$upload = str_contains($_GET['page'], 'tjg-csbs-admin-upload');
		if ($pagenow == 'admin.php' && $csbs_admin) {
			wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
			wp_enqueue_script('dataTables', plugin_dir_url(__FILE__) . 'datatables/datatables.min.js', array('jquery'), $this->version, false);
			wp_enqueue_script('busy-app', plugin_dir_url(__FILE__) . 'js/busy-app.min.js', array('jquery'), $this->version, false);
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tjg-csbs-admin.js', array('jquery'), $this->version, false);
			($upload) ? wp_enqueue_script('upload', plugin_dir_url(__FILE__) . 'js/tjg-csbs-upload.js', array('jquery'), $this->version, false) : null;

			// Collect CSBS Agent IDs and Names to pass to JS
			$common = new Common();
			$agents = $common->get_agents();
			// Lookup each agent's name and map to $agent->id
			$agent_names = array();
			foreach ($agents as $agent) {
				$name = $common->get_agent_name($agent->id);
				$agent_names[$agent->id] = $name;
			}

			// Pass to JS
			wp_localize_script($this->plugin_name, 'ajax_object', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'action' => 'tjg_csbs_admin',
				'nonce' => wp_create_nonce('tjg_csbs_nonce'),
				'agent_list' => $agent_names
			));
		}
	}

	#region Admin Side AJAX Handler ###############################################
	/**
	 * Handles AJAX requests from the admin side
	 * 
	 * @since 1.0.0
	 */
	public function tjg_csbs_admin_ajax_handler()
	{
		// Collect nonce
		$nonce = $_POST['nonce'] ?? $_GET['nonce'] ?? null;
		if (!wp_verify_nonce($nonce, 'tjg_csbs_nonce')) {
			wp_send_json_error('Invalid nonce');
		}

		// Check for user permissions
		$tjg_csbs_admin = (current_user_can('tjg_csbs_admin') || current_user_can('tjg_csbs_agent'));
		if (!$tjg_csbs_admin) {
			wp_send_json_error('You do not have permission to do this');
		}

		// Load method handler
		$common = new Common();
		// $payload = [];




		// Collect variables from POST or GET
		$method 			= $_POST['method'] ?? $_GET['method'] ?? null;
		$agent_id 			= $_POST['agent_id'] ?? $_GET['agent_id'] ?? null;
		$candidate_data 	= array(
			'id'			=> $_POST['candidate_id'] ?? $_GET['candidate_id'] ?? null,
			'first_name' 	=> $_POST['first_name'] ?? $_GET['first_name'] ?? null,
			'last_name' 	=> $_POST['last_name'] ?? $_GET['last_name'] ?? null,
			'email' 		=> $_POST['email_address'] ?? $_GET['email_address'] ?? null,
			'phone' 		=> $_POST['phone_number'] ?? $_GET['phone_number'] ?? null,
			'city' 			=> $_POST['city'] ?? $_GET['city'] ?? null,
			'state' 		=> $_POST['state'] ?? $_GET['state'] ?? null,
			'lead_source' 	=> $_POST['lead_source'] ?? $_GET['lead_source'] ?? null,
		);


		$candidate_to_email = $_POST['candidate_to_email'] ?? $_GET['candidate_to_email'] ?? null;


		// Check for method
		if (!isset($method)) {
			wp_send_json_error('No method specified');
		}

		// Handle method
		switch ($method) {
			case 'get_candidates':
				$payload = $common->get_candidates();
				break;
			case 'delete_candidate':
				$payload[] = $common->delete_candidate($_POST['id']);
				break;
			case 'update_candidate':
				// wp_send_json_error($candidate_data, 400);
				$candidate_update = new Candidate($candidate_data['id']);
				$candidate_update->set_first_name($candidate_data['first_name']);
				$candidate_update->set_last_name($candidate_data['last_name']);
				$candidate_update->set_email($candidate_data['email']);
				$candidate_update->set_phone($candidate_data['phone']);
				$candidate_update->set_city($candidate_data['city']);
				$candidate_update->set_state($candidate_data['state']);
				$payload[] = $candidate_update->save();
				break;
			case 'get_spreadsheet_summary':
				$file = $_FILES['file'] ?? null;
				if (is_null($file)) wp_send_json_error('No file specified');
				$payload[] = $common->tjg_csbs_ajax_get_spreadsheet_summary($file);
				break;
			case 'upload_new_candidates':
				$file = $_FILES['file'] ?? null;
				if (is_null($file)) wp_send_json_error('No file specified');
				$mode = $_POST['mode'] ?? null;
				if (is_null($mode)) wp_send_json_error('No mode specified');
				$selected_columns = json_decode(stripslashes($_POST['selectData'])) ?? null;
				$payload[] = $common->tjg_csbs_ajax_parse_spreadsheet(
					$file,
					$selected_columns,
					$mode
				);
				break;
				// create single candidate form
			case 'create_single_candidate':
				if (is_null($candidate_data)) wp_send_json_error('No $candidate_data specified', 400);
				$new_candidate = new Candidate();
				$new_candidate->set_first_name($candidate_data['first_name']);
				$new_candidate->set_last_name($candidate_data['last_name']);
				$new_candidate->set_email($candidate_data['email']);
				$new_candidate->set_phone($candidate_data['phone']);
				$new_candidate->set_city($candidate_data['city']);
				$new_candidate->set_state($candidate_data['state']);
				$new_candidate->set_lead_source($candidate_data['lead_source']);
				$payload[] = $new_candidate->save();
				break;

				// Send error message if Phone or Email exist, or if both exist send existing candidate
				$existing_phone = Candidate::phone_exists($new_candidate->phone);
				$existing_email = Candidate::email_exists($new_candidate->email);
				$phone_error = 'Phone number';
				$email_error = 'Email address';
				$append = ' already exists.';

				if ($existing_phone && $existing_email) { // 'Phone number and email address already exist'
					wp_send_json_error($phone_error . ' and ' . $email_error . $append, 400);
				} elseif ($existing_phone) { // 'Phone number already exists'
					wp_send_json_error($phone_error . $append, 400);
				} elseif ($existing_email) { // 'Email address already exists'
					wp_send_json_error($email_error . $append, 400);
				}

				// Save new candidate
				$new_candidate->save();
				$payload[] = $new_candidate;
				break;
			case 'assign_candidate':
				$payload[] = $common->assign_candidate($_POST['agent_id'], $_POST['candidate_ids']);
				break;
			case 'get_agents':
				$payload[] = $common->get_agents();
				break;
			case 'get_agent_name':
				$payload[] = $common->get_agent_name($agent_id);
				break;
			case 'get_sendgrid_templates':
				$payload[] = $common->get_sendgrid_templates();
				break;
			case 'send_bulk_sms':
				$numbers = $_POST['numbers'] ?? null;
				$message = $_POST['message'] ?? null;
				foreach ($numbers as $number) {
					$payload[] = $common->twilio_message($number, $message);
				}
				break;
			case 'send_confirmation_email':
				// require candidate id
				$candidate_id = $candidate_to_email ?? null;
				if (is_null($candidate_id)) wp_send_json_error('No candidate id specified');
				$candidate = new Candidate($candidate_id);
				$payload[] = $common->sendgrid_email_send_confirmation($candidate, 'd-360a649159244606804328a383d9a9fc', 'Candidate Confirmation', 'https://thejohnson.group/');
				break;
			default:
				wp_send_json_error('Invalid method');
				break;
		}

		// Send response
		if (!empty($payload)) {
			wp_send_json_success($payload);
		} else {
			wp_send_json_error('Payload empty, but checks passed.');
		}
	}


	#endregion

	#region Settings and Menu Configuration #######################################
	public function tjg_csbs_register_settings()
	{
		$settings = new Settings($this->plugin_name, $this->version);
	}

	public function tjg_csbs_create_admin_menu()
	{
		$menu = new Menu($this->plugin_name, $this->version);
	}
	#endregion Settings and Menu Configuration ###################################

	#region Roles and Capabilities ################################################

	/**
	 * Adds the necessary roles
	 * 
	 * @since 1.0.0
	 */
	public function tjg_csbs_add_roles()
	{
		add_role(
			'tjg_csbs_admin',
			'CSBS Admin',
			array(
				'read' => true,
				'upload_files' => true,
			)
		);
		add_role(
			'tjg_csbs_agent',
			'CSBS Agent',
			array(
				'read' => true,
			)
		);
	}

	/**
	 * Adds the necessary capabilities
	 * 
	 * @since 1.0.0
	 */

	public function tjg_csbs_add_capabilities()
	{
		$role = get_role('tjg_csbs_admin');
		$role->add_cap('tjg_csbs_admin', true);

		$role = get_role('administrator');
		$role->add_cap('tjg_csbs_admin', true);

		$role = get_role('tjg_csbs_agent');
		$role->add_cap('tjg_csbs_agent', true);
	}

	#endregion Roles and Capabilities #############################################

	#region Sendgrid Webhook Callback #############################################
	public function tjg_csbs_sendgrid_webhook_handler()
	{
		// Signed events seem to be failing, might require a paid account

		// In the mean time, try unsigned and check php://input
		error_log('Sendgrid Webhook Handler');
		error_log(print_r(file_get_contents('php://input'), true));

		$webhook_data 			= json_decode(file_get_contents('php://input'), true);
		// error_log(print_r($webhook_data, true));
		// return false;

		foreach ($webhook_data as $request) {
			$webhook_timestamp 		= $request['timestamp'];
			$merge 					= $request['event'];
			$email 					= $request['email'];
			$message 				= $request['sg_message_id'];
		}

		$common 	= new Common();
		$candidate 	= $common->get_candidate_by_email($email);

		$candidate_id = $candidate->id;

		$error_log_fields = array(
			'webhook_data' => $webhook_data,
			'webhook_timestamp' => $webhook_timestamp,
			'merge' => $merge,
			'email' => $email,
			'message' => $message,
			'candidate' => $candidate,
			'candidate_id' => $candidate_id,
		);

		// error_log(print_r($error_log_fields, true));
		// return false;

		// Update candidate merge_status, sg_message_id, and sg_timestamp
		$merge_update = $common->tjg_csbs_update_candidate_merge_status($candidate_id, $merge, $message, $webhook_timestamp);

		// Add merge to primary log database
		$timestamp_date = date('Y-m-d H:i:s', $webhook_timestamp);
		$log = $common->tjg_csbs_create_log_entry($candidate->rep_user_id, $candidate->id, $merge, $timestamp_date);


		if ($merge_update === false || is_string($merge_update)) {
			error_log('Error updating candidate merge status');
			error_log(print_r($error_log_fields, true));
			error_log(print_r($merge_update, true));
			wp_send_json_error($merge_update);
		} else {
			error_log('Candidate merge status appears to be updated');
			error_log(print_r($error_log_fields, true));
		}

		return true;






		// Get Header Signature and Timestamp
		$signature = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE'] ?? null;
		$timestamp = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP'] ?? null;

		// Create PublicKey ECDSA Object from Verification Key
		$public_key = PublicKey::fromString(Common::sendgrid_verification_key());

		// Get the raw body
		$payload = json_decode(file_get_contents('php://input'), true);
		$event  = $payload['event'] ?? null;

		// try sending raw payload instead
		$payload = file_get_contents('php://input');

		// Verify the signature
		$check = $this->tjg_csbs_sendgrid_webhook_verify($public_key, $payload, $signature, $timestamp);


		// If the signature is valid, process the event
		if ($check) {
			error_log('Signature is valid');
			// Process the event
			error_log('Processing event: ' . $event);
		} else {
			error_log('Invalid signature');
			error_log(print_r($payload, true));
		}
	}

	public function tjg_csbs_sendgrid_webhook_verify($public_key, $payload_event, $signature, $timestamp)
	{
		// append timestamp to payload
		$timestamp_payload = $payload_event . $timestamp;

		error_log('Timestamp Payload: ' . $timestamp_payload);

		// Decode signature
		$decode_signature = Signature::fromBase64($signature);

		try {
			// Verify the signature
			$check = ECDSA::verify($timestamp_payload, $decode_signature, $public_key);
			return $check;
		} catch (Exception $e) {
			error_log('Error verifying signature: ' . $e->getMessage());
			return false;
		}

		// Verify signature
		// return ECDSA::verify($timestamp_payload, $decode_signature, $public_key);
	}
}
