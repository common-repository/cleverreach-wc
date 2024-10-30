<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Subscriber_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use Exception;

/**
 * Class High_Performance_Subscriber_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance
 */
class High_Performance_Subscriber_Repository extends High_Performance_Base_Receiver_Repository implements Subscriber_Repository_Interface {


	/**
	 * Subscriber repository.
	 *
	 * @var Subscriber_Repository
	 */
	private $subscriber_repository;

	/**
	 * High_Performance_Subscriber_Repository constructor
	 */
	public function __construct() {
		$this->subscriber_repository = new Subscriber_Repository();
	}

	/**
	 * Get subscriber by email
	 *
	 * @param string $email Email.
	 *
	 * @return array|false|mixed|null
	 */
	public function get_by_email( $email ) {
		if ( empty( $email ) ) {
			return null;
		}

		$recipients = $this->get_registered_subscribers_by_emails( array( $email ) );

		if ( ! empty( $recipients ) ) {
			return current( $recipients );
		}

		$recipients = $this->get_guest_subscribers_by_emails( array( $email ) );

		return ! empty( $recipients ) ? current( $recipients ) : null;
	}

	/**
	 * Get emails
	 *
	 * @return string[]
	 */
	public function get_emails() {
		$registered_emails = $this->get_registered_emails();
		$guest_emails      = $this->get_guest_emails();

		return array_merge( $registered_emails, $guest_emails );
	}

	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @inheritDoc
	 */
	public function get_by_emails( $emails ) {
		if ( empty( $emails ) ) {
			return array();
		}

		$registered_subscribers = $this->get_registered_subscribers_by_emails( $emails );
		$guest_subscribers      = $this->get_guest_subscribers_by_emails( $emails );

		return array_merge( $registered_subscribers, $guest_subscribers );
	}

	/**
	 * Get subscribed buyers
	 *
	 * @return string[]
	 */
	public function get_subscribed_buyers() {
		$subscriber_value = Subscriber_Repository::STATUS_SUBSCRIBED;
		$subscriber_key   = Subscriber_Repository::get_newsletter_column();

		$sql_query = "select user.user_email as registered_email
						from {$this->get_db()->users} user
         					left outer join {$this->get_db()->prefix}wc_orders orders on orders.billing_email = user.user_email
         					left outer join {$this->get_db()->prefix}wc_orders_meta orders_meta on orders_meta.order_id = orders.id
						where (
    						orders.customer_id = '0'
    						and orders_meta.meta_key = '$subscriber_key'
    						and orders_meta.meta_value = '$subscriber_value'
    						) or (
        					orders.customer_id = user.ID
        					and orders_meta.meta_key = '$subscriber_key'
        					and orders_meta.meta_value = '$subscriber_value'
    					)
						group by user.user_email";

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

	/**
	 * Retrieves newsletter (subscribers and unsubscribes) by specific email.
	 *
	 * @param string $email Receiver email.
	 *
	 * @return mixed[]|null
	 * @throws Exception Exception.
	 */
	public function get_newsletter_by_email( $email ) {
		if ( empty( $email ) ) {
			return null;
		}

		$recipients = parent::get_by_emails( array( $email ) );

		return ! empty( $recipients ) ? current( $recipients ) : null;
	}

	/**
	 * Get subscribed users
	 *
	 * @return string[]
	 */
	public function get_subscribed_users() {
		return $this->subscriber_repository->get_subscribed_users();
	}

	/**
	 * Updates subscriber status.
	 *
	 * @param Receiver $receiver Subscriber.
	 *
	 * @return void
	 * @throws Exception Exception.
	 */
	public function update( Receiver $receiver ) {
		$users = $this->get_registered_receivers_by_emails( array( $receiver->getEmail() ) );

		if ( empty( $users ) ) {
			$this->update_guest_receiver( $receiver );
		} else {
			$users = current( $users );
			$receiver->setId( $users['ID'] );

			$this->update_registered_receiver( $receiver );
		}
	}

	/**
	 * Get registered emails
	 *
	 * @return mixed[]
	 */
	private function get_registered_emails() {
		$capability_users  = $this->get_users_with_capabilities();
		$subscribed_users  = $this->subscriber_repository->get_subscribed_users();
		$subscribed_buyers = $this->get_subscribed_buyers();

		return array_intersect( $capability_users, array_merge( $subscribed_users, $subscribed_buyers ) );
	}

	/**
	 * Retrieves registered subscribers by emails.
	 *
	 * @param string[] $emails Emails.
	 *
	 * @return mixed[]
	 */
	private function get_registered_subscribers_by_emails( $emails ) {
		if ( empty( $emails ) ) {
			return array();
		}

		$prepared_emails  = implode( "', '", esc_sql( $emails ) );
		$meta_keys        = implode( "', '", esc_sql( $this->get_registered_user_meta_keys() ) );
		$blog_prefix      = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$subscriber_value = Subscriber_Repository::STATUS_SUBSCRIBED;
		$subscriber_key   = Subscriber_Repository::get_newsletter_column();

		$sql_query = "select distinct user.ID, user.user_email, user.user_registered, user_meta.meta_key, user_meta.meta_value
				from {$this->get_db()->users} user
         			join {$this->get_db()->usermeta} user_meta on user.ID = user_meta.user_id
         			join {$this->get_db()->usermeta} as capabilities ON (capabilities.user_id = user.ID)
         			join {$this->get_db()->usermeta} as user_subscriber ON (user_subscriber.user_id = user.ID)
         			left outer join {$this->get_db()->prefix}wc_orders orders on (orders.billing_email = user.user_email)
         			left outer join {$this->get_db()->prefix}wc_orders_meta orders_meta on (orders_meta.order_id = orders.id)
				where user.user_email in ('$prepared_emails')
  					and capabilities.meta_key = '{$blog_prefix}capabilities'
				    and user_meta.meta_key in ('$meta_keys')
  					and ((user_subscriber.meta_key = '$subscriber_key' and user_subscriber.meta_value = '$subscriber_value')
        				or (orders.billing_email = user.user_email 
							and orders.customer_id = '0'
            				and orders_meta.meta_key = '$subscriber_key'
            				and orders_meta.meta_value = '$subscriber_value') 
						or (orders.customer_id = user.ID 
							and orders_meta.meta_key = '$subscriber_key'
               	 			and orders_meta.meta_value = '$subscriber_value')
               	 			)
				order by user_email";

		$receivers = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $receivers ) ) {
			return array();
		}

		$receivers      = $this->sanitize( $receivers, self::CUSTOMER_ID_PREFIX );
		$fetched_emails = $this->get_fetched_emails( $receivers, self::CUSTOMER_ID_PREFIX );

		$orders_data = $this->get_order_repository()->get_order_data_for_registered_emails( $fetched_emails );

		return $this->merge_orders_data_with_receivers(
			$receivers,
			$orders_data,
			self::CUSTOMER_ID_PREFIX
		);
	}

	/**
	 * Gets guest subscribers by emails.
	 *
	 * @param string[] $emails Emails.
	 *
	 * @return mixed[]
	 */
	private function get_guest_subscribers_by_emails( $emails ) {
		if ( empty( $emails ) ) {
			return array();
		}

		$prepared_emails  = implode( "', '", esc_sql( $emails ) );
		$subscriber_key   = Subscriber_Repository::get_newsletter_column();
		$subscriber_value = Subscriber_Repository::STATUS_SUBSCRIBED;
		$blog_prefix      = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders.ID, orders.date_created_gmt as 'user_registered', address.email, address.first_name, address.last_name, address.address_1, address.address_2, address.postcode, address.city, address.company, address.state, address.country, address.phone, order_meta.meta_value as '$subscriber_key'
						from {$this->get_db()->prefix}wc_orders orders
    					join {$this->get_db()->prefix}wc_order_addresses as address on orders.id = address.order_id
         				join (select om.order_id, om.meta_key, om.meta_value
               					from {$this->get_db()->prefix}wc_orders_meta om
               					where om.meta_key = '$subscriber_key') as order_meta on orders.ID = order_meta.order_id
								where address.address_type = 'billing' and orders.ID in (
									select max(orders.id) as post_id
                    				from {$this->get_db()->prefix}wc_orders orders
                    				where orders.id in (
                    					select orders_meta.order_id
                                        from {$this->get_db()->prefix}wc_orders orders
                                        inner join {$this->get_db()->prefix}wc_orders_meta orders_meta on orders_meta.order_id = orders.id
                                        where  orders.billing_email in ('$prepared_emails')
                                          and orders.billing_email not in (
                                            select u.user_email as email
                                            from {$this->get_db()->users} u
                                            join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
                                            where capabilities.meta_key = '{$blog_prefix}capabilities'
                                              and u.user_email in ('$prepared_emails')
                                        )
                                          and orders.customer_id = '0'
                                          and orders_meta.meta_key = '$subscriber_key'
                                          and orders_meta.meta_value = '$subscriber_value'
                                        group by orders_meta.order_id)
                    				group by orders.billing_email)";

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
	 * Returns array of guest subscribers emails
	 *
	 * @return string[]
	 */
	private function get_guest_emails() {
		$subscriber_key   = Subscriber_Repository::get_newsletter_column();
		$subscriber_value = Subscriber_Repository::STATUS_SUBSCRIBED;
		$blog_prefix      = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders.billing_email as guest_email
					  from {$this->get_db()->prefix}wc_orders orders
         				inner join {$this->get_db()->prefix}wc_orders_meta orders_meta on (orders.id = orders_meta.order_id)
					  where  orders.billing_email not in (
    					select u.user_email as email
    					from {$this->get_db()->users} u
             				join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
    					where capabilities.meta_key = '{$blog_prefix}capabilities'
						)
  						and orders.customer_id = '0'
  						and orders_meta.meta_key = '$subscriber_key'
  						and orders_meta.meta_value = '$subscriber_value'
					  group by orders.billing_email";

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $res ) {
				return $res['guest_email'];
			},
			$results
		);
	}

	/**
	 * Updates receiver subscriber status.
	 *
	 * @param Receiver $receiver Receiver.
	 *
	 * @return void
	 */
	private function update_guest_receiver( Receiver $receiver ) {
		// If guest is subscribed there should be at least one record with status SUBSCRIBED,
		// otherwise every record should have status UNSUBSCRIBED.
		if ( $receiver->isActive() ) {
			$sql_query = "select MAX(orders.id) as id
							from {$this->get_db()->prefix}wc_orders_meta order_meta
							join {$this->get_db()->prefix}wc_orders orders on orders.id = order_meta.order_id
							where orders.billing_email = '" . esc_sql( $receiver->getEmail() ) . "'
  							and orders.customer_id = '0'";

			$guest_id = $this->get_db()->get_var( $sql_query );

			/**
			 * Guest receiver ID.
			 *
			 * @var int| null $guest_id
			 */
			$guest_id = $guest_id ? (int) $guest_id : null;

			$order = wc_get_order( $guest_id );
			$order->update_meta_data( Subscriber_Repository::get_newsletter_column(), Subscriber_Repository::STATUS_SUBSCRIBED );
			$order->save();
		} else {
			$sql_query = "update {$this->get_db()->prefix}wc_orders_meta subscriber_status
    						join {$this->get_db()->prefix}wc_orders orders on subscriber_status.order_id = orders.id
							set subscriber_status.meta_value = %s
							where subscriber_status.meta_key = %s
  								and orders.billing_email = %s
  								and orders.customer_id = '0'";

			$prepared = $this->get_db()->prepare(
				$sql_query,
				Subscriber_Repository::STATUS_UNSUBSCRIBED,
				Subscriber_Repository::get_newsletter_column(),
				$receiver->getEmail()
			);

			$this->get_db()->query( $prepared );
		}
	}

	/**
	 * Updates registered receiver subscriber status.
	 *
	 * @param Receiver $receiver Receiver.
	 *
	 * @return void
	 */
	private function update_registered_receiver( Receiver $receiver ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		update_user_meta(
			$receiver->getId(),
			Subscriber_Repository::get_newsletter_column(),
			$receiver->isActive() ? Subscriber_Repository::STATUS_SUBSCRIBED : Subscriber_Repository::STATUS_UNSUBSCRIBED
		);
		if ( ! $receiver->isActive() ) {
			$sql_query = "update {$this->get_db()->prefix}wc_orders_meta subscriber_status
    						join {$this->get_db()->prefix}wc_orders orders on orders.id = subscriber_status.order_id
    							left outer join {$this->get_db()->users} u on orders.customer_id = u.ID
    							left outer join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
						  set subscriber_status.meta_value = %s
							where subscriber_status.meta_key = %s
  								and ((orders.billing_email = %s
    							and orders.customer_id = '0')
    						or (orders.customer_id = u.ID
        						and u.user_email = %s and capabilities.meta_key = '{$blog_prefix}capabilities'))";

			$prepared = $this->get_db()->prepare(
				$sql_query,
				Subscriber_Repository::STATUS_UNSUBSCRIBED,
				Subscriber_Repository::get_newsletter_column(),
				$receiver->getEmail(),
				$receiver->getEmail()
			);

			$this->get_db()->query( $prepared );
		}
	}
}
