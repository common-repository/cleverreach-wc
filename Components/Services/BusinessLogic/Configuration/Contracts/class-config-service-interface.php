<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class Config_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Contracts
 */
interface Config_Service_Interface {

	/**
	 * Sets offline mode check time.
	 *
	 * @param int $time Offline mode check time.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function set_offline_mode_check_time( $time );

	/**
	 * Provides offline mode check time.
	 *
	 * @return int
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function get_offline_mode_check_time();

	/**
	 * Retrieves default form.
	 *
	 * @return int|string|bool
	 */
	public function get_default_form();

	/**
	 * Sets default DOI form.
	 *
	 * @param string $form_api_id Form API id.
	 *
	 * @return void
	 */
	public function set_default_form( $form_api_id );

	/**
	 * Sets flag that indicates that there is admin message
	 *
	 * @param bool $show_notice Should show notice.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function set_show_admin_notice( $show_notice );

	/**
	 * Stores html formatted message that will be shown to admin
	 *
	 * @param Notification $notification Admin notification.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function save_admin_notification_data( Notification $notification );

	/**
	 * Returns notification parameters as array
	 *
	 * @return Notification|null
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function get_admin_notification_data();

	/**
	 * Returns flag that indicates that there is admin message.
	 *
	 * @return bool
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function get_show_admin_notice();

	/**
	 * Saves whether the subscribe for newsletter checkbox is disabled or not.
	 *
	 * @param bool $is_checkbox_disabled Is checkbox disabled.
	 *
	 * @return void
	 */
	public function save_newsletter_checkbox_disabled( $is_checkbox_disabled );

	/**
	 * Returns whether the subscribe for newsletter checkbox is disabled or not.
	 *
	 * @return bool
	 */
	public function get_newsletter_checkbox_disabled();

	/**
	 * Returns caption for newsletter subscription.
	 *
	 * @return string|bool
	 */
	public function get_subscribe_for_newsletter_caption();

	/**
	 * Returns confirmation message for newsletter subscription.
	 *
	 * @return string|bool
	 */
	public function get_newsletter_subscription_confirmation_message();

	/**
	 * Saves newsletter subscription caption.
	 *
	 * @param string $caption Newsletter checkbox caption.
	 *
	 * @return void
	 */
	public function save_subscribe_for_newsletter_caption( $caption );

	/**
	 * Saves newsletter subscription confirmation message.
	 *
	 * @param string $caption Newsletter subscription confirmation message.
	 *
	 * @return void
	 */
	public function save_newsletter_confirmation_message( $caption );

	/**
	 * Gets database version
	 *
	 * @return string
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function get_database_version();

	/**
	 * Sets database version for migration scripts
	 *
	 * @param string $database_version Database version.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	public function set_database_version( $database_version );
}
