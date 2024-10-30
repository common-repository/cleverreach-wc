<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag;

use CleverReach\WooCommerce\Components\Repositories\Role_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Contracts\Tag_Service_Interface;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Buyer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Contact;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\SpecialTag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Subscriber;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;

/**
 * Class Tag_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag
 */
class Tag_Service implements Tag_Service_Interface {

	const TAG_TYPE_ROLE = 'Role';
	const TAG_TYPE_SITE = 'Site';

	const GUEST_CUSTOMER_TAG = 'GuestCustomer';

	/**
	 * Role repository.
	 *
	 * @var Role_Repository
	 */
	private $role_repository;

	/**
	 * Get tags.
	 *
	 * @return Tag[]
	 */
	public function get_tags() {
		$origin_tags  = $this->get_origin_tags();
		$special_tags = $this->get_special_tags();

		return array_merge( $origin_tags, $special_tags );
	}

	/**
	 * Get list of special tags.
	 *
	 * @return SpecialTag[]
	 */
	public function get_special_tags() {
		return array(
			new Buyer( Config_Service::INTEGRATION_NAME ),
			new Contact( Config_Service::INTEGRATION_NAME ),
			new Subscriber( Config_Service::INTEGRATION_NAME ),
		);
	}

	/**
	 * Get list of origin tags.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags() {
		$role_tags = $this->format_tags( $this->get_role_repository()->get_user_roles(), self::TAG_TYPE_ROLE );

		$guest_customer_tag = new Tag( Config_Service::INTEGRATION_NAME, self::GUEST_CUSTOMER_TAG );
		$guest_customer_tag->setType( self::TAG_TYPE_ROLE );
		$role_tags[] = $guest_customer_tag;

		$site_tag = new Tag(
			Config_Service::INTEGRATION_NAME,
			Shop_Helper::get_shop_name() ? Shop_Helper::get_shop_name() : Shop_Helper::get_shop_url()
		);

		$site_tag->setType( self::TAG_TYPE_SITE );

		$role_tags[] = $site_tag;

		return $role_tags;
	}

	/**
	 * Repacks entities into core Tag objects
	 *
	 * @param string[] $source_tags Array of source tags.
	 * @param string   $tag_type Tag type to format.
	 *
	 * @return Tag[]
	 */
	private function format_tags( $source_tags, $tag_type ) {
		$tag_collection = array();

		foreach ( $source_tags as $source_tag ) {
			if ( ! empty( $source_tag ) ) {
				$tag = new Tag( Config_Service::INTEGRATION_NAME, $source_tag );
				$tag->setType( $tag_type );
				$tag_collection[] = $tag;
			}
		}

		return $tag_collection;
	}

	/**
	 * Retrieves recipient repository.
	 *
	 * @return Role_Repository
	 */
	private function get_role_repository() {
		if ( null === $this->role_repository ) {
			$this->role_repository = new Role_Repository();
		}

		return $this->role_repository;
	}

	/**
	 * Returns array of origin tags for given roles.
	 *
	 * @param mixed[] $roles Array of roles.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags_by_roles( $roles ) {
		$results = array();
		foreach ( $roles as $role ) {
			if ( is_array( $role ) ) {
				$results[] = translate_user_role( array_key_exists( 'name', $role ) ? $role['name'] : '' );
			} else {
				$results[] = translate_user_role( $role );
			}
		}

		return $this->format_tags( $results, self::TAG_TYPE_ROLE );
	}

	/**
	 * Returns array of origin tags for given website name.
	 *
	 * @param string $website_name Name of the website.
	 *
	 * @return Tag[]
	 */
	public function get_origin_tags_by_website_name( $website_name ) {
		return $this->format_tags( array( $website_name ), self::TAG_TYPE_SITE );
	}
}
