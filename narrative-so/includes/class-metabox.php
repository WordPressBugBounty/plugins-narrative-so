<?php
/**
 * Metabox.
 *
 * @package Narrative_Publisher/Metabox;
 */


namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Add new metabox.
 */
class Metabox {

	/**
	 * Handlers constructor.
	 *
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 0 );
		add_action( 'save_post', array( $this, 'savePost' ) );
		add_action( 'wp_head', array( $this, 'display_html_meta' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	}

	/**
	 * Regester narrative metabox.
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'narrative-meta-tags',
			esc_html__( 'Narrative SEO', 'narrative-publisher' ),
			array(
				$this,
				'show_meta_boxes',
			),
			'post',
			'side',
			'core'
		);
	}

	/**
	 * Render the metabox.
	 *
	 * @param object $post The post object.
	 */
	public function show_meta_boxes( $post ) {

		// Retrieve the metadata values if they exist.
		$narrative_meta_title       = get_post_meta( $post->ID, '_narrative_meta_title', true );
		$narrative_meta_description = get_post_meta( $post->ID, '_narrative_meta_description', true );
		$narrative_meta_keywords    = get_post_meta( $post->ID, '_narrative_meta_keywords', true );

		// Add an nonce field so we can check for it later when validating.
		wp_nonce_field( 'narrative_nonce', 'narrative_metabox_nonce' );

		// Get post title if narrative_meta_title is not exist.
		if ( empty( $narrative_meta_title ) ) {
			$title                = isset( $post->post_title ) ? $post->post_title : '';
			$id                   = isset( $post->ID ) ? $post->ID : 0;
			$narrative_meta_title = apply_filters( 'the_title', $title, $id );
		}

		?>
        <div>
            <table>
                <tr>
                    <td>
                        <strong><?php esc_html_e( 'Title', 'narrative-publisher' ); ?>:</strong></td>
                    <td>
                        <input style="padding: 6px 4px;" type="text" name="narrative_meta_title"
                               value="<?php echo esc_attr( $narrative_meta_title ); ?>"/>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Description', 'narrative-publisher' ); ?>:</strong></td>
                    <td>
                        <textarea rows="3" cols="22"
                                  name="narrative_meta_description"><?php echo esc_attr( $narrative_meta_description ); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Keywords', 'narrative-publisher' ); ?>:</strong></td>
                    <td>
                        <input style="padding: 6px 4px; " type="text" name="narrative_meta_keywords"
                               value="<?php echo esc_attr( $narrative_meta_keywords ); ?>"/>
                    </td>
                </tr>
            </table>
        </div>
		<?php
	}

	/**
	 * Save post to the DB.
	 *
	 * @param string $post_id Post id.
	 *
	 * @return mixed
	 */
	public function savePost( $post_id ) {

		$nonce = sanitize_text_field( $_POST['narrative_metabox_nonce'] );

		// Check if our nonce is set.
		if ( empty( $nonce ) ) {
			return $post_id;
		}


		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'narrative_nonce' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// If old entries exist, retrieve them.
		$old_title       = get_post_meta( $post_id, '_narrative_meta_title', true );
		$old_description = get_post_meta( $post_id, '_narrative_meta_description', true );
		$old_keywords    = get_post_meta( $post_id, '_narrative_meta_keywords', true );

		// Sanitize user input.
		$title       = sanitize_text_field( $_POST['narrative_meta_title'] );
		$description = sanitize_text_field( $_POST['narrative_meta_description'] );
		$keywords    = sanitize_text_field( $_POST['narrative_meta_keywords'] );

		// Update the meta field in the database.
		update_post_meta( $post_id, '_narrative_meta_title', $title, $old_title );
		update_post_meta( $post_id, '_narrative_meta_description', $description, $old_description );
		update_post_meta( $post_id, '_narrative_meta_keywords', $keywords, $old_keywords );

	}

	public function display_html_meta() {

		if ( ! is_single() ) {
			return;
		}

		// Retrieve the metadata values if they exist.
		$narrative_meta_title = get_post_meta( get_the_ID(), '_narrative_meta_title', true );
		$meta_description     = get_post_meta( get_the_ID(), '_narrative_meta_description', true );
		$meta_keywords        = get_post_meta( get_the_ID(), '_narrative_meta_keywords', true );

		// Get post title if narrative_meta_title is not exist.
		if ( empty( $narrative_meta_title ) ) {
			$narrative_meta_title = apply_filters( 'the_title', get_the_title(), get_the_ID() );
		}

		?>
        <meta property="og:title" content="<?php echo esc_attr( $narrative_meta_title ); ?>"/>
        <meta property="og:image" content="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID() ) ); ?>"/>
        <meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>"/>
        <meta name="description" content="<?php echo esc_attr( $meta_description ); ?>"/>
        <meta name="keywords" content="<?php echo esc_attr( $meta_keywords ); ?>"/>
		<?php
	}

	/**
	 * Add script to the front end
	 */
	public function wp_enqueue_scripts() {

		$body = get_post_meta( get_the_ID(), 'narrative_post_script', true );
		$body = stripslashes( base64_decode( $body ) );

		$pattern = '/\<script.*?src=(?:(?:\'([^\']*)\')|(?:"([^"]*)")|([^\s]*))/';

		preg_match( $pattern, $body, $matches );

		if ( ! empty( $matches[2] ) ) {

			wp_enqueue_script( 'narrative-publisher-script', $matches[2], null, '', true );
		}
	}


}
