<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Integration;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Plugin;
use CleverReach\WooCommerce\ViewModel\Billing_Email_Listener_Config;
use CleverReach\WooCommerce\ViewModel\Dashboard\Abandoned_Cart;
use CleverReach\WooCommerce\ViewModel\NewsletterCheckbox\Newsletter_Checkbox;
use CleverReach\WooCommerce\ViewModel\Settings\Newsletter_Settings;

/**
 * Class Clever_Reach_Checkout_Integration
 *
 * @package CleverReach\WooCommerce\Integration
 */
class Clever_Reach_Checkout_Integration implements IntegrationInterface {

	const INTEGRATION_NAME = 'cleverreach-wc';

	/**
	 * Returns integration name.
	 *
	 * @return string
	 */
	public function get_name() {
		return self::INTEGRATION_NAME;
	}

	/**
	 * Initialize checkout.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->register_shipping_workshop_block_frontend_scripts();
		$this->register_main_integration();
	}

	/**
	 * Get script handlers.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array(
			'cleverreach-wc-block-integration',
			'cleverreach-wc-block-frontend',
		);
	}

	/**
	 * Returns editor script handles.
	 *
	 * @return array
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * Returns script data.
	 *
	 * @return array
	 */
	public function get_script_data() {
		$listener_config = Billing_Email_Listener_Config::get_config();

		$newsletter_checkbox_view_model = new Newsletter_Checkbox();
		$newsletter_settings_view_model = new Newsletter_Settings();
		$abandoned_cart_view_model      = new Abandoned_Cart();
		$is_abandoned_cart_enabled      = $abandoned_cart_view_model->is_ac_function_enabled();
		$ac_time                        = $abandoned_cart_view_model->get_ac_time();

		$current_user    = wp_get_current_user();
		$current_user_id = $current_user->ID;
		$checked         = get_user_meta( $current_user_id, Subscriber_Repository::get_newsletter_column(), true ) === '1';

		$newsletter_caption           = $newsletter_checkbox_view_model->get_newsletter_checkbox_caption();
		$is_checkbox_disabled         = $newsletter_checkbox_view_model->get_newsletter_checkbox_disabled();
		$subscription_success_message = $newsletter_settings_view_model->get_newsletter_subscription_confirmation_message();
		$config                       = Newsletter_Checkbox::get_config();

		return array(
			'listener_url'                 => $listener_config['listenerUrl'],
			'is_checkbox_disabled'         => $is_checkbox_disabled,
			'newsletter_caption'           => $newsletter_caption,
			'checked'                      => $checked,
			'subscription_success_message' => $subscription_success_message,
			'newsletter_status_field'      => $config['newsletterStatusField'],
			'subscribe_url'                => $config['subscribeUrl'],
			'undo_url'                     => $config['undoUrl'],
			'is_abandoned_cart_enabled'    => $is_abandoned_cart_enabled,
			'ac_time'                      => $ac_time,
		);
	}

	/**
	 * Register shipping workshop block scripts.
	 *
	 * @return void
	 */
	private function register_shipping_workshop_block_frontend_scripts() {
		$script_url        = Plugin::get_plugin_url( '/cleverreach-wc/resources/blocks-build/checkout/cleverreach-wc-block-frontend.js' );
		$script_asset_path = Plugin::get_plugin_dir_path() . '/cleverreach-wc/resources/blocks-build/checkout/cleverreach-wc-block-frontend-asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

		wp_register_script(
			'cleverreach-wc-block-frontend',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_set_script_translations(
			'cleverreach-wc-block-frontend',
			'cleverreach-wc',
			Plugin::get_plugin_dir_path() . '/i18n/languages'
		);
	}

	/**
	 * Register main integration.
	 *
	 * @return void
	 */
	private function register_main_integration() {
		$script_url = Plugin::get_plugin_url( '/cleverreach-wc/resources/blocks-build/checkout/index.js' );

		$script_asset_path = Plugin::get_plugin_dir_path() . '/cleverreach-wc/resources/blocks-build/checkout/index-asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( '/cleverreach-wc/resources/blocks-build/checkout/index.js' ),
			);

		wp_register_script(
			'cleverreach-wc-block-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_set_script_translations(
			'cleverreach-wc-block-integration',
			'cleverreach-wc',
			Plugin::get_plugin_dir_path() . '/i18n/languages'
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 *
	 * @return string The cache buster value to use for the given file.
	 */
	private function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return SHIPPING_WORKSHOP_VERSION;
	}
}
