## Overview

The Email Piping Plugin is a WordPress plugin designed to enable the creation of posts via email. With this plugin, you can seamlessly integrate your Gmail account to automatically generate WordPress posts from incoming emails. This can be particularly useful for content creators who want to streamline their workflow and publish content directly from their email accounts.

## Features

- **Email Integration:** Connects to an IMAP-enabled email server (e.g., Gmail) to retrieve unread emails.
- **Post Generation:** Creates WordPress posts from the content of incoming emails.
- **Attachments Handling:** Supports the extraction and attachment of files included in the emails.

## Installation

1. Upload the 'email-piping-plugin' directory to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

1. Go to the plugin settings page and enter your Gmail username and password.
2. Customize the post settings such as post author, status, and type as needed.

## Usage

1. Compose an email with the desired post content and subject.
2. Send the email to the configured Gmail account.
3. The plugin will automatically fetch the email, extract relevant information, and create a corresponding WordPress post.

## Requirements

- WordPress 4.7 or higher
- PHP IMAP extension enabled

## Plugin Details

- **Plugin Name:** Email Piping Plugin
- **Description:** Create posts via email.
- **Version:** 1.0.0
- **Author:** Suman Wagle
- **Text Domain:** cpmemailpiping

## Notes

- Make sure the IMAP extension is enabled on your server.
- Replace placeholder values in the plugin code (e.g., Gmail username and password) with actual credentials.
- Customize the postarr array to adjust post settings according to your preferences.

## Disclaimer

Use this plugin at your own risk. Ensure that you follow security best practices, especially when handling email credentials. The plugin is provided as-is, without warranty or support.

Feel free to contribute to the development of this plugin on [GitHub](https://github.com/Waglesuman/Email-Piping).

**Happy Email Piping!**
