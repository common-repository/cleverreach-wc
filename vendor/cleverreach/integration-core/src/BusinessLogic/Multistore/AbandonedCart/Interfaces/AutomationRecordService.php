<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Interface AutomationRecordService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces
 */
interface AutomationRecordService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates an instance of a record.
     *
     * @param AutomationRecord $record
     *
     * @return AutomationRecord
     *
     * @throws FailedToCreateAutomationRecordException
     */
    public function create(AutomationRecord $record);

    /**
     * Updates Record.
     *
     * @param AutomationRecord $record
     *
     * @return AutomationRecord
     *
     * @throws FailedToUpdateAutomationRecordException
     */
    public function update(AutomationRecord $record);

    /**
     * Refreshes schedule time.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     */
    public function refreshScheduleTime(AutomationRecord $record);

    /**
     * Provides Record identified by id.
     *
     * @param int|string $id
     *
     * @return AutomationRecord | null
     */
    public function find($id);

    /**
     * Provides Records identified by query.
     *
     * @param array<string, mixed> $query
     *
     * @return AutomationRecord[]
     */
    public function findBy(array $query);

    /**
     * Provides AutomationRecords by provided criteria (condition, limit, offset, sorting)
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter $filter
     *
     * @return AutomationRecord[]
     */
    public function filter(QueryFilter $filter);

    /**
     * Deletes Record identified by id.
     *
     * @param int $id
     *
     * @return void
     *
     * @throws FailedToDeleteAutomationRecordException
     */
    public function delete($id);

    /**
     * Deletes Records identified by query.
     *
     * @param array<string, mixed> $query
     *
     * @return void
     *
     * @throws FailedToDeleteAutomationRecordException
     */
    public function deleteBy(array $query);

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function triggerRecord($id);
}
