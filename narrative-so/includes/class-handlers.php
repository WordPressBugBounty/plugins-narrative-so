<?php
/**
 * Handlers class.
 *
 * @package Narrative_Publisher/Handlers;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main handlers.
 */
class Handlers {


	/**
	 * Handlers constructor.
	 */
	public function __construct() {

	}

	/**
	 * Load the admin plugin helpers on demand.
	 *
	 * These live in wp-admin and are not loaded on front end requests, which is
	 * where the /narrative/ endpoints are served from.
	 */
	protected function load_plugin_helpers() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

	}

	/**
	 * Run a function after the request.
	 *
	 * @param string $type The request type.
	 */
	protected function work( $type ) {

		if ( 'info' === $type ) {
			$this->get_info();
		}

		if ( 'post' === $type ) {

			// Requests are authenticated by Authenticator::checkCode(), not by a nonce.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['post_id'] ) ) {
				$post_id = absint( wp_unslash( $_GET['post_id'] ) );
			} else {
				$post_id = absint( get_query_var( 'post_id' ) );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading the raw request body.
			$data = file_get_contents( 'php://input' );

			if ( ! empty( $data ) ) {
				$this->set_post( $data, $post_id );
			} elseif ( ! empty( $post_id ) ) {
				$this->get_post( $post_id );
			} else {
				status_header( 404 );
			}
		}
	}

	/**
	 * Get the website info for API.
	 */
	private function get_info() {

		$this->load_plugin_helpers();

		$results = array();

		$results['plugin_version'] = NARRATIVE_PUBLISHER_VERSION;

		/**
		 * Get WordPress version
		 */
		global $wp_version;
		$results['wp_version'] = $wp_version;

		/**
		 * Check if the Gutenberg plugin is activated
		 */
		$results['has_gutenberg']        = 'false';
		$results['guttenburg_available'] = 'false';

		if ( file_exists( WP_PLUGIN_DIR . '/gutenberg/gutenberg.php' ) ) {
			$results['has_gutenberg'] = 'true';
		}

		if ( is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			$results['guttenburg_available'] = 'true';
		}

		$categories = array();
		foreach (
			get_categories( array( 'hide_empty' => false ) ) as $category
		) {
			$categories[] = $category->name;
		}

		if ( ! empty( $categories ) && is_array( $categories ) ) {
			$results['categories'] = $categories;
		}

		$this->render_data( $results );

	}

	/**
	 * Get post by API.
	 *
	 * @param string $post_id Post id.
	 */
	private function get_post( $post_id ) {

		$results = array();

		$post = get_post( $post_id );

		if ( ! empty( $post ) ) {
			$results['blogLink'] = get_the_permalink( $post_id );
			$results['body']     = $post->post_content;
			$this->render_data( $results );

		} else {
			status_header( 404 );
			die();
		}

	}

	/**
	 * Get param.
	 *
	 * @param array  $decoded_params All decoded params.
	 * @param string $key            The param to read.
	 *
	 * @return string
	 */
	private function get_param( $decoded_params, $key = '' ) {

		if ( empty( $key ) ) {
			return '';
		}

		if ( empty( $decoded_params[ $key ] ) ) {
			return '';
		}

		return $decoded_params[ $key ];

	}

	/**
	 * Add new post to the website.
	 *
	 * @param string $json_params All json params.
	 * @param string $post_id     Post id.
	 *
	 * @return string
	 */
	private function set_post( $json_params, $post_id = '' ) {

		if ( empty( $json_params ) ) {
			return '';
		}

		if ( ! $this->is_valid_json( $json_params ) ) {
			return '';
		}

		$decoded_params = json_decode( $json_params, true );

		if ( empty( $decoded_params ) ) {
			return '';
		}

		if ( ! empty( $decoded_params ) && is_array( $decoded_params ) ) {

			$this->load_plugin_helpers();

			$name = sanitize_text_field( $this->get_param( $decoded_params,
				'name' ) );

			$slug = sanitize_title( $this->get_param( $decoded_params,
				'slug' ) );

			$meta_description
				= sanitize_text_field( $this->get_param( $decoded_params,
				'metaDescription' ) );

			$meta_title
				= sanitize_text_field( $this->get_param( $decoded_params,
				'metaTitle' ) );

			$meta_keywords
				= sanitize_text_field( $this->get_param( $decoded_params,
				'metaKeywords' ) );

			$image_url = esc_url_raw( $this->get_param( $decoded_params,
				'featuredImageLink' ) );

			$excerpt
				= sanitize_textarea_field( $this->get_param( $decoded_params,
				'excerpt' ) );

			$category = sanitize_text_field( $this->get_param( $decoded_params,
				'category' ) );

			$body = $this->get_param( $decoded_params, 'body' );

			$publish = sanitize_text_field( $this->get_param( $decoded_params,
				'publish' ) );

			$publish = ( 'true' === $publish ) ? 'publish' : 'draft';


			$args = array(
				'post_title'   => $name,
				'post_name'    => $slug,
				'post_status'  => $publish,
				'post_excerpt' => $excerpt,
			);

			if ( ! $this->is_base64( $body ) ) {
				$body = base64_encode( stripslashes( $body ) );
			}

			global $wp_version;

			if ( empty( $post_id ) ) {

				$args['post_status'] = $publish;

				// Add shortcode if this is an old version of WordPress.
				$args['post_content'] = '[narrative]';

				// Add block if version of WordPress 5+.
				if ( is_plugin_active( 'gutenberg/gutenberg.php' )
					 || version_compare( $wp_version, '5.0', '>=' )
				) {
					$args['post_content'] = '<!-- wp:narrative/block /-->';
				}

				// Replace to the shortcode if the classic editor is enabled.
				if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
					$args['post_content'] = '[narrative]';
				}

				// Create a new post.
				$post_id = wp_insert_post( $args );

			}

			if ( empty( $post_id ) || is_wp_error( $post_id ) ) {
				status_header( 500 );
				die();
			}

			update_post_meta( $post_id, 'narrative_post_script', $body );


			/*
			 * Add all categories
			 */
			$categories = explode( ',', $category );
			wp_set_object_terms( $post_id, $categories, 'category' );

			/*
			 * Add meta
			 */
			update_post_meta( $post_id, '_narrative_meta_title', $meta_title );
			update_post_meta( $post_id, '_narrative_meta_description',
				$meta_description );
			update_post_meta( $post_id, '_narrative_meta_keywords',
				$meta_keywords );

			/*
			 * Add featured image
			 */
			$this->download_image( $image_url, $post_id );

			/*
			 * Return results
			 */
			$results             = array();
			$results['blogLink'] = get_the_permalink( $post_id );
			$results['post_id']  = $post_id;
			$results['body']     = stripslashes( base64_decode( $body ) );

			$this->render_data( $results );

			exit();
		}

		return '';

	}

	/**
	 * Return result for API.
	 *
	 * @param array $results Result in array.
	 */
	private function render_data( $results ) {
		if ( ! empty( $results ) && is_array( $results ) ) {
			$this->save_time_request();

			if ( ! headers_sent() ) {
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			}

			echo wp_json_encode( $results );
		}

		die();

	}

	/**
	 * Check if the json is valid.
	 *
	 * @param string $str Json string.
	 *
	 * @return bool
	 */
	private function is_valid_json( $str ) {
		json_decode( $str );

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Check if the string is base64.
	 *
	 * This round trips through base64_decode()/base64_encode() rather than
	 * matching a character class, because the old pattern also matched ordinary
	 * alphanumeric text and left such bodies stored un-encoded.
	 *
	 * @param string $str Base 64 string.
	 *
	 * @return bool
	 */
	public function is_base64( $str ) {

		if ( ! is_string( $str ) || '' === $str ) {
			return false;
		}

		// Encoders wrap long output; ignore the line breaks when comparing.
		$normalized = preg_replace( '/\s+/', '', $str );

		if ( '' === $normalized ) {
			return false;
		}

		$decoded = base64_decode( $normalized, true );

		if ( false === $decoded ) {
			return false;
		}

		return base64_encode( $decoded ) === $normalized;
	}

	/**
	 * Sideload the featured image and attach it to the post.
	 *
	 * @param string $image_url Remote image URL.
	 * @param int    $post_id   Post to attach the image to.
	 */
	private function download_image( $image_url, $post_id ) {

		if ( empty( $image_url ) ) {
			return;
		}

		// Rejects non-http(s) schemes and hosts that resolve to the local network.
		$image_url = wp_http_validate_url( $image_url );

		if ( false === $image_url ) {
			return;
		}

		$path = wp_parse_url( $image_url, PHP_URL_PATH );

		if ( empty( $path ) ) {
			return;
		}

		$filename = sanitize_file_name( wp_basename( $path ) );

		if ( empty( $filename ) ) {
			return;
		}

		// Reuse the attachment if this image has already been sideloaded.
		$attach_id = $this->get_image_id_by_name( pathinfo( $filename, PATHINFO_FILENAME ) );

		if ( ! empty( $attach_id ) ) {
			set_post_thumbnail( $post_id, $attach_id );

			return;
		}

		/*
		 * Refuse anything that is not an image before it reaches the filesystem.
		 * The previous implementation wrote the remote response body straight
		 * into the uploads directory under the caller supplied filename, which
		 * allowed arbitrary file types — including .php — to be written there.
		 */
		$filetype = wp_check_filetype( $filename );

		if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// download_url() fetches via wp_safe_remote_get(), so redirects cannot be
		// used to reach internal hosts.
		$tmp_file = download_url( $image_url );

		if ( is_wp_error( $tmp_file ) ) {
			return;
		}

		$file = array(
			'name'     => $filename,
			'tmp_name' => $tmp_file,
		);

		// Re-checks that the file contents actually match the extension, and
		// removes tmp_name on success.
		$attach_id = media_handle_sideload( $file, $post_id );

		if ( is_wp_error( $attach_id ) ) {
			wp_delete_file( $tmp_file );

			return;
		}

		set_post_thumbnail( $post_id, $attach_id );

	}

	/**
	 * Filter a decoded story body for output on the front end.
	 *
	 * wp_kses_post() alone drops <iframe>, which stories use for embedded video,
	 * so the standard post allowlist is extended with a constrained iframe. kses
	 * still applies wp_kses_bad_protocol() to src, so a javascript: URL cannot
	 * survive. Script tags and event handler attributes are still removed — the
	 * story's own script is enqueued separately by Metabox::wp_enqueue_scripts().
	 *
	 * @param string $body Decoded story markup.
	 *
	 * @return string
	 */
	public static function filter_story_html( $body ) {

		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['iframe'] = array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'title'           => true,
			'class'           => true,
			'id'              => true,
			'style'           => true,
			'loading'         => true,
			'frameborder'     => true,
			'scrolling'       => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'referrerpolicy'  => true,
		);

		return wp_kses( $body, $allowed );
	}

	/**
	 * Update time of request.
	 */
	public static function save_time_request() {
		update_option( 'narrative_last_request', time() );
	}

	/**
	 * Get attachment id by name.
	 *
	 * @param string $filename Name of image file..
	 *
	 * @return null|string
	 */
	public static function get_image_id_by_name( $filename ) {

		if ( empty( $filename ) ) {
			return null;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'attachment' LIMIT 1;",
				$filename
			)
		);
	}


}
