<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification\Contracts\Notification_Service_Interface;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Notification_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification
 */
class Notification_Service implements Notification_Service_Interface {


	/**
	 * Config service.
	 *
	 * @var Config_Service
	 * */
	protected $config_service;

	/**
	 * Creates a new notification in system integration.
	 *
	 * @param Notification $notification Notification object that contains info such as
	 *                                   identifier, name, date, description, url.
	 *
	 * @return boolean
	 * @throws QueryFilterInvalidParamException Exception if query filter params invalid.
	 */
	public function push( Notification $notification ) {
		$this->get_config_service()->set_show_admin_notice( true );
		$this->get_config_service()->save_admin_notification_data( $notification );

		return true;
	}

	/**
	 * Returns whether CleverReach notification should be shown.
	 *
	 * @return bool
	 */
	public function should_show_notifications() {
		try {
			return ! Shop_Helper::is_current_page_cleverreach() && $this->get_config_service()->get_show_admin_notice();
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Show notifications enabled.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);

			return false;
		}
	}

	/**
	 * Shows admin notification.
	 *
	 * @return void
	 */
	public function show_message() {
		try {
			$notification = $this->get_config_service()->get_admin_notification_data();
			if ( ! $notification ) {
				return;
			}

			$notification_title       = __( $notification->getName(), 'cleverreach-wc' ); // phpcs:ignore
			$notification_description = __( $notification->getDescription(), 'cleverreach-wc' ); // phpcs:ignore
			$dismiss_button_url       = Shop_Helper::get_controller_url(
				'Initial_Sync',
				'dismiss_notification_button'
			);
			echo "
			<div class='notice notice-info is-dismissible'>
				<p>
					<strong>" . esc_html( $notification_title ) . '</strong>
					<br />
					' . esc_html( $notification_description ) . "
				</p>
				<button id='cr-initial-sync-completed' type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
				<script>
			    	const closeBtn = document.getElementById('cr-initial-sync-completed');
			    	closeBtn.addEventListener('click', function(){
			        	jQuery.ajax({
                        	type: 'get',
                        	url: '" . esc_html( $dismiss_button_url ) . "',
                    });
			        closeBtn.parentNode.remove();
			    });
				</script>
			</div>";
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Admin notification data.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Retrieves config service.
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
			$config_service       = ServiceRegister::getService( Config_Service::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}
}
