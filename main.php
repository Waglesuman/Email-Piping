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