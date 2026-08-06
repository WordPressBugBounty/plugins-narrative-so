<?php
/**
 * Admin class
 *
 * @package Narrative_Publisher/Admin;
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Init a settings page.
 */
class Admin {

	/**
	 * Slug of DB option.
	 *
	 * @var $options_slug
	 */
	public $options_slug = 'narrative_options';

	/**
	 * Request constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( &$this, 'add_menu_items' ) );

		add_action( 'admin_init', array( &$this, 'settings_init' ) );

		add_action( 'init', array( &$this, 'add_tiny_plugin' ) );

		add_action( 'admin_print_styles',
			array( &$this, 'admin_print_styles' ) );
		add_action( 'admin_print_styles',
			array( &$this, 'admin_print_script' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue' ) );

		add_action( 'edit_form_after_title', array(
			&$this,
			'do_meta_boxes',
		), 100 );

		add_filter( 'tiny_mce_before_init',
			array( &$this, 'fb_change_mce_options' ) );
	}

	/**
	 * Add a new button for MCE editor.
	 */
	public function add_tiny_plugin() {

		add_filter( 'mce_external_plugins', function ( $plugin_array ) {
			$plugin_array['narrative'] = plugins_url( 'assets/tiny-plugin.js',
				dirname( __FILE__ ) );

			return $plugin_array;
		} );

		add_filter( 'mce_buttons', function ( $buttons ) {
			array_push( $buttons, 'dropcap', 'showrecent' );

			return $buttons;
		} );
	}

	/**
	 * Add item to the admin menu.
	 */
	public function add_menu_items() {

		add_menu_page( esc_html__( 'Narrative', 'narrative-so' ),
			esc_html__( 'Narrative', 'narrative-so' ), 'manage_options',
			'narrative', array(
				$this,
				'setting_page',
			), plugins_url( 'assets/narrative-brand-m.svg', dirname( __FILE__ ) ), 99 );

	}

	/**
	 * Register settings page.
	 */
	public function settings_init() {
		register_setting(
			'narrative_settings',
			$this->options_slug,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize the settings before they are stored.
	 *
	 * Nothing used to validate the key: any string was accepted and reported as
	 * a success, so a mistyped key failed silently and the only symptom was that
	 * "Last connected" never moved.
	 *
	 * @param mixed $value Raw submitted value.
	 *
	 * @return array
	 */
	public function sanitize_options( $value ) {

		$existing        = get_option( $this->options_slug );
		$existing_secret = ( is_array( $existing ) && ! empty( $existing['secret'] ) )
			? $existing['secret']
			: '';

		$submitted = ( is_array( $value ) && isset( $value['secret'] ) ) ? $value['secret'] : '';
		$secret    = $this->normalize_secret( $submitted );

		if ( '' === $secret ) {
			add_settings_error(
				$this->options_slug,
				'narrative_secret_empty',
				esc_html__( 'Please paste the Access Key from your Narrative app.', 'narrative-so' ),
				'error'
			);

			return array( 'secret' => $existing_secret );
		}

		if ( ! $this->is_valid_secret( $secret ) ) {
			add_settings_error(
				$this->options_slug,
				'narrative_secret_invalid',
				esc_html__( 'That does not look like a valid Narrative Access Key. Copy it again from the Narrative app and paste it here. Your previous key has been kept.', 'narrative-so' ),
				'error'
			);

			return array( 'secret' => $existing_secret );
		}

		add_settings_error(
			$this->options_slug,
			'narrative_secret_saved',
			esc_html__( 'Access Key saved. Return to Narrative and publish a post to finish connecting.', 'narrative-so' ),
			'success'
		);

		return array( 'secret' => $secret );
	}

	/**
	 * Put a submitted key into the form the authenticator expects.
	 *
	 * Keys are base32, which is upper case. A lower case key stored fine and then
	 * failed to decode on every request, with nothing to indicate why. Spaces are
	 * stripped because keys are sometimes displayed in readable groups.
	 *
	 * @param mixed $secret Raw submitted key.
	 *
	 * @return string
	 */
	private function normalize_secret( $secret ) {

		if ( ! is_string( $secret ) ) {
			return '';
		}

		$secret = sanitize_text_field( $secret );

		return strtoupper( preg_replace( '/\s+/', '', $secret ) );
	}

	/**
	 * Check that a normalized key is one the authenticator can actually decode.
	 *
	 * Characters outside the base32 alphabet are skipped silently while decoding,
	 * so a key containing them would decode to something other than the key
	 * Narrative issued and would never authenticate.
	 *
	 * @param string $secret Normalized key.
	 *
	 * @return bool
	 */
	private function is_valid_secret( $secret ) {

		if ( ! preg_match( '/^[A-Z2-7]+=*$/', $secret ) ) {
			return false;
		}

		$base32 = new FixedBitNotation( 5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true );

		return '' !== $base32->decode( $secret );
	}

	/**
	 * Print style for logos
	 */
	public function admin_print_styles() {
		?>
        <style>
            #adminmenu #toplevel_page_narrative img {
                width: 21px;
                padding: 6.5px 0 0 4px;
            }

            #adminmenu #toplevel_page_narrative a.menu-top .wp-menu-name {
                color: #f46771;
                font-weight: 600;
                opacity: .6;
            }

            #adminmenu #toplevel_page_narrative.current img,
            #adminmenu #toplevel_page_narrative.current a.menu-top .wp-menu-name,
            #adminmenu #toplevel_page_narrative a.menu-top:hover .wp-menu-name {
                opacity: 1;
            }

            .narrative-big-logo {
                margin-top: 10px;
                margin-bottom: 10px;
            }

            .wp-core-ui .button.button-large.narrative_open_app_button {
                margin: 10px 5px 0;
                border-radius: 5px;
            }
        </style>


		<?php
	}

	/**
	 * Add scripts to the admin page.
	 */
	public function admin_print_script() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading the post being edited, not acting on input.
		if ( empty( $_GET['post'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- as above.
		$get_post = absint( wp_unslash( $_GET['post'] ) );

		if ( ! current_user_can( 'edit_post', $get_post ) ) {
			return;
		}

		$post_script = get_post_meta( $get_post, 'narrative_post_script',
			true );

		if ( empty( $post_script ) ) {
			return;
		}

		?>

        <script>
            var narrative_post_script = '<?php echo esc_js( $post_script ); ?>';
        </script>

		<?php
	}

	/**
	 * Show the admin template.
	 */
	public function setting_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.',
				'narrative-so' ) );
		}

		/*
		 * Include template admin settings
		 */
		include plugin_dir_path( dirname( __FILE__ ) ) . 'tmpl/admin.php';

	}

	/**
	 *  Get options by key.
	 *
	 * @param string $param It's param key.
	 *
	 * @return string
	 */
	public function general_options( $param = '' ) {

		if ( empty( $param ) ) {
			return '';
		}

		$general_option = get_option( $this->options_slug );

		if ( empty( $general_option [ $param ] ) ) {
			return '';
		}

		return $general_option [ $param ];
	}

	/**
	 * Enqueue scripts for admin page
	 *
	 * @return void
	 */
	public function admin_enqueue() {

		if ( ! is_admin() ) {
			return;
		}
		// moment is bundled with WordPress; declaring it as a dependency is
		// enough. Shipping our own copy overrode core's registration and is not
		// permitted in the plugin directory.
		wp_enqueue_script( 'narrative-admin-script',
			plugins_url( 'assets/admin-script.js', dirname( __FILE__ ) ), array(
				'jquery',
				'moment',
			), NARRATIVE_PUBLISHER_VERSION, true );

	}


	/**
	 * Add button to the single post.
	 */
	public function do_meta_boxes() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading the post being edited, not acting on input.
		if ( empty( $_GET['post'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- as above.
		$post = get_post( absint( wp_unslash( $_GET['post'] ) ) );

		if ( empty( $post->post_content ) ) {
			return;
		}

		if ( ! has_shortcode( $post->post_content, 'narrative' ) ) {
			return;
		}
		?>
        <a target="_blank" href="narrative-app://open/"
           class="button button-primary button-large narrative_open_app_button">
			<?php esc_html_e( 'Edit in Narrative', 'narrative-so' ); ?>
        </a>
		<?php
	}

	public function fb_change_mce_options( $init ) {
		$ext = 'div[id|name|class|style]';
		if ( isset( $init['extended_valid_elements'] ) ) {
			$init['extended_valid_elements'] .= ',' . $ext;
		} else {
			$init['extended_valid_elements'] = $ext;
		}

		return $init;
	}


}
