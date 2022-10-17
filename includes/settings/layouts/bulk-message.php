<?php

/** Bandaid to send bulk response message */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



?>

<form id="bulk-form">
    <label for="phone-numbers">Phone Numbers</label>
    <textarea id="phone-numbers" type="textarea"
    rows="10"
    cols="50" name="phone-numbers"></textarea>
    <label for="message">Message</label>
    <input id="message" type="textarea" name="message" value="Awesome, we will be conducting a virtual career briefing this Wednesday at 10 AM via Zoom. Please register, and we'll see you then! https://us02web.zoom.us/webinar/register/WN___mUcBTbRJiBptjaL2ZBGQ"></input>
    <input id="form-submit" type="submit" name="submit" value="Send" class="btn btn-primary"></input>
</form>