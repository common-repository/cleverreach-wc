<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartRecordService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSettings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToCreateAbandonedCartRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Tasks\AbandonedCartTriggerTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\DailySchedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

class AbandonedCartRecordService implements BaseService
{
    /**
     * Retrieves abandoned cart record.
     *
     * @param string $groupId
     * @param string $poolId
     *
     * @return AbandonedCartRecord|null
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get($groupId, $poolId)
    {
        $filter = new QueryFilter();
        $filter->where('groupId', Operators::EQUALS, $groupId);
        $filter->where('poolId', Operators::EQUALS, $poolId);
        $filter->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

        /** @var AbandonedCartRecord|null $abandonedCartRecord */
        $abandonedCartRecord = $this->getRepository()->selectOne($filter);

        return $abandonedCartRecord;
    }

    /**
     * Retrieves abandoned cart record.
     *
     * @param int $id
     *
     * @return AbandonedCartRecord|null
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getById($id)
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $id);
        $filter->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

        /** @var AbandonedCartRecord|null $abandonedCartRecord */
        $abandonedCartRecord = $this->getRepository()->selectOne($filter);

        return $abandonedCartRecord;
    }

    /**
     * Retrieves abandoned cart record.
     *
     * @param string $email
     *
     * @return AbandonedCartRecord|null
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getByEmail($email)
    {
        $filter = new QueryFilter();
        $filter->where('email', Operators::EQUALS, $email);
        $filter->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

        /** @var AbandonedCartRecord|null $abandonedCartRecord */
        $abandonedCartRecord = $this->getRepository()->selectOne($filter);

        return $abandonedCartRecord;
    }

    /**
     * Retrieves abandoned cart record.
     *
     * @param string $cartId
     *
     * @return AbandonedCartRecord|null
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getByCartId($cartId)
    {
        $filter = new QueryFilter();
        $filter->where('cartId', Operators::EQUALS, $cartId);
        $filter->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

        /** @var AbandonedCartRecord|null $abandonedCartRecord */
        $abandonedCartRecord = $this->getRepository()->selectOne($filter);

        return $abandonedCartRecord;
    }

    /**
     * Creates abandoned cart record.
     *
     * @param AbandonedCartTrigger $trigger
     *
     * @return AbandonedCartRecord
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToCreateAbandonedCartRecordException
     */
    public function create(AbandonedCartTrigger $trigger)
    {
        $record = new AbandonedCartRecord();
        $record->setContext($this->getConfigManager()->getContext());
        $record->setTrigger($trigger);
        $record->setPoolId($trigger->getPoolId());
        $record->setGroupId($trigger->getGroupId());
        $record->setCartId($trigger->getCartId());
        $record->setCustomerId($trigger->getCustomerId());

        if (($settings = $this->getAbandonedCartSettingsService()->get()) === null) {
            throw new FailedToCreateAbandonedCartRecordException("Settings not provided.");
        }

        try {
            $receiver = $this->getReceiverProxy()->getReceiver($trigger->getGroupId(), $trigger->getPoolId());
        } catch (\Exception $e) {
            throw new FailedToCreateAbandonedCartRecordException(
                "Receiver [{$trigger->getGroupId()}:{$trigger->getPoolId()}] not found.",
                $e->getCode(),
                $e
            );
        }

        $record->setEmail($receiver->getEmail());

        $this->getRepository()->save($record);

        // Scheduled task requires record id. Therefore we must save record, before the schedule is created.
        $scheduleId = $this->addSchedule($record, $settings);
        $record->setScheduleId($scheduleId);

        $this->getRepository()->update($record);

        return $record;
    }

    /**
     * Updates abandoned cart record.
     *
     * @param AbandonedCartRecord $record
     *
     * @return void
     */
    public function update(AbandonedCartRecord $record)
    {
        $this->getRepository()->update($record);
    }

    /**
     * Deletes abandoned cart record.
     *
     * @param string $groupId
     * @param string $poolId
     *
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function delete($groupId, $poolId)
    {
        $record = $this->get($groupId, $poolId);
        if ($record === null) {
            return;
        }

        $this->doDelete($record);
    }

    /**
     * Deletes record.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartRecord $record
     *
     * @return void
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function deleteRecord(AbandonedCartRecord $record)
    {
        $this->doDelete($record);
    }

    /**
     * Deletes all records with associated schedules.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function deleteAllRecords()
    {
        // delete all records
        $query = new QueryFilter();
        $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes $repository */
        $repository = $this->getRepository();
        $repository->deleteWhere();

        // delete all schedules for abandoned cart
        $query = new QueryFilter();
        $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());
        $query->where('taskType', Operators::EQUALS, 'AbandonedCartTriggerTask');

        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes $repository */
        $repository = $this->getScheduleRepository();
        $repository->deleteWhere($query);
    }

    /**
     * Creates abandoned cart trigger schedule.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartRecord $record
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSettings $settings
     *
     * @return int
     */
    private function addSchedule(AbandonedCartRecord $record, AbandonedCartSettings $settings)
    {
        $currentTime = $this->getTimeProvider()->getCurrentLocalTime();
        $targetTime = $currentTime->modify("+{$settings->getDelay()} hour");

        $queueName = $this->getConfigService()->getDefaultQueueName();
        $context = $this->getConfigManager()->getContext();

        $schedule = new DailySchedule(new AbandonedCartTriggerTask($record->getId()), $queueName, $context);
        $schedule->setHour((int)$targetTime->format('G'));
        $schedule->setMinute((int)$targetTime->format('i'));
        $schedule->setRecurring(false);
        $schedule->setNextSchedule();

        return $this->getScheduleRepository()->save($schedule);
    }

    /**
     * Retrieves abandoned cart repository.
     *
     * @return RepositoryInterface
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getRepository()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return RepositoryRegistry::getRepository(AbandonedCartRecord::getClassName());
    }

    /**
     * Retrieves receiver proxy.
     *
     * @return Proxy
     */
    private function getReceiverProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
    }

    /**
     * Retrieves abandoned cart settings service
     *
     * @return AbandonedCartSettingsService
     */
    private function getAbandonedCartSettingsService()
    {
        /** @var AbandonedCartSettingsService $abandonedCartSettingsService */
        $abandonedCartSettingsService = ServiceRegister::getService(AbandonedCartSettingsService::CLASS_NAME);

        return $abandonedCartSettingsService;
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
     * Deletes record.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartRecord $record
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function doDelete(AbandonedCartRecord $record)
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $record->getScheduleId());
        if (($schedule = $this->getScheduleRepository()->selectOne($filter)) !== null) {
            $this->getScheduleRepository()->delete($schedule);
        }

        $this->getRepository()->delete($record);
    }
}
