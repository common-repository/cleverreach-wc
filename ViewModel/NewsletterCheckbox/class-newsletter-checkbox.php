<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\NewsletterCheckbox;

use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Newsletter_Checkbox
 *
 * @package CleverReach\WooCommerce\ViewModel\NewsletterCheckbox
 */
class Newsletter_Checkbox {


	/**
	 * Newsletter_Checkbox constructor.
	 */
	public function __construct() {
	}

	/**
	 * Retrieves newsletter checkbox caption.
	 *
	 * @return string
	 */
	public function get_newsletter_checkbox_caption() {
		$caption = $this->get_config_service()->get_subscribe_for_newsletter_caption();
		if ( ! $caption ) {
			$caption = '';
		}

		return $caption;
	}

	/**
	 * Checks if newsletter checkbox is disabled.
	 *
	 * @return bool
	 */
	public function get_newsletter_checkbox_disabled() {
		return $this->get_config_service()->get_newsletter_checkbox_disabled();
	}

	/**
	 * Retrieves config service.
	 *
	 * @return Config_Service
	 */
	protected function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Config_Service::CLASS_NAME );

		return $config_service;
	}

	/**
	 * Returns controller actions.
	 *
	 * @return array<string,string>
	 */
	public static function get_config() {
		return array(
			'subscribeUrl'          => Shop_Helper::get_controller_url( 'Newsletter_Subscription', 'subscribe' ),
			'undoUrl'               => Shop_Helper::get_controller_url( 'Newsletter_Subscription', 'undo' ),
			'newsletterStatusField' => Subscriber_Repository::NEWSLETTER_STATUS_FIELD,
		);
	}
}
