<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\SingleSignOn\SingleSignOnProvider;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Clever_Reach_Single_Sign_On_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Single_Sign_On_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 *  Retrieves SSO url.
	 *
	 * @return void
	 */
	public function get_single_sign_on() {
		$redirect_url = SingleSignOnProvider::FALLBACK_URL;

		try {
			$param        = HTTP_Helper::get_param( 'param' );
			$redirect_url = SingleSignOnProvider::getUrl( $param );
		} catch ( BaseException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );
		}

		$this->return_json(
			array(
				'success' => true,
				'url'     => $redirect_url,
			)
		);
	}
}
