<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

use CleverReach\WooCommerce\ViewModel\Billing_Email_Listener_Config;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox;

$listener_config = Billing_Email_Listener_Config::get_config();

$newsletter_checkbox_view_model = new CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox();
$newsletter_settings_view_model = new CleverReach\WooCommerce\ViewModel\Settings\Newsletter_Settings();
$abandoned_cart_view_model = new CleverReach\WooCommerce\ViewModel\Dashboard\Abandoned_Cart();
$is_abandoned_cart_enabled = $abandoned_cart_view_model->is_ac_function_enabled();
$ac_time = $abandoned_cart_view_model->get_ac_time();

$cr_checked = false;

$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$cr_checked = get_user_meta($current_user_id, Subscriber_Repository::get_newsletter_column(), true) === '1';

$cr_newsletter_caption = $newsletter_checkbox_view_model->get_newsletter_checkbox_caption();
$is_checkbox_disabled = $newsletter_checkbox_view_model->get_newsletter_checkbox_disabled();
$cr_subscription_success_message = $newsletter_settings_view_model->get_newsletter_subscription_confirmation_message();
$config = Newsletter_Checkbox::get_config();

if (wc()->session) {
	wc()->session->set('from_profile', false);
}
?>

<input id="cr-billing-email-listener" type="hidden" value="<?php
echo esc_attr($listener_config['listenerUrl']) ?>">

<?php
if (!$is_checkbox_disabled) { ?>
	<input type="hidden" id="crNewsletterStatusField"
		   value="<?php echo esc_attr($config['newsletterStatusField']) ?>"
	><input type="hidden" id="crSubscribeUrl"
			value="<?php echo esc_attr($config['subscribeUrl']) ?>"
	><input type="hidden" id="crUndoUrl"
			value="<?php echo esc_attr($config['undoUrl']) ?>"
	><input type="hidden" id="isAbandonedCartEnabled"
			value="<?php echo esc_attr($is_abandoned_cart_enabled) ?>"
	><input type="hidden" id="acTime"
			value="<?php echo esc_attr($ac_time) ?>"
	>
	<div class="wc-block-components-checkbox"><label style="display:none;"
		><input type="checkbox"
				class="wc-block-components-checkbox__input"
				name="<?php
	            echo esc_attr($config['newsletterStatusField']); ?>"
				id="<?php
	            echo esc_attr($config['newsletterStatusField']); ?>"
				value="1"
				<?php
				echo esc_attr($cr_checked ? ' checked="checked"' : ''); ?>
			>
			<svg
					class="wc-block-components-checkbox__mark" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
					viewBox="0 0 24 20">
				<path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"></path>
			</svg>
			<span><?php
				echo esc_html(__($cr_newsletter_caption, 'cleverreach-wc')); ?>
	</span>
		</label></div><label id="crSubscriptionConfirmationMessage"
							 style="font-weight: normal; display: none"><span>
		<?php
		echo esc_html(__($cr_subscription_success_message, 'cleverreach-wc')); ?>
        </span><a href="javascript:" id="crUndo"><?php
			echo esc_html(__('Undo', 'cleverreach-wc')); ?></a>
	</label>
	<?php
} ?>
