<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

/*
 * Plugin Name: CleverReach® WooCommerce Integration
 * Plugin URI: https://wordpress.org/plugins/cleverreach-wc/
 * Description: Connect your WooCommerce store to our email software and say hello to successful and simple newsletter marketing – just like Spotify, Bugatti & DHL!
 * Version: 3.4.1
 * Author: CleverReach GmbH & Co. KG
 * Author URI: https://www.cleverreach.com
 * License: GPL
 * Text Domain: cleverreach-wc
 * Domain Path: /i18n/languages
 * WC requires at least: 3.0.0
 * WC tested up to: 8.5.2
 */

use CleverReach\WooCommerce\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
require_once trailingslashit( __DIR__ ) . 'inc/autoloader.php';

global $wpdb;

Plugin::instance( $wpdb, __FILE__ );
