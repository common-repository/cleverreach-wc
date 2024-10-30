<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Contracts\Double_Opt_In_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoiData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Double_Opt_In_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI
 */
class Double_Opt_In_Service implements Double_Opt_In_Interface {


	/**
	 * Save DOI value.
	 *
	 * @param bool $value DOI value.
	 */
	public function save_double_opt_in( $value ) {
		try {
			ConfigurationManager::getInstance()->saveConfigValue( 'doubleOptIn', $value );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to set DOI enabled.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Check if DOI is enabled.
	 *
	 * @return bool
	 */
	public function is_doi_enabled() {
		try {
			return ConfigurationManager::getInstance()->getConfigValue( 'doubleOptIn', false );
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to get is DOI enabled.',
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
	 * Creates DOI data.
	 *
	 * @return DoiData
	 */
	public function create_doi_data() {
		$host         = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$remote_addr  = isset( $_SERVER['REMOTE_ADDR'] ) ?
			sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) :
			'127.0.0.1';
		$http_referer = isset( $_SERVER['HTTP_REFERER'] ) ?
			sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) :
			$host;
		$user_agent   = isset( $_SERVER['HTTP_USER_AGENT'] ) ?
			sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) :
			'Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/64.0.3282.186 Safari\/537.36';
		return new DoiData( $remote_addr, $http_referer, $user_agent );
	}
}
