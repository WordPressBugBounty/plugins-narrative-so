<?php
/**
 * Core file of plugin.
 *
 * @package Narrative_Publisher;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Narrative Service plugin.
 *
 * The plugin is designed to allow the NarrativePublisher to communicate with a WordPress instance
 * via a JSON REST API in order to retrieve information about the instance and create blog posts.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;


	/**
	 * Run the plugin core.
	 *
	 * @return Plugin Get Instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			/**
			 * Narrative loaded.
			 *
			 * @since 1.0.0
			 */
			do_action( 'narrative_publisher/loaded' );
		}

		return self::$instance;
	}

	/**
	 * Register autoloader.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function register_autoloader() {

		// Get the autoloader.
		$loader = new Autoloader;

		// Register the autoloader.
		$loader->register();

		// Register the base directories for the namespace prefix.
		$loader->add_namespace( 'Narrative_Publisher', plugin_dir_path( __FILE__ ) );
	}


	/**
	 * Init components.
	 *
	 * Initialize NarrativePublisher components.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	public function init_components() {

		// Init components.
		new API();
		new Handlers();
		new Admin();
		new Request();
		new Metabox();
		new Authenticator();
		new Shortcodes();
		new Blocks();

		/**
		 * Narrative loaded.
		 *
		 * @since 1.0.0
		 */
		do_action( 'narrative_publisher/init' );
	}

	/**
	 * Plugin constructor.
	 *
	 * Initializing Elementor plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function __construct() {

		if ( class_exists( 'Narrative_Publisher' ) ) {
			return null;
		}

		// Include autoloader.php.
		require plugin_dir_path( __FILE__ ) . 'class-autoloader.php';

		/*
		 * Register autoloader and add namespaces
		 *
		 */
		$this->register_autoloader();

		/**
		 * Init components.
		 */
		$this->init_components();

	}

}

Plugin::instance();
