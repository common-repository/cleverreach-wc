<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Form_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Group_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Receiver_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Uninstall_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SupportConsole\SupportService as Base_Support_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Support_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Support_Service extends Base_Support_Service {

	/**
	 * Uninstall service.
	 *
	 * @var Uninstall_Service
	 */
	private $uninstall_service;

	/**
	 * Dynamic content service.
	 *
	 * @var Dynamic_Content_Service
	 */
	private $dynamic_content_service;

	/**
	 * Form events service.
	 *
	 * @var Form_Events_Service
	 */
	private $form_events_service;

	/**
	 * Receiver events service.
	 *
	 * @var Receiver_Events_Service
	 */
	private $receiver_events_service;

	/**
	 * Group events service.
	 *
	 * @var Group_Events_Service
	 */
	private $group_events_service;

	/**
	 * Retrieves system version.
	 *
	 * @inheritDoc
	 */
	protected function getSystemVersion() {
		return Shop_Helper::get_woocommerce_version();
	}

	/**
	 * Retrieves integration version.
	 *
	 * @inheritDoc
	 */
	protected function getIntegrationVersion() {
		return Shop_Helper::get_plugin_version();
	}

	/**
	 * Retrieves dynamic content urls.
	 *
	 * @inheritDoc
	 *
	 * @return string[]
	 */
	protected function getDynamicContentUrls() {
		$url = $this->get_dynamic_content_service()->get_url();

		return array( 'PRODUCT_SEARCH_ENDPOINT' => $url );
	}

	/**
	 * Retrieves webhook url.
	 *
	 * @inheritDoc
	 *
	 * @return string[]|string
	 */
	protected function getWebhookUrl() {
		$form_event_url     = $this->get_form_events_service()->getEventUrl();
		$receiver_event_url = $this->get_receiver_events_service()->getEventUrl();
		$group_event_url    = $this->get_group_events_service()->getEventUrl();

		return array(
			'RECEIVER_HOOK_ENDPOINT' => $form_event_url,
			'FORM_HOOK_ENDPOINT'     => $receiver_event_url,
			'GROUP_HOOK_ENDPOINT'    => $group_event_url,
		);
	}

	/**
	 * Removes all data.
	 *
	 * @inheritDoc
	 *
	 * @return void
	 */
	protected function hardReset() {
		$this->get_uninstall_service()->remove_data();
	}

	/**
	 * Returns uninstall service
	 *
	 * @return Uninstall_Service
	 */
	private function get_uninstall_service() {
		if ( null === $this->uninstall_service ) {
			/**
			 * Uninstall service.
			 *
			 * @var Uninstall_Service $uninstall_service
			 */
			$uninstall_service       = ServiceRegister::getService( Uninstall_Service_Interface::CLASS_NAME );
			$this->uninstall_service = $uninstall_service;
		}

		return $this->uninstall_service;
	}

	/**
	 * Returns dynamic content service
	 *
	 * @return Dynamic_Content_Service
	 */
	private function get_dynamic_content_service() {
		if ( null === $this->dynamic_content_service ) {
			/**
			 * Dynamic content service.
			 *
			 * @var Dynamic_Content_Service $dynamic_content_service
			 */
			$dynamic_content_service       = ServiceRegister::getService( DynamicContentService::CLASS_NAME );
			$this->dynamic_content_service = $dynamic_content_service;
		}

		return $this->dynamic_content_service;
	}

	/**
	 * Return form events service
	 *
	 * @return Form_Events_Service
	 */
	private function get_form_events_service() {
		if ( null === $this->form_events_service ) {
			/**
			 * Form events service.
			 *
			 * @var Form_Events_Service $form_events_service
			 */
			$form_events_service       = ServiceRegister::getService( FormEventsService::CLASS_NAME );
			$this->form_events_service = $form_events_service;
		}

		return $this->form_events_service;
	}

	/**
	 * Return receiver events service
	 *
	 * @return Receiver_Events_Service
	 */
	private function get_receiver_events_service() {
		if ( null === $this->receiver_events_service ) {
			/**
			 * Receiver events service.
			 *
			 * @var Receiver_Events_Service $receiver_events_service
			 */
			$receiver_events_service       = ServiceRegister::getService( ReceiverEventsService::CLASS_NAME );
			$this->receiver_events_service = $receiver_events_service;
		}

		return $this->receiver_events_service;
	}

	/**
	 * Return group events service
	 *
	 * @return Group_Events_Service
	 */
	private function get_group_events_service() {
		if ( null === $this->group_events_service ) {
			/**
			 * Group events service.
			 *
			 * @var Group_Events_Service $group_events_service
			 */
			$group_events_service       = ServiceRegister::getService( GroupEventsService::CLASS_NAME );
			$this->group_events_service = $group_events_service;
		}

		return $this->group_events_service;
	}
}
