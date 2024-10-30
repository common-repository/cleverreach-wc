<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Subscriber_Repository_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use Exception;

/**
 * Class Subscriber_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers
 */
class Subscriber_Repository extends Base_Receiver_Repository implements Subscriber_Repository_Interface {


	const STATUS_UNSUBSCRIBED     = 0;
	const STATUS_SUBSCRIBED       = 1;
	const NEWSLETTER_STATUS_FIELD = 'cr_newsletter_status';

	/**
	 * Get receiver by email.
	 *
	 * @param string $email Receiver email.
	 *
	 * @inheritDoc
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
	 * Retrieves list of receiver emails provided by the integration.
	 *
	 * @inheritDoc
	 */
	public function get_emails() {
		$registered_emails = $this->get_registered_emails();
		$guest_emails      = $this->get_guest_emails();

		return array_merge( $registered_emails, $guest_emails );
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
	 * Returns full newsletter column name.
	 *
	 * @return string
	 */
	public static function get_newsletter_column() {
		global $wpdb;

		return $wpdb->prefix . self::NEWSLETTER_STATUS_FIELD;
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
	 * Retrieves registered subscribers by emails.
	 *
	 * @param string[] $emails List of emails.
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
		$subscriber_value = self::STATUS_SUBSCRIBED;
		$subscriber_key   = self::get_newsletter_column();

		$sql_query = "select distinct user.ID, user.user_email, user.user_registered, user_meta.meta_key, user_meta.meta_value
				from {$this->get_db()->users} user
				    join {$this->get_db()->usermeta} user_meta on user.ID = user_meta.user_id
				    join {$this->get_db()->usermeta} as capabilities ON (capabilities.user_id = user.ID)
				    join {$this->get_db()->usermeta} as user_subscriber ON (user_subscriber.user_id = user.ID)
					left outer join {$this->get_db()->postmeta} order_be on (order_be.meta_value = user.user_email)
					left outer join {$this->get_db()->postmeta} order_cu on (order_cu.post_id = order_be.post_id)
					left outer join {$this->get_db()->postmeta} order_subscriber on (order_subscriber.post_id = order_cu.post_id)
				where user.user_email in ('$prepared_emails')
				    and capabilities.meta_key = '{$blog_prefix}capabilities'
				    and user_meta.meta_key in ('$meta_keys')
				    and (
				        (user_subscriber.meta_key = '$subscriber_key' and user_subscriber.meta_value = '$subscriber_value')
				            or 
				        (order_be.meta_key = '_billing_email'
						    and order_be.meta_value = user.user_email
						    and order_cu.meta_key = '_customer_user'
						    and order_cu.meta_value = '0'
				            and order_subscriber.meta_key = '$subscriber_key' 
				            and order_subscriber.meta_value = '$subscriber_value'				                
				         ) or (
                        order_cu.meta_key = '_customer_user'
                           	and order_cu.meta_value = user.ID
				        	and order_subscriber.meta_key = '$subscriber_key' 
				            and order_subscriber.meta_value = '$subscriber_value'
				        ))
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
	 * Retrieves guest subscribers by emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	private function get_guest_subscribers_by_emails( $emails ) {
		if ( empty( $emails ) ) {
			return array();
		}

		$prepared_emails  = implode( "', '", esc_sql( $emails ) );
		$meta_keys        = implode( "', '", esc_sql( $this->get_guest_user_meta_keys() ) );
		$subscriber_key   = self::get_newsletter_column();
		$subscriber_value = self::STATUS_SUBSCRIBED;
		$blog_prefix      = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders.ID, orders.post_date as 'user_registered', order_meta.meta_key, order_meta.meta_value
					  from {$this->get_db()->posts} orders 
					      join (select om.post_id, om.meta_key, om.meta_value
					      		from {$this->get_db()->postmeta} om
					      		where om.meta_key in ('$meta_keys')) as order_meta on orders.ID = order_meta.post_id
					  where orders.post_type = 'shop_order'
					  	and orders.ID in (select max(pm2.post_id) as post_id
					  	                   from {$this->get_db()->postmeta} pm2 
					  	                   where pm2.meta_key = '_billing_email'
					  	                   		 and pm2.post_id in (select orders_be.post_id
                                                                        from {$this->get_db()->postmeta} orders_be
                                                                        inner join {$this->get_db()->postmeta} orders_cu on (orders_be.post_id = orders_cu.post_id)
                                                                        inner join {$this->get_db()->postmeta} orders_subscirber on (orders_be.post_id = orders_subscirber.post_id)
                                                                        where  orders_be.meta_key = '_billing_email'
                                                                        and orders_be.meta_value in ('$prepared_emails')
					  	                   								and orders_be.meta_value not in (
												                                select u.user_email as email
												                                from {$this->get_db()->users} u 
												                                join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
												                                where capabilities.meta_key = '{$blog_prefix}capabilities' 
												                                and u.user_email in ('$prepared_emails')
                            												)
                                                                        and orders_cu.meta_key = '_customer_user'
                                                                        and orders_cu.meta_value = '0'
                                                                        and orders_subscirber.meta_key = '$subscriber_key'
                                                                        and orders_subscirber.meta_value = '$subscriber_value'
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
	 * Returns array of registered subscribers emails
	 *
	 * @return string[]
	 */
	private function get_registered_emails() {
		$capability_users  = $this->get_users_with_capabilities();
		$subscribed_users  = $this->get_subscribed_users();
		$subscribed_buyers = $this->get_subscribed_buyers();

		return array_intersect( $capability_users, array_merge( $subscribed_users, $subscribed_buyers ) );
	}

	/**
	 * Get subscribed users
	 *
	 * @return string[]
	 */
	public function get_subscribed_users() {
		$subscriber_value = self::STATUS_SUBSCRIBED;
		$subscriber_key   = self::get_newsletter_column();

		$sql_query = "select user.user_email as registered_email
				from {$this->get_db()->users} user
				    join {$this->get_db()->usermeta} as user_subscriber ON (user_subscriber.user_id = user.ID)
				where user_subscriber.meta_key = '$subscriber_key' and user_subscriber.meta_value = '$subscriber_value'";

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
	 * Get subscribed buyers
	 *
	 * @return string[]
	 */
	public function get_subscribed_buyers() {
		$subscriber_value = self::STATUS_SUBSCRIBED;
		$subscriber_key   = self::get_newsletter_column();

		$sql_query = "select user.user_email as registered_email
				from {$this->get_db()->users} user
					left outer join {$this->get_db()->postmeta} order_be on (order_be.meta_value = user.user_email)
					left outer join {$this->get_db()->postmeta} order_cu on (order_cu.post_id = order_be.post_id)
					left outer join {$this->get_db()->postmeta} order_subscriber on (order_subscriber.post_id = order_cu.post_id)
				where (order_be.meta_key = '_billing_email'
						    and order_be.meta_value = user.user_email
						    and order_cu.meta_key = '_customer_user'
						    and order_cu.meta_value = '0'
				            and order_subscriber.meta_key = '$subscriber_key' 
				            and order_subscriber.meta_value = '$subscriber_value'				                
				         ) or (
                        order_cu.meta_key = '_customer_user'
                           	and order_cu.meta_value = user.ID
				        	and order_subscriber.meta_key = '$subscriber_key' 
				            and order_subscriber.meta_value = '$subscriber_value'
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
	 * Returns array of guest subscribers emails
	 *
	 * @return string[]
	 */
	private function get_guest_emails() {
		$subscriber_key   = self::get_newsletter_column();
		$subscriber_value = self::STATUS_SUBSCRIBED;
		$blog_prefix      = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders_be.meta_value as guest_email
	                    from {$this->get_db()->postmeta} orders_be
	                    inner join {$this->get_db()->postmeta} orders_cu on (orders_be.post_id = orders_cu.post_id)
	                    inner join {$this->get_db()->postmeta} orders_subscirber on (orders_be.post_id = orders_subscirber.post_id)
	                    where  orders_be.meta_key = '_billing_email'
	                    and orders_be.meta_value not in (
	                            select u.user_email as email
	                            from {$this->get_db()->users} u 
	                            join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
	                            where capabilities.meta_key = '{$blog_prefix}capabilities' 
	                        )
	                    and orders_cu.meta_key = '_customer_user'
	                    and orders_cu.meta_value = '0'
	                    and orders_subscirber.meta_key = '$subscriber_key'
	                    and orders_subscirber.meta_value = '$subscriber_value'
	                    group by orders_be.meta_value";

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
	 * Updates registered receiver subscriber status.
	 *
	 * @param Receiver $receiver Receiver object.
	 *
	 * @return void
	 */
	private function update_registered_receiver( Receiver $receiver ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		update_user_meta(
			$receiver->getId(),
			self::get_newsletter_column(),
			$receiver->isActive() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED
		);
		if ( ! $receiver->isActive() ) {
			$sql_query = "update {$this->get_db()->postmeta} subscriber_status 
    							join {$this->get_db()->postmeta} order_be on subscriber_status.post_id = order_be.post_id
    							join {$this->get_db()->postmeta} order_cu on order_be.post_id = order_cu.post_id
								left outer join {$this->get_db()->users} u on order_cu.meta_value = u.ID
    							left outer join {$this->get_db()->usermeta} capabilities on u.ID = capabilities.user_id
					  		  set subscriber_status.meta_value = %s
					  		  where subscriber_status.meta_key = %s 
					  		    and ((order_be.meta_key = '_billing_email' and order_be.meta_value = %s
					  		        and order_cu.meta_key = '_customer_user' and order_cu.meta_value = '0')
					  		    or (order_cu.meta_key = '_customer_user' and order_cu.meta_value = u.ID 
					  		            and u.user_email = %s and capabilities.meta_key = '{$blog_prefix}capabilities'))";

			$prepared = $this->get_db()->prepare(
				$sql_query,
				self::STATUS_UNSUBSCRIBED,
				self::get_newsletter_column(),
				$receiver->getEmail(),
				$receiver->getEmail()
			);

			$this->get_db()->query( $prepared );
		}
	}

	/**
	 * Updates receiver subscriber status.
	 *
	 * @param Receiver $receiver Receiver object.
	 *
	 * @return void
	 */
	private function update_guest_receiver( Receiver $receiver ) {
		// If guest is subscribed there should be at least one record with status SUBSCRIBED,
		// otherwise every record should have status UNSUBSCRIBED.
		if ( $receiver->isActive() ) {
			$sql_query = "select MAX(order_be.post_id) as id
						  from {$this->get_db()->postmeta} order_be
						      join {$this->get_db()->postmeta} order_cu on order_be.post_id = order_cu.post_id	
						  where order_be.meta_key = '_billing_email' 
						    and order_be.meta_value = '" . esc_sql( $receiver->getEmail() ) . "'
						    and order_cu.meta_key = '_customer_user' 
						    and order_cu.meta_value = '0'";

			$guest_id = $this->get_db()->get_var( $sql_query );

			/**
			 * Guest receiver ID.
			 *
			 * @var int| null $guest_id
			 */
			$guest_id = $guest_id ? (int) $guest_id : null;

			update_post_meta( $guest_id, self::get_newsletter_column(), self::STATUS_SUBSCRIBED );
		} else {
			$sql_query = "update {$this->get_db()->postmeta} subscriber_status
    						join {$this->get_db()->postmeta} order_be on subscriber_status.post_id = order_be.post_id
    						join {$this->get_db()->postmeta} order_cu on subscriber_status.post_id = order_cu.post_id
						  set subscriber_status.meta_value = %s
						  where subscriber_status.meta_key = %s 
						    and order_be.meta_key = '_billing_email' 
						    and order_be.meta_value = %s
						    and order_cu.meta_key = '_customer_user'
						    and order_cu.meta_value = '0'";

			$prepared = $this->get_db()->prepare(
				$sql_query,
				self::STATUS_UNSUBSCRIBED,
				self::get_newsletter_column(),
				$receiver->getEmail()
			);

			$this->get_db()->query( $prepared );
		}
	}
}
