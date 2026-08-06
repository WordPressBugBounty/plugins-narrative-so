<?php
/**
 * Add a new shortcode.
 *
 * @package Narrative_Publisher/Shortcodes;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create Narrative shortcode.
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {

		/**
		 * Add shortcode.
		 */
		add_shortcode( 'narrative', array( $this, 'add_shortcode_narrative' ) );

	}

	/**
	 * Add new narrative shortcode to show the narrative posts.
	 *
	 * @return string
	 */
	public function add_shortcode_narrative() {

		global $post;
		$body = get_post_meta( $post->ID, 'narrative_post_script', true );

		$body = stripslashes( base64_decode( $body ) );

		if ( empty( $body ) ) {
			return '';
		}

		// A shortcode returns its output rather than echoing it; the value is
		// run through wp_kses() inside filter_story_html().
		return Handlers::filter_story_html( $body );
	}

}
