<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\ArchivedQueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Interface ArchivedQueueItemRepository.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces
 * @method ArchivedQueueItem[] select(QueryFilter $filter = null)
 * @method ArchivedQueueItem|null selectOne(QueryFilter $filter = null)
 */
interface ArchivedQueueItemRepository extends RepositoryInterface
{
    /**
     * Archives given queue item.
     *
     * @param QueueItem $queueItem Item to archive
     * @param array<string, string> $additionalWhere List of key/value pairs that must be satisfied upon archiving queue item. Key is
     *  queue item property and value is condition value for that property.
     *
     * @return void
     * @throws QueueItemSaveException if queue item could not be saved
     */
    public function archiveQueueItem(QueueItem $queueItem, array $additionalWhere = array());
}
