<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Setup;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Uninstall_Service;
use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Uninstall
 *
 * @package CleverReach\CleverReachIntegration\Setup
 */
class Uninstall {


	/**
	 * Uninstall Service
	 *
	 * @var Uninstall_Service $uninstall_service
	 */
	private $uninstall_service;

	/**
	 * Plugin uninstall method.
	 *
	 * @return void
	 */
	public function uninstall() {
		if ( is_multisite() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->uninstall_plugin_from_site();

				restore_current_blog();
			}
		} else {
			$this->uninstall_plugin_from_site();
		}
	}

	/**
	 * Removes plugin tables and configuration from the current site.
	 *
	 * @return void
	 */
	private function uninstall_plugin_from_site() {
		$this->get_uninstall_service()->remove_data();

		global $wpdb;

		$database_handler = new Database( $wpdb );
		$database_handler->uninstall();
	}

	/**
	 * Retrieve Uninstall service
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
}
