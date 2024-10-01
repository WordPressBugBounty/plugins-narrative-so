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
				'wp-editor',
				'underscore',
			),
			filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'assets/blocks.js' )
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
				'show_in_rest' => true,
				'single'       => true,
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
				'narrative-blocks-script'
			);
		}

	}

	/**
	 * Get the narrative script when the block is created.
	 *
	 * @return mixed
	 */
	public function callback() {

		$body = get_post_meta( get_the_ID(), 'narrative_post_script', true );
		$body = stripslashes( base64_decode( $body ) );
		return $body;
	}
}
