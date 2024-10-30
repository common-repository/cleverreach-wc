<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Contact_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;

/**
 * Class High_Performance_Contact_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance
 */
class High_Performance_Contact_Repository extends High_Performance_Base_Receiver_Repository implements Contact_Repository_Interface {


	/**
	 * Get receiver by email.
	 *
	 * @param string $email Email.
	 *
	 * @return array|false|mixed|null
	 */
	public function get_by_email( $email ) {
		$recipients = $this->get_by_emails( array( $email ) );

		return ! empty( $recipients ) ? current( $recipients ) : null;
	}

	/**
	 *  Retrieves list of receiver emails provided by the integration.
	 *
	 * @inheritDoc
	 */
	public function get_emails() {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select u.user_email as email
					  from {$this->get_db()->users} u
					  join {$this->get_db()->usermeta} meta_capabilities on u.ID = meta_capabilities.user_id
                      left join {$this->get_db()->prefix}wc_orders orders on orders.customer_id = u.ID
                      left join {$this->get_db()->prefix}wc_orders orders_emails on orders_emails.billing_email = u.user_email
                      left join {$this->get_db()->usermeta} meta_cr_status on u.ID = meta_cr_status.user_id
                      where meta_capabilities.meta_key = '{$blog_prefix}capabilities'
                        and orders.customer_id IS NULL
                        and orders_emails.billing_email IS NULL
                        and meta_cr_status.meta_key != '" . Subscriber_Repository::get_newsletter_column() . "'
                        and meta_cr_status.meta_value != '" . Subscriber_Repository::STATUS_SUBSCRIBED . "'
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

	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @inheritDoc
	 */
	public function get_by_emails( $emails ) {
		$prepared_emails = implode( "', '", esc_sql( $emails ) );
		$meta_keys       = implode( "', '", esc_sql( $this->get_registered_user_meta_keys() ) );

		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "SELECT u.ID, u.user_email, u.user_registered, meta.meta_key, meta.meta_value
      				  FROM {$this->get_db()->users} u
                      JOIN {$this->get_db()->usermeta} meta_capabilities ON u.ID = meta_capabilities.user_id AND meta_capabilities.meta_key = '{$blog_prefix}capabilities'
                      JOIN (
                        SELECT user_meta.user_id, user_meta.meta_key, user_meta.meta_value
                        FROM {$this->get_db()->usermeta} user_meta
                        WHERE user_meta.meta_key IN ('$meta_keys')
                      ) AS meta ON u.ID = meta.user_id
                      LEFT JOIN {$this->get_db()->postmeta} order_cu ON u.ID = order_cu.meta_value AND order_cu.meta_key = '_customer_user'
                      LEFT JOIN (
                        SELECT order_be.meta_value
                        FROM {$this->get_db()->postmeta} order_be
                        JOIN {$this->get_db()->postmeta} order_cu ON order_be.post_id = order_cu.post_id
                        WHERE order_be.meta_key = '_billing_email'
                         AND order_cu.meta_key = '_customer_user'
                         AND order_cu.meta_value = 0
                      ) AS order_be ON u.user_email = order_be.meta_value
                      LEFT JOIN (
                        SELECT subscriber_meta.user_id
                        FROM {$this->get_db()->usermeta} subscriber_meta
                        JOIN {$this->get_db()->usermeta} capabilities ON subscriber_meta.user_id = capabilities.user_id
                        WHERE subscriber_meta.meta_key = '" . Subscriber_Repository::get_newsletter_column() . "'
                         AND subscriber_meta.meta_value = '" . Subscriber_Repository::STATUS_SUBSCRIBED . "'
                         AND capabilities.meta_key = '{$blog_prefix}capabilities'
                      ) AS subscriber_meta ON u.ID = subscriber_meta.user_id
                      WHERE u.user_email IN ('$prepared_emails')
                       AND order_cu.meta_value IS NULL
                       AND order_be.meta_value IS NULL
                       AND subscriber_meta.user_id IS NULL
                      ORDER BY u.user_email;";

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return $this->sanitize( $results, self::CUSTOMER_ID_PREFIX );
	}
}
