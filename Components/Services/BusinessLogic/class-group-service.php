<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupService;

/**
 * Class Group_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Group_Service extends GroupService {


	/**
	 * Retrieves name
	 *
	 * @inheritDoc
	 */
	public function getName() {
		return $this->getDefaultName();
	}

	/**
	 * Retrieves suffix of blacklisted emails.
	 *
	 * @inheritDoc
	 */
	public function getBlacklistedEmailsSuffix() {
		return '-' . $this->getConfigurationService()->getIntegrationName();
	}

	/**
	 * Retrieves integration specific group name.
	 *
	 * @return string Integration provided group name.
	 */
	public function getDefaultName() {
		// @phpstan-ignore-next-line
		return $this->getConfigurationService()->getIntegrationName() . ' - ' . get_bloginfo( 'name' );
	}
}
