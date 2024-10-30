<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel\Settings;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Double_Opt_In_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormCacheService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Newsletter_Settings
 *
 * @package CleverReach\WooCommerce\ViewModel\Settings
 */
class Newsletter_Settings {


	/**
	 * Double opt in service
	 *
	 * @var Double_Opt_In_Service
	 */
	private $doi_service;

	/**
	 * Newsletter_Settings constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get all forms.
	 *
	 * @return Form[]
	 */
	public function get_forms() {
		try {
			return $this->get_form_cache_service()->getForms();
		} catch ( FailedToRetrieveFormCacheException $e ) {
			Logger::logError(
				'Failed to get Forms.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);

			return array();
		}
	}

	/**
	 * Retrieves display time of newsletter checkbox.
	 *
	 * @return int|null
	 */
	public function get_display_time_of_newsletter_checkbox() {
		return $this->get_config_service()->get_checkbox_display_time();
	}

	/**
	 * Get newsletter checkbox caption.
	 *
	 * @return string|false
	 */
	public function get_subscribe_for_newsletter_caption() {
		return $this->get_config_service()->get_subscribe_for_newsletter_caption();
	}

	/**
	 * Get confirmation message for newsletter subscription.
	 *
	 * @return string|false
	 */
	public function get_newsletter_subscription_confirmation_message() {
		return $this->get_config_service()->get_newsletter_subscription_confirmation_message();
	}

	/**
	 * Get default form ID for store.
	 *
	 * @return mixed
	 */
	public function get_default_form_id() {
		return $this->get_config_service()->get_default_form();
	}

	/**
	 * Checks if user newsletter checkbox is enabled.
	 *
	 * @return bool
	 */
	public function is_newsletter_checkbox_enabled() {
		return ! $this->get_config_service()->get_newsletter_checkbox_disabled();
	}

	/**
	 * Checks if DOI is enabled
	 *
	 * @return bool
	 */
	public function is_doi_enabled() {
		return $this->get_doi_service()->is_doi_enabled();
	}

	/**
	 * Checks if user data is complete.
	 *
	 * @return bool
	 */
	public function is_user_data_complete() {
		try {
			$user_info = $this->get_user_proxy()->getUserInfo();

			return $user_info->getFirstName() && $user_info->getLastName() && $user_info->getStreet()
					&& $user_info->getZip() && $user_info->getCompany() && $user_info->getCity()
					&& $user_info->getPhone() && $user_info->getCountry();
		} catch ( BaseException $e ) {
			Logger::logError(
				"Unable to get UserInfo: {$e->getMessage()}",
				'Integration',
				array( new LogContextData( 'trace', $e->getTraceAsString() ) )
			);

			return false;
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
		$config_service = ServiceRegister::getService( Config_Service::CLASS_NAME );

		return $config_service;
	}

	/**
	 * Retrieves FormCache service.
	 *
	 * @return FormCacheService
	 */
	protected function get_form_cache_service() {
		/**
		 * Form cache service.
		 *
		 * @var FormCacheService $form_cache_service
		 */
		$form_cache_service = ServiceRegister::getService( FormCacheService::CLASS_NAME );

		return $form_cache_service;
	}

	/**
	 * Retrieves UserProxy service.
	 *
	 * @return UserProxy
	 */
	protected function get_user_proxy() {
		/**
		 * User proxy.
		 *
		 * @var UserProxy $proxy
		 */
		$proxy = ServiceRegister::getService( UserProxy::CLASS_NAME );

		return $proxy;
	}
}
