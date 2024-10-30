<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Auth_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\UserInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveUserInfoException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class User_Info
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class User_Info {

	/**
	 * User Info
	 *
	 * @var UserInfo
	 */
	private $user_info;

	/**
	 * UserInfo constructor.
	 *
	 * @throws FailedToRetrieveUserInfoException When user info can't be retrieved.
	 * @throws QueryFilterInvalidParamException When config data can't be retrieved.
	 */
	public function __construct() {
		$this->user_info = $this->get_auth_service()->getUserInfo();
	}

	/**
	 * Return user id
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->user_info->getId();
	}

	/**
	 * Returns user email
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->user_info->getEmail();
	}

	/**
	 * Returns Auth service
	 *
	 * @return Auth_Service
	 */
	private function get_auth_service() {
		/**
		 * Auth service.
		 *
		 * @var Auth_Service $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );

		return $auth_service;
	}
}
