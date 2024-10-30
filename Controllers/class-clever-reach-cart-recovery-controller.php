<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Cart_Automation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Recovery_Record_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class Clever_Reach_Cart_Recovery_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Cart_Recovery_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;


	/**
	 * Executes cart recovery.
	 *
	 * @return void
	 */
	public function execute() {
		$token = HTTP_Helper::get_param( 'token' );

		if ( empty( $token ) ) {
			return;
		}

		$recovery_record_service = new Recovery_Record_Service();
		$recovery_record         = null;
		try {
			$recovery_record = $recovery_record_service->find_by_token( $token );
		} catch ( BaseException $e ) {
			Logger::logError(
				'Recovery cart not found.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
		}

		if ( ! $recovery_record ) {
			wp_redirect( wp_login_url( wc_get_checkout_url() ) );

			return;
		}

		$user = get_user_by( 'id', $recovery_record->get_session_key() );

		if ( empty( $user ) || get_current_user_id() === $user->ID ) {
			$cart_automation_service = new Cart_Automation_Service();
			$cart_automation_service->merge_carts_before_checkout( $recovery_record );

			wp_redirect( wc_get_checkout_url() );
		} else {
			wp_redirect( wp_login_url( wc_get_checkout_url() . '?cr_redirect=1&token=' . $token ) );
		}
	}
}
