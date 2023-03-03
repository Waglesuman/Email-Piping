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

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/* Defining a constant. */
if (!defined('CPM_PLUGIN_DIR')) {
  define('CPM_PLUGIN_DIR', plugin_dir_url(__FILE__));
}
if (!defined('CPM_PLUGIN_DIR_PATH')) {
  define('CPM_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

add_action('init', 'email_piping_init');



function email_piping_init()
{
  if (extension_loaded('imap')) {
    // Server details
    $email = 'test.sumanwagle@gmail.com';
    $password = 'eixbjprgzpzvzxow';
    $server = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';

    // Connect to the server
    $imap = imap_open($server, $email, $password) or die('Cannot connect to the server: ' . imap_last_error());

    $emails = imap_search($imap, 'UNSEEN');

    // Loop through each email and extract its information

    foreach ($emails as $email) {
      $headerInfo = imap_headerinfo($imap, $email);
      $message = imap_fetchbody($imap, $email, 1);

      // Extract information from the message
      $subject = $headerInfo->subject;
      $date = date('Y-m-d H:i:s', strtotime($headerInfo->date));
      $body = $message;

      // Extract attachments, if any
      $attachments = array();
      $structure = imap_fetchstructure($imap, $email);

      if (isset($structure->parts) && count($structure->parts)) {
        for ($i = 0; $i < count($structure->parts); $i++) {
          $attachment = array(
            'is_attachment' => false,
            'filename' => '',
            'name' => '',
            'attachment' => ''
          );
          if ($structure->parts[$i]->ifdparameters) {
            foreach ($structure->parts[$i]->dparameters as $object) {
              if (strtolower($object->attribute) == 'filename') {
                $attachment['is_attachment'] = true;
                $attachment['filename'] = $object->value;
              }
            }
          }
          if ($structure->parts[$i]->ifparameters) {
            foreach ($structure->parts[$i]->parameters as $object) {
              if (strtolower($object->attribute) == 'name') {
                $attachment['is_attachment'] = true;
                $attachment['name'] = $object->value;
              }
            }
          }
          if ($attachment['is_attachment']) {
            $attachment['attachment'] = imap_fetchbody($imap, $email, $i + 1);
            if ($structure->parts[$i]->encoding == 3) {
              $attachment['attachment'] = base64_decode($attachment['attachment']);
            } elseif ($structure->parts[$i]->encoding == 4) {
              $attachment['attachment'] = quoted_printable_decode($attachment['attachment']);
            }
            $attachments[] = $attachment;
          }
        }
      }
      
      // Store the information in the WordPress database
      $postarr = array(
        'post_title' => $subject,
        'post_content' => $body,
        'post_date' => $date,
        'post_author' => 1,
        // the ID of the author who will be attributed to the post
        'post_status' => 'publish',
        'post_type' => 'post' // the type of post to create
      );
      $postID = wp_insert_post($postarr);
      // Store the attachments, if any, as media attachments to the post
      if (is_array($attachments) || is_object($argument)) {

        foreach ($attachments as $attachment) {
          require_once(ABSPATH . 'wp-admin/includes/media.php');
          $mediaID = media_handle_sideload($attachment, $postID, $attachment['name']);
          if (!is_wp_error($mediaID)) {
            $attachmentData = array(
              'ID' => $mediaID,
              'post_parent' => $postID
            );
            wp_update_post($attachmentData);
          }
        }
      }

      // Close the mailbox connection
      imap_close($imap);
    }
  }
}