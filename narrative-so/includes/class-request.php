<?php
/**
 * Request.
 *
 * @package Narrative_Publisher/Request;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Request handler.
 */
class Request extends Handlers {

	/**
	 * @var $general_options
	 */
	public $options_slug = 'narrative_options';

	/**
	 * Request constructor.
	 */
	public function __construct() {

		add_action( 'wp', array( &$this, 'get_query' ), 0 );

		add_action( 'wp_ajax_narrative', array( &$this, 'ajax_narrative' ), 0 );
		add_action( 'wp_ajax_nopriv_narrative', array( &$this, 'ajax_narrative' ), 0 );

	}

	/**
	 * Run handlers
	 */
	public function get_query() {

		$general_option = get_option( $this->options_slug );


		if ( ! sanitize_text_field( get_query_var( 'narrative' ) ) ) {
			return;
		}

		if ( ! NARRATIVE_PUBLISHER_AUTH_DISABLED ) {

			if ( empty( $general_option['secret'] ) ) {
				header( 'HTTP/1.1 422' );
				die();
			}
		}


		if ( NARRATIVE_PUBLISHER_AUTH_DISABLED || $this->getToken() ) {

			$auth = new Authenticator();

			if ( ! NARRATIVE_PUBLISHER_AUTH_DISABLED ) {
				if ( ! $auth->checkCode( $general_option['secret'], $this->getToken() ) ) {

					header( 'HTTP/1.1 422' );
					die();

				}
			}

			header( 'Cache-Control: private, no-cache, must-revalidate, max-age=0' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );

			/**
			 * Get info
			 */
			if ( sanitize_text_field( get_query_var( 'narrative' ) ) == 'info' ) {
				$this->work( 'info' );
				die();
			}

			/**
			 * Get post
			 */
			if ( sanitize_text_field( get_query_var( 'narrative' ) ) == 'post' ) {
				$this->work( 'post' );
				die();
			}
		} else {

			header( 'HTTP/1.1 404' );
			die();
		}


		die();

	}

	public function ajax_narrative() {

		$general_option = get_option( $this->options_slug );


		if ( ! NARRATIVE_PUBLISHER_AUTH_DISABLED ) {

			if ( empty( $general_option['secret'] ) ) {
				header( 'HTTP/1.1 422' );
				die();
			}
		}


		if ( NARRATIVE_PUBLISHER_AUTH_DISABLED || $this->getToken() ) {

			$auth = new Authenticator();

			if ( ! NARRATIVE_PUBLISHER_AUTH_DISABLED ) {
				if ( ! $auth->checkCode( $general_option['secret'], $this->getToken() ) ) {

					header( 'HTTP/1.1 422' );
					die();

				}
			}

			header( 'Cache-Control: private, no-cache, must-revalidate, max-age=0' );
			header( 'Cache-Control: post-check=0, pre-check=0', false );
			header( 'Pragma: no-cache' );

			if ( ! empty( $_GET['type'] ) && 'info' === sanitize_text_field( $_GET['type'] ) ) {
				$this->work( 'info' );
			} else {
				/**
				 * Get post
				 */
				$this->work( 'post' );
			}

		} else {

			header( 'HTTP/1.1 404' );

		}


		die();

	}

	private function getAuthorizationHeader() {
		$headers = null;
		if ( isset( $_SERVER['Authorization'] ) ) {
			$headers = trim( $_SERVER["Authorization"] );
		}
		if ( isset( $_SERVER['Auth'] ) ) {
			$headers = trim( $_SERVER["Auth"] );
		} else if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI
			$headers = trim( $_SERVER["HTTP_AUTHORIZATION"] );
		} else if ( isset( $_SERVER['HTTP_AUTH'] ) ) { //custom
			$headers = trim( $_SERVER["HTTP_AUTH"] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine( array_map( 'ucwords', array_keys( $requestHeaders ) ), array_values( $requestHeaders ) );
			if ( isset( $requestHeaders['Authorization'] ) ) {
				$headers = trim( $requestHeaders['Authorization'] );
			}
		}


		return $headers;
	}

	private function getToken() {
		$headers = $this->getAuthorizationHeader();

		// HEADER: Get the access token from the header

		if ( ! empty( $headers ) ) {
			if ( preg_match( '/Token\s(\S+)/', $headers, $matches ) ) {
				return $matches[1];
			}
		}

		return null;
	}


}
