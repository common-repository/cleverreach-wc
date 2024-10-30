<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Integration;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\StoreApi\StoreApi;

/**
 * Class Clever_Reach_Block_Extend_Store_Endpoint
 *
 * @package CleverReach\WooCommerce\Integration
 */
class Clever_Reach_Block_Extend_Store_Endpoint {
	/**
	 * Stores Rest Extending instance.
	 *
	 * @var ExtendSchema
	 */
	private static $extend;

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'cleverreach-wc';

	/**
	 * Bootstraps the class and hooks required data.
	 *
	 * @return void
	 */
	public static function init() {
		self::$extend = StoreApi::container()->get( ExtendSchema::class );
		self::extend_store();
	}

	/**
	 * Registers the actual data into each endpoint.
	 *
	 * @return void
	 */
	public static function extend_store() {
		if ( is_callable( array( self::$extend, 'register_endpoint_data' ) ) ) {
			self::$extend->register_endpoint_data(
				array(
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'schema_callback' => function () {
						return array(
							'is_subscribed' => array(
								'description' => __( 'Sign up for our newsletter.', 'cleverreach-wc' ),
								'type' => array( 'boolean', 'null' ),
							),
						);
					},
				)
			);
		}
	}
}
