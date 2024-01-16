<?php
/*
Plugin Name: Email Piping Plugin 
Plugin URI: http://example.com/
Description: Create posts via email.
Version: 1.0.0
Author: Suman Wagle
Author URI: http://emailpiping.com/
License: GPL3
Text Domain: cpmemailpiping
*/

// Prevent direct access to the script.
if (!defined('WPINC')) {
  die;
}

// Define plugin directory URLs and paths.
if (!defined('CPM_PLUGIN_DIR')) {
  define('CPM_PLUGIN_DIR', plugin_dir_url(__FILE__));
}
if (!defined('CPM_PLUGIN_DIR_PATH')) {
  define('CPM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

// Hook the 'email_piping_init' function to the WordPress 'init' action.
add_action('init', 'email_piping_init');

/**
 * Initializes the email piping process.
 */
function email_piping_init()
{
  // Check if the IMAP extension is loaded before proceeding.
  if (!extension_loaded('imap')) {
    return; // Exit if IMAP is not enabled.
  }
  
  // Email server connection details.
  $email = 'gmail'; // Replace with actual Gmail username.
  $password = 'xxx'; // Replace with actual Gmail password.
  $server = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';

  // Attempt to connect to the email server.
  $imap = imap_open($server, $email, $password) or die('Cannot connect to the server: ' . imap_last_error());

  // Search for unread emails.
  $emails = imap_search($imap, 'UNSEEN');

  // If emails are found, process each one.
  if ($emails) {
    foreach ($emails as $email_number) {
      // Retrieve email header information.
      $headerInfo = imap_headerinfo($imap, $email_number);
      $message = imap_fetchbody($imap, $email_number, 1);

      // Extract email subject, date, and body.
      $subject = $headerInfo->subject;
      $date = date('Y-m-d H:i:s', strtotime($headerInfo->date));
      $body = $message;

      // Initialize an array to store attachments.
      $attachments = array();
      $structure = imap_fetchstructure($imap, $email_number);

      // Check if there are any attachments and process them.
      if (isset($structure->parts) && count($structure->parts)) {
        for ($i = 0; $i < count($structure->parts); $i++) {
          // Initialize attachment array with default values.
          $attachment = array(
            'is_attachment' => false,
            'filename' => '',
            'name' => '',
            'attachment' => ''
          );

          // Check for 'filename' parameter in each part.
          if ($structure->parts[$i]->ifdparameters) {
            foreach ($structure->parts[$i]->dparameters as $object) {
              if (strtolower($object->attribute) == 'filename') {
                $attachment['is_attachment'] = true;
                $attachment['filename'] = $object->value;
              }
            }
          }

          // Check for 'name' parameter in each part.
          if ($structure->parts[$i]->ifparameters) {
            foreach ($structure->parts[$i]->parameters as $object) {
              if (strtolower($object->attribute) == 'name') {
                $attachment['is_attachment'] = true;
                $attachment['name'] = $object->value;
              }
            }
          }

          // If an attachment is found, decode it based on its encoding.
          if ($attachment['is_attachment']) {
            $attachment['attachment'] = imap_fetchbody($imap, $email_number, $i + 1);
            switch ($structure->parts[$i]->encoding) {
              case 3:
                $attachment['attachment'] = base64_decode($attachment['attachment']);
                break;
              case 4:
                $attachment['attachment'] = quoted_printable_decode($attachment['attachment']);
                break;
            }
            $attachments[] = $attachment;
          }
        }
      }
      
      // Prepare the post data array.
      $postarr = array(
        'post_title'   => $subject,
        'post_content' => $body,
        'post_date'    => $date,
        'post_author'  => 1, // Replace with the actual author ID.
        'post_status'  => 'publish',
        'post_type'    => 'post'
      );

      // Insert the post into the WordPress database.
      $postID = wp_insert_post($postarr);

      // If attachments are present, handle them.
      if ($attachments) {
        foreach ($attachments as $attachment) {
          // Load WordPress media handling functions.
          require_once(ABSPATH . 'wp-admin/includes/media.php');
          $mediaID = media_handle_sideload($attachment, $postID, $attachment['name']);
          if (!is_wp_error($mediaID)) {
            // Update attachment post data.
            wp_update_post(array('ID' => $mediaID, 'post_parent' => $postID));
          }
        }
      }
    }
  }
  
  // Close the IMAP connection.
  imap_close($imap);
}
