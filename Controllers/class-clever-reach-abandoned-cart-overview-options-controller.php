<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Automation_Record_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Clever_Reach_Show_Abandoned_Cart_Overview_Options_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Abandoned_Cart_Overview_Options_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Automation record service.
	 *
	 * @var Automation_Record_Service
	 */
	private $automation_record_service;

	/**
	 * Save pagination.
	 *
	 * @return void
	 */
	public function save_pagination() {
		$per_page = HTTP_Helper::get_param( 'per_page' );
		if ( null !== $per_page ) {
			$pagination_result = $this->get_automation_record_service()->save_pagination( (int) $per_page );

			$this->return_json( array( 'success' => $pagination_result ) );
		}
		$this->return_json( array( 'success' => false ) );
	}

	/**
	 * Retrieves Cart automation service.
	 *
	 * @return Automation_Record_Service
	 */
	private function get_automation_record_service() {
		if ( null === $this->automation_record_service ) {
			$this->automation_record_service = new Automation_Record_Service();
		}

		return $this->automation_record_service;
	}
}
