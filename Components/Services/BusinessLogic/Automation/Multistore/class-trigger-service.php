<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Cart_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Recovery_Record_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\AbandonedCart;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\CartItem;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\CartAutomationTriggerService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Trigger_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore
 */
class Trigger_Service implements CartAutomationTriggerService {


	/**
	 * Automation Record Service.
	 *
	 * @var AutomationRecordService
	 */
	private $automation_record_service;

	/**
	 * Cart Service.
	 *
	 * @var Cart_Service
	 */
	private $cart_service;

	/**
	 * Config Service
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * Returns trigger.
	 *
	 * @param string $cart_id Cart ID.
	 *
	 * @return Trigger|null
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	public function getTrigger( $cart_id ) {
		$record = $this->get_automation_record_service()->findBy(
			array(
				'cartId' => (string) $cart_id,
				'status' => RecoveryEmailStatus::SENDING,
			)
		);

		if ( empty( $record ) ) {
			return null;
		}

		$cart_items = $this->get_cart_service()->get_cart_items_by_session_key( $cart_id );
		$record     = $record[0];

		$recovery_record = $this->create_recovery_record( $cart_id, $record->getEmail(), $cart_items, (string) $record->getId() );
		$abandoned_cart  = $this->prepare_cart( $cart_items, $record, $recovery_record );

		$trigger = new Trigger();
		$trigger->setCart( $abandoned_cart );
		$trigger->setGroupId( $record->getGroupId() );
		$trigger->setPoolId( $record->getEmail() );

		return $trigger;
	}

	/**
	 * Retrieves Automation Record Service.
	 *
	 * @return AutomationRecordService
	 */
	protected function get_automation_record_service() {
		if ( null === $this->automation_record_service ) {
			/**
			 * Automation record service.
			 *
			 * @var AutomationRecordService $automation_record_service
			 */
			$automation_record_service       = ServiceRegister::getService( AutomationRecordService::CLASS_NAME );
			$this->automation_record_service = $automation_record_service;
		}

		return $this->automation_record_service;
	}

	/**
	 * Prepares abandoned cart.
	 *
	 * @param mixed[]          $cart_items Cart items.
	 * @param AutomationRecord $automation_record Automation Record.
	 * @param Recovery_Record  $record Recovery record.
	 *
	 * @return AbandonedCart Abandoned cart.
	 */
	protected function prepare_cart( array $cart_items, $automation_record, Recovery_Record $record ) {
		$cart = new AbandonedCart();

		$total = 0;
		$vat   = 0;

		$items = $this->prepare_cart_items( $cart_items, $total, $vat );

		$cart->setCurrency( get_woocommerce_currency() );
		$cart->setStoreId( $this->get_config_service()->getIntegrationName() . ' - ' . get_current_blog_id() );
		$cart->setTotal( $automation_record->getAmount() );
		$cart->setVat( $vat );

		$cart->setCartItems( $items );

		$url = $this->get_cart_service()->get_recovery_link( $record );
		$cart->setReturnUrl( $url );

		return $cart;
	}

	/**
	 * Returns array of cart item objects.
	 *
	 * @param mixed[] $cart_items Items from cart.
	 * @param double  $total Total sum of cart.
	 * @param double  $vat Total vat of cart.
	 *
	 * @return CartItem[] Array of CartItem objects.
	 */
	private function prepare_cart_items( array $cart_items, &$total, &$vat ) {
		$items = array();

		foreach ( $cart_items as $cart_item ) {
			$product_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
			$wp_product = wc_get_product( $product_id );
			if ( ! $wp_product ) {
				continue;
			}

			$post = get_post( $product_id );

			$item = new CartItem();

			$item->setId( $product_id );
			$item->setName( $post->post_title );
			$item->setAmount( $cart_item['quantity'] );
			$item->setDescription( wp_strip_all_tags( $post->post_excerpt ) );

			// image width is set to 149 and not 190 because if there is no image which is 190px wide WordPress
			// will return image width which will be greater than 190px.
			$item->setImage( get_the_post_thumbnail_url( $post->ID, array( 149, 0 ) ) );
			$item->setProductUrl( get_permalink( $post->ID ) );

			$wp_price = (float) $wp_product->get_price( '' );
			$item->setSinglePrice( $wp_price );

			$item->setSku( (string) $post->ID );

			$total += $cart_item['line_total'];
			$vat   += $cart_item['line_tax'];

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Create recovery record
	 *
	 * @param string  $cart_id Cart id.
	 * @param string  $email User email.
	 * @param mixed[] $items Cart items.
	 * @param string  $automation_record_id Automation record id.
	 *
	 * @return Recovery_Record Recovery Record.
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	private function create_recovery_record( $cart_id, $email, $items, $automation_record_id ) {
		$recovery_record = new Recovery_Record();

		$recovery_record->set_token( hash( 'md5', time() . $cart_id ) );
		$recovery_record->set_email( $email );
		$recovery_record->set_session_key( $cart_id );
		$recovery_record->set_items( $items );
		$recovery_record->set_automation_record_id( $automation_record_id );

		$recovery_record_service = new Recovery_Record_Service();
		$recovery_record_service->create( $recovery_record );

		return $recovery_record;
	}

	/**
	 * Returns cart service
	 *
	 * @return Cart_Service
	 */
	private function get_cart_service() {
		if ( null === $this->cart_service ) {
			$this->cart_service = new Cart_Service();
		}

		return $this->cart_service;
	}

	/**
	 * Returns configuration service.
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		if ( null === $this->config_service ) {
			/**
			 * Config service.
			 *
			 * @var Config_Service $config_service
			 */
			$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}
}
