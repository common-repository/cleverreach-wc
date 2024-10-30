<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Receiver_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Orders\Contracts\Order_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Orders\Order_Repository;
use wpdb;

/**
 * Class Base_Receiver_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers
 */
abstract class Base_Receiver_Repository implements Receiver_Repository_Interface {


	const CUSTOMER_ID_PREFIX = 'C-';
	const GUEST_ID_PREFIX    = 'G-';

	/**
	 * Returns meta keys for registered users
	 *
	 * @return string[]
	 */
	protected function get_registered_user_meta_keys() {
		$registered_user_meta_keys = array(
			'last_update',
			'first_name',
			'last_name',
			'billing_address_1',
			'billing_postcode',
			'billing_city',
			'billing_company',
			'billing_state',
			'billing_country',
			'billing_phone',
		);

		$registered_user_meta_keys[] = Subscriber_Repository::get_newsletter_column();
		$registered_user_meta_keys[] = $this->get_db()->prefix . 'capabilities';

		return $registered_user_meta_keys;
	}

	/**
	 * Returns meta keys for guest users
	 *
	 * @return string[]
	 */
	protected function get_guest_user_meta_keys() {
		$guest_user_meta_keys = array(
			'_billing_email',
			'_billing_first_name',
			'_billing_last_name',
			'_billing_address_1',
			'_billing_postcode',
			'_billing_city',
			'_billing_company',
			'_billing_state',
			'_billing_country',
			'_billing_phone',
		);

		$guest_user_meta_keys[] = Subscriber_Repository::get_newsletter_column();

		return $guest_user_meta_keys;
	}

	/**
	 * Database session object.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Order Repository Interface
	 *
	 * @var Order_Repository_Interface $order_repository
	 */
	private $order_repository;

	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @inheritDoc
	 */
	public function get_by_emails( $emails ) {
		$registered_subscribers = $this->get_registered_receivers_by_emails( $emails );
		$guest_subscribers      = $this->get_guest_receivers_by_emails( $emails );

		return array_merge( $registered_subscribers, $guest_subscribers );
	}

	/**
	 * Retrieves registered receiver by id.
	 *
	 * @param integer $receiver_id Receiver identification.
	 *
	 * @inheritDoc
	 */
	public function get_registered_receiver_by_id( $receiver_id ) {
		$user = get_userdata( $receiver_id );

		if ( ! $user ) {
			return null;
		}

		$users = $this->get_registered_receivers_by_emails( array( $user->user_email ) );

		return ! empty( $users ) ? current( $users ) : null;
	}

	/**
	 * Retrieves registered receivers by emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	protected function get_registered_receivers_by_emails( $emails ) {
		$prepared_emails = implode( "', '", esc_sql( $emails ) );
		$meta_keys       = implode( "', '", esc_sql( $this->get_registered_user_meta_keys() ) );
		$blog_prefix     = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select u.ID, u.user_email, u.user_registered, meta.meta_key, meta.meta_value
                      from {$this->get_db()->users} u
                      join {$this->get_db()->usermeta} meta_capabilities on u.ID = meta_capabilities.user_id
                      join {$this->get_db()->usermeta} meta on u.ID = meta.user_id
                      where u.user_email in ('$prepared_emails')
                       and meta.meta_key in ('$meta_keys')
                       and meta_capabilities.meta_key = '{$blog_prefix}capabilities'
                      order by user_email";

		$receivers = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $receivers ) ) {
			return array();
		}

		$receivers      = $this->sanitize( $receivers, self::CUSTOMER_ID_PREFIX );
		$fetched_emails = $this->get_fetched_emails( $receivers, self::CUSTOMER_ID_PREFIX );

		$orders_data = $this->get_order_repository()->get_order_data_for_registered_emails( $fetched_emails );

		return $this->merge_orders_data_with_receivers( $receivers, $orders_data, self::CUSTOMER_ID_PREFIX );
	}

	/**
	 * Retrieves guest receivers by emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	protected function get_guest_receivers_by_emails( $emails ) {
		$prepared_emails = implode( "', '", esc_sql( $emails ) );
		$meta_keys       = implode( "', '", esc_sql( $this->get_guest_user_meta_keys() ) );
		$blog_prefix     = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders.ID, orders.post_date as 'user_registered', order_meta.meta_key, order_meta.meta_value
					  from {$this->get_db()->posts} orders 
					  left join {$this->get_db()->postmeta} order_meta on orders.ID = order_meta.post_id
					  where orders.post_type = 'shop_order'
					    and order_meta.meta_key in ('$meta_keys')
					  	and orders.ID in (select max(pm2.post_id) as post_id
					  	                   from {$this->get_db()->postmeta} pm2 
					  	                   where pm2.meta_key = '_billing_email'
					  	                   		 and pm2.post_id in (select orders_be.post_id
                                                                        from {$this->get_db()->postmeta} orders_be
                                                                        where  orders_be.meta_key = '_billing_email'
                                                                            and orders_be.meta_value in ('$prepared_emails')
					  	                   									and orders_be.meta_value not in (
					  	                   									select u.user_email as email
					  	                   									from {$this->get_db()->users} u 
					  	                   										join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
					  	                   									where capabilities.meta_key = '{$blog_prefix}capabilities'
					  	                   									    and u.user_email in ('$prepared_emails'))
                                                                          	group by orders_be.post_id)
					  	                   group by pm2.meta_value)";

		$receivers = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $receivers ) ) {
			return array();
		}

		$receivers      = $this->sanitize( $receivers, self::GUEST_ID_PREFIX );
		$fetched_emails = $this->get_fetched_emails( $receivers, self::GUEST_ID_PREFIX );

		$orders_data = $this->get_order_repository()->get_order_data_for_guest_emails( $fetched_emails );

		return $this->merge_orders_data_with_receivers( $receivers, $orders_data, self::GUEST_ID_PREFIX );
	}

	/**
	 * Returns database session object.
	 *
	 * @return wpdb
	 */
	protected function get_db() {
		if ( null === $this->db ) {
			global $wpdb;
			$this->db = $wpdb;
		}

		return $this->db;
	}

	/**
	 * Return order repository
	 *
	 * @return Order_Repository_Interface
	 */
	protected function get_order_repository() {
		if ( null === $this->order_repository ) {
			$this->order_repository = new Order_Repository();
		}

		return $this->order_repository;
	}

	/**
	 * Merges orders data with receivers.
	 *
	 * @param mixed[] $receivers List of sanitized receivers.
	 * @param mixed[] $orders_data List of orders data.
	 * @param string  $user_prefix User prefix ['C-', 'G-'].
	 *
	 * @return mixed[]
	 */
	protected function merge_orders_data_with_receivers( $receivers, $orders_data, $user_prefix ) {
		foreach ( $orders_data as $order_data ) {
			if ( self::CUSTOMER_ID_PREFIX === $user_prefix ) {
				$key = $order_data['cr_email'];
			} else {
				$key = $order_data['userID'];
			}
			$receivers[ $user_prefix . $key ]['total_spent']     = $order_data['total_spent'];
			$receivers[ $user_prefix . $key ]['order_count']     = $order_data['order_count'];
			$receivers[ $user_prefix . $key ]['last_order_date'] = $order_data['last_order_date'];
		}

		return $receivers;
	}

	/**
	 * Sanitizes receivers data from database.
	 *
	 * @param mixed[] $data Array from database.
	 * @param string  $user_prefix User prefix ['C-', 'G-'].
	 *
	 * @return mixed[]
	 */
	protected function sanitize( $data, $user_prefix ) {
		$result = array();

		foreach ( $data as $record ) {
			if ( Subscriber_Repository::CUSTOMER_ID_PREFIX === $user_prefix ) {
				$index = $user_prefix . $record['user_email'];
			} else {
				$index = $user_prefix . $record['ID'];
			}
			$result[ $index ]                     = isset( $result[ $index ] ) ? $result[ $index ] : array();
			$result[ $index ] ['ID']              = $record['ID'];
			$result[ $index ] ['customer_number'] = $user_prefix . $record['ID'];
			$result[ $index ] ['user_email']      = isset( $record['user_email'] ) ? $record['user_email'] : '';
			$result[ $index ] ['user_registered'] = isset( $record['user_registered'] ) ? $record['user_registered'] : '';

			$meta_key                      = strpos(
				$record['meta_key'],
				'capabilities'
			) !== false ? 'roles' : $record['meta_key'];
			$result[ $index ][ $meta_key ] = $record['meta_value'];
		}

		return $result;
	}

	/**
	 * Returns emails from sanitized receivers data.
	 *
	 * @param mixed[] $receivers Array of sanitized receivers.
	 * @param string  $user_prefix User prefix ['C-', 'G-'].
	 *
	 * @return mixed[]
	 */
	protected function get_fetched_emails( $receivers, $user_prefix ) {
		$fetched_emails = array();

		if ( self::CUSTOMER_ID_PREFIX === $user_prefix ) {
			$key = 'user_email';
		} else {
			$key = '_billing_email';
		}

		foreach ( $receivers as $receiver ) {
			$fetched_emails[] = $receiver[ $key ];
		}

		return $fetched_emails;
	}

	/**
	 * Returns users with capabilities
	 *
	 * @return string[]
	 */
	protected function get_users_with_capabilities() {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$sql_query   = "select user.user_email as registered_email
				from {$this->get_db()->users} user
				    join {$this->get_db()->usermeta} as capabilities ON (capabilities.user_id = user.ID)
				where capabilities.meta_key = '{$blog_prefix}capabilities'";

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );
		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $res ) {
				return $res['registered_email'];
			},
			$results
		);
	}
}
