<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Dashboard;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Site_Automation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Abandoned_Cart
 *
 * @package CleverReach\WooCommerce\ViewModel\Dashboard
 */
class Abandoned_Cart {


	const DEFAULT_DELAY_IN_HOURS = 10;

	/**
	 * Cart automation
	 *
	 * @var CartAutomation $cart_automation
	 */
	private $cart_automation;

	/**
	 * Abandoned_Cart constructor.
	 */
	public function __construct() {
		$site_automation_service = new Site_Automation_Service();
		$this->cart_automation   = $site_automation_service->get();
	}

	/**
	 * Checks if Cart Automation is enabled.
	 *
	 * @return bool
	 */
	public function is_ac_function_enabled() {
		return null !== $this->cart_automation && 'created' === $this->cart_automation->getStatus();
	}

	/**
	 * Retrieves abandoned cart display time.
	 *
	 * @return int|null
	 */
	public function get_ac_time() {
		return $this->get_config_service()->get_checkbox_display_time();
	}

	/**
	 * Checks if Cart Automation is active.
	 *
	 * @return bool
	 */
	public function is_thea_active() {
		return $this->is_ac_function_enabled() && $this->cart_automation->isActive();
	}

	/**
	 * Retrieves THEA id.
	 *
	 * @return string
	 */
	public function get_thea_id() {
		if ( null === $this->cart_automation || null === $this->cart_automation->getCondition() ) {
			return '';
		}

		return $this->cart_automation->getCondition();
	}

	/**
	 * Retrieves Abandoned cart email delay.
	 *
	 * @return int
	 */
	public function get_delay() {
		if ( $this->is_ac_function_enabled() ) {
			$settings = $this->cart_automation->getSettings();
			if ( ! empty( $settings['delay'] ) ) {
				return (int) $settings['delay'];
			}
		}

		return self::DEFAULT_DELAY_IN_HOURS;
	}

	/**
	 * Gets config service.
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}
}
