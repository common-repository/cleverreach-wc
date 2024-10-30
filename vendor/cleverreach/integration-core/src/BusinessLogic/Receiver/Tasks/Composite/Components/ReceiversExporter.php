<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldMapConfigService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldMapService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\FieldService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\Decrement;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\UnsetModifier;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverExportCompleteEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiversSynchronizedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class ReceiversExporter extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var int $reconfiguredBatchSize
     */
    private $reconfiguredBatchSize = 0;
    /**
     * @var int $totalNumberOfReceivers
     */
    protected $totalNumberOfReceivers = 0;
    /**
     * @var int $numberOfSynchronizedReceivers
     */
    private $numberOfSynchronizedReceivers = 0;

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(
            array(
                $this->reconfiguredBatchSize,
                $this->totalNumberOfReceivers,
                $this->numberOfSynchronizedReceivers,
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->reconfiguredBatchSize,
            $this->totalNumberOfReceivers,
            $this->numberOfSynchronizedReceivers
        ) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'reconfiguredBatchSize' => $this->reconfiguredBatchSize,
            'totalNumberOfReceivers' => $this->totalNumberOfReceivers,
            'numberOfSynchronizedReceivers' => $this->numberOfSynchronizedReceivers,
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        $static = new static();
        $static->reconfiguredBatchSize = $data['reconfiguredBatchSize'];
        $static->totalNumberOfReceivers = $data['totalNumberOfReceivers'];
        $static->numberOfSynchronizedReceivers = $data['numberOfSynchronizedReceivers'];

        return $static;
    }

    /**
     * Checks whether task can be reconfigured or not.
     *
     * @return bool
     */
    public function canBeReconfigured()
    {
        return (int)$this->reconfiguredBatchSize !== 1;
    }

    /**
     * Reconfigures task.
     *
     * @return void
     */
    public function reconfigure()
    {
        if ($this->reconfiguredBatchSize === 0) {
            $this->reconfiguredBatchSize = $this->getConfigService()->getSynchronizationBatchSize();
        }

        $this->reconfiguredBatchSize = (int)ceil($this->reconfiguredBatchSize / 2);
    }

    /**
     * Exports receivers to CleverReach api.
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \Exception
     */
    public function execute()
    {
        $this->initializeNumberOfReceivers();
        $batchOfEmails = $this->getBatchOfEmails();
        $enabledFieldNames = $this->getEnabledFieldNames();
        $this->reportAlive();
        $fieldMap = $this->getFieldMapConfigService()->get();
        $this->reportAlive();
        while (!empty($batchOfEmails)) {
            $batchOfReceivers = $this->getReceivers($batchOfEmails);

            $this->reportAlive();

            $this->getFieldMapService()->applyMapping($fieldMap, $batchOfReceivers);
            $this->reportAlive();

            $this->addModifiers($batchOfReceivers, $enabledFieldNames);
            $this->unsubscribeBlacklistedReceivers($batchOfReceivers);

            $this->reportAlive();

            if (!empty($batchOfReceivers)) {
                $this->getReceiverProxy()->upsertPlus($this->getExecutionContext()->groupId, $batchOfReceivers);
            }

            $this->reportAlive();

            $this->unsetExportedBatch($batchOfEmails);

            /** @var string[] $receiverEmails */
            $receiverEmails = array_keys($batchOfEmails);
            ReceiverEventBus::getInstance()->fire(new ReceiversSynchronizedEvent($receiverEmails));

            $this->numberOfSynchronizedReceivers += count($batchOfEmails);
            $this->reportProgress(min(99, $this->getCurrentProgress()));

            $batchOfEmails = $this->getBatchOfEmails();
        }

        ReceiverEventBus::getInstance()->fire(new ReceiverExportCompleteEvent($this->numberOfSynchronizedReceivers));

        $this->reportProgress(100);
    }

    /**
     * Returns current batch size.
     *
     * @return int
     */
    protected function getBatchSize()
    {
        if ($this->reconfiguredBatchSize !== 0) {
            return $this->reconfiguredBatchSize;
        }

        return $this->getConfigService()->getSynchronizationBatchSize();
    }

    /**
     * Retrieves batch of receivers for synchronization.
     *
     * @return mixed[]
     */
    protected function getBatchOfEmails()
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();

        return array_slice($executionContext->receiverEmails, 0, $this->getBatchSize(), true);
    }

    /**
     * Retrieves batch of receivers for the list of receiver emails.
     *
     * @param mixed[] $receiverEmailsBatch
     *
     * @return Receiver[]
     *
     */
    private function getReceivers(array &$receiverEmailsBatch)
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $isServiceSpecificDataRequired = $executionContext->syncConfiguration->isClassSpecificDataRequired();
        $batchOfReceivers = array();

        foreach ($executionContext->services as $service) {
            $emailBatchForService = $this->getEmailsForService($service->getUuid(), $receiverEmailsBatch);

            if (!empty($emailBatchForService)) {
                $receiverService = $this->getReceiverService($service->getService());
                $receivers = $receiverService->getReceiverBatch($emailBatchForService, $isServiceSpecificDataRequired);
                foreach ($receivers as $receiver) {
                    $this->addReceiverToBatchOfReceivers($service, $batchOfReceivers, $receiver);
                }
            }
        }

        return array_values($batchOfReceivers);
    }

    /**
     * Retrieves emails for receiver service.
     *
     * @param string $service
     * @param mixed[] $batch
     *
     * @return string[]
     */
    private function getEmailsForService($service, array &$batch)
    {
        $result = array();

        foreach ($batch as $email => $services) {
            if (in_array($service, $services, true)) {
                $result[] = $email;
            }
        }

        return $result;
    }

    /**
     * Prepares receiver from service from export.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService $service
     * @param mixed[] $exportBatch
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return void
     */
    private function addReceiverToBatchOfReceivers(SyncService $service, array &$exportBatch, Receiver $receiver)
    {
        if (!array_key_exists($receiver->getEmail(), $exportBatch)) {
            $exportBatch[$receiver->getEmail()] = $receiver;

            return;
        }

        $this->getMerger($service->getMerger())->merge($receiver, $exportBatch[$receiver->getEmail()]);
    }

    /**
     * Sets removed tags.
     *
     * @param Receiver[] $receivers
     * @param string[] $enabledFields
     *
     * @return void
     */
    private function addModifiers(array $receivers, array $enabledFields)
    {
        $tagModifiers = $this->getTagModifiers();

        foreach ($receivers as $receiver) {
            $receiver->addModifiers($tagModifiers);
            $this->addUnsetModifiers($receiver, $enabledFields);
        }
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     * @param string[] $enabledFields
     *
     * @return void
     */
    protected function addUnsetModifiers(Receiver $receiver, array $enabledFields)
    {
        $globalAttributes = array_keys($receiver->getGlobalAttributes());
        $disabledFields = array_diff($globalAttributes, $enabledFields);
        foreach ($disabledFields as $field) {
            $receiver->addModifier(new UnsetModifier($field));
        }
    }

    /**
     * Retrieves removed tags modifiers.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Modifier[]
     */
    protected function getTagModifiers()
    {
        $result = array();

        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $tagsToRemove = $executionContext->syncConfiguration->getTagsToRemove();

        foreach ($tagsToRemove as $tag) {
            $result[] = new Decrement('tags', (string)$tag);
        }

        return $result;
    }

    /**
     * Unsubscribes blacklisted receivers.
     *
     * @param Receiver[] $receivers
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function unsubscribeBlacklistedReceivers(array &$receivers)
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();

        foreach ($receivers as $receiver) {
            if (!in_array($receiver->getEmail(), $executionContext->blacklistedEmails, true)) {
                continue;
            }

            if (!empty($executionContext->receiverEmails[$receiver->getEmail()])) {
                foreach ($executionContext->receiverEmails[$receiver->getEmail()] as $serviceId) {
                    $this->unsubscribeReceiver($serviceId, $receiver);
                }
            }

            $this->deactivateReceiver($receiver);
        }
    }

    /**
     * Unsubscribes reciever.
     *
     * @param mixed $serviceId
     * @param Receiver $receiver
     *
     * @return void
     */
    protected function unsubscribeReceiver($serviceId, $receiver)
    {
        $service = $this->getExecutionContext()->services[$serviceId];
        $this->getReceiverService($service->getService())->unsubscribe($receiver);
    }

    /**
     * Unsets exported batch.
     *
     * @param mixed[] $batch
     *
     * @return void
     */
    protected function unsetExportedBatch(array &$batch)
    {
        foreach ($batch as $email => $value) {
            // @phpstan-ignore-next-line
            unset($this->getExecutionContext()->receiverEmails[$email]);
        }
    }

    /**
     * Retrieves current progress.
     *
     * @return float
     */
    protected function getCurrentProgress()
    {
        return ($this->numberOfSynchronizedReceivers * 100.0) / $this->totalNumberOfReceivers;
    }

    /**
     * Retrieves total number of receivers for sync.
     *
     * @return int
     */
    protected function initializeNumberOfReceivers()
    {
        if ($this->totalNumberOfReceivers === 0) {
            /** @var ExecutionContext $executionContext */
            $executionContext = $this->getExecutionContext();
            $this->totalNumberOfReceivers = count($executionContext->receiverEmails);
        }

        return $this->totalNumberOfReceivers ?: 1;
    }

    /**
     * Deactivates receiver.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function deactivateReceiver(Receiver $receiver)
    {
        $receiver->setActivated('0');
    }

    /**
     * @return string[]
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getEnabledFieldNames()
    {
        $fieldNames = array();

        /** @var FieldService $fieldsService */
        $fieldsService = ServiceRegister::getService(FieldService::CLASS_NAME);
        $fields = $fieldsService->getEnabledFields();

        /*** @var FieldMapConfigService */
        $fieldMapService = $this->getFieldMapConfigService();
        $map = $fieldMapService->get()->getItems();
        $fieldMap = array_combine(
            array_map(static function ($item) {
                return $item->getSource()->getName();
            }, $map),
            $map
        ) ?: array();
        foreach ($fields as $field) {
            if (array_key_exists($field->getName(), $fieldMap)) {
                $fieldNames[] = $fieldMap[$field->getName()]->getDestination()->getName();
            } else {
                $fieldNames[] = $field->getName();
            }
        }

        return $fieldNames;
    }

    /**
     * @return FieldMapService
     */
    protected function getFieldMapService()
    {
        /** @var FieldMapService $fieldMapService */
        $fieldMapService = ServiceRegister::getService(FieldMapService::CLASS_NAME);

        return $fieldMapService;
    }

    /**
     * @return FieldMapConfigService
     */
    protected function getFieldMapConfigService()
    {
        /** @var FieldMapConfigService $fieldMapConfigService */
        $fieldMapConfigService = ServiceRegister::getService(FieldMapConfigService::CLASS_NAME);

        return $fieldMapConfigService;
    }
}
