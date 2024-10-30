<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;

/**
 * Interface Automation_Record_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Contracts
 */
interface Automation_Record_Service_Interface {


	/**
	 * Retrieves Automation Records.
	 *
	 * @param array<string,mixed> $filters Query filters.
	 * @param int                 $per_page Number of records per page.
	 * @param int                 $page_number Page number.
	 * @param string              $order_by Order column.
	 * @param string              $order Order direction.
	 *
	 * @return AutomationRecord[]
	 */
	public function get_records( $filters = array(), $per_page = 0, $page_number = 0, $order_by = '', $order = 'ASC' );

	/**
	 * Retrieves number of Automation Records that meet given criteria.
	 *
	 * @param array<string,mixed> $filters Conditions.
	 *
	 * @return int
	 */
	public function count( $filters = array() );

	/**
	 * Saves pagination for Abandoned Cart overview page.
	 *
	 * @param int $per_page Number of records per page.
	 *
	 * @return int|bool Meta ID if pagination wasn't set, true on successful update, false on failure.
	 */
	public function save_pagination( $per_page );
}
