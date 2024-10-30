<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository as QueueItemRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Priority;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use Exception;

/**
 * Class Queue_Item_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Queue_Item_Repository extends Base_Repository implements QueueItemRepositoryInterface {


	/**
	 * Returns full class name.
	 *
	 * @noinspection SenselessMethodDuplicationInspection
	 *
	 * @return string Full class name.
	 */
	public static function getClassName() {
		return __CLASS__;
	}

	/**
	 * Finds list of earliest queued queue items per queue. Following list of criteria for searching must be satisfied:
	 *      - Queue must be without already running queue items
	 *      - For one queue only one (oldest queued) item should be returned
	 *
	 * @param int $priority Queue item priority.
	 * @param int $limit Result set limit. By default max 10 earliest queue items will be returned.
	 *
	 * @return QueueItem[] Found queue item list
	 */
	public function findOldestQueuedItems( $priority, $limit = 10 ) {
		if ( Priority::NORMAL !== $priority ) {
			return array();
		}

		$this->table_name = $this->db->prefix . Database::BASE_TABLE;

		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity    = new $this->entity_class();
		$type      = $this->escape_value( $entity->getConfig()->getType() );
		$index_map = IndexHelper::mapFieldsToIndexes( $entity );

		$status_index     = 'index_' . $index_map['status'];
		$queue_name_index = 'index_' . $index_map['queueName'];

		$running_queues_query = "SELECT $queue_name_index FROM `$this->table_name` q2 WHERE q2.`$status_index` = '"
								. QueueItem::IN_PROGRESS . "' AND q2.`type` = $type";

		$sql = "SELECT queueTable.* 
	            FROM (
	                 SELECT $queue_name_index, MIN(id) AS id
	                 FROM `$this->table_name` AS q
	                 WHERE q.`type` = $type AND q.`$status_index` = '" . QueueItem::QUEUED . "' AND q.`$queue_name_index` NOT IN ($running_queues_query)
	                 GROUP BY `$queue_name_index` LIMIT $limit
	            ) AS queueView  
	            INNER JOIN `$this->table_name` as queueTable
	            ON queueView.id = queueTable.id";

		$result = $this->db->get_results( $sql, ARRAY_A );

		/**
		 * Array of queued items
		 *
		 * @var QueueItem[] $queued_items
		 */
		$queued_items = $this->translateToEntities( $result );

		return $queued_items;
	}

	/**
	 * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise update will be performed.
	 *
	 * @param QueueItem           $queue_item Item to save.
	 * @param array<string,mixed> $additional_where List of key/value pairs that must be satisfied upon saving queue item.
	 *                                        Key is queue item property and value is condition value for that property.
	 *
	 * @return int Id of saved queue item.
	 * @throws QueueItemSaveException If queue item could not be saved.
	 */
	public function saveWithCondition( QueueItem $queue_item, array $additional_where = array() ) {
		$item_id = null;
		try {
			$queue_item_id = $queue_item->getId();
			if ( null === $queue_item_id || $queue_item_id <= 0 ) {
				$item_id = $this->save( $queue_item );
			} else {
				$this->update_queue_item( $queue_item, $additional_where );
				$item_id = $queue_item_id;
			}
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
	 * Removes queue item.
	 *
	 * @param QueueItem $queue_item Queue item.
	 *
	 * @return bool
	 */
	public function removeQueueItem( QueueItem $queue_item ) {
		return $this->delete( $queue_item );
	}

	/**
	 * Fails task that cannot be deserialized.
	 *
	 * @param QueueItem $queue_item Queue item.
	 *
	 * @return bool
	 */
	public function forceFail( QueueItem $queue_item ) {
		$result = false;

		$query  = "SELECT * FROM {$this->table_name} WHERE id = '" . esc_sql( $queue_item->getId() ) . "' ";
		$entity = $this->db->query( $query );

		if ( $entity ) {
			$data = $this->transform_force_failed_item( $queue_item );
			$this->db->update(
				$this->table_name,
				$data,
				array( 'id' => $queue_item->getId() )
			);

			$result = true;
		}

		return $result;
	}

	/**
	 * Transforms force failed item.
	 *
	 * @param QueueItem $queue_item Queue item.
	 *
	 * @return mixed[]
	 */
	private function transform_force_failed_item( QueueItem $queue_item ) {
		$data['type']    = $queue_item->getConfig()->getType();
		$data['index_1'] = $queue_item->getStatus();
		$data['index_3'] = $queue_item->getQueueName();
		$data['index_4'] = $queue_item->getContext();
		$data['index_5'] = $queue_item->getQueueTimestamp();
		$data['index_6'] = $queue_item->getLastExecutionProgress();
		$data['index_7'] = $queue_item->getLastUpdateTimestamp();
		$data['index_8'] = $queue_item->getPriority();

		$entity_values = $queue_item->toArray();

		if ( 'Form' === $data['type'] ) {
			$entity_values['content'] = htmlentities( $entity_values['content'] );
		}

		$data['data'] = json_encode( $entity_values );

		if ( ! $data['data'] ) {
			$data['data'] = '';
		}

		return $data;
	}


	/**
	 * Updates database record with data from provided $queueItem.
	 *
	 * @param QueueItem           $queue_item Queue item.
	 * @param array<string,mixed> $conditions Array of update conditions.
	 *
	 * @return void
	 *
	 * @throws QueueItemSaveException Queue item save exception.
	 */
	private function update_queue_item( QueueItem $queue_item, array $conditions = array() ) {
		$conditions = array_merge( $conditions, array( 'id' => $queue_item->getId() ) );

		$item = $this->select_for_update( $conditions );
		$this->check_if_record_exists( $item );

		if ( null !== $item ) {
			$success = $this->update_with_condition( $queue_item, $conditions );
			if ( ! $success ) {
				$message = 'DB failed to update QueueItem.';
				Logger::logError( $message );
				throw new QueueItemSaveException( esc_html( $message ) );
			}
		}
	}

	/**
	 * Validates if item exists.
	 *
	 * @param Entity $item Queue item.
	 *
	 * @return void
	 *
	 * @throws QueueItemSaveException Queue item save exception.
	 */
	private function check_if_record_exists( Entity $item = null ) {
		if ( null === $item ) {
			$message = 'Failed to save queue item, update condition(s) not met.';
			Logger::logDebug( 'Failed to save queue item, update condition(s) not met.', 'Integration' );

			throw new QueueItemSaveException( esc_html( $message ) );
		}
	}
}
