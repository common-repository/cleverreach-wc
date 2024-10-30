<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use wpdb;

/**
 * Class Cart_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Cart_Repository {


	/**
	 * Database session object.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Returns cart items by session key.
	 *
	 * @param string $session_key Session_key.
	 *
	 * @return array|mixed|string
	 */
	public function get_cart_items_by_session_key( $session_key ) {
		$session_data = $this->get_db()
							->get_var(
								$this->get_db()
									->prepare(
										"SELECT session_value FROM {$this->get_table_name()} WHERE session_key = %s",
										$session_key . ''
									)
							);

		$session_data = maybe_unserialize( $session_data );

		if ( isset( $session_data['cart'] ) ) {
			return maybe_unserialize( $session_data['cart'] );
		}

		return $this->get_persistent_cart_items( $session_key );
	}

	/**
	 * Returns database session object.
	 *
	 * @return wpdb
	 */
	private function get_db() {
		if ( null === $this->db ) {
			global $wpdb;
			$this->db = $wpdb;
		}

		return $this->db;
	}

	/**
	 * Returns sessions table
	 *
	 * @return string
	 */
	private function get_table_name() {
		return $this->get_db()->get_blog_prefix( get_current_blog_id() ) . 'woocommerce_sessions';
	}

	/**
	 * Return persistent cart items for user_id
	 *
	 * @param string $user_id User ID.
	 *
	 * @return mixed[]|mixed|string
	 */
	private function get_persistent_cart_items( $user_id ) {
		$meta_key   = '_woocommerce_persistent_cart_' . get_current_blog_id();
		$meta_value = get_user_meta( $user_id, $meta_key, true );

		if ( $meta_value && ! empty( $meta_value['cart'] ) ) {
			return $meta_value['cart'];
		}

		return array();
	}
}
