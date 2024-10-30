<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks;

class EventRegistrator extends SubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     */
    public function execute()
    {
        $result = $this->getEventsProxy()->registerEvent($this->getExecutionContext()->getEvent());
        $this->getExecutionContext()->setEventResult($result);

        $this->reportProgress(100);
    }
}
