<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\AutoConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService as Base_Queue_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskRunnerWakeupService;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Clever_Reach_Frontend_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Frontend_Controller extends Clever_Reach_Base_Controller {

	const CURL_NOT_ENABLED_STATE_CODE      = 'curl-not-enabled';
	const WC_NOT_ACTIVE_STATE_CODE         = 'wc-not-active';
	const AUTOCONFIGURE_STATE_CODE         = 'autoconfigure';
	const WELCOME_STATE_CODE               = 'welcome';
	const INITIAL_SYNC_SETTINGS_STATE_CODE = 'initial-sync-settings';
	const REFRESH_STATE_CODE               = 'refresh';
	const DASHBOARD_STATE_CODE             = 'dashboard';

	const SCRIPT_VERSION = 13;

	/**
	 * Initial sync task.
	 *
	 * @var QueueItem $initial_sync_task
	 */
	private $initial_sync_task;

	/**
	 * Is initial sync task loaded.
	 *
	 * @var bool $is_initial_sync_task_loaded
	 */
	private $is_initial_sync_task_loaded;

	/**
	 * Configuration service.
	 *
	 * @var Config_Service $config_service
	 */
	private $config_service;

	/**
	 * Queue service.
	 *
	 * @var QueueService $queue_service
	 */
	private $queue_service;

	/**
	 * Authorization service.
	 *
	 * @var AuthorizationService $auth_service
	 */
	private $auth_service;

	/**
	 * Page name.
	 *
	 * @var string $page
	 */
	private $page;

	/**
	 * Task runner wakeup service.
	 *
	 * @var TaskRunnerWakeupService
	 */
	private $task_runner_wakeup_service;

	/**
	 * Renders appropriate view
	 *
	 * @return void
	 */
	public function render() {
		$this->set_page();
		$this->load_resources();

		$this->get_task_runner_wakeup_service()->wakeup();

		include dirname( __DIR__ ) . '/resources/views/wrapper-start.php';
		include dirname( __DIR__ ) . '/resources/views/' . $this->page . '.php';
		include dirname( __DIR__ ) . '/resources/views/wrapper-end.php';
	}

	/**
	 * Returns image resource.
	 *
	 * @param string $resource Resource name.
	 *
	 * @return string
	 */
	public function get_image_resources( $resource ) {
		return esc_url( Shop_Helper::get_clever_reach_base_url( "/resources/images/$resource" ) );
	}

	/**
	 * Set current page code
	 *
	 * @return void
	 */
	private function set_page() {
		if ( ! Shop_Helper::is_curl_enabled() ) {
			$this->page = self::CURL_NOT_ENABLED_STATE_CODE;
		} elseif ( ! Shop_Helper::is_woocommerce_active() ) {
			$this->page = self::WC_NOT_ACTIVE_STATE_CODE;
		} elseif ( ! $this->is_auto_configured() ) {
			$this->page = self::AUTOCONFIGURE_STATE_CODE;
		} elseif ( ! $this->is_auth_token_valid() ) {
			$this->page = self::WELCOME_STATE_CODE;
		} elseif ( $this->is_user_offline() ) {
			$this->page = self::REFRESH_STATE_CODE;
		} elseif ( ! $this->is_initial_sync_enqueued() ) {
			$this->page = self::INITIAL_SYNC_SETTINGS_STATE_CODE;
		} else {
			$this->page = self::DASHBOARD_STATE_CODE;
		}
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

	/**
	 * Loads initial sync task.
	 *
	 * @return void
	 */
	private function load_initial_sync_task() {
		if ( ! $this->is_initial_sync_task_loaded ) {
			$this->initial_sync_task = $this->get_queue_service()->findLatestByType( 'InitialSyncTask' );
		}

		$this->is_initial_sync_task_loaded = true;
	}

	/**
	 * Checks if initial sync task has already been enqueued.
	 *
	 * @return bool
	 */
	private function is_initial_sync_enqueued() {
		$this->load_initial_sync_task();

		return null !== $this->initial_sync_task;
	}

	/**
	 * Loads CSS and JS for specific CleverReach pages.
	 *
	 * @return void
	 */
	private function load_resources() {
		$base_url = Shop_Helper::get_clever_reach_base_url( '/resources/' );

		wp_enqueue_style(
			'cr_global-admin-styles',
			$base_url . 'css/cleverreach.css',
			array(),
			self::SCRIPT_VERSION
		);
		wp_enqueue_style(
			'cr_global-admin-icofont',
			$base_url . 'css/cleverreach-icofont.css',
			array(),
			self::SCRIPT_VERSION
		);
		wp_enqueue_style(
			'cr_font-awesome',
			'https://use.fontawesome.com/releases/v5.5.0/css/all.css',
			array(),
			self::SCRIPT_VERSION
		);

		wp_enqueue_script(
			'cr_ajax',
			esc_url( $base_url . 'js/cleverreach.ajax.js' ),
			array(),
			self::SCRIPT_VERSION,
			true
		);
		wp_enqueue_script(
			'cr_authorization',
			esc_url( $base_url . 'js/cleverreach.authorization.js' ),
			array(),
			self::SCRIPT_VERSION,
			true
		);

		switch ( $this->page ) {
			case self::AUTOCONFIGURE_STATE_CODE:
				wp_enqueue_style(
					'cr_autoconfigure',
					$base_url . 'css/cleverreach-autoconfigure.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_script(
					'cr_autoconfigure',
					esc_url( $base_url . 'js/cleverreach.autoconfigure.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				break;
			case self::WELCOME_STATE_CODE:
				wp_enqueue_style(
					'cr_auth-iframe',
					$base_url . 'css/cleverreach-auth-iframe.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_script(
					'cr_welcome',
					esc_url( $base_url . 'js/cleverreach.welcome.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				break;
			case self::REFRESH_STATE_CODE:
				wp_enqueue_style(
					'cr_auth-iframe',
					$base_url . 'css/cleverreach-auth-iframe.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_script(
					'cr_refresh',
					esc_url( $base_url . 'js/cleverreach.refresh.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				break;
			case self::DASHBOARD_STATE_CODE:
				wp_enqueue_style(
					'cr_dashboard',
					$base_url . 'css/cleverreach-dashboard.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_style(
					'cr_settings',
					$base_url . 'css/cleverreach-settings.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_script(
					'cr_interval',
					esc_url( $base_url . 'js/cleverreach.interval.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_script(
					'cr_dashboard',
					esc_url( $base_url . 'js/cleverreach.dashboard.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_script(
					'cr_status-checker',
					esc_url( $base_url . 'js/cleverreach.status-checker.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_script(
					'cr_support',
					esc_url( $base_url . 'js/cleverreach.support.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_script(
					'cr_abandoned-cart',
					esc_url( $base_url . 'js/cleverreach.abandoned-cart.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_style( 'woocommerce_admin_styles' );
				break;
			case self::INITIAL_SYNC_SETTINGS_STATE_CODE:
				wp_enqueue_style(
					'cr_initial-sync-settings',
					$base_url . 'css/cleverreach-initial-sync-settings.css',
					array(),
					self::SCRIPT_VERSION
				);
				wp_enqueue_script(
					'cr_initial-sync-settings',
					esc_url( $base_url . 'js/cleverreach.initial-sync-settings.js' ),
					array(),
					self::SCRIPT_VERSION,
					true
				);
				wp_enqueue_style( 'woocommerce_admin_styles' );
				break;
			default:
				break;
		}
	}

	/**
	 * Returns queue service.
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
			$queue_service       = ServiceRegister::getService( Base_Queue_Service::CLASS_NAME );
			$this->queue_service = $queue_service;
		}

		return $this->queue_service;
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
	 * Checks if auto configured
	 *
	 * @return bool
	 */
	private function is_auto_configured() {
		$is_auto_configured = $this->get_config_service()->getAutoConfigurationState();

		return empty( $is_auto_configured ) || AutoConfiguration::STATE_SUCCEEDED === $is_auto_configured;
	}

	/**
	 * Checks is auth token valid.
	 *
	 * @return bool
	 */
	private function is_auth_token_valid() {
		try {
			$access_token = $this->get_auth_service()->getAuthInfo()->getAccessToken();
		} catch ( BaseException $e ) {
			return false;
		}

		return ! empty( $access_token );
	}

	/**
	 * Check if credentials are valid
	 *
	 * @return bool
	 */
	private function is_user_offline() {
		try {
			return $this->get_auth_service()->getFreshOfflineStatus();
		} catch ( BaseException $e ) {
			return true;
		}
	}

	/**
	 * Retrieves task runner wakeup service.
	 *
	 * @return TaskRunnerWakeupService
	 */
	private function get_task_runner_wakeup_service() {
		if ( null === $this->task_runner_wakeup_service ) {
			/**
			 * Task runner wakeup service.
			 *
			 * @var TaskRunnerWakeupService $task_runner_wakeup_service
			 */
			$task_runner_wakeup_service       = ServiceRegister::getService( TaskRunnerWakeup::CLASS_NAME );
			$this->task_runner_wakeup_service = $task_runner_wakeup_service;
		}

		return $this->task_runner_wakeup_service;
	}
}
