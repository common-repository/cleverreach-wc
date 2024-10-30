<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

/**
 * Class Recovery_Record
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities
 */
class Recovery_Record extends Entity {

	const CLASS_NAME = __CLASS__;

	/**
	 * Token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Customer's email.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * Current session.
	 *
	 * @var string
	 */
	protected $session_key;

	/**
	 * Automation record id.
	 *
	 * @var string
	 */
	protected $automation_record_id;

	/**
	 * Cart items.
	 *
	 * @var mixed[]
	 */
	protected $items;

	/**
	 * Array of fields' names.
	 *
	 * @var string[]
	 */
	protected $fields = array(
		'id',
		'token',
		'email',
		'session_key',
		'items',
		'automation_record_id',
	);

	/**
	 * Returns value of the token field.
	 *
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Sets token field.
	 *
	 * @param string $token Token.
	 *
	 * @return void
	 */
	public function set_token( $token ) {
		$this->token = $token;
	}

	/**
	 * Returns value of the email field.
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Sets email field.
	 *
	 * @param string $email User's email.
	 *
	 * @return void
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Returns value of the session field.
	 *
	 * @return string
	 */
	public function get_session_key() {
		return $this->session_key;
	}

	/**
	 * Sets session field.
	 *
	 * @param string $session_key Session key.
	 *
	 * @return void
	 */
	public function set_session_key( $session_key ) {
		$this->session_key = $session_key;
	}

	/**
	 * Returns an array of items.
	 *
	 * @return mixed[]
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Sets items field.
	 *
	 * @param mixed[] $items Items.
	 *
	 * @return void
	 */
	public function set_items( $items ) {
		$this->items = $items;
	}

	/**
	 * Returns automation record's ID.
	 *
	 * @return string
	 */
	public function get_automation_record_id() {
		return $this->automation_record_id;
	}

	/**
	 * Sets automation record's ID.
	 *
	 * @param string $automation_record_id Automation record's ID.
	 *
	 * @return void
	 */
	public function set_automation_record_id( $automation_record_id ) {
		$this->automation_record_id = $automation_record_id;
	}

	/**
	 * Returns configuration of the recovery record entity.
	 *
	 * @return EntityConfiguration
	 */
	public function getConfig() {
		$map = new IndexMap();
		$map->addStringIndex( 'token' );
		$map->addStringIndex( 'email' );
		$map->addStringIndex( 'session_key' );
		$map->addStringIndex( 'items' );

		return new EntityConfiguration( $map, 'RecoveryRecord' );
	}
}
