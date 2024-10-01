<?php
/**
 * API class.
 *
 * @package Narrative_Publisher/API;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Parse query.
 */
class API {

	/**
	 * Routes constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init_narrative_rewrite' ), 10, 0 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1 );

	}

	/**
	 * Add rewrite rules.
	 */
	public function init_narrative_rewrite() {

		add_rewrite_rule( '^narrative/?$', 'index.php?narrative=/', 'top' );
		add_rewrite_rule( '^narrative/info/?$', 'index.php?narrative=info', 'top' );
		add_rewrite_rule( '^narrative/post/?$', 'index.php?narrative=post', 'top' );
		add_rewrite_rule( '^narrative/post/([0-9]{1,})/?$', 'index.php?narrative=post&post_id=$matches[1]', 'top' );

		global $wp_rewrite;
		$wp_rewrite->flush_rules( true );
		flush_rewrite_rules();

	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars All query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'narrative';
		$vars[] = 'post_id';

		return $vars;
	}

}
