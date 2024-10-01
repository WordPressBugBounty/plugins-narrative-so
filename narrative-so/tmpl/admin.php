<?php
/**
 * Created by PhpStorm.
 * User: narrativeapp
 * Date: 08.11.2018
 * Time: 10:52
 */

namespace Narrative_Publisher;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


$admin = new Admin();
?>
<div class="wrap narrative-settings">

    <form method="post" action="options.php" novalidate="novalidate">
        <img src="<?php echo esc_url( plugins_url( 'assets/narrative-brand.svg', dirname( __FILE__ ) ) ); ?>"
             class="narrative-big-logo" alt="">
        <hr>
        <table class="form-table">

            <tbody>
            <tr>
                <td width="150">
                    <label for="">
                        <b><?php esc_html_e( 'Your Access Key', 'narrative-publisher' ); ?></b>:
                    </label>
                </td>
                <td class="field">
                    <label for="">

                        <input class="form-control" type="password"
                               name="<?php echo esc_attr( $admin->options_slug ); ?>[secret]"
                               id="access_key"
                               placeholder="" value="<?php echo esc_attr( $admin->general_options( 'secret' ) ); ?>"
                               data-notice="<?php esc_html_e( 'Are you sure you want to change your Narrative Access Key, doing so may stop your plugin from working?', 'narrative-publisher' ); ?>">
                    </label>
                </td>
            </tr>
			<?php if ( ! empty( get_option( 'narrative_last_request' ) ) && is_numeric( get_option( 'narrative_last_request' ) ) ): ?>
                <tr>
                    <td width="150">
						<?php esc_html_e( 'Last connected', 'narrative-publisher' ); ?>:
                    </td>
                    <td class="field">
	                    <?php if ( empty( $admin->general_options( 'secret' ) ) ) : ?>
                            <b style="color: red;font-size: 14px;"><?php esc_html_e( 'Not connected. Please paste your access key from your Narrative app', 'narrative-publisher' ); ?></b>
	                    <?php else: ?>
                            <span class="nar-last-last-request"
                                  data-val="<?php echo esc_attr( get_option( 'narrative_last_request' ) ); ?>">
                            </span>
	                    <?php endif; ?>

                    </td>
                </tr>
			<?php endif; ?>

            <tr>
                <td>
                    <a target="_blank" href="<?php echo esc_url( 'https://help.narrative.so/articles/2866370-narrative-wordpress-plugin' ); ?>">
                        <?php esc_html_e( 'I need help', 'narrative-publisher' ); ?>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>

	    <?php
	    settings_fields( 'narrative_settings' );
	    submit_button( '', 'button-hero', 'submit', true, '' );

	    ?>

        <hr>

        <p style="font-size: 14px;padding-top: 10px;">
            <b><?php esc_html_e( 'This WordPress plugin integrates with Narrative\'s desktop app', 'narrative-publisher' ); ?></b>
            <br>
            <a target="_blank" href="<?php echo esc_url( 'https://my.narrative.so/#/free-trial', 'narrative-publisher' ); ?>">
                <?php esc_html_e( 'Click here to sign up', 'narrative-publisher' ); ?>
            </a>
        </p>


    </form>
</div>
