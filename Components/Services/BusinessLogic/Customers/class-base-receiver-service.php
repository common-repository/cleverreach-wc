<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Base_Receiver_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Receiver_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Components\Repositories\Role_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Contracts\Receiver_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Order_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Tag_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts\OrderService as Base_Order_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\Decrement;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Contact;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Subscriber;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;
use DateTime;
use Exception;

/**
 * Class Base_Receiver_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers
 */
abstract class Base_Receiver_Service implements Receiver_Service_Interface {

	/**
	 * Tag service.
	 *
	 * @var Tag_Service
	 */
	protected $tag_service;

	/**
	 * Receiver repository.
	 *
	 * @var Receiver_Repository_Interface
	 */
	protected $receiver_repository;

	/**
	 * Role repository.
	 *
	 * @var Role_Repository
	 */
	private $role_repository;

	/**
	 * Retrieves receiver.
	 *
	 * @param string $email Email of the receiver.
	 * @param bool   $is_service_specific_data_required Is specific data required.
	 *
	 * @inheritDoc
	 * @throws Exception Exception.
	 */
	public function getReceiver( $email, $is_service_specific_data_required = false ) {
		$receiver = $this->receiver_repository->get_by_email( $email );

		if ( null === $receiver ) {
			return null;
		}

		/**
		 * Receiver.
		 *
		 * @var Receiver $receiver
		 */
		$receiver = current( $this->format_user_batch( array( $receiver ) ) );

		$this->set_tags( $receiver );

		return $receiver;
	}

	/**
	 * Retrieves receiver batch
	 *
	 * @param string[] $emails Array of emails to retrieve.
	 * @param bool     $is_service_specific_data_required Is specific data required.
	 *
	 * @inheritDoc
	 * @throws Exception Exception.
	 */
	public function getReceiverBatch( array $emails, $is_service_specific_data_required = false ) {
		$receivers = $this->receiver_repository->get_by_emails( $emails );

		if ( empty( $receivers ) ) {
			return array();
		}

		$receivers = $this->format_user_batch( $receivers );

		foreach ( $receivers as $receiver ) {
			$this->set_tags( $receiver );
		}

		return $receivers;
	}

	/**
	 * Retrieve Receiver emails.
	 *
	 * @inheritDoc
	 */
	public function getReceiverEmails() {
		return $this->receiver_repository->get_emails();
	}

	/**
	 * Subscribes receiver.
	 *
	 * @param Receiver $receiver Receiver to subscribe.
	 *
	 * @inheritDoc
	 */
	public function subscribe( Receiver $receiver ) {
		$receiver->addModifier( new Decrement( 'tags', (string) ( new Contact( Config_Service::INTEGRATION_NAME ) ) ) );
		$receiver->addTag( new Subscriber( Config_Service::INTEGRATION_NAME ) );
	}

	/**
	 * Unsubscribes receiver.
	 *
	 * @param Receiver $receiver Receiver to unsubscribe.
	 *
	 * @inheritDoc
	 */
	public function unsubscribe( Receiver $receiver ) {
		$receiver->addModifier(
			new Decrement(
				'tags',
				(string) ( new Subscriber( Config_Service::INTEGRATION_NAME ) )
			)
		);
	}

	/**
	 * Retrieves registered receiver by ID.
	 *
	 * @param int $receiver_id ID of receiver.
	 *
	 * @inheritDoc
	 * @throws Exception Exception.
	 */
	public function get_registered_receiver_by_id( $receiver_id ) {
		$receiver = $this->receiver_repository->get_registered_receiver_by_id( $receiver_id );

		if ( null === $receiver ) {
			return null;
		}

		/**
		 * Receiver.
		 *
		 * @var Receiver|null $receiver
		 */
		$receiver = current( $this->format_user_batch( array( $receiver ) ) );

		return $receiver;
	}

	/**
	 * Sets order data to receiver.
	 *
	 * @param Receiver $receiver Receiver to set order data to.
	 *
	 * @return void
	 */
	protected function set_order_data( $receiver ) {
		$order_items = $this->get_orders_service()->get_order_items_by_customer_email( $receiver->getEmail() );

		$receiver->setOrderItems( $order_items );

		foreach ( $order_items as $item ) {
			foreach ( $item->getCategories() as $category ) {
				$tag = new Tag( Config_Service::INTEGRATION_NAME, $category->getValue() );
				$tag->setType( 'Category' );
				$receiver->addTag( $tag );
			}
		}
	}

	/**
	 * Sets receiver tags.
	 *
	 * @param Receiver $receiver Receiver to set tags to.
	 *
	 * @return void
	 */
	protected function set_tags( Receiver $receiver ) {
		$is_guest = strpos( $receiver->getCustomerNumber(), Base_Receiver_Repository::GUEST_ID_PREFIX ) === 0;

		if ( $is_guest ) {
			$guest_customer_tag = new Tag( Config_Service::INTEGRATION_NAME, Tag_Service::GUEST_CUSTOMER_TAG );
			$guest_customer_tag->setType( Tag_Service::TAG_TYPE_ROLE );

			$receiver->addTag( $guest_customer_tag );
		}

		$site_tag = new Tag(
			Config_Service::INTEGRATION_NAME,
			Shop_Helper::get_shop_name() ? Shop_Helper::get_shop_name() : Shop_Helper::get_shop_url()
		);
		$site_tag->setType( Tag_Service::TAG_TYPE_SITE );

		$receiver->addTag( $site_tag );
	}

	/**
	 * Formats users into receivers.
	 *
	 * @param mixed[][] $users Array of users to format.
	 *
	 * @return Receiver[]
	 * @throws Exception Exception.
	 */
	protected function format_user_batch( $users ) {
		/**
		 * Array of receivers.
		 *
		 * @var Receiver[]
		 */
		$receivers = array();

		$wp_roles = $this->get_role_repository()->get_user_roles();

		foreach ( $users as $user ) {
			$receiver = new Receiver();
			$receiver->setId( $user['ID'] );
			$receiver->setEmail( $user['user_email'] ? $user['user_email'] : $user['_billing_email'] );
			$registered_date = new DateTime();
			if ( ! empty( $user['user_registered'] ) ) {
				$registered_date = DateTime::createFromFormat( Shop_Helper::DATE_FORMAT, $user['user_registered'] );
				if ( ! $registered_date ) {
					$registered_date = new DateTime();
				}
			}

			$receiver->setRegistered( $registered_date );

			$is_active = isset( $user[ Subscriber_Repository::get_newsletter_column() ] )
						&& ( Subscriber_Repository::STATUS_SUBSCRIBED == $user[ Subscriber_Repository::get_newsletter_column() ] );

			$receiver->setActive( $is_active );
			$receiver->setActivated(
				! empty( $user['last_update'] )
					? TimeProvider::getInstance()->getDateTime( $user['last_update'] )
					: new DateTime()
			);

			$receiver->setSource( Shop_Helper::get_shop_url() );
			$receiver->setShop( Shop_Helper::get_shop_name() );
			$receiver->setCustomerNumber( $user['customer_number'] );

			$receiver->setTotalSpent(
				! empty( $user['total_spent'] ) ? number_format( $user['total_spent'], 2 ) : $this->get_total_spent_for_customer( $user['ID'] )
			);
			$receiver->setOrderCount(
				! empty( $user['order_count'] ) ? (int) $user['order_count'] : wc_get_customer_order_count( $user['ID'] )
			);

			$last_order_date = ! empty( $user['last_order_date'] )
				? DateTime::createFromFormat( Shop_Helper::DATE_FORMAT, $user['last_order_date'] ) : null;
			$last_order = wc_get_customer_last_order( $user['ID'] );
			if ( null === $last_order_date && $last_order ) {
				$last_order_date = $last_order->get_date_created();
			}

			if ( $last_order_date instanceof DateTime ) {
				$receiver->setLastOrderDate( $last_order_date );
			}

			if ( isset( $user['roles'] ) ) {
				$tags = $this->get_tag_roles_for_receiver( $user['roles'], $wp_roles );
				$receiver->setTags( $tags );
			}

			$is_registered = strpos( $receiver->getCustomerNumber(), Subscriber_Repository::CUSTOMER_ID_PREFIX ) === 0;

			if ( $is_registered ) {
				$this->format_registered_user_data( $user, $receiver );
			} else {
				$this->format_guest_user_data( $user, $receiver );
			}

			$receivers[] = $receiver;
		}

		return $receivers;
	}

	/**
	 * Retrieves Tag service.
	 *
	 * @return Tag_Service
	 */
	protected function get_tag_service() {
		if ( null === $this->tag_service ) {
			$this->tag_service = new Tag_Service();
		}

		return $this->tag_service;
	}

	/**
	 * Retrieves tag roles for receiver.
	 *
	 * @param string              $user_roles User roles.
	 * @param array<string,mixed> $wp_roles WP roles.
	 *
	 * @return Tag[]
	 */
	private function get_tag_roles_for_receiver( $user_roles, $wp_roles ) {
		$roles = array();
		foreach ( $wp_roles as $key => $role ) {
			if ( strpos( $user_roles, $key ) !== false ) {
				$roles[] = $role;
			}
		}

		if ( ! empty( $roles ) ) {
			$roles = $this->get_tag_service()->get_origin_tags_by_roles( $roles );
		}

		return $roles;
	}

	/**
	 * Retrieves Role repository.
	 *
	 * @return Role_Repository
	 */
	private function get_role_repository() {
		if ( null === $this->role_repository ) {
			$this->role_repository = new Role_Repository();
		}

		return $this->role_repository;
	}

	/**
	 * Retrieves Order service.
	 *
	 * @return Order_Service
	 */
	private function get_orders_service() {
		/**
		 * Order service.
		 *
		 * @var Order_Service $order_service
		 */
		$order_service = ServiceRegister::getService( Base_Order_Service::CLASS_NAME );

		return $order_service;
	}

	/**
	 * Formats registered user data to Receiver.
	 *
	 * @param array<string,mixed> $user_data Data of the user.
	 * @param Receiver            $receiver Receiver to set data to.
	 *
	 * @return void
	 */
	private function format_registered_user_data( $user_data, $receiver ) {
		$receiver->setFirstName( isset( $user_data['first_name'] ) ? $user_data['first_name'] : '' );
		$receiver->setLastName( isset( $user_data['last_name'] ) ? $user_data['last_name'] : '' );
		$receiver->setStreet( isset( $user_data['billing_address_1'] ) ? $user_data['billing_address_1'] : '' );
		$receiver->setZip( isset( $user_data['billing_postcode'] ) ? $user_data['billing_postcode'] : '' );
		$receiver->setCity( isset( $user_data['billing_city'] ) ? $user_data['billing_city'] : '' );
		$receiver->setState( isset( $user_data['billing_state'] ) ? $user_data['billing_state'] : '' );

		if ( ! empty( $user_data['billing_country'] ) ) {
			$receiver->setCountry( wc()->countries->countries[ $user_data['billing_country'] ] );
		} else {
			$receiver->setCountry( '' );
		}

		$receiver->setCompany( isset( $user_data['billing_company'] ) ? $user_data['billing_company'] : '' );
		$receiver->setPhone( isset( $user_data['billing_phone'] ) ? $user_data['billing_phone'] : '' );
	}

	/**
	 * Formats guest user data to Receiver.
	 *
	 * @param array<string,mixed> $user_data Data of the user.
	 * @param Receiver            $receiver Receiver to set data to.
	 *
	 * @return void
	 */
	private function format_guest_user_data( $user_data, $receiver ) {
		$receiver->setFirstName( isset( $user_data['_billing_first_name'] ) ? $user_data['_billing_first_name'] : '' );
		$receiver->setLastName( isset( $user_data['_billing_last_name'] ) ? $user_data['_billing_last_name'] : '' );
		$receiver->setStreet( isset( $user_data['_billing_address_1'] ) ? $user_data['_billing_address_1'] : '' );
		$receiver->setZip( isset( $user_data['_billing_postcode'] ) ? $user_data['_billing_postcode'] : '' );
		$receiver->setCity( isset( $user_data['_billing_city'] ) ? $user_data['_billing_city'] : '' );
		$receiver->setState( isset( $user_data['_billing_state'] ) ? $user_data['_billing_state'] : '' );

		if ( ! empty( $user_data['_billing_country'] ) ) {
			$receiver->setCountry( wc()->countries->countries[ $user_data['_billing_country'] ] );
		} else {
			$receiver->setCountry( '' );
		}

		$receiver->setCompany( isset( $user_data['_billing_company'] ) ? $user_data['_billing_company'] : '' );
		$receiver->setPhone( isset( $user_data['_billing_phone'] ) ? $user_data['_billing_phone'] : '' );
	}

	/**
	 * Get total spent for customer.
	 *
	 * @param int $user_id - Customer ID.
	 *
	 * @return float
	 */
	private function get_total_spent_for_customer( $user_id ) {
		$customer_orders = wc_get_orders(
			array(
				'customer' => $user_id,
				'limit'    => -1,
			)
		);

		$total_amount = 0;
		foreach ( $customer_orders as $customer_order ) {
			$total_amount += $customer_order->get_total();
		}

		return $total_amount;
	}
}
