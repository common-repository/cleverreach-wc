<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\ArchivedQueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use Exception;

/**
 * Class Archive_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Archive_Repository extends Base_Repository implements ArchivedQueueItemRepository {
	/**
	 * Archive_Repository constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . Database::ARCHIVE_TABLE;
	}

	/**
	 * Archive queue item
	 *
	 * @param QueueItem           $queue_item Queue item.
	 * @param array<string,mixed> $additional_where List of key/value pairs that must be satisfied upon archiving queue item.
	 *
	 * @return int
	 *
	 * @throws QueueItemSaveException Exception.
	 */
	public function archiveQueueItem( QueueItem $queue_item, array $additional_where = array() ) {
		$item_id          = null;
		try {
			$item_id = $this->save_entity_to_storage( $queue_item );
		} catch ( Exception $exception ) {
			throw new QueueItemSaveException(
				'Failed to save queue item with id: ' . esc_html( $item_id ),
				0,
				$exception // phpcs:ignore
			);
		}

		return $item_id;
	}

	/**
	 * Gets class name.
	 *
	 * @return string
	 */
	public static function getClassName() {
		return __CLASS__;
	}
}
