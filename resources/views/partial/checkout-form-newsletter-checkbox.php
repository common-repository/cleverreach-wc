<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox;

$newsletter_checkbox_view_model = new CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox();
$newsletter_settings_view_model = new CleverReach\WooCommerce\ViewModel\Settings\Newsletter_Settings();
$abandoned_cart_view_model      = new CleverReach\WooCommerce\ViewModel\Dashboard\Abandoned_Cart();
$is_abandoned_cart_enabled      = $abandoned_cart_view_model->is_ac_function_enabled();
$ac_time                        = $abandoned_cart_view_model->get_ac_time();

$cr_checked = false;

$current_active_user    = wp_get_current_user();
$current_active_user_id = $current_active_user->ID;
$cr_checked             = get_user_meta( $current_active_user_id, Subscriber_Repository::get_newsletter_column(), true ) === '1';

$cr_newsletter_caption           = $newsletter_checkbox_view_model->get_newsletter_checkbox_caption();
$is_checkbox_disabled            = $newsletter_checkbox_view_model->get_newsletter_checkbox_disabled();
$cr_subscription_success_message = $newsletter_settings_view_model->get_newsletter_subscription_confirmation_message();
$config                          = Newsletter_Checkbox::get_config();

if ( wc()->session ) {
	wc()->session->set( 'from_profile', false );
}
?>
<?php
if ( ! $is_checkbox_disabled ) {
	?>
	<input type="hidden" id="crNewsletterStatusField"
			value="<?php echo esc_attr( $config['newsletterStatusField'] ); ?>"
	><input type="hidden" id="crSubscribeUrl"
			value="<?php echo esc_attr( $config['subscribeUrl'] ); ?>"
	><input type="hidden" id="crUndoUrl"
			value="<?php echo esc_attr( $config['undoUrl'] ); ?>"
	><input type="hidden" id="isAbandonedCartEnabled"
			value="<?php echo esc_attr( $is_abandoned_cart_enabled ); ?>"
	><input type="hidden" id="acTime"
			value="<?php echo esc_attr( $ac_time ); ?>"
	><label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"
	><input type="checkbox"
			class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
			name="<?php	// phpcs:ignore
			echo esc_attr( $config['newsletterStatusField'] );
			// phpcs:ignore
	        ?>"
			id="<?php	// phpcs:ignore
			echo esc_attr( $config['newsletterStatusField'] );
			// phpcs:ignore
	        ?>"
			value="1"<?php	// phpcs:ignore
			echo esc_attr( $cr_checked ? ' checked="checked"' : '' );
		// phpcs:ignore
		?>><span>
		<?php
			echo esc_html( __( $cr_newsletter_caption, 'cleverreach-wc' ) ); // phpcs:ignore
		?>
	</span>
	</label><label id="crSubscriptionConfirmationMessage" class="hidden"
					style="font-weight: normal"><span>
		<?php
		echo esc_html( __( $cr_subscription_success_message, 'cleverreach-wc' ) ); // phpcs:ignore
		?>
		</span><a href="javascript:" id="crUndo">
		<?php
			echo esc_html( __( 'Undo', 'cleverreach-wc' ) );
		?>
			</a>
	</label>
	<?php
} ?>
