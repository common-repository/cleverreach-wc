<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Contracts;

interface Double_Opt_In_Interface {


	const CLASS_NAME = __CLASS__;

	/**
	 * Save DOI value.
	 *
	 * @param bool $value DOI value.
	 *
	 * @return void
	 */
	public function save_double_opt_in( $value );

	/**
	 * Check if DOI is enabled.
	 *
	 * @return bool
	 */
	public function is_doi_enabled();
}
