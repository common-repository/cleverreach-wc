<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Language;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService;

/**
 * Class Translation_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Language
 */
class Translation_Service implements TranslationService {


	/**
	 * Translates string
	 *
	 * @param string  $string String to translate.
	 * @param mixed[] $arguments Arguments for translation.
	 *
	 * @return string
	 */
	public function translate( $string, array $arguments = array() ) {
		$translated_string = __( $string, 'cleverreach-wc' ); // phpcs:ignore

		return vsprintf( $translated_string, $arguments );
	}

	/**
	 * Returns system language
	 *
	 * @return string|void
	 */
	public function getSystemLanguage() {
		$locale = explode( '_', get_locale() );

		return $locale[0];
	}

	/**
	 * Format number based on locale.
	 *
	 * @param float $number Number to format.
	 *
	 * @return string
	 */
	public function format_number( $number ) {
		$locale = get_user_locale();

		if ( strpos( $locale, 'en' ) === 0 ) {
			$thousand_separator = ',';
			$decimal_separator  = '.';
		} else {
			$thousand_separator = '.';
			$decimal_separator  = ',';
		}

		return number_format( (float) $number, 0, $decimal_separator, $thousand_separator );
	}
}
