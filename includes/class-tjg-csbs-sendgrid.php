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
            $this->api_key = get_option('tjg_csbs_sendgrid_api_key');
        }        
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

        var_dump($sg);

        try {
            $response = $sg->client->templates()->get(null);
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
     * @param string $from_email
     * @param string $from_name
     * @param string $to_email
     * @param string $to_name
     * @param string $subject
     * @param string $content
     * @param string $content_type
     * @return bool
     */
    public function send_email($from_email, $from_name, $to_email, $to_name, $subject, $content, $content_type = 'text/plain') {
        $mail = new Mail();
        $mail->setFrom($from_email, $from_name);
        $mail->setSubject($subject);
        $mail->addTo($to_email, $to_name);
        $mail->addContent($content_type, $content);

        $sendgrid = new \SendGrid($this->api_key);
        try {
            $response = $sendgrid->send($mail);
            return true;
        } catch (TypeException $e) {
            return false;
        } catch (SendGridException $e) {
            return false;
        }

    }

    public function get_api_key() {
        return $this->api_key;
    }

}