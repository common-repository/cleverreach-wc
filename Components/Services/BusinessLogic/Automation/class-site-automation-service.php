<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService as AutomationRecordServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService as CartAutomationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\ViewModel\Dashboard\Abandoned_Cart;
use Exception;

/**
 * Class Automation_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Site_Automation_Service {


	/**
	 * Config Service
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * Creates automation record.
	 *
	 * @return CartAutomation
	 * @throws FailedToCreateCartException Failed To Create Cart Exception.
	 */
	public function create() {
		$cart = $this->get();
		if ( null !== $cart ) {
			return $cart;
		}

		$store_id        = $this->get_config_service()->getIntegrationName() . ' - ' . get_current_blog_id();
		$automation_name = $this->get_config_service()->getIntegrationName() . ' - Abandoned Cart - ' . get_bloginfo( 'name' );

		return $this->get_cart_automation_service()->create(
			$store_id,
			$automation_name,
			$this->get_config_service()->getIntegrationName(),
			array( 'delay' => Abandoned_Cart::DEFAULT_DELAY_IN_HOURS )
		);
	}

	/**
	 * Deletes automation.
	 *
	 * @return void
	 *
	 * @throws FailedToDeleteCartException Failed To Delete Cart Exception.
	 * @throws FailedToDeleteAutomationRecordException Failed To Delete Automation Record Exception.
	 */
	public function delete() {
		$cart = $this->get();
		if ( null !== $cart ) {
			$this->do_delete( $cart->getId() );
		}
	}

	/**
	 * Updates delay.
	 *
	 * @param int $delay Delay.
	 *
	 * @return CartAutomation
	 * @throws FailedToUpdateCartException Failed To Update Cart Exception.
	 */
	public function update_delay( $delay ) {
		$delay = (int) $delay;

		$cart = $this->get();
		if ( null === $cart ) {
			throw new FailedToUpdateCartException( 'Cart not found.' );
		}
		$cart->setSettings( array( 'delay' => $delay ) );
		$this->get_cart_automation_service()->update( $cart );

		return $cart;
	}

	/**
	 * Returns cart automation.
	 *
	 * @return CartAutomation|null
	 */
	public function get() {
		$store_id = $this->get_config_service()->getIntegrationName() . ' - ' . get_current_blog_id();
		try {
			$result = $this->get_cart_automation_service()->findBy( array( 'storeId' => $store_id ) );
		} catch ( Exception $e ) {
			Logger::logError(
				'Automation record not found.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
		}

		return ! empty( $result[0] ) ? $result[0] : null;
	}

	/**
	 * Returns configuration service.
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		if ( null === $this->config_service ) {
			/**
			 * Config service.
			 *
			 * @var Config_Service $config_service
			 */
			$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}

	/**
	 * Returns Cart automation service.
	 *
	 * @return CartAutomationService
	 */
	private function get_cart_automation_service() {
		/**
		 * Cart automation service.
		 *
		 * @var CartAutomationService $cart_automation_service
		 */
		$cart_automation_service = ServiceRegister::getService( CartAutomationServiceInterface::CLASS_NAME );

		return $cart_automation_service;
	}

	/**
	 * Returns Cart automation service.
	 *
	 * @return AutomationRecordService
	 */
	private function get_automation_record_service() {
		/**
		 * Automation record service.
		 *
		 * @var AutomationRecordService $automation_record_service
		 */
		$automation_record_service = ServiceRegister::getService( AutomationRecordServiceInterface::CLASS_NAME );

		return $automation_record_service;
	}

	/**
	 * Deletes automation.
	 *
	 * @param int $id Automation record's id.
	 *
	 * @return void
	 *
	 * @throws FailedToDeleteCartException Failed To Delete Cart Exception.
	 * @throws FailedToDeleteAutomationRecordException Failed To Delete Automation Record Exception.
	 */
	private function do_delete( $id ) {
		$this->get_automation_record_service()->deleteBy(
			array(
				'automationId' => $id,
				'status'       => RecoveryEmailStatus::PENDING,
			)
		);

		$this->get_cart_automation_service()->delete( $id );
	}
}
