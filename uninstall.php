<?php
/**
 * CleverReach Uninstall
 *
 * @package CleverReach
 *
 * Uninstalling CleverReach deletes all user data.
 */

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Init_Service;
use CleverReach\WooCommerce\Components\Setup\Uninstall;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
require_once trailingslashit( __DIR__ ) . 'inc/autoloader.php';

global $wpdb;

Init_Service::init();

$uninstall = new Uninstall();
$uninstall->uninstall();
