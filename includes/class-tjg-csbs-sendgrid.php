<?php

/**
 * SendGrid API Class for WordPress
 * 
 * Handles all SendGrid API calls and webhooks
 * 
 * @package Tjg_Csbs
 * @subpackage Tjg_Csbs_Sendgrid
 * @version 1.0.0
 * @since 1.0.0
 * @link https://sendgrid.com/docs/for-developers/sending-email/api-getting-started/
 * @link https://sendgrid.com/docs/for-developers/tracking-events/event/
 * @link https://sendgrid.com/docs/for-developers/tracking-events/getting-started-event-webhook/
 */

require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';
use SendGrid\Mail\Mail as Mail;
use SendGrid\Mail\TypeException as TypeException;
use SendGrid\Mail\SendGridException as SendGridException;

class Tjg_Csbs_Sendgrid {

    private $api_key;

    public function __construct($api_key = null) {
        if ($api_key) {
            $this->api_key = $api_key;
        } else {
            $this->api_key   = get_option('tjg_csbs_sendgrid_api_key');
        }
        
        $this->from_email    = get_option('tjg_csbs_sendgrid_email_from');
        $this->from_name     = get_option('tjg_csbs_sendgrid_email_from_name');
    }

    /**
     * Retrieve Transactional Templates
     * 
     * List all transactional templates available in your account
     * and their associated IDs, names, and versions.
     * 
     * @link https://docs.sendgrid.com/api-reference/transactional-templates/retrieve-paged-transactional-templates
     * 
     * @return array
     */
    public function get_templates() {
        $api_key = $this->api_key;
        $sg = new \SendGrid($api_key);
        $dynamic = json_decode(
            '{
                "generations": "dynamic",
                "limit": 100,
                "page_size": 100
            }'
        );

        var_dump($sg);

        try {
            $response = $sg->client->templates()->get(null, $dynamic);
            print $code = $response->statusCode() . '\n';
            echo '<pre>';
            print_r($body = $response->body());
            echo '</pre>';
            print $headers = $response->headers() . '\n';
        } catch (Exception $e) {
            error_log('Error retrieving SendGrid templates: ' . $e->getMessage());
            echo 'Caught exception: ', $e->getMessage();
        }
    }

    /**
     * Send an email using SendGrid
     * 
     * Generated by GitHub Copilot (ALPHA ALPHA ALPHA)
     * 
     * @param object $candidate
     * @param string $subject
     * @param string $template_id
     * @param array $personalization
     * 
     * 
     * @return bool
     */
    public function send_email( Candidate $candidate, string $subject, string $template_id, array $personalization ) {
        error_log('SendGrid API Key: ' . $this->api_key . 'send_email()');
        // get plugin settings for SendGrid
        $api_key = $this->api_key;
        $from_email = $this->from_email;
        $from_name = $this->from_name;

        $to_name = $candidate->first_name . ' ' . $candidate->last_name;
        $to_email = $candidate->email;

        $mail = new Mail();
        $mail->setFrom($from_email, $from_name);
        $mail->setSubject($subject);
        $mail->addTo($to_email, $to_name);
        $mail->setTemplateId($template_id);

        // add personalization
        if (is_array($personalization)) {
            foreach ($personalization['dynamic_template_data'] as $key => $value) {
                $mail->addDynamicTemplateData($key, $value);
            }
        }

        error_log('Sending email to ' . $to_email . ' using template ' . $template_id);
        error_log('Personalization: ' . print_r($personalization, true));
        error_log('From: ' . $from_email . ' (' . $from_name . ')');
        error_log('Subject: ' . $subject);
        error_log('To: ' . $to_email . ' (' . $to_name . ')');
        error_log(print_r($mail, true));

        $sendgrid = new \SendGrid($api_key);
        try {
            $response = $sendgrid->send($mail);
            error_log('SendGrid response: ' . print_r($response, true));
            return true;
        } catch (TypeException $e) {
            error_log('TypeException sending email: ' . $e->getMessage());
            return false;
        } catch (SendGridException $e) {
            error_log('SendGridException sending email: ' . $e->getMessage());
            return false;
        }

    }

    public function get_api_key() {
        return $this->api_key;
    }

}