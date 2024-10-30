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
use CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Double_Opt_In_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Newsletter_Settings_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Newsletter_Settings_Controller extends Clever_Reach_Base_Controller {


	/**
	 * DOI service.
	 *
	 * @var Double_Opt_In_Service $doi_service
	 */
	private $doi_service;

	/**
	 * Saves newsletter settings.
	 *
	 * @return void
	 */
	public function save_newsletter_settings() {
		$this->save_settings();
		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Saves services
	 *
	 * @return void
	 */
	private function save_settings() {
		$is_newsletter_checkbox_enabled  = HTTP_Helper::get_param( 'isNewsletterCheckboxEnabled' ) === 'true';
		$newsletter_checkbox_caption     = HTTP_Helper::get_param( 'newsletterCheckboxCaption' );
		$newsletter_confirmation_message = HTTP_Helper::get_param( 'newsletterConfirmationMessage' );
		$is_doi_enabled                  = HTTP_Helper::get_param( 'isDoiEnabled' ) === 'true';
		$default_form                    = HTTP_Helper::get_param( 'defaultForm' );
		$display_time                    = HTTP_Helper::get_param( 'displayTime' );

		if ( $display_time ) {
			$this->get_config_service()
				->save_checkbox_display_time( (int) $display_time );
		}

		$this->get_config_service()
			->save_newsletter_checkbox_disabled( ! $is_newsletter_checkbox_enabled );

		if ( $is_newsletter_checkbox_enabled ) {
			$this->get_config_service()
				->save_subscribe_for_newsletter_caption( $newsletter_checkbox_caption );
			$this->get_config_service()
				->save_newsletter_confirmation_message( $newsletter_confirmation_message );

			$this->get_doi_service()->save_double_opt_in( $is_doi_enabled );

			if ( $is_doi_enabled ) {
				$this->get_config_service()->set_default_form( $default_form );
			}
		} else {
			$this->get_config_service()
				->save_subscribe_for_newsletter_caption( '' );
		}
	}

	/**
	 * Retrieves DOI service.
	 *
	 * @return Double_Opt_In_Service
	 */
	protected function get_doi_service() {
		if ( ! $this->doi_service ) {
			$this->doi_service = new Double_Opt_In_Service();
		}

		return $this->doi_service;
	}

	/**
	 * Retrieves config service.
	 *
	 * @return Config_Service
	 */
	protected function get_config_service() {
		/**
		 * Config service.
		 *
		 * @var Config_Service $config_service
		 */
		$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

		return $config_service;
	}
}
