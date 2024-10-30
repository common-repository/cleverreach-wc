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
use CleverReach\WooCommerce\Components\WebHooks\Handlers\Group_Deleted_Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class Welcome_Config
 *
 * @package CleverReach\WooCommerce\ViewModel
 */
class Welcome_Config {


	/**
	 * Gets configuration for welcome page.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_welcome_config() {
		/**
		 * Translation service
		 *
		 * @var Translation_Service $translation_service
		 */
		$translation_service = ServiceRegister::getService( Translation_Service::CLASS_NAME );
		/**
		 * Queue service.
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service        = ServiceRegister::getService( QueueService::CLASS_NAME );
		$language            = $translation_service->getSystemLanguage();

		/**
		 * Auth service
		 *
		 * @var Auth_Service $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );

		return array(
			'checkStatusUrl' => Shop_Helper::get_controller_url( 'Auth', 'check_status' ),
			'authUrl'        => $auth_service->getAuthIframeUrl( $language ),
			'isGroupDeleted' => $queue_service->findLatestByType( Group_Deleted_Handler::getClassName() ) !== null,
		);
	}
}
