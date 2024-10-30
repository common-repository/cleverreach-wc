<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Setup;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\Components\Util\Version_File_Reader;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use Exception;

/**
 * Class Update
 *
 * @package CleverReach\CleverReachIntegration\Setup
 */
class Update {


	/**
	 * Config Service
	 *
	 * @var Config_Service $config_service
	 */
	private $config_service;

	/**
	 * Previous plugin version
	 *
	 * @var string|null
	 * */
	private $previous_plugin_version;

	/**
	 * Checks and initializes or updates plugin if needed for multisite
	 *
	 * @param bool $is_activation Is activation.
	 * @param bool $is_network_wide Is network wide.
	 *
	 * @return void
	 */
	public function check_and_initialize_sites( $is_activation = false, $is_network_wide = false ) {
		if ( $is_network_wide ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->initialize_or_update_plugin();

				restore_current_blog();
			}
		} elseif ( $is_activation ) {
				$this->initialize_or_update_plugin();
		} elseif ( is_multisite() ) {
				$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				if ( Shop_Helper::is_plugin_enabled() ) {
					$this->initialize_or_update_plugin();
				}

				restore_current_blog();
			}
		} elseif ( Shop_Helper::is_plugin_active_for_current_site() ) {
				$this->initialize_or_update_plugin();
		}
	}

	/**
	 * Checks if plugin was already installed and initialized.
	 *
	 * @return bool
	 */
	public function plugin_already_initialized() {
		global $wpdb;
		$handler = new Database( $wpdb );

		return $handler->plugin_already_initialized();
	}

	/**
	 * Checks and initializes or updates plugin if needed for a single site (current site)
	 *
	 * @return void
	 */
	private function initialize_or_update_plugin() {
		if ( ! $this->plugin_already_initialized() ) {
			$this->initialize_plugin_on_site();
		} elseif ( $this->is_plugin_version_newer() ) {
				$this->update_plugin_on_site();
		}
	}

	/**
	 * Initialize plugin on a site
	 *
	 * @return void
	 */
	private function initialize_plugin_on_site() {
		$this->init_database();
		$this->init_config();
	}

	/**
	 * Update plugin on a site
	 *
	 * @return void
	 */
	private function update_plugin_on_site() {
		try {
			$this->run_migrations( $this->get_previous_plugin_version() );
			$this->get_config_service()->set_database_version( Shop_Helper::get_plugin_version() );
		} catch ( Exception $exception ) {
			Logger::logError( $exception->getMessage(), 'Database Update' );
		}
	}

	/**
	 * Get previous plugin version
	 *
	 * @return string|null
	 */
	private function get_previous_plugin_version() {
		if ( empty( $this->previous_plugin_version ) ) {
			global $wpdb;
			$db = new Database( $wpdb );
			if ( $db->plugin_older_than_v3_already_initialized() ) {
				$this->previous_plugin_version = $db->get_old_config_value( 'CLEVERREACH_DATABASE_VERSION' );
			} else {
				try {
					$this->previous_plugin_version = $this->get_config_service()->get_database_version();
				} catch ( QueryFilterInvalidParamException $e ) {
					Logger::logError(
						'Failed to get database version.',
						'Integration',
						array(
							new LogContextData( 'message', $e->getMessage() ),
							new LogContextData( 'trace', $e->getTraceAsString() ),
						)
					);
				}
			}
		}

		return $this->previous_plugin_version;
	}

	/**
	 * Check if update script should execute
	 *
	 * @return bool
	 */
	private function is_plugin_version_newer() {
		$previous_version = $this->get_previous_plugin_version();
		$current_version  = Shop_Helper::get_plugin_version();

		return version_compare( $previous_version, $current_version, 'lt' );
	}

	/**
	 * Initializes plugin database.
	 *
	 * @return void
	 */
	private function init_database() {
		global $wpdb;
		$installer = new Database( $wpdb );
		$installer->install();
	}

	/**
	 * Initializes default configuration values.
	 *
	 * @return void
	 */
	private function init_config() {
		try {
			$this->get_config_service()->setTaskRunnerStatus( '', null );
		} catch ( TaskRunnerStatusStorageUnavailableException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );
		}
		try {
			$this->get_config_service()->set_database_version( Shop_Helper::get_plugin_version() );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set database version.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Starts migration for one site
	 *
	 * @param string $previous_version Previous version.
	 *
	 * @return void
	 */
	private function run_migrations( $previous_version ) {
		$migration_dir       = realpath( __DIR__ ) . '/../../Migrations/Scripts/';
		$version_file_reader = new Version_File_Reader( $migration_dir, $previous_version );
		while ( $version_file_reader->has_next() ) {
			/**
			 * Update schema
			 *
			 * @var Update_Schema $update_schema
			 */
			$update_schema = $version_file_reader->read_next();
			$update_schema->update();
		}
	}


	/**
	 * Retrieve Config Service
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
