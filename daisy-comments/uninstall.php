<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('daisy_comments_disable_all');

// For site options in Multisite
delete_site_option('daisy_comments_disable_all');

// Remove any additional database tables if we had created any
// global $wpdb;
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}daisy_comments_log");