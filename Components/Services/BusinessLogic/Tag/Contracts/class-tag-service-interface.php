<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\SpecialTag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;

interface Tag_Service_Interface {

	const CLASS_NAME = __CLASS__;

	/**
	 * Get tags.
	 *
	 * @return Tag[]
	 */
	public function get_tags();

	/**
	 * Get list of special tags.
	 *
	 * @return SpecialTag[]
	 */
	public function get_special_tags();

	/**
	 * Get list of origin tags.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags();

	/**
	 * Returns array of origin tags for given roles.
	 *
	 * @param mixed[] $roles Array of roles.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags_by_roles( $roles );

	/**
	 * Returns array of origin tags for given website name.
	 *
	 * @param string $website_name Name of the website.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags_by_website_name( $website_name );
}
