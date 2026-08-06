<?php
/**
 * Register Gutenberg block.
 *
 * @package Narrative_Publisher/Blocks;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Blocks for Gutenberg.
 */
class Blocks {

	/**
	 * Blocks constructor.
	 */
	public function __construct() {

		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		add_action( 'init', array( &$this, 'init' ) );

	}

	/**
	 * Register a new block and js scripts when init hook is run.
	 */
	public function init() {

		wp_register_script(
			'narrative-blocks-script',
			plugins_url( 'assets/blocks.js', dirname( __FILE__ ) ),
			array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-data',
				'underscore',
			),
			NARRATIVE_PUBLISHER_VERSION,
			true
		);


		register_block_type(
			'narrative/block',
			array(
				'style'           => 'narrative-blocks-script',
				'editor_script'   => 'narrative-blocks-script',
				'render_callback' => array( &$this, 'callback' ),
			)

		);

		register_meta(
			'post',
			'narrative_post_script',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				/*
				 * Without an auth_callback, register_meta() falls back to
				 * allowing any authenticated user to write this meta over the
				 * REST API. The value is decoded and rendered on the front end,
				 * so writing it is gated on unfiltered_html — the same
				 * capability WordPress itself requires before a user may publish
				 * executable markup. An Author can edit their own post but does
				 * not hold unfiltered_html, so cannot reach this meta.
				 *
				 * Narrative's own endpoint writes the meta through
				 * update_post_meta(), which this does not apply to.
				 */
				'auth_callback'     => static function () {
					return current_user_can( 'unfiltered_html' );
				},
				/*
				 * The body is only ever stored base64 encoded, so reject anything
				 * that is not. Stripping non-base64 characters instead would leave
				 * a mangled remnant of whatever was submitted.
				 */
				'sanitize_callback' => static function ( $value ) {
					if ( ! is_string( $value ) ) {
						return '';
					}

					// Encoders wrap long output; the line breaks are not payload.
					$normalized = preg_replace( '/\s+/', '', $value );

					if ( '' === $normalized ) {
						return '';
					}

					$decoded = base64_decode( $normalized, true );

					if ( false === $decoded || base64_encode( $decoded ) !== $normalized ) {
						return '';
					}

					return $value;
				},
			)

		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			/**
			 * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
			 * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
			 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			 */
			wp_set_script_translations(
				'narrative-blocks-script',
				'narrative-so'
			);
		}

	}

	/**
	 * Get the narrative script when the block is created.
	 *
	 * @return string
	 */
	public function callback() {

		$body = get_post_meta( get_the_ID(), 'narrative_post_script', true );
		$body = stripslashes( base64_decode( $body ) );

		/*
		 * The block previously returned the decoded meta verbatim, so a stored
		 * <script> survived to the front end. Both render paths now share one
		 * allowlist.
		 */
		return Handlers::filter_story_html( $body );
	}
}
