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


$narrative_admin = new Admin();

$narrative_secret       = $narrative_admin->general_options( 'secret' );
$narrative_last_request = get_option( 'narrative_last_request' );
?>
<div class="wrap narrative-settings">

	<?php settings_errors( $narrative_admin->options_slug ); ?>

    <form method="post" action="options.php" novalidate="novalidate">
        <img src="<?php echo esc_url( plugins_url( 'assets/narrative-brand.svg', dirname( __FILE__ ) ) ); ?>"
             class="narrative-big-logo" alt="">
        <hr>
        <table class="form-table">

            <tbody>
            <tr>
                <td width="150">
                    <label for="">
                        <b><?php esc_html_e( 'Your Access Key', 'narrative-so' ); ?></b>:
                    </label>
                </td>
                <td class="field">
                    <label for="">

                        <input class="form-control" type="password"
                               name="<?php echo esc_attr( $narrative_admin->options_slug ); ?>[secret]"
                               id="access_key"
                               autocomplete="off"
                               spellcheck="false"
                               placeholder="" value="<?php echo esc_attr( $narrative_secret ); ?>"
                               data-notice="<?php esc_attr_e( 'Replace your existing Narrative Access Key? Publishing will stop working until the new key connects.', 'narrative-so' ); ?>">
                    </label>
                </td>
            </tr>
            <tr>
                <td width="150">
					<?php esc_html_e( 'Status', 'narrative-so' ); ?>:
                </td>
                <td class="field">
					<?php if ( empty( $narrative_secret ) ) : ?>
                        <b style="color: #d63638;font-size: 14px;"><?php esc_html_e( 'Not connected. Please paste your Access Key from your Narrative app.', 'narrative-so' ); ?></b>
					<?php elseif ( empty( $narrative_last_request ) || ! is_numeric( $narrative_last_request ) ) : ?>
                        <b style="color: #996800;font-size: 14px;"><?php esc_html_e( 'Access Key saved. Waiting for Narrative to connect — publish a post from the app to finish.', 'narrative-so' ); ?></b>
					<?php else : ?>
                        <b style="color: #008a20;font-size: 14px;"><?php esc_html_e( 'Connected', 'narrative-so' ); ?></b>
                        &mdash; <?php esc_html_e( 'last connected', 'narrative-so' ); ?>
                        <span class="nar-last-last-request"
                              data-val="<?php echo esc_attr( $narrative_last_request ); ?>">
                        </span>
					<?php endif; ?>
                </td>
            </tr>

            <tr>
                <td>
                    <a target="_blank" href="<?php echo esc_url( 'https://help.narrative.so/articles/2866370-narrative-wordpress-plugin' ); ?>">
                        <?php esc_html_e( 'I need help', 'narrative-so' ); ?>
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
            <b><?php esc_html_e( 'This WordPress plugin integrates with Narrative\'s desktop app', 'narrative-so' ); ?></b>
            <br>
            <a target="_blank" href="<?php echo esc_url( 'https://my.narrative.so/#/free-trial', 'narrative-so' ); ?>">
                <?php esc_html_e( 'Click here to sign up', 'narrative-so' ); ?>
            </a>
        </p>


    </form>
</div>
