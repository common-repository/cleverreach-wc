<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Dashboard\Contracts\DeepLinks;

/**
 * Class Dashboard_Config
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Dashboard_Config {


	/**
	 * Sets up values for Dashboard tab.
	 *
	 * @return array<string,string>
	 */
	public static function get_dashboard_config() {
		return array(
			'helpUrl'                 => __( 'https://support.cleverreach.de/hc/en-us', 'cleverreach-wc' ),
			'uninstallUrl'            => Shop_Helper::get_controller_url( 'Uninstall', 'execute' ),
			'retrySyncUrl'            => Shop_Helper::get_controller_url( 'Initial_Sync', 'retry' ),
			'redirectUrl'             => Shop_Helper::get_controller_url( 'Single_Sign_On', 'get_single_sign_on' ),
			'displaySupportParamsUrl' => Shop_Helper::get_controller_url( 'Support', 'display' ),
			'updateSupportParamsUrl'  => Shop_Helper::get_controller_url( 'Support', 'modify' ),
			'statusCheckUrl'          => Shop_Helper::get_controller_url( 'Initial_Sync', 'check_status' ),
		);
	}

	/**
	 * Sets up values for sync settings tab.
	 *
	 * @return array<string,string>
	 */
	public static function get_settings_config() {
		return array(
			'saveSyncSettingsUrl'       => Shop_Helper::get_controller_url(
				'Sync_Settings',
				'save_sync_settings'
			),
			'saveNewsletterSettingsUrl' => Shop_Helper::get_controller_url(
				'Newsletter_Settings',
				'save_newsletter_settings'
			),
			'fetchSettingsUrl'          => Shop_Helper::get_controller_url(
				'Sync_Settings',
				'get_sync_settings'
			),
			'retrySecondarySyncUrl'     => Shop_Helper::get_controller_url( 'Secondary_Sync', 'retry' ),
			'checkSecondarySyncUrl'     => Shop_Helper::get_controller_url( 'Secondary_Sync', 'check_status' ),
			'getIntervalUrl'            => Shop_Helper::get_controller_url( 'Interval', 'get' ),
		);
	}

	/**
	 * Sets up values for abandoned cart tab.
	 *
	 * @return array<string,string>
	 */
	public static function get_abandoned_cart_config() {
		return array(
			'activateACUrl'            => Shop_Helper::get_controller_url(
				'Abandoned_Cart_Settings',
				'activate_abandoned_cart'
			),
			'deactivateACUrl'          => Shop_Helper::get_controller_url(
				'Abandoned_Cart_Settings',
				'deactivate_abandoned_cart'
			),
			'updateACUrl'              => Shop_Helper::get_controller_url(
				'Abandoned_Cart_Settings',
				'save_abandoned_cart_settings'
			),
			'theaStatusCheckUrl'       => Shop_Helper::get_controller_url(
				'Abandoned_Cart_Settings',
				'check_thea_status'
			),
			'automationStatusCheckUrl' => Shop_Helper::get_controller_url(
				'Abandoned_Cart_Settings',
				'check_automation_status'
			),
			'fetchSettingsUrl'         => Shop_Helper::get_controller_url(
				'Sync_Settings',
				'get_sync_settings'
			),
			'editEmailUrl'             => DeepLinks::CLEVERREACH_EDIT_AUTOMATION_URL,
		);
	}

	/**
	 * Returns deep links
	 *
	 * @return array<string,string>
	 */
	public static function get_deep_links() {
		return array(
			'createNewsletterUrl' => DeepLinks::CLEVERREACH_NEW_MAILING_URL,
			'reportsUrl'          => DeepLinks::CLEVERREACH_REPORTS_URL,
			'formsUrl'            => DeepLinks::CLEVERREACH_EDIT_FORM_URL,
			'theaUrl'             => DeepLinks::CLEVERREACH_AUTOMATION_URL,
			'emailsUrl'           => DeepLinks::CLEVERREACH_MAILINGS_URL,
			'pricePlanUrl'        => DeepLinks::CLEVERREACH_PRICE_PLANS_URL,
		);
	}
}
