<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToTriggerAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Trigger\TriggerCartAutomationTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\DailySchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

class AutomationRecordService implements BaseService
{
    /**
     * Creates an instance of a record.
     *
     * @param AutomationRecord $record
     *
     * @return AutomationRecord
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function create(AutomationRecord $record)
    {
        $automation = $this->getCartService()->find($record->getAutomationId());
        if ($automation === null) {
            throw new FailedToCreateAutomationRecordException('Automation does not exist.');
        }

        if (!$automation->isActive()) {
            throw new FailedToCreateAutomationRecordException('Automation is not active.');
        }

        $records = $this->findBy(array(
            'automationId' => $record->getAutomationId(),
            'email' => $record->getEmail(),
            'status' => RecoveryEmailStatus::PENDING,
        ));

        if (!empty($records)) {
            throw new FailedToCreateAutomationRecordException('Record already exists for receiver.');
        }

        $records = $this->findBy(array('automationId' => $record->getAutomationId(), 'cartId' => $record->getCartId()));
        if (!empty($records)) {
            throw new FailedToCreateAutomationRecordException('Record already exists for cart.');
        }

        $record->setStatus(RecoveryEmailStatus::PENDING);
        $record->setIsRecovered(false);

        // We have to save record first in order to get access to its ID.
        $this->getRepository()->save($record);

        $settings = $automation->getSettings();
        $scheduleId = $this->scheduleTrigger($record, $settings['delay']);
        $record->setScheduleId($scheduleId);
        $this->getRepository()->update($record);

        return $record;
    }

    /**
     * Updates Record.
     *
     * @param AutomationRecord $record
     *
     * @return AutomationRecord
     *
     * @throws FailedToUpdateAutomationRecordException
     */
    public function update(AutomationRecord $record)
    {
        try {
            $this->getRepository()->update($record);
        } catch (\Exception $e) {
            throw new FailedToUpdateAutomationRecordException($e->getMessage(), $e->getCode(), $e);
        }

        return $record;
    }

    /**
     * Refreshes schedule time.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function refreshScheduleTime(AutomationRecord $record)
    {
        $automation = $this->getCartService()->find($record->getAutomationId());
        if ($automation === null) {
            throw new FailedToUpdateAutomationRecordException('Automation does not exist.');
        }

        try {
            if ($schedule = $this->getSchedule($record->getScheduleId())) {
                $settings = $automation->getSettings();
                $this->setScheduleTime($record, $schedule, $settings['delay']);
                $this->getScheduleRepository()->update($schedule);
            }
        } catch (\Exception $e) {
            throw new FailedToUpdateAutomationRecordException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Provides Record identified by id.
     *
     * @param int|string $id
     *
     * @return AutomationRecord | null
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function find($id)
    {
        $query = new QueryFilter();
        $query->where('id', Operators::EQUALS, $id);

        /** @var AutomationRecord | null $automationRecord */
        $automationRecord = $this->getRepository()->selectOne($query);

        return $automationRecord;
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function findBy(array $query)
    {
        $queryFilter = new QueryFilter();

        foreach ($query as $column => $value) {
            if ($value === null) {
                $queryFilter->where($column, Operators::NULL);
            } else {
                $queryFilter->where($column, Operators::EQUALS, $value);
            }
        }

        /** @var AutomationRecord[] $automationRecords */
        $automationRecords = $this->getRepository()->select($queryFilter);

        return $automationRecords;
    }

    /**
     * Provides AutomationRecords by provided criteria
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter $filter
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord[]
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function filter(QueryFilter $filter)
    {
        /** @var AutomationRecord[] $automationRecords */
        $automationRecords = $this->getRepository()->select($filter);

        return $automationRecords;
    }

    /**
     * Deletes Record identified by id.
     *
     * @param int|string $id
     *
     * @return void
     *
     * @throws FailedToDeleteAutomationRecordException
     */
    public function delete($id)
    {
        try {
            if ($record = $this->find($id)) {
                $this->deleteSchedule($record->getScheduleId());
                $this->getRepository()->delete($record);
            }
        } catch (\Exception $e) {
            throw new FailedToDeleteAutomationRecordException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteBy(array $query)
    {
        try {
            $records = $this->findBy($query);
            $repository = $this->getRepository();
            foreach ($records as $record) {
                $this->deleteSchedule($record->getScheduleId());
                $repository->delete($record);
            }
        } catch (\Exception $e) {
            throw new FailedToDeleteAutomationRecordException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $recordId
     *
     * @return void
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToTriggerAutomationRecordException
     */
    public function triggerRecord($recordId)
    {
        $record = $this->find($recordId);
        if (!$record) {
            throw new FailedToTriggerAutomationRecordException("Automation record not found for id: $recordId");
        }

        $queueName = $this->getConfigService()->getDefaultQueueName();
        $context = $this->getConfigManager()->getContext();

        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        $queueService->enqueue($queueName, new TriggerCartAutomationTask($recordId), $context);

        $this->deleteSchedule($record->getScheduleId());
    }

    /**
     * Provides automation record repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function getRepository()
    {
        return RepositoryRegistry::getRepository(AutomationRecord::getClassName());
    }

    /**
     * Provides cart automation service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\CartAutomationService
     */
    protected function getCartService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services\CartAutomationService $cartService */
        $cartService = ServiceRegister::getService(CartAutomationService::CLASS_NAME);

        return $cartService;
    }

    /**
     * Schedules trigger for automation record.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     * @param int $delay
     *
     * @return int
     */
    protected function scheduleTrigger(AutomationRecord $record, $delay)
    {
        $queueName = $this->getConfigService()->getDefaultQueueName();
        $context = $this->getConfigManager()->getContext();

        $schedule = new DailySchedule(new TriggerCartAutomationTask($record->getId()), $queueName, $context);
        $this->setScheduleTime($record, $schedule, $delay);

        return $this->getScheduleRepository()->save($schedule);
    }

    /**
     * Sets schedule time.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord $record
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule $schedule
     * @param int $delay
     *
     * @return void
     */
    protected function setScheduleTime(AutomationRecord $record, Schedule $schedule, $delay)
    {
        $currentTime = $this->getTimeProvider()->getCurrentLocalTime();
        $targetTime = $currentTime->modify("+{$delay} hour");
        $record->setScheduledTime($targetTime);
        $schedule->setHour((int)$targetTime->format('G'));
        $schedule->setMinute((int)$targetTime->format('i'));
        $schedule->setRecurring(false);
        $schedule->setNextSchedule();
    }

    /**
     * Deletes schedule.
     *
     * @param mixed $scheduleId
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function deleteSchedule($scheduleId)
    {
        if (!$scheduleId) {
            return;
        }

        if ($schedule = $this->getSchedule($scheduleId)) {
            $this->getScheduleRepository()->delete($schedule);
        }
    }

    /**
     * Retrieves time provider.
     *
     * @return TimeProvider
     */
    private function getTimeProvider()
    {
        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        return $timeProvider;
    }

    /**
     * Retrieves schedule repository.
     *
     * @return RepositoryInterface
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getScheduleRepository()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return RepositoryRegistry::getRepository(Schedule::getClassName());
    }

    /**
     * Retrieves config service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration
     */
    private function getConfigService()
    {
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configurationService */
        $configurationService = ServiceRegister::getService(Configuration::CLASS_NAME);

        return $configurationService;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * Provides schedule identified by id.
     *
     * @param int $scheduleId
     *
     * @return Schedule|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function getSchedule($scheduleId)
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $scheduleId);
        $repository = $this->getScheduleRepository();
        /** @var Schedule|null $schedule */
        $schedule = $repository->selectOne($filter);

        return $schedule;
    }
}
