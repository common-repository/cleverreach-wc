<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Components;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BlacklistFilter\Contracts\BlacklistFilterService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Context\ExecutionContext;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class ReceiverEmailsResolver extends ReceiverSyncSubTask
{
    const CLASS_NAME = __CLASS__;
    /**
     * List of SyncService::uuids.
     * List contains only services whose receiver emails are yet to be resolved.
     *
     * @var string []
     */
    private $receiverSources;

    /**
     * ReceiverEmailsResolver constructor.
     */
    public function __construct()
    {
        $this->receiverSources = array();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize($this->receiverSources);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->receiverSources = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array('receiverSources' => $this->receiverSources);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        $static = new static();

        $static->receiverSources = $data['receiverSources'];

        return $static;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $specificReceivers = $executionContext->syncConfiguration->getSpecificReceivers();

        if (!empty($specificReceivers)) {
            $this->prepareSpecificReceivers($specificReceivers);
        } else {
            $this->prepareAllReceivers();
        }

        $executionContext->receiverEmails = $this->getBlacklistEmailService()->filterEmails($executionContext->receiverEmails);

        $this->reportProgress(100);
    }

    /**
     * Prepares specific receivers for synchronization.
     *
     * @param string[] $specificReceivers
     *
     * @return void
     */
    private function prepareSpecificReceivers(array $specificReceivers)
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $serviceIds = array_keys($this->getExecutionContext()->services);

        foreach ($specificReceivers as $receiver) {
            $executionContext->receiverEmails[$receiver] = $serviceIds;
        }
    }

    /**
     * Prepares all available receiver emails.
     *
     * @return void
     */
    protected function prepareAllReceivers()
    {
        // We are caching list of available receiver services in a serializable variable
        // In order to prevent receiver email retrieval from the same service
        // In case of a subsequent executions
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();
        $resolvedEmails = $executionContext->receiverEmails;
        if (empty($this->receiverSources) && empty($resolvedEmails)) {
            $this->receiverSources = array_keys($this->getExecutionContext()->services);
            $this->reportAlive();
        }

        foreach ($this->receiverSources as $key => $serviceId) {
            $this->retrieveEmailsForService($serviceId);

            unset($this->receiverSources[$key]);

            $this->reportAlive();
        }
    }

    /**
     * Retrieves list of available emails in a service.
     *
     * @param string $serviceId
     *
     * @return void
     */
    protected function retrieveEmailsForService($serviceId)
    {
        $service = $this->getExecutionContext()->services[$serviceId];
        $emails = $this->getReceiverService($service->getService())->getReceiverEmails();
        $this->appendReceiverEmails($serviceId, $emails);
    }

    /**
     * Appends list of receiver emails to the already available list of receiver emails for synchronization.
     *
     * @param string $serviceId
     * @param string[] $emails
     *
     * @return void
     */
    protected function appendReceiverEmails($serviceId, &$emails)
    {
        /** @var ExecutionContext $executionContext */
        $executionContext = $this->getExecutionContext();

        foreach ($emails as $email) {
            $executionContext->receiverEmails[$email][] = $serviceId;
        }
    }

    /**
     * @return BlacklistFilterService
     */
    protected function getBlacklistEmailService()
    {
        /** @var BlacklistFilterService $blacklistFilterService */
        $blacklistFilterService = ServiceRegister::getService(BlacklistFilterService::CLASS_NAME);

        return $blacklistFilterService;
    }
}
