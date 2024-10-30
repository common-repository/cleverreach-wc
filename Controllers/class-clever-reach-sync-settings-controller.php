<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CleverReach\WooCommerce\Components\Exceptions\Invalid_Filter_Settings_Exception;
use CleverReach\WooCommerce\Components\Exceptions\Invalid_Sync_Settings_Exception;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Site_Automation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Order_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Buyer_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Contact_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Subscriber_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Sync_Settings_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\DTO\BlacklistFilterConfig;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\StaticFilterNotValidException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Exceptions\WildcardFilterNotValidException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts\OrderService as Base_Order_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Contracts\SyncSettingsService as Base_Sync_Settings_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Sync_Settings_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Sync_Settings_Controller extends Clever_Reach_Base_Controller {
	/**
	 * Default filter configuration
	 *
	 * @var array<string>
	 */
	private static $default_filter_config = array(
		'type' => 'static',
		'rule' => '',
	);
	/**
	 * Sync settings service.
	 *
	 * @var Sync_Settings_Service $sync_settings_service
	 */
	private $sync_settings_service;

	/**
	 * Blacklist filter service.
	 *
	 * @var BlacklistFilterService
	 */
	private $blacklist_filter_service;
	/**
	 * Buffer config service.
	 *
	 * @var BufferConfigurationInterface
	 */
	private $buffer_config_service;

	/**
	 * Saves sync settings.
	 *
	 * @return void
	 *
	 * @throws Invalid_Filter_Settings_Exception Invalid Filter Settings Exception.
	 */
	public function save_sync_settings() {
		try {
			$this->save_filter_configuration();
			$this->save_interval_config();
			$this->save_services();
			$this->return_json( array( 'success' => true ) );
		} catch ( StaticFilterNotValidException $e ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => __( 'Static filter is not valid. Please enter up to 500 coma-separated emails.', 'cleverreach-wc' ),
				)
			);
		} catch ( WildcardFilterNotValidException $e ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => __( 'Wildcard filter pattern is not valid. Please use * for any sequence of characters and ? for any single character.', 'cleverreach-wc' ),
				)
			);
		} catch ( Invalid_Sync_Settings_Exception $e ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				)
			);
		} catch ( \InvalidArgumentException $e ) {
			$this->return_json(
				array(
					'success' => false,
					'message' => __( $e->getMessage(), 'cleverreach-wc' ), // phpcs:ignore
				)
			);
		}
	}

	/**
	 * Retrieves sync settings
	 *
	 * @return void
	 */
	public function get_sync_settings() {
		$enabled_services        = $this->get_enabled_services_formatted();
		$site_automation_service = new Site_Automation_Service();
		$cart_automation         = $site_automation_service->get();
		$filter_configuration    = $this->get_filter_service()->getBlacklistFilterConfig();

		$this->return_json(
			array(
				'enabled_services'     => $enabled_services,
				'thea_activated'       => null !== $cart_automation && 'created' === $cart_automation->getStatus(),
				'filter_configuration' => null !== $filter_configuration ? $filter_configuration->toArray() : self::$default_filter_config,
			)
		);
	}

	/**
	 * Save filter configuration
	 *
	 * @return void
	 *
	 * @throws Invalid_Filter_Settings_Exception Exception.
	 */
	private function save_filter_configuration() {
		$this->validate_filter_payload();
		$settings      = json_decode( HTTP_Helper::get_param( 'filterSettings' ), true );
		$filter_config = new BlacklistFilterConfig( $settings['filterType'], $settings['input'] );
		$this->get_filter_service()->saveBlacklistFilterConfig( $filter_config );
	}

	/**
	 * Validate filter payload
	 *
	 * @return void
	 *
	 * @throws Invalid_Filter_Settings_Exception Exception.
	 */
	private function validate_filter_payload() {

		$settings = json_decode( HTTP_Helper::get_param( 'filterSettings' ), true );
		if ( empty( $settings ) || ! array_key_exists( 'filterType', $settings ) || ! array_key_exists( 'input', $settings ) ) {
			throw new Invalid_Filter_Settings_Exception( 'Invalid filter configuration' );
		}
	}

	/**
	 * Saves interval config.
	 *
	 * @return void
	 */
	private function save_interval_config() {
		$config = json_decode( HTTP_Helper::get_param( 'intervalSettings' ), true );

		if ( empty( $config ) ) {
			return;
		}

		$this->get_buffer_config_service()->saveInterval(
			'',
			$config['intervalType'],
			! empty( $config['customInterval'] ) ? (int) $config['customInterval'] : 0,
			! empty( $config['customTime'] ) ? $config['customTime'] : 0
		);
	}

	/**
	 * Saves services
	 *
	 * @return void
	 *
	 * @throws Invalid_Sync_Settings_Exception Exception if invalid sync settings.
	 */
	private function save_services() {
		$settings      = HTTP_Helper::get_param( 'syncSettings' );
		$sync_settings = explode( ',', $settings );

		$this->validate( $sync_settings );

		/**
		 * Subscriber sync service.
		 *
		 * @var Subscriber_Sync_Service $subscriber_sync_service
		 */
		$subscriber_sync_service = ServiceRegister::getService( Subscriber_Sync_Service::CLASS_NAME );
		$enabled_services        = array( $subscriber_sync_service );

		if ( in_array( 'buyers', $sync_settings, true ) ) {
			/**
			 * Buyer sync service.
			 *
			 * @var Buyer_Sync_Service $buyer_sync_service
			 */
			$buyer_sync_service = ServiceRegister::getService( Buyer_Sync_Service::CLASS_NAME );
			$enabled_services[] = $buyer_sync_service;
		}

		if ( in_array( 'contacts', $sync_settings, true ) ) {
			/**
			 * Contract sync service.
			 *
			 * @var Contact_Sync_Service $contact_sync_service
			 */
			$contact_sync_service = ServiceRegister::getService( Contact_Sync_Service::CLASS_NAME );
			$enabled_services[]   = $contact_sync_service;
		}

		$this->get_sync_settings_service()->setEnabledServices( $enabled_services );
	}

	/**
	 * Validates sync settings params
	 *
	 * @param string[] $sync_settings Array of sync settings.
	 *
	 * @return void
	 *
	 * @throws Invalid_Sync_Settings_Exception Exception if invalid sync settings.
	 */
	private function validate( $sync_settings ) {
		if ( ! in_array( 'subscribers', $sync_settings, true ) ) {
			throw new Invalid_Sync_Settings_Exception( esc_html( __( 'Subscribers must be selected.', 'cleverreach-wc' ) ) );
		}

		if ( in_array( 'contacts', $sync_settings, true ) &&
			! in_array( 'buyers', $sync_settings, true ) ) {
			throw new Invalid_Sync_Settings_Exception(
				esc_html(
					__(
						'Contacts cannot be selected without buyers.',
						'cleverreach-wc'
					)
				)
			);
		}
	}

	/**
	 * Returns enabled services
	 *
	 * @return array<string,bool>
	 */
	private function get_enabled_services_formatted() {
		$buyer            = false;
		$contact          = false;
		$enabled_services = $this->get_sync_settings_service()->getEnabledServices();

		foreach ( $enabled_services as $service ) {
			if ( $service->getUuid() === 'buyer-service' ) {
				$buyer = true;
			}

			if ( $service->getUuid() === 'contact-service' ) {
				$contact = true;
			}
		}

		return array(
			'buyers'   => $buyer,
			'contacts' => $contact,
		);
	}

	/**
	 * Retrieves Sync settings service.
	 *
	 * @return Sync_Settings_Service
	 */
	private function get_sync_settings_service() {
		if ( null === $this->sync_settings_service ) {
			/**
			 * Sync settings service.
			 *
			 * @var Sync_Settings_Service $sync_settings_service
			 */
			$sync_settings_service       = ServiceRegister::getService( Base_Sync_Settings_Service::CLASS_NAME );
			$this->sync_settings_service = $sync_settings_service;
		}

		return $this->sync_settings_service;
	}

	/**
	 * Retrieves blacklist filter service
	 *
	 * @return BlacklistFilterService
	 */
	private function get_filter_service() {
		if ( null === $this->blacklist_filter_service ) {
			/**
			 * Blacklist filter service.
			 *
			 * @var BlacklistFilterService $blacklist_filter_service
			 */
			$blacklist_filter_service       = ServiceRegister::getService( BlacklistFilterService::CLASS_NAME );
			$this->blacklist_filter_service = $blacklist_filter_service;
		}

		return $this->blacklist_filter_service;
	}

	/**
	 * Retrieves Buffer config service.
	 *
	 * @return BufferConfigurationInterface Buffer config service.
	 */
	private function get_buffer_config_service() {
		if ( null === $this->buffer_config_service ) {
			/**
			 * Buffer config service.
			 *
			 * @var BufferConfigurationInterface $buffer_config_service
			 */
			$buffer_config_service       = ServiceRegister::getService( BufferConfigurationInterface::CLASS_NAME );
			$this->buffer_config_service = $buffer_config_service;
		}

		return $this->buffer_config_service;
	}

	/**
	 * Retrieves order service.
	 *
	 * @return Order_Service
	 */
	protected function get_order_service() {
		/**
		 * Order service.
		 *
		 * @var Order_Service $order_service
		 */
		$order_service = ServiceRegister::getService( Base_Order_Service::CLASS_NAME );

		return $order_service;
	}
}
