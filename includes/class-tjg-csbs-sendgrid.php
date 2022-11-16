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
use SendGrid\Mail\TypeException;
use SendGrid\Mail\SendGridException;
use Eluceo\iCal\Domain\ValueObject\Attachment;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\Entity\Attendee;
use Eluceo\iCal\Domain\Enum\ParticipationStatus;
use Eluceo\iCal\Domain\Enum\RoleType;
use Eluceo\iCal\Domain\Enum\CalendarUserType;

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
    
    public function get_api_key() {
        return $this->api_key;
    }

    public static function api_key() {
        return get_option('tjg_csbs_sendgrid_api_key');
    }

    public static function sender_details() {
        $from_email = get_option('tjg_csbs_sendgrid_email_from');
        $from_name  = get_option('tjg_csbs_sendgrid_email_from_name');
        return array(
            'from_email' => $from_email,
            'from_name'  => $from_name
        );
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
        } catch (Exception $e) {
            error_log('SendGridException sending email: ' . $e->getMessage());
            return false;
        }

    }

    public static function send_webinar_link(array $data = [])
    {
       $key = self::api_key();
       // error_log('API Key: ' . $key);
       $mail = new Mail();
       $sender = self::sender_details();
       $to_email = $data['email-address'];
       $to_name = $data['full-name'];
       $briefing_id = $data['select-briefing'];
       $briefing = new TwilioCSVBriefing($briefing_id);
       $briefing_link = $briefing->_weblink;
       $briefing_time = $briefing->_scheduled;
       $briefing_friendly_time = date('l, F jS, Y \a\t g:i A', strtotime($briefing_time));
       $mail->setFrom($sender['from_email'], $sender['from_name']);
       $mail->setSubject('Webinar Link');
       $mail->addTo($to_email, $to_name);
       $mail->addContent(
          'text/html',
          '<p>Hi There,</p><p>You are invited to a Zoom webinar.<br />When: ' . $briefing_friendly_time . '<br />Topic: Manager In Training</p>
          <p>Register in advance for this webinar: <a href="' . $briefing_link . '">' . $briefing_link . '</a></p><p>Regards,</p>
          <img class="alignnone wp-image-2276" src="https://thejohnson.group/wp-content/uploads/2021/02/BlackTextLogo.png" alt="" width="106" height="69" />
          <br /><span style="font-size: 10pt;">Email: <a href="info@thejohnson.group">info@thejohnson.group</a></span>
          <br /><span style="font-size: 10pt;">Phone: <a href="tel:+13863013703">(386) 301-3703</a></span>
          <br /><span style="font-size: 10pt;"><mark><strong>Note: This email is intended only for internal use. If you have received this email in error, please discard it and notify the admin. Thank you.</strong></mark></span>'
       );
 
       $briefing_DTI = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $briefing_time);
       $briefing_DTI_one_hour_later = $briefing_DTI->add(new DateInterval('PT1H'));
       // $briefing_DTI = DateTime::createFromFormat('Y-m-d H:i:s', $briefing_time);
       // $briefing_DTI_one_hour_later = $briefing_DTI->add(new DateInterval('PT1H'));
       $briefing_ical = self::ical($briefing_DTI, $briefing_DTI_one_hour_later, $briefing_link, $to_name, $to_email, $sender['from_name'], $sender['from_email']);
       $ical_encoded = base64_encode(file_get_contents(plugin_dir_path(__FILE__) . 'assets/clb.ics'));
       $mail->addAttachment(
          $ical_encoded,
          'text/calendar',
          'clb.ics',
          'attachment'
       );
 
       $sendgrid = new \SendGrid($key);
       try {
          $response = $sendgrid->send($mail);
          printf("Response status: %d\n\n", $response->statusCode());
 
          $headers = array_filter($response->headers());
          echo "Response Headers\n\n";
          foreach ($headers as $header) {
             echo '- ' . $header . "\n";
          }
       } catch (Exception $e) {
          echo 'Caught exception: ' . $e->getMessage() . "\n";
       }
    }

    public static function ical($start, $end, $uri, $attendee_name, $attendee_email, $organizer_name = 'The Johnson Group', $organizer_email = '')
    {
 
       $start_time = new DateTime($start, false);
       $end_time = new DateTime($end, false);
       $day = new TimeSpan($start_time, $end_time);
       $location = new Location('Zoom Webinar');
       $urlAttachment = new Attachment(
          new Uri($uri),
          'text/plain'
       );
 
       // Organizer
       $organizer = new Organizer(
          new EmailAddress($organizer_email, $organizer_name)
       );
 
       // Attendee
       $attendee = new Attendee(
          new EmailAddress($attendee_email, $attendee_name)
       );
       $attendee->setCalendarUserType(CalendarUserType::INDIVIDUAL());
       $attendee->setParticipationStatus(ParticipationStatus::NEEDS_ACTION());
       $attendee->setRole(RoleType::REQ_PARTICIPANT());
       $attendee->setResponseNeededFromAttendee(true);
       $attendee->addSentBy(new EmailAddress($organizer_email, $organizer_name));
       $attendee->setDisplayName($attendee_name);
       $attendee->setLanguage('en-US');
 
       
 
 
       $event = new Event();
       $event->setOccurrence($day);
       $event->setSummary('Career Life Briefing');
       $event->setDescription('Learn more in this briefing about careers with The Johnson Group.');
       $event->setOrganizer($organizer);
       $event->addAttendee($attendee);
       $event->setLocation($location);
       $event->addAttachment($urlAttachment);
 
       $calendar = new Eluceo\iCal\Domain\Entity\Calendar([$event]);
 
       // 3. Transform domain entity into an iCalendar component
       $componentFactory = new Eluceo\iCal\Presentation\Factory\CalendarFactory();
       $calendarComponent = $componentFactory->createCalendar($calendar);
 
       // 4. Store file
       $file = file_put_contents(plugin_dir_path(__FILE__) . 'assets/clb.ics', (string)$calendarComponent);
 
       return $file;
    }


}