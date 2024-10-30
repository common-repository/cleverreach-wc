<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Contracts\Config_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Forms\Form_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Config_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration
 */
class Config_Service extends Configuration implements Config_Service_Interface {


	const INTEGRATION_NAME      = 'WooCommerce';
	const CLIENT_ID             = 'Hk9nekrQC5';
	const CLIENT_SECRET         = '2Xb5EabEugq1qmFukkG4Rop0MDlduXjx';
	const QUEUE_NAME            = 'WooCommerceDefault';
	const CHECKOUT_DISPLAY_TIME = 5;

	/**
	 * Retrieves min log level from integration database.
	 *
	 * @return int Min log level.
	 */
	public function getMinLogLevel() {
		return $this->getConfigValue( 'minLogLevel', Logger::ERROR );
	}

	/**
	 * Retrieves system url.
	 *
	 * @inheritdoc
	 */
	public function getSystemUrl() {
		return Shop_Helper::get_shop_url();
	}

	/**
	 * Retrieves async process url.
	 *
	 * @param string $guid Process identifier.
	 *
	 * @inheritdoc
	 */
	public function getAsyncProcessUrl( $guid ) {
		$params = array( 'guid' => $guid );

		if ( $this->isAutoTestMode() ) {
			$params['auto-test'] = 1;
		}

		return Shop_Helper::get_controller_url( 'Async_Process', 'run', $params );
	}

	/**
	 * Retrieves default queue name.
	 *
	 * @inheritdoc
	 */
	public function getDefaultQueueName() {
		return self::QUEUE_NAME;
	}

	/**
	 * Retrieves client id.
	 *
	 * @inheritdoc
	 */
	public function getClientId() {
		return self::CLIENT_ID;
	}

	/**
	 * Retrieves integration name.
	 *
	 * @inheritdoc
	 */
	public function getIntegrationName() {
		return self::INTEGRATION_NAME;
	}

	/**
	 * Retrieves client secret.
	 *
	 * @inheritdoc
	 */
	public function getClientSecret() {
		return self::CLIENT_SECRET;
	}

	/**
	 * Sets offline mode check time.
	 *
	 * @param int $time Time.
	 *
	 * @inheritdoc
	 */
	public function set_offline_mode_check_time( $time ) {
		$this->getConfigurationManager()->saveConfigValue( 'offlineModeCheckTime', $time, true );
	}

	/**
	 * Retrieves offline mode check time.
	 *
	 * @inheritdoc
	 */
	public function get_offline_mode_check_time() {
		return (int) $this->getConfigurationManager()->getConfigValue( 'offlineModeCheckTime', true );
	}

	/**
	 * Retrieves default DOI form.
	 *
	 * @inheritdoc
	 */
	public function get_default_form() {
		try {
			$form_id = $this->getConfigurationManager()->getConfigValue( 'defaultForm' );

			if ( null === $form_id ) {
				/**
				 * Form service
				 *
				 * @var Form_Service $form_service
				 */
				$form_service = ServiceRegister::getService( FormService::CLASS_NAME );

				return $form_service->getId();
			}

			return $form_id;
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get default form.',
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
	 * Sets default DOI form.
	 *
	 * @param string $form_api_id form api id.
	 *
	 * @inheritdoc
	 */
	public function set_default_form( $form_api_id ) {
		try {
			$this->getConfigurationManager()->saveConfigValue( 'defaultForm', $form_api_id );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set default form.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Sets show admin notice status.
	 *
	 * @param bool $show_notice Should show notice.
	 *
	 * @inheritdoc
	 */
	public function set_show_admin_notice( $show_notice ) {
		$this->getConfigurationManager()->saveConfigValue( 'SHOW_ADMIN_NOTICE', $show_notice );
	}

	/**
	 * Saves newsletter checkbox disabled status.
	 *
	 * @param bool $is_checkbox_disabled Is checkbox disabled.
	 *
	 * @inheritdoc
	 */
	public function save_newsletter_checkbox_disabled( $is_checkbox_disabled ) {
		try {
			$this->getConfigurationManager()->saveConfigValue(
				'IS_CLEVERREACH_NEWSLETTER_CHECKBOX_DISABLED',
				$is_checkbox_disabled
			);
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set Newsletter checkbox disabled.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Retrieves newsletter checkbox disabled status.
	 *
	 * @inheritdoc
	 */
	public function get_newsletter_checkbox_disabled() {
		try {
			return $this->getConfigurationManager()
						->getConfigValue( 'IS_CLEVERREACH_NEWSLETTER_CHECKBOX_DISABLED', false );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Subscribe for newsletter disabled.',
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
	 * Retrieves newsletter checkbox caption.
	 *
	 * @inheritdoc
	 *
	 * @return string|false
	 */
	public function get_subscribe_for_newsletter_caption() {
		try {
			return $this->getConfigurationManager()->getConfigValue(
				'CLEVERREACH_SUBSCRIBE_FOR_NEWSLETTER_LABEL',
				__( 'Sign up for our newsletter', 'cleverreach-wc' )
			);
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Subscribe for newsletter label.',
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
	 * Retrieves newsletter subscription confirmation message.
	 *
	 * @inheritdoc
	 *
	 * @return string|false
	 */
	public function get_newsletter_subscription_confirmation_message() {
		try {
			return $this->getConfigurationManager()->getConfigValue(
				'CLEVERREACH_NEWSLETTER_SUBSCRIPTION_CONFIRMATION_MESSAGE',
				__( 'You have successfully subscribed', 'cleverreach-wc' )
			);
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Newsletter subscription confirmation message.',
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
	 * Saves newsletter checkbox label.
	 *
	 * @param string $caption Checkbox caption.
	 *
	 * @inheritdoc
	 */
	public function save_subscribe_for_newsletter_caption( $caption ) {
		try {
			$this->getConfigurationManager()->saveConfigValue( 'CLEVERREACH_SUBSCRIBE_FOR_NEWSLETTER_LABEL', $caption );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set Subscribe for newsletter label.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Saves newsletter subscription confirmation message.
	 *
	 * @param string $caption Newsletter subscription confirmation message.
	 *
	 * @inheritdoc
	 */
	public function save_newsletter_confirmation_message( $caption ) {
		try {
			$this->getConfigurationManager()->saveConfigValue(
				'CLEVERREACH_NEWSLETTER_SUBSCRIPTION_CONFIRMATION_MESSAGE',
				$caption
			);
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set Newsletter subscription confirmation message.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Saves admin notification data.
	 *
	 * @param Notification $notification Admin notification.
	 *
	 * @inheritdoc
	 */
	public function save_admin_notification_data( Notification $notification ) {
		$json_notification = json_encode(
			array(
				'name'        => $notification->getName(),
				'description' => $notification->getDescription(),
				'url'         => $notification->getUrl(),
				'date'        => $notification->getDate(),
			)
		);

		$this->getConfigurationManager()->saveConfigValue( 'ADMIN_NOTIFICATION', $json_notification );
	}

	/**
	 * Retrieves admin notification data.
	 *
	 * @inheritdoc
	 */
	public function get_admin_notification_data() {
		$raw_notification = $this->getConfigurationManager()->getConfigValue( 'ADMIN_NOTIFICATION' );
		/**
		 * Notification.
		 *
		 * @var Notification|null $notification
		 */
		$notification = $raw_notification ? Notification::fromArray( json_decode( $raw_notification, true ) ) : null;

		return $notification;
	}

	/**
	 * Saves checkbox display time.
	 *
	 * @param int $display_time Checkbox display time.
	 *
	 * @return void
	 */
	public function save_checkbox_display_time( $display_time ) {
		try {
			$this->getConfigurationManager()
				->saveConfigValue( 'CHECKOUT_DISPLAY_TIME', $display_time );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set display time of newsletter checkbox caption.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Gets checkbox display time.
	 *
	 * @return int | null
	 */
	public function get_checkbox_display_time() {
		try {
			return $this->getConfigurationManager()
						->getConfigValue( 'CHECKOUT_DISPLAY_TIME', self::CHECKOUT_DISPLAY_TIME );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get Newsletter subscription confirmation message.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}

		return null;
	}

	/**
	 * Retrieves show admin notice.
	 *
	 * @inheritdoc
	 */
	public function get_show_admin_notice() {
		return (bool) $this->getConfigurationManager()->getConfigValue( 'SHOW_ADMIN_NOTICE' );
	}

	/**
	 * Retrieves database version.
	 *
	 * @return string
	 * @throws QueryFilterInvalidParamException Exception if query params invalid.
	 */
	public function get_database_version() {
		return $this->getConfigurationManager()->getConfigValue( 'CLEVERREACH_DATABASE_VERSION', '3.0.0' );
	}

	/**
	 * Sets database version.
	 *
	 * @param string $database_version database version.
	 *
	 * @throws QueryFilterInvalidParamException Exception if query params invalid.
	 */
	public function set_database_version( $database_version ) {
		$this->getConfigurationManager()->saveConfigValue( 'CLEVERREACH_DATABASE_VERSION', $database_version );
	}
}
