<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Auth_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Language\Translation_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Refresh_Config
 *
 * @package CleverReach\WooCommerce\ViewModel
 */
class Refresh_Config {


	/**
	 * Gets configuration for refresh page.
	 *
	 * @return array<string,string>
	 */
	public static function get_refresh_config() {
		/**
		 * Translation Service
		 *
		 * @var Translation_Service
		 */
		$translation_service = ServiceRegister::getService( Translation_Service::CLASS_NAME );
		$language            = $translation_service->getSystemLanguage();

		/**
		 * Auth Service
		 *
		 * @var Auth_Service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );

		return array(
			'checkStatusUrl' => Shop_Helper::get_controller_url( 'Refresh', 'check_status' ),
			'authUrl'        => $auth_service->getAuthIframeUrl( $language, true ),
		);
	}
}
