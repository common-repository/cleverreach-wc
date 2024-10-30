<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Site_Automation_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Clever_Reach_Abandoned_Cart_Settings_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Abandoned_Cart_Settings_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Activates abandoned cart.
	 *
	 * @return void
	 */
	public function activate_abandoned_cart() {
		try {
			$automation = new Site_Automation_Service();
			$automation->create();
			$this->return_json( array( 'success' => true ) );
		} catch ( Exception $e ) {
			$this->return_json(
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				),
				400
			);
		}
	}

	/**
	 * Deactivates abandoned cart.
	 *
	 * @return void
	 */
	public function deactivate_abandoned_cart() {
		try {
			$automation = new Site_Automation_Service();
			$automation->delete();
			$this->return_json( array( 'success' => true ) );
		} catch ( Exception $e ) {
			$this->return_json(
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				),
				400
			);
		}
	}

	/**
	 * Updates abandoned cart.
	 *
	 * @return void
	 */
	public function save_abandoned_cart_settings() {
		$delay = HTTP_Helper::get_param( 'hours' );

		try {
			if ( null !== $delay ) {
				$automation = new Site_Automation_Service();
				$automation->update_delay( (int) $delay );
			}
		} catch ( Exception $e ) {
			$this->return_json(
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				),
				400
			);
		}
		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Checks THEA activation status.
	 *
	 * @return void
	 */
	public function check_thea_status() {
		$automation = new Site_Automation_Service();
		$cart       = $automation->get();

		$this->return_json(
			array(
				'theaIsActive'       => null !== $cart && $cart->isActive(),
				'automationIsActive' => null !== $cart && 'created' === $cart->getStatus(),
			)
		);
	}

	/**
	 * Checks automation status.
	 *
	 * @return void
	 */
	public function check_automation_status() {
		$automation = new Site_Automation_Service();
		$cart       = $automation->get();

		$this->return_json(
			array(
				'theaID' => null === $cart ? '' : $cart->getCondition(),
				'status' => null === $cart ? '' : $cart->getStatus(),
			)
		);
	}
}
