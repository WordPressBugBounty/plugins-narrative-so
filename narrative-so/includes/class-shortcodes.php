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

		if ( ! empty( $body ) ) {

			ob_start();
			echo wp_kses_post( $body );

			return ob_get_clean();
		}

		return '';
	}

}
