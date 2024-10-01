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
	 * Run a function after the request.
	 *
	 * @param string $type The request type.
	 */
	protected function work( $type ) {

		if ( 'info' === $type ) {
			$this->get_info();
		}

		if ( 'post' === $type ) {

			if ( ! empty( $_GET['post_id'] ) ) {
				$post_id = sanitize_text_field( $_GET['post_id'] );
			} else {
				$post_id = sanitize_text_field( get_query_var( 'post_id' ) );
			}

			$data = file_get_contents( 'php://input' );

			if ( ! empty( $data ) ) {
				$this->set_post( $data, $post_id );
			} elseif ( ! empty( $post_id ) ) {
				$this->get_post( $post_id );
			} else {
				header( 'HTTP/1.1 404' );
			}
		}
	}

	/**
	 * Get the website info for API.
	 */
	private function get_info() {

		$results = array();

		$results['plugin_version'] = '1.0.7';

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
			get_categories( array( 'hide_empty' => FALSE ) ) as $category
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
			header( 'HTTP/1.1 404"' );
			die();
		}

	}

	/**
	 * Get param.
	 *
	 * @param array  $decoded_params
	 * @param string $key
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

		$decoded_params = json_decode( $json_params, TRUE );

		if ( empty( $decoded_params ) ) {
			return '';
		}

		if ( ! empty( $decoded_params ) && is_array( $decoded_params ) ) {

			$name = sanitize_text_field( $this->get_param( $decoded_params,
				'name' ) );

			$slug = sanitize_text_field( $this->get_param( $decoded_params,
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

			$image_url = sanitize_text_field( $this->get_param( $decoded_params,
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

			// back filters.
			add_filter( 'content_save_pre', 'wp_filter_post_kses' );
			add_filter( 'content_filtered_save_pre', 'wp_filter_post_kses' );

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
			echo json_encode( $results );
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

		return json_last_error() == JSON_ERROR_NONE;
	}

	/**
	 * Check if the string is base64.
	 *
	 * @param string $str Base 64 string.
	 *
	 * @return bool
	 */
	function is_base64( $str ) {
		return (bool) preg_match( '/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $str );
	}

	/**
	 * @param $image_url
	 * @param $post_id
	 *
	 * @return string
	 */
	private function download_image( $image_url, $post_id ) {

		// Set upload folder.
		$upload_dir = wp_upload_dir();

		// Create image file name.
		$filename = basename( $image_url );

		$filename_crop = substr( sanitize_file_name( $filename ), 0, - 4 );

		$attach_id = $this->get_image_id_by_name( $filename_crop );

		if ( ! empty( $attach_id ) ) {
			// And finally assign featured image to post.
			set_post_thumbnail( $post_id, $attach_id );

			return '';
		}

		$image_data = file_get_contents( $image_url ); // Get image data.


		// Check folder permission and define file location.
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server.
		file_put_contents( $file, $image_data );

		// Check image file type.
		$wp_filetype = wp_check_filetype( $filename, NULL );


		// Set attachment data.
		$attachment = array(
			'guid'           => $upload_dir['url'] . '/' . $filename,
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => $filename_crop,
			'post_content'   => '',
			'post_status'    => 'inherit',
		);


		// Create the attachment.
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Define attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment.
		wp_update_attachment_metadata( $attach_id, $attach_data );


		// And finally assign featured image to post.
		set_post_thumbnail( $post_id, $attach_id );

	}

	/**
	 * Update time of request.
	 */
	public static function save_time_request() {
		update_option( 'narrative_last_request', date( 'U' ) );
	}

	/**
	 * Get attachment id by name.
	 *
	 * @param string $filename Name of image file..
	 *
	 * @return null|string
	 */
	public static function get_image_id_by_name( $filename ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s LIMIT 1;",
			$filename ) );
	}


}
