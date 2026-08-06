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
	 * Option holding the plugin version the rewrite rules were last flushed for.
	 *
	 * @var string
	 */
	const REWRITE_VERSION_OPTION = 'narrative_publisher_rewrite_version';

	/**
	 * Routes constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_rewrite_rules' ), 10, 0 );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 11, 0 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1 );

	}

	/**
	 * Add rewrite rules.
	 */
	public function add_rewrite_rules() {

		add_rewrite_rule( '^narrative/?$', 'index.php?narrative=/', 'top' );
		add_rewrite_rule( '^narrative/info/?$', 'index.php?narrative=info', 'top' );
		add_rewrite_rule( '^narrative/post/?$', 'index.php?narrative=post', 'top' );
		add_rewrite_rule( '^narrative/post/([0-9]{1,})/?$', 'index.php?narrative=post&post_id=$matches[1]', 'top' );

	}

	/**
	 * Flush the rewrite rules once per plugin version.
	 *
	 * Flushing on every request rebuilds and re-saves the whole rule set on each
	 * page load, which is expensive. Activation covers new installs; this covers
	 * sites upgrading from a version that did not register the rules the same way.
	 */
	public function maybe_flush_rewrite_rules() {

		if ( get_option( self::REWRITE_VERSION_OPTION ) === NARRATIVE_PUBLISHER_VERSION ) {
			return;
		}

		flush_rewrite_rules();

		update_option( self::REWRITE_VERSION_OPTION, NARRATIVE_PUBLISHER_VERSION );

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
