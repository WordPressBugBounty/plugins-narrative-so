<?php
/*
Plugin Name: Narrative Publisher
Plugin URI: https://wordpress.org/plugins/narrative-so/
Description:  This plugin connects your website with your Narrative account allowing you to publish your Narrative posts directly to your website. Please contact support@narrative.so for any help.
Version: 1.1.0
Requires at least: 6.5
Requires PHP: 7.4
Author: Narrative
Author URI: https://narrative.so/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: narrative-so
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'NARRATIVE_PUBLISHER_DEBUG', false );
define( 'NARRATIVE_PUBLISHER_AUTH_DISABLED', false );

define( 'NARRATIVE_PUBLISHER_VERSION', '1.1.0' );
define( 'NARRATIVE_PUBLISHER_FILE', __FILE__ );
define( 'NARRATIVE_PUBLISHER_PATH', plugin_dir_path( __FILE__ ) );

include_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

/**
 * Register the /narrative/ rewrite rules and flush them once, on activation.
 *
 * The rules are normally added on `init`, which has already run by the time the
 * activation hook fires, so they have to be registered again here or the
 * endpoints 404 until the next permalink save.
 */
register_activation_hook(
	__FILE__,
	function () {
		$api = new Narrative_Publisher\API();
		$api->add_rewrite_rules();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
