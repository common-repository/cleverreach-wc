<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;

/**
 * Class Auth_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Auth_Service extends AuthorizationService {


	/**
	 * Returns redirect url
	 *
	 * @param false $is_refresh Is refresh action.
	 *
	 * @return string
	 */
	public function getRedirectURL( $is_refresh = false ) {
		return Shop_Helper::get_controller_url( 'Auth', 'callback', array( 'isRefresh' => $is_refresh ) );
	}

	/**
	 * Retrieves color code of authentication iframe background.
	 *
	 * @return string
	 *     Color code.
	 */
	public function getAuthIframeColor() {
		return 'f1f1f1';
	}
}
