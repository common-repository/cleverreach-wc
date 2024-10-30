<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance;

use CleverReach\WooCommerce\Components\Repositories\Customers\Base_Receiver_Repository;

/**
 * Class High_Performance_Base_Receiver_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance
 */
abstract class High_Performance_Base_Receiver_Repository extends Base_Receiver_Repository {

	/**
	 * Retrieves guest receivers by emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	protected function get_guest_receivers_by_emails( $emails ) {
		$prepared_emails = implode( "', '", esc_sql( $emails ) );

		$filter = "SELECT users.user_email
        FROM {$this->get_db()->prefix}users users
        WHERE users.user_email IN ('$prepared_emails')";

		$user_emails = $this->get_db()->get_results( $filter, ARRAY_A );

		if ( empty( $user_emails ) ) {
			$user_emails = array();
		} else {
			$user_emails = array_map(
				function ( $email_record ) {
					return $email_record['user_email'];
				},
				$user_emails
			);
		}

		$guest_emails    = array_filter(
			$emails,
			function ( $email ) use ( $user_emails ) {
				return ! in_array( $email, $user_emails );
			}
		);
		$prepared_emails = implode( "', '", esc_sql( $guest_emails ) );

		$sql_query = "SELECT orders.id as ID, orders.date_created_gmt AS 'user_registered', order_meta.email as user_email,
         				       order_meta.first_name as _billing_first_name, order_meta.last_name as _billing_last_name,
         				       order_meta.address_1 as _billing_address_1, order_meta.postcode as _billing_postcode, order_meta.city as _billing_city,
         				       order_meta.company as _billing_company, order_meta.state as _billing_state,
         				       order_meta.country as _billing_country, order_meta.phone as _billing_phone
         				FROM {$this->get_db()->prefix}wc_orders orders
         				    JOIN {$this->get_db()->prefix}wc_order_addresses AS order_meta ON orders.id = order_meta.order_id
         				WHERE order_meta.address_type = 'billing'
           					AND order_meta.email IN ('$prepared_emails')
                        ORDER BY orders.id";

		$receivers = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $receivers ) ) {
			return array();
		}

		$filtered_receivers = array();

		foreach ( $receivers as $receiver ) {
			$filtered_receiver = array_filter(
				$filtered_receivers,
				function ( $r ) use ( $receiver ) {
					return $r['user_email'] === $receiver['user_email'];
				}
			);

			if ( empty( $filtered_receiver ) ) {
				$filtered_receivers[] = $receiver;
			}
		}

		$receivers      = $this->sanitize( $receivers, static::GUEST_ID_PREFIX );
		$fetched_emails = $this->get_fetched_emails( $receivers, static::GUEST_ID_PREFIX );

		$orders_data = $this->get_order_repository()->get_order_data_for_guest_emails( $fetched_emails );

		return $this->merge_orders_data_with_receivers( $receivers, $orders_data, static::GUEST_ID_PREFIX );
	}

	/**
	 * Gets fetched emails.
	 *
	 * @inheritDoc
	 * @param mixed[] $receivers Receivers.
	 * @param string  $user_prefix User prefix.
	 *
	 * @return string[]
	 */
	protected function get_fetched_emails( $receivers, $user_prefix ) {
		$fetched_emails = array();
		foreach ( $receivers as $receiver ) {
			$fetched_emails[] = $receiver['user_email'];
		}

		return $fetched_emails;
	}

	/**
	 * Sanitize.
	 *
	 * @inheritDoc
	 * @param mixed[] $data Array.
	 * @param string  $user_prefix User prefix.
	 *
	 * @return mixed[]
	 */
	protected function sanitize( $data, $user_prefix ) {
		$result = array();

		if ( self::CUSTOMER_ID_PREFIX === $user_prefix ) {
			return parent::sanitize( $data, $user_prefix );
		}

		foreach ( $data as $record ) {
			$index = $user_prefix . $record['ID'];

			$result[ $index ]                    = $record;
			$result[ $index ]['customer_number'] = $user_prefix . $record['ID'];
		}

		return $result;
	}
}
