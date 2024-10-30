<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToTriggerAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService as AutomationRecordServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Abandoned_Cart_Overview_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Abandoned_Cart_Overview_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Force sending.
	 *
	 * @return void
	 */
	public function force_send() {
		$record_id = HTTP_Helper::get_param( 'recordID' );
		try {
			$this->get_automation_record_service()->triggerRecord( (int) $record_id );
		} catch ( FailedToTriggerAutomationRecordException $e ) {
			Logger::logError(
				'Triggering automation record failed.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
			$this->return_json( array( 'success' => false ) );
		}
		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Delete automation record.
	 *
	 * @return void
	 */
	public function delete() {
		$record_id = HTTP_Helper::get_param( 'recordID' );

		try {
			$this->get_automation_record_service()->delete( $record_id );
		} catch ( FailedToDeleteAutomationRecordException $e ) {
			Logger::logError(
				'Deleting automation record failed.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
			$this->return_json( array( 'success' => false ) );
		}

		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Returns Cart automation service
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
}
