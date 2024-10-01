<?php
/*
Plugin Name: Narrative Publisher
Plugin URI:
Description:  This plugin connects your website with your Narrative account allowing you to publish your Narrative posts directly to your website. Please contact support@narrative.so for any help.
Version: 1.0.7
Author: Narrative
Author URI: https://narrative.so/
Text Domain: narrative-publisher
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'NARRATIVE_PUBLISHER_DEBUG', false );
define( 'NARRATIVE_PUBLISHER_AUTH_DISABLED', false );

include_once ABSPATH . 'wp-admin/includes/plugin.php';
include_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

