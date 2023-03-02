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

    // Get the number of messages in the inbox
    $MC = imap_check($imap);
    $num = $MC->Nmsgs;

    // Fetch the latest message in the mailbox
    $result = imap_fetch_overview($imap, "$num", 0);

    // Check if there is a message to process
    if (count($result) > 0) {
      // Get the message details
      $overview = array_shift($result);
      $msgno = $overview->msgno;
      $subject = $overview->subject;
      $body = imap_fetchbody($imap, $msgno, 1);

      // Check if the message has not been processed before
      $processed_messages = get_option('processed_messages', array());
      if (!in_array($msgno, $processed_messages)) {
        // Check if the message has a subject and body
        if (!empty($subject) && !empty($body)) {
          // Create a new WordPress post
          $post_title = $subject; // Set the post title
          $post_content = $body; // Use the email message as the post content
          // Create the new post
          $new_post = array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_status' => 'publish'
          );
          wp_insert_post($new_post);

          // Update the list of processed messages
          $processed_messages[] = $msgno;
          update_option('processed_messages', $processed_messages);
        } else {
          // Log an error if the message is empty
          error_log('Empty subject or body in email message');
        }
      }
    }

    // Close the IMAP connection
    imap_close($imap);
  }
}