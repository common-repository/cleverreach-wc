<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\EventRegisterResult;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\EventRegistrator as BaseTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class EventRegistrator
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks
 */
class EventRegistrator extends BaseTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function execute()
    {
        try {
            parent::execute();
        } catch (HttpRequestException $exception) {
            Logger::logWarning("Failed to register event: {$exception->getMessage()}", 'Core');
            $this->setExistingResult();
        }

        $this->reportProgress(100);
    }

    /**
     * Set existing call token and secret as fallback
     *
     * @return void
     */
    protected function setExistingResult()
    {
        $result = new EventRegisterResult();
        $result->setCallToken($this->getEventsService()->getCallToken());
        $result->setSecret($this->getEventsService()->getSecret());

        $this->getExecutionContext()->setEventResult($result);
    }
}
