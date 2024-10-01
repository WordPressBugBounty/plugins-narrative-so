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

		add_filter( 'pre_update_option_narrative_options',
			array( &$this, 'update_option' ) );
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
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

		add_menu_page( esc_html__( 'Narrative', 'narrative-publisher' ),
			esc_html__( 'Narrative', 'narrative-publisher' ), 'manage_options',
			'narrative', array(
				$this,
				'setting_page',
			), plugins_url( 'narrative-so/assets/narrative-brand-m.svg' ), 99 );

	}

	/**
	 * Register settings page.
	 */
	public function settings_init() {
		register_setting( 'narrative_settings', $this->options_slug );
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

		if ( empty( $_GET['post'] ) ) {
			return;
		}

		$get_post = sanitize_text_field( $_GET['post'] );

		$post_script = get_post_meta( $get_post, 'narrative_post_script',
			TRUE );

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
				'narrative-publisher' ) );
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
		wp_enqueue_script( 'moment',
			plugins_url( 'assets/moment.min.js', dirname( __FILE__ ) ),
			array( 'jquery' ), filemtime( plugin_dir_path( dirname( __FILE__ ) )
			                              . 'assets/moment.min.js' ), TRUE );

		wp_enqueue_script( 'narrative-admin-script',
			plugins_url( 'assets/admin-script.js', dirname( __FILE__ ) ), array(
				'jquery',
				'moment',
			), filemtime( plugin_dir_path( dirname( __FILE__ ) )
			              . 'assets/admin-script.js' ), TRUE );

	}


	/**
	 * Add button to the single post.
	 */
	public function do_meta_boxes() {

		if ( empty( $_GET['post'] ) ) {
			return;
		}

		$post = get_post( sanitize_text_field( $_GET['post'] ) );

		if ( empty( $post->post_content ) ) {
			return;
		}

		if ( ! has_shortcode( $post->post_content, 'narrative' ) ) {
			return;
		}
		?>
        <a target="_blank" href="narrative-app://open/"
           class="button button-primary button-large narrative_open_app_button">
			<?php esc_html_e( 'Edit in Narrative', 'narrative-publisher' ); ?>
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

	/**
	 * Update option hook
	 */
	public function update_option( $value ) {
		if ( ! empty( $_POST['narrative_options']['secret'] ) ) {
			set_transient( 'narratibe_update_secret', 'true' );
		}

		return $value;
	}

	/**
	 * Admin notices
	 */
	public function admin_notices() {

		if ( get_transient( 'narratibe_update_secret' ) ) {
			delete_transient( 'narratibe_update_secret' );
			?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Access Key added, please return to Narrative to publish your post',
						'narrative-publisher' ); ?></p>
            </div>
			<?php
		}
	}

}
