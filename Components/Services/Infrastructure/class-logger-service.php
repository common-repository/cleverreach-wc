<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\Infrastructure;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces\LoggerAdapter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LoggerConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class Logger_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\Infrastructure
 */
class Logger_Service extends Singleton implements LoggerAdapter {


	const LOG_DEF     = "[%s][%d][%s] %s\n";
	const CONTEXT_DEF = "\tContext[%s]: %s\n";

	/**
	 * Singleton instance of this class.
	 *
	 * @var static
	 */
	protected static $instance;

	/**
	 * Returns log file name.
	 *
	 * @return string Log file name.
	 */
	public static function get_log_file() {
		$upload_dir = wp_get_upload_dir();

		$dir_path = $upload_dir['basedir'] . '/cleverreach-logs';
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0644, true );
		}

		return $dir_path . '/' . gmdate( 'Y-m-d' ) . '.log';
	}

	/**
	 * Log message in system.
	 *
	 * @param LogData $data Log data.
	 *
	 * @return void
	 */
	public function logMessage( LogData $data ) {
		$config_service = LoggerConfiguration::getInstance();

		/**
		 * Config service.
		 *
		 * @var Config_Service $configuration
		 */
		$configuration = ServiceRegister::getService( Config_Service::CLASS_NAME );
		$min_log_level = $config_service->getMinLogLevel();
		$log_level     = $data->getLogLevel();
		if ( ! Shop_Helper::is_woocommerce_active() ) {
			return;
		}

		// Min log level is actually max log level.
		if ( $log_level > $min_log_level && ! $configuration->isDebugModeEnabled() ) {
			return;
		}

		$level = 'info';
		switch ( $log_level ) {
			case Logger::ERROR:
				$level = 'error';
				break;
			case Logger::WARNING:
				$level = 'warning';
				break;
			case Logger::DEBUG:
				$level = 'debug';
				break;
		}

		$message = sprintf(
			static::LOG_DEF,
			$level,
			$data->getTimestamp(),
			$data->getComponent(),
			$data->getMessage()
		);
		foreach ( $data->getContext() as $item ) {
			$message .= sprintf( static::CONTEXT_DEF, $item->getName(), $item->getValue() );
		}

		$filename = self::get_log_file();
		$log      = fopen( $filename, 'ab+' );
		if ( false !== $log ) {
			fwrite( $log, $message );
			fclose( $log );
		}
	}
}
