<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Orders;

use CleverReach\WooCommerce\Components\Repositories\Orders\Contracts\Order_Repository_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\Category\Category;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem;
use DateTime;
use Exception;
use WC_Order;
use WC_Order_Item;
use WC_Product;
use wpdb;

/**
 * Class Order_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Orders
 */
class Order_Repository implements Order_Repository_Interface {


	/**
	 * Database session object.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Returns order items by order ids.
	 *
	 * @param string[] $order_ids list of order identifications.
	 *
	 * @inheritDoc
	 */
	public function get_order_items_by_order_ids( $order_ids ) {
		$order_items = array();
		if ( ! empty( $order_ids ) ) {
			foreach ( $order_ids as $id ) {
				/**
				 * WooCommerce order.
				 *
				 * @var WC_Order $order
				 */
				$order = $this->get_order_by_id( $id );

				if ( null !== $order ) {
					/**
					 * WooCommerce order item.
					 */
					foreach ( $order->get_items() as $item ) {
						$order_items[] = $this->format_order_item( $order, $item );
					}
				}
			}
		}

		return $order_items;
	}

	/**
	 * Retrieves list of order items for a given order id.
	 *
	 * @param string $order_id Order identification.
	 *
	 * @inheritDoc
	 */
	public function get_order_items( $order_id ) {
		return $this->get_order_items_by_order_ids( array( $order_id ) );
	}

	/**
	 * Returns orders data for the list of registered emails.
	 *
	 * @param string[] $emails emails.
	 *
	 * @inheritDoc
	 */
	public function get_order_data_for_registered_emails( $emails ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$emails      = implode( "', '", esc_sql( $emails ) );

		$sql_query = "select sum(pm2.meta_value) as total_spent, 
       						 count(pm2.meta_value) as order_count, 
       						 max(orders.post_date) as last_order_date,
       						 orders.cr_email as cr_email
					  from {$this->get_db()->postmeta} pm2 
					      join ( select distinct p.ID, p.post_date, ifnull(user1.user_email, user2.user_email) as cr_email
               					 from {$this->get_db()->posts} p 
               					     join {$this->get_db()->postmeta} orders_be on p.ID = orders_be.post_id
               					     join {$this->get_db()->postmeta} orders_cu on orders_be.post_id = orders_cu.post_id
					      			 left outer join {$this->get_db()->users} user1 on orders_cu.meta_value = user1.ID
               					     left outer join {$this->get_db()->users} user2 on orders_be.meta_value = user2.user_email
               					     left outer join {$this->get_db()->usermeta} capabilities1 on user1.ID = capabilities1.user_id
               					     left outer join {$this->get_db()->usermeta} capabilities2 on user2.ID = capabilities2.user_id
					      		 where p.post_type = 'shop_order'
					      		   and 
					      		       ((orders_be.meta_key = '_billing_email' and orders_be.meta_value in ('$emails')
					      		             and orders_cu.meta_key = '_customer_user' and orders_cu.meta_value = 0
					      		             and capabilities2.meta_key = '{$blog_prefix}capabilities') 
					      		            or (orders_be.meta_key = '_billing_email' and orders_cu.meta_key = '_customer_user'
					      		                    and orders_cu.meta_value > 0 and user1.user_email in ('$emails') 
					      		                	and capabilities1.meta_key = '{$blog_prefix}capabilities'))) as orders
					      on pm2.post_id = orders.ID
					  where pm2.meta_key = '_order_total'
					  group by orders.cr_email";

		$data = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Returns orders data for the list of guest emails.
	 *
	 * @param string[] $emails emails.
	 *
	 * @inheritDoc
	 */
	public function get_order_data_for_guest_emails( $emails ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$emails      = implode( "', '", esc_sql( $emails ) );

		$sql_query = "select sum(pm2.meta_value) as total_spent, 
       						 count(pm2.meta_value) as order_count, 
       						 max(orders.post_date) as last_order_date,
       						 max(orders.ID) as userID,
       						 orders.meta_value as cr_email
					  from {$this->get_db()->postmeta} pm2
					      join (select distinct p.ID, p.post_date, order_be.meta_value
					      		from {$this->get_db()->posts} p
					      			join {$this->get_db()->postmeta} order_be on p.ID = order_be.post_id
					      			join {$this->get_db()->postmeta} order_cu on p.ID = order_cu.post_id
					      		where p.post_type = 'shop_order'
					      		  and order_be.meta_key = '_billing_email'
					      		  and order_be.meta_value in ('$emails')
					      		  and order_cu.meta_key = '_customer_user'
					      		  and order_cu.meta_value = '0'
                      			  and order_be.meta_value not in (select u.user_email 
                      			                            from {$this->get_db()->users} u 
                      			  								join {$this->get_db()->usermeta} capabilities on capabilities.user_id = u.id
                      			                            where u.user_email in ('$emails') 
                      			                              and capabilities.meta_key = '{$blog_prefix}capabilities')) as orders
					          on pm2.post_id = orders.ID
					  where pm2.meta_key = '_order_total'
					  group by orders.meta_value";

		$data = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Returns orders data for email.
	 *
	 * @param string $email email.
	 *
	 * @inheritDoc
	 */
	public function get_orders_by_email( $email ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$where_condition = " where orders.post_type = 'shop_order'								
								and ((orders_be.meta_key = '_billing_email' and orders_be.meta_value = '$email'
								and orders_cu.meta_key = '_customer_user' and orders_cu.meta_value = '0')
								or (orders_be.meta_key = '_billing_email' and orders_cu.meta_key = '_customer_user'
									and orders_cu.meta_value > 0 and u.user_email = '$email' and 
									capabilities.meta_key = '{$blog_prefix}capabilities'))";

		$order_by = ' order by orders.post_date desc';

		$sql_query = "select orders.ID as id, orders.post_date
					  from {$this->get_db()->posts} orders
					      join {$this->get_db()->postmeta} orders_be on orders.ID = orders_be.post_id
					      join {$this->get_db()->postmeta} orders_cu on orders_be.post_id = orders_cu.post_id
					      left outer join {$this->get_db()->users} u on orders_cu.meta_value = u.ID
					      left outer join {$this->get_db()->usermeta} capabilities on orders_cu.meta_value = capabilities.user_id";

		$sql_query .= $where_condition . $order_by;

		$results = $this->get_db()->get_results( $sql_query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map(
			function ( $res ) {
				return $res['id'];
			},
			$results
		);
	}

	/**
	 * Formats one order item in proper format for order items sync.
	 *
	 * @param WC_Order      $order WooCommerce order.
	 * @param WC_Order_Item $item WooCommerce order item.
	 *
	 * @return OrderItem
	 *
	 * @throws Exception Exception.
	 */
	protected function format_order_item( WC_Order $order, WC_Order_Item $item ) {
		$product    = wc_get_product( wc_get_order_item_meta( $item->get_id(), '_product_id' ) );
		$order_item = new OrderItem( $order->get_order_number(), $item->get_id(), $item->get_name() );

		$order_item->setMailingId( get_post_meta( $order->get_id(), '_cr_mailing_id', true ) );
		$categories = array();

		/**
		 * WooCommerce product.
		 *
		 * @var WC_Product $product
		 */
		if ( is_object( $product ) ) {
			$categories = get_the_terms( $product->get_id(), 'product_cat' );
			$order_item->setProductId( $product->get_id() );
		}

		$category_names = array();
		if ( ! empty( $categories ) ) {
			$category_names = array_map(
				function ( $category ) {
					return new Category( $category->name );
				},
				$categories
			);
		}

		$post_date = new DateTime( $order->get_date_created() );
		$order_item->setStamp( $post_date->getTimestamp() );
		$order_item->setPrice( (float) wc_get_order_item_meta( $item->get_id(), '_line_total' ) );
		$order_item->setCurrency( get_woocommerce_currency() );
		$order_item->setQuantity( (int) wc_get_order_item_meta( $item->get_id(), '_qty' ) );

		$order_item->setCategories( $category_names );

		return $order_item;
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
	 * Retrieves order by id.
	 *
	 * @param int|string $id Order ID.
	 *
	 * @return WC_Order | null
	 */
	private function get_order_by_id( $id ) {
		$order = wc_get_order( $id );
		if ( ! $order ) {
			return null;
		}

		return $order;
	}
}
