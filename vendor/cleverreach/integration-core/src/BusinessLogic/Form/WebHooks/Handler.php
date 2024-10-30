<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\WebHooks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\CacheFormsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts\WebHookHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\WebHook;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Exceptions\UnableToHandleWebHookException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\WebHooks
 */
class Handler implements WebHookHandler
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string[]
     */
    protected static $supportedEvents = array(
        'form.created',
        'form.updated',
        'form.deleted',
    );

    /**
     * @inheritDoc
     */
    public function handle(WebHook $hook)
    {
        if (!in_array($hook->getEvent(), static::$supportedEvents, true)) {
            throw new UnableToHandleWebHookException('Event [' . $hook->getEvent() . '] not supported.');
        }

        if ($hook->getCondition() !== $this->getGroupService()->getId()) {
            throw new UnableToHandleWebHookException('Invalid group id.');
        }

        $payload = $hook->getPayload();
        if (empty($payload['form_id'])) {
            throw new UnableToHandleWebHookException('Invalid payload.');
        }

        $queueName = $this->getConfigService()->getDefaultQueueName();
        $context = $this->getConfigManager()->getContext();
        $task = new CacheFormsTask();

        try {
            $this->getQueue()->enqueue($queueName, $task, $context);
        } catch (QueueStorageUnavailableException $e) {
            throw new UnableToHandleWebHookException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return GroupService
     */
    private function getGroupService()
    {
        /** @var GroupService $groupService */
        $groupService = ServiceRegister::getService(GroupService::CLASS_NAME);

        return $groupService;
    }

    /**
     * @return QueueService
     */
    private function getQueue()
    {
        /** @var QueueService $queueService */
        $queueService = ServiceRegister::getService(QueueService::CLASS_NAME);

        return $queueService;
    }

    /**
     * @return Configuration
     */
    private function getConfigService()
    {
        /** @var Configuration $configurationService */
        $configurationService = ServiceRegister::getService(\CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration::CLASS_NAME);

        return $configurationService;
    }

    /**
     * @return ConfigurationManager
     */
    private function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
