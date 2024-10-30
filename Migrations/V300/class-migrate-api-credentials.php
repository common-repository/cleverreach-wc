<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\OauthStatusProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;

/**
 * Class Migrate_API_Credentials.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Migrate_API_Credentials extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Migrate user API credentials.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	public function execute() {
		$database               = new Database( $this->db );
		$access_token           = $database->get_old_config_value( 'CLEVERREACH_ACCESS_TOKEN' );
		$refresh_token          = $database->get_old_config_value( 'CLEVERREACH_REFRESH_TOKEN' );
		$expiration_time_config = $database->get_old_config_value( 'CLEVERREACH_ACCESS_TOKEN_EXPIRATION_TIME' );

		$expiration_time = 0;
		if ( $expiration_time_config ) {
			$expiration_time = (int) $expiration_time_config;
		}

		$auth_info = new AuthInfo( $access_token, $refresh_token, $expiration_time );

		try {
			$this->get_auth_service()->setAuthInfo( $auth_info );
		} catch ( QueryFilterInvalidParamException $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}

		$connection_status = $this->get_oauth_status_proxy()->getConnectionStatus();

		if ( ! $connection_status->isConnected() ) {
			$this->get_auth_service()->setIsOffline( true );
		}
	}

	/**
	 * Retrieve Authorization service
	 *
	 * @return AuthorizationService
	 */
	private function get_auth_service() {
		/**
		 * Authorization service.
		 *
		 * @var AuthorizationService $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationServiceInterface::CLASS_NAME );

		return $auth_service;
	}

	/**
	 * Retrieve OAuth status proxy.
	 *
	 * @return OauthStatusProxy
	 */
	private function get_oauth_status_proxy() {
		/**
		 * Oauth status proxy.
		 *
		 * @var OauthStatusProxy $proxy
		 */
		$proxy = ServiceRegister::getService( OauthStatusProxy::CLASS_NAME );

		return $proxy;
	}
}
