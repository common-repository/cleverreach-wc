<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Orders\HighPerformance;

use CleverReach\WooCommerce\Components\Repositories\Orders\Order_Repository;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\Category\Category;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem;
use DateTime;
use Exception;
use WC_Order;
use WC_Order_Item;
use WC_Product;

/**
 * Class High_Performance_Order_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Orders\HighPerformance
 */
class High_Performance_Order_Repository extends Order_Repository {

	/**
	 * Returns order data for guest emails.
	 *
	 * @param string[] $emails Guest emails.
	 *
	 * @return mixed[]|object|\stdClass[]
	 */
	public function get_order_data_for_guest_emails( $emails ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$emails      = implode( "', '", esc_sql( $emails ) );

		$sql_query = "select sum(orders.total_amount) as total_spent,
					   	count(orders.total_amount) as order_count,
       				  	max(o.date_created_gmt) as last_order_date,
       				   	max(o.id) as userID,
       					o.billing_email as cr_email
				from {$this->get_db()->prefix}wc_orders orders join (
    				select distinct id, date_created_gmt, billing_email
    				from {$this->get_db()->prefix}wc_orders orders
    				where orders.billing_email in ('$emails')
      				and orders.customer_id = '0'
      				and orders.billing_email not in (
      					select u.user_email
                        from {$this->get_db()->users} u
                        join {$this->get_db()->usermeta} capabilities on capabilities.user_id = u.id
                        where u.user_email in ('$emails')
                        and capabilities.meta_key = '{$blog_prefix}capabilities')
                        )
					as o on orders.id = o.ID
				group by o.billing_email";

		$data = $this->get_db()->get_results( $sql_query, ARRAY_A );

		return empty( $data ) ? array() : $data;
	}

	/**
	 * Gets order data for registered emails.
	 *
	 * @param string[] $emails Registered emails.
	 *
	 * @return mixed[]|object|\stdClass[]
	 */
	public function get_order_data_for_registered_emails( $emails ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );
		$emails      = implode( "', '", esc_sql( $emails ) );

		$sql_query = "select sum(o.total_amount) as total_spent,
       				  		count(o.id) as order_count,
       				  		max(orders.date_created_gmt) as last_order_date,
       				  		orders.billing_email as cr_email
					  from {$this->get_db()->prefix}wc_orders o
         				join ( select distinct orders.id, orders.date_created_gmt, orders.billing_email
                			   from {$this->get_db()->prefix}wc_orders orders
                        		left outer join {$this->get_db()->prefix}users user1 on orders.customer_id = user1.ID
                         		left outer join {$this->get_db()->prefix}users user2 on orders.billing_email = user2.user_email
                         		left outer join {$this->get_db()->prefix}usermeta capabilities1 on user1.ID = capabilities1.user_id
                         		left outer join {$this->get_db()->prefix}usermeta capabilities2 on user2.ID = capabilities2.user_id
                	  where (orders.billing_email in ('$emails')
                    		and orders.customer_id = 0
                    		and capabilities2.meta_key = '{$blog_prefix}capabilities')
                   		or (orders.customer_id > 0
                    		and orders.billing_email in ('$emails')
                    		and capabilities1.meta_key = '{$blog_prefix}capabilities')
					  ) as orders
              		  on o.id = orders.id
					  group by orders.billing_email";

		$data = $this->get_db()->get_results( $sql_query, ARRAY_A );

		return empty( $data ) ? array() : $data;
	}

	/**
	 * Returns orders by emails.
	 *
	 * @param string $email Emails.
	 *
	 * @return string[]
	 */
	public function get_orders_by_email( $email ) {
		$blog_prefix = $this->get_db()->get_blog_prefix( get_current_blog_id() );

		$sql_query = "select orders.id as id, orders.date_created_gmt
					  	from {$this->get_db()->prefix}wc_orders orders
         				left outer join {$this->get_db()->prefix}users u on orders.customer_id = u.ID
         				left outer join {$this->get_db()->prefix}usermeta capabilities on orders.customer_id = capabilities.user_id
					   where ((orders.billing_email = '$email'and orders.customer_id = '0')
       		 			or (orders.customer_id > 0 and u.user_email = '$email' and
            			capabilities.meta_key = '{$blog_prefix}capabilities')) order by orders.date_created_gmt desc";

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

		$order_item->setMailingId( $order->get_meta( '_cr_mailing_id' ) );
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
}
