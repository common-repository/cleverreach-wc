<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\InitialSyncSettings;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;

/**
 * Class Initial_Sync_Settings_Config
 *
 * @package CleverReach\WooCommerce\ViewModel\InitialSyncSettings
 */
class Initial_Sync_Settings_Config {


	/**
	 * Gets configuration for initial sync page.
	 *
	 * @return array<string,string>
	 */
	public static function get_initial_sync_settings_config() {
		return array(
			'saveSettingsUrl' => Shop_Helper::get_controller_url(
				'Sync_Settings',
				'save_sync_settings'
			),
			'cancelUrl'       => Shop_Helper::get_controller_url( 'Uninstall', 'execute' ),
		);
	}
}
