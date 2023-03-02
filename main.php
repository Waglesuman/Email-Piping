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
// Server details
$email = 'test.sumanwagle@gmail.com';
$password = 'eixbjprgzpzvzxow';
$server = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';

// Connect to the server
$imap = imap_open($server, $email, $password) or die('Cannot connect to the server: ' . imap_last_error());

// Get the number of messages in the inbox
$num_msgs = imap_num_msg($imap);

// Loop through each message and create a new WordPress post
for ($i = 1; $i <= $num_msgs; $i++) {
  
  // Read the email message contents
  $email_msg = imap_fetchbody($imap, $i, "1.2");
  if ($email_msg == "") {
    $email_msg = imap_fetchbody($imap, $i, "1");
  }
  $email_msg = quoted_printable_decode($email_msg);

  // Extract the email address and subject
  $message = imap_fetchheader($imap, $i);
  $email_address = imap_rfc822_parse_headers($message)->from[0]->mailbox . "@" . imap_rfc822_parse_headers($message)->from[0]->host;
  $subject = imap_utf8(imap_fetchheader($imap, $i, FT_PREFETCHTEXT));

  // Create a new WordPress post
  require_once(ABSPATH . 'wp-load.php');
  $post_title = $subject; // Set the post title
  $post_content = $email_msg; // Use the email message as the post content

  // Create the new post
  $new_post = array(
    'post_title' => $post_title,
    'post_content' => $post_content,
    'post_status' => 'publish'
  );
  $post_id = wp_insert_post($new_post);

  // Redirect the user to the new post
  if ($post_id) {
    wp_redirect(get_permalink($post_id));
    exit;
  } else {
    echo 'Error creating post';
  }
}

// Close the IMAP connection
imap_close($imap);