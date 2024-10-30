<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Newsletter_Subscription_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Clever_Reach_Newsletter_Subscription_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Newsletter_Subscription_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Schedule task for user subscription.
	 *
	 * @return void
	 */
	public function subscribe() {
		$cr_status     = HTTP_Helper::get_param( 'cr_status' );
		$billing_email = HTTP_Helper::get_param( 'billing_email' );
		$billing_email = is_user_logged_in() ? wp_get_current_user()->user_email : $billing_email;

		if ( ! is_email( $billing_email ) ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => 'Email address is not valid',
				),
				400
			);
		}

		if ( $cr_status ) {
			$service = new Newsletter_Subscription_Service();

			$scheduled_task_id = $service->subscribe( $billing_email );

			if ( $scheduled_task_id ) {
				WC()->session->set( 'undo_id', $scheduled_task_id );
			}

			$this->return_json( array( 'success' => true ) );
		}

		$this->return_json( array( 'success' => false ) );
	}

	/**
	 * Delete scheduled task for user subscription.
	 *
	 * @return void
	 */
	public function undo() {
		$service = new Newsletter_Subscription_Service();

		$task_id = WC()->session->get( 'undo_id' );

		if ( isset( $task_id ) ) {
			$service->undo( $task_id );

			WC()->session->set( 'undo_id', null );
			$this->return_json( array( 'success' => true ) );
		}

		$this->return_json( array( 'success' => false ) );
	}
}
