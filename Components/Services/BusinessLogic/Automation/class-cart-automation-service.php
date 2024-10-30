<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts\Cart_Automation_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\CartAutomationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class Cart_Automation_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Cart_Automation_Service extends CartAutomationService implements Cart_Automation_Service_Interface {


	/**
	 * Automation Record Service.
	 *
	 * @var AutomationRecordService
	 */
	private $automation_record_service;

	/**
	 * Merge carts before checkout.
	 *
	 * @param Recovery_Record $recovery_record Recovery record.
	 *
	 * @inheritDoc
	 */
	public function merge_carts_before_checkout( Recovery_Record $recovery_record ) {
		$cart_service = new Cart_Service();

		try {
			$cart_service->merge_carts( $recovery_record );

			$this->set_recovered_status_to_automation_record( $recovery_record );

			$recovery_record_service = new Recovery_Record_Service();
			$recovery_record_service->delete( $recovery_record );
		} catch ( Exception $e ) {
			Logger::logError(
				'Merging carts failed.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
		}
	}

	/**
	 * Set recovered status to automation record.
	 *
	 * @param Recovery_Record $recovery_record Recovery record.
	 *
	 * @return void
	 */
	private function set_recovered_status_to_automation_record( Recovery_Record $recovery_record ) {
		$automation_record = $this->get_automation_record_service()->find( $recovery_record->get_automation_record_id() );

		if ( ! isset( $automation_record ) ) {
			Logger::logError(
				'Failed to set recovery status (Recovered) on automation record.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', 'There is no automation record with provided id.' ),
				)
			);
		}

		$automation_record->setIsRecovered( true );
		try {
			$this->get_automation_record_service()->update( $automation_record );
		} catch ( FailedToUpdateAutomationRecordException $e ) {
			Logger::logError(
				'Failed to set recovery status (Recovered) on automation record.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
		}
	}

	/**
	 * Retrieves Automation Record Service.
	 *
	 * @return AutomationRecordService
	 */
	private function get_automation_record_service() {
		if ( null === $this->automation_record_service ) {
			/**
			 * Automation record service.
			 *
			 * @var AutomationRecordService $automation_record_service
			 */
			$automation_record_service       = ServiceRegister::getService( AutomationRecordService::CLASS_NAME );
			$this->automation_record_service = $automation_record_service;
		}

		return $this->automation_record_service;
	}
}
