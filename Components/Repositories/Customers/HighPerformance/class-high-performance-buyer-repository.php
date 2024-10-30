<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Buyer_Repository_Interface;

/**
 * Class High_Performance_Buyer_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance
 */
class High_Performance_Buyer_Repository extends High_Performance_Base_Receiver_Repository implements Buyer_Repository_Interface {


	/**
	 * Get receiver by email.
	 *
	 * @param string $email Receiver email.
	 *
	 * @inheritDoc
	 */
	public function get_by_email( $email ) {
		$recipients = $this->get_registered_buyers_by_emails( array( $email ) );

		if ( ! empty( $recipients ) ) {
			return current( $recipients );
		}

		$recipients = $this->get_guest_receivers_by_emails( array( $email ) );

		return ! empty( $recipients ) ? current( $recipients ) : null;
	}

	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @inheritDoc
	 */
	public function get_by_emails( $emails ) {
		$registered_buyers = $this->get_registered_buyers_by_emails( $emails );
		$guest_buyers      = $this->get_guest_receivers_by_emails( $emails );

		return array_merge( $registered_buyers, $guest_buyers );
	}

	/**
	 * Retrieves list of receiver emails provided by the integration.
	 *
	 * @inheritDoc
	 */
	public function get_emails() {
		$registered_buyers = $this->get_registered_buyers_emails();
		$guest_buyers      = $this->get_guest_buyers_emails();

		return array_merge( $registered_buyers, $guest_buyers );
	}

	/**
	 * Returns registered buyers emails.
	 *
	 * @return string[]
	 */
	private function get_registered_buyers_emails() {
		$buyers = $this->get_buyer_emails_from_orders();
		$users  = $this->get_users_with_capabilities();

		return array_intersect( $users, $buyers );
	}

	/**
	 * Returns emails of guest buyers
	 *
	 * @return string[]
	 */
	private function get_guest_buyers_emails() {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$sql_query   = "select orders.billing_email as email
				from {$this->get_db()->prefix}wc_orders orders
				where orders.billing_email not in (
    				select u.user_email as email
    				from {$this->get_db()->users} u
             		join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
   					where capabilities.meta_key = '{$blog_prefix}capabilities'
				)
				group by orders.billing_email";

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $res ) {
				return $res['email'];
			},
			$results
		);
	}

	/**
	 * Gets registered buyers by email.
	 *
	 * @param string[] $emails Emails.
	 *
	 * @return mixed[]
	 */
	private function get_registered_buyers_by_emails( array $emails ) {
		$prepared_emails = implode( "', '", esc_sql( $emails ) );
		$meta_keys       = implode( "', '", esc_sql( $this->get_registered_user_meta_keys() ) );
		$blog_prefix     = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select user.ID, user.user_email, user.user_registered, user_meta.meta_key, user_meta.meta_value
						from {$this->get_db()->users} user
						join {$this->get_db()->usermeta} as user_meta on user.ID = user_meta.user_id
         				join {$this->get_db()->usermeta} as capabilities ON capabilities.user_id = user.ID
         				join {$this->get_db()->prefix}wc_orders orders on orders.billing_email = user.user_email
						where user.user_email in ('$prepared_emails')
  							and capabilities.meta_key = '{$blog_prefix}capabilities'
  							and (orders.customer_id = '0' or orders.customer_id = user.ID)
						    and user_meta.meta_key in ('$meta_keys')
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
	 * Returns emails from orders
	 *
	 * @return string[]
	 */
	protected function get_buyer_emails_from_orders() {
		$sql_query = "select distinct u.user_email as email
						from {$this->get_db()->users} u
         				join {$this->get_db()->prefix}wc_orders orders on orders.billing_email = u.user_email
						where orders.customer_id = '0'or orders.customer_id = u.ID
						group by u.user_email";

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $res ) {
				return $res['email'];
			},
			$results
		);
	}
}
