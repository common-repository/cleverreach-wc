<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\User;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Offline_Mode_Tick_Handler
 *
 * @package CleverReach\WooCommerce\Components\User
 */
class Offline_Mode_Tick_Handler {


	const CLASS_NAME = __CLASS__;

	// 12 hours in seconds.
	const MIN_TIME_BETWEEN_CHECKS = 43200;

	/**
	 * Recovers user from offline mode.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Invalid filter param provided.
	 */
	public static function handle() {
		$last_check = static::get_config_service()->get_offline_mode_check_time();
		if ( static::get_auth_service()->isOffline() && ( ( time() - self::MIN_TIME_BETWEEN_CHECKS ) > $last_check ) ) {
			static::get_auth_service()->getFreshOfflineStatus();
			static::get_config_service()->set_offline_mode_check_time( time() );
		}
	}

	/**
	 * Retrieves authorization service.
	 *
	 * @return AuthorizationService
	 */
	protected static function get_auth_service() {
		/**
		 * Authorization service.
		 *
		 * @var AuthorizationService $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationServiceInterface::CLASS_NAME );

		return $auth_service;
	}

	/**
	 * Retrieves configuration service
	 *
	 * @return Config_Service
	 */
	protected static function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}
}
