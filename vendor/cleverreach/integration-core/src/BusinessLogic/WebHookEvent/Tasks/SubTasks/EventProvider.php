<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Random\RandomString;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event;

class EventProvider extends SubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $service = $this->getEventsService();

        $event = new Event();
        $event->setEvent($service->getType());
        $event->setUrl($service->getEventUrl());
        $event->setGroupId($this->getGroupService()->getId());

        $token = $service->getVerificationToken();
        if (empty($token)) {
            $token = RandomString::generate();
            $service->setVerificationToken($token);
        }

        $event->setVerificationToken($token);

        $this->getExecutionContext()->setEvent($event);
        $this->reportProgress(100);
    }
}
