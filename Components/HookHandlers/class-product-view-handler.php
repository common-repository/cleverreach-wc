<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Product_View_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Product_View_Handler extends Base_Handler {


	/**
	 * Handles get event on single product.
	 *
	 * @return void
	 */
	public function handle() {
		if ( ! $this->should_handle_event() ) {
			return;
		}

		$cr_mailing_id = HTTP_Helper::get_param( 'crmailing' );

		if ( ! empty( $cr_mailing_id ) ) {
			wc()->session->set( 'cr_mailing_id', $cr_mailing_id );
		}
	}
}
