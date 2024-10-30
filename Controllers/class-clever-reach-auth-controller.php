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

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Contracts\Schedule_Service_Interface;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO\AuthInfo;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\AuthProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Tasks\Composite\ConnectTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class Clever_Reach_Auth_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Auth_Controller extends Clever_Reach_Base_Controller {


	const IN_PROGRESS = 'in_progress';
	const FINISHED    = 'finished';

	/**
	 * Config service.
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * AuthProxy service.
	 *
	 * @var AuthProxy
	 */
	private $auth_proxy;

	/**
	 * Auth service.
	 *
	 * @var AuthorizationService
	 */
	private $auth_service;

	/**
	 * Queue service.
	 *
	 * @var QueueService
	 */
	private $queue_service;

	/**
	 * Schedule service.
	 *
	 * @var Schedule_Service_Interface
	 */
	private $schedule_service;

	/**
	 * Checks status.
	 *
	 * @return void
	 */
	public function check_status() {
		$status = self::FINISHED;

		/**
		 * Queue service.
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service = ServiceRegister::getService( QueueService::CLASS_NAME );

		/**
		 * Queue item.
		 *
		 * @var QueueItem $queue_item
		 */
		$queue_item = $queue_service->findLatestByType( 'ConnectTask' );

		if ( null === $queue_item ) {
			$this->return_json( array( 'status' => QueueItem::QUEUED ) );
		}

		$queue_status = $queue_item->getStatus();
		if ( ! in_array( $queue_status, array( QueueItem::FAILED, QueueItem::COMPLETED ), true ) ) {
			$status = self::IN_PROGRESS;
		}

		$this->return_json( array( 'status' => $status ) );
	}

	/**
	 * Dispatches request.
	 *
	 * @return void
	 */
	public function callback() {
		try {
			$code = HTTP_Helper::get_param( 'code' );

			if ( empty( $code ) ) {
				$this->return_json(
					array(
						'status'  => false,
						'message' => __( 'Wrong parameters. Code not set.', 'cleverreach-wc' ),
					)
				);
			}

			$is_refresh = HTTP_Helper::get_param( 'isRefresh' );
			$auth_info  = $this->get_auth_proxy()->getAuthInfo(
				$code,
				$this->get_auth_service()->getRedirectURL( (bool) $is_refresh )
			);

			if ( $is_refresh ) {
				$token_info = $this->get_token_info( $auth_info );
				$user_info  = $this->get_auth_service()->getUserInfo();
				if ( ( $user_info->getId() !== (string) $token_info->client_id ) ) {
					include dirname( __DIR__ ) . '/resources/views/close-frame.php';

					return;
				}
			}

			$this->get_auth_service()->setAuthInfo( $auth_info );
			$this->get_auth_service()->setIsOffline( false );

			$this->get_queue_service()->enqueue(
				$this->get_config_service()->getDefaultQueueName(),
				new ConnectTask()
			);

			if ( ! $is_refresh ) {
				$this->get_schedule_service()->register_schedules();
			}
		} catch ( BaseException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );
		}

		include dirname( __DIR__ ) . '/resources/views/close-frame.php';
	}

	/**
	 * Validate internal call.
	 *
	 * @return void
	 */
	protected function validate_internal_call() {
		// In this case call is being made from cleverreach.
		if ( ! $this->is_user_admin() && ( isset( $_SERVER['HTTP_REFERER'] ) && 'https://rest.cleverreach.com/' !== $_SERVER['HTTP_REFERER'] ) ) {
			status_header( 401 );
			nocache_headers();

			exit();
		}
	}

	/**
	 * Retrieves token info.
	 *
	 * @param AuthInfo $auth_info User auth info.
	 *
	 * @return mixed
	 */
	private function get_token_info( $auth_info ) {
		$access_token = $auth_info->getAccessToken();
		$parts        = explode( '.', $access_token );

		return json_decode(
			base64_decode( str_replace( '_', '/', str_replace( '-', '+', $parts[1] ) ) ),
			false
		);
	}

	/**
	 * Returns config service
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

	/**
	 * Returns auth proxy service.
	 *
	 * @return AuthProxy
	 */
	private function get_auth_proxy() {
		if ( null === $this->auth_proxy ) {
			/**
			 * Auth proxy.
			 *
			 * @var AuthProxy $auth_proxy
			 */
			$auth_proxy       = ServiceRegister::getService( AuthProxy::CLASS_NAME );
			$this->auth_proxy = $auth_proxy;
		}

		return $this->auth_proxy;
	}

	/**
	 * Return authorization service.
	 *
	 * @return AuthorizationService
	 */
	private function get_auth_service() {
		if ( null === $this->auth_service ) {
			/**
			 * Authorization service.
			 *
			 * @var AuthorizationService $auth_service
			 */
			$auth_service       = ServiceRegister::getService( AuthorizationService::CLASS_NAME );
			$this->auth_service = $auth_service;
		}

		return $this->auth_service;
	}

	/**
	 * Return queue service
	 *
	 * @return QueueService
	 */
	private function get_queue_service() {
		if ( null === $this->queue_service ) {
			/**
			 * Queue service.
			 *
			 * @var QueueService $queue_service
			 */
			$queue_service       = ServiceRegister::getService( QueueService::CLASS_NAME );
			$this->queue_service = $queue_service;
		}

		return $this->queue_service;
	}

	/**
	 * Return schedule service
	 *
	 * @return Schedule_Service_Interface
	 */
	private function get_schedule_service() {
		if ( null === $this->schedule_service ) {
			/**
			 * Schedule service interface.
			 *
			 * @var Schedule_Service_Interface $schedule_service
			 */
			$schedule_service       = ServiceRegister::getService( Schedule_Service_Interface::CLASS_NAME );
			$this->schedule_service = $schedule_service;
		}

		return $this->schedule_service;
	}
}
