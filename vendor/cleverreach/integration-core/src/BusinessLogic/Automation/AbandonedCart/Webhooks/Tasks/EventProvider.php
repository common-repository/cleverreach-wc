<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Webhooks\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Random\RandomString;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Tasks\SubTasks\SubTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

class EventProvider extends SubTask
{
    const CLASS_NAME = __CLASS__;

    /**
     * @return void
     */
    public function execute()
    {
        $service = $this->getEventsService();

        $event = new Event();
        $event->setEvent($service->getType());
        $event->setUrl($service->getEventUrl());
        $event->setGroupId($this->getEntityService()->get()->getId());

        $token = $service->getVerificationToken();
        if (empty($token)) {
            $token = RandomString::generate();
            $service->setVerificationToken($token);
        }

        $event->setVerificationToken($token);

        $this->getExecutionContext()->setEvent($event);
        $this->reportProgress(100);
    }

    /**
     * @return AbandonedCartEntityService
     */
    private function getEntityService()
    {
        /** @var AbandonedCartEntityService $entityService */
        $entityService = ServiceRegister::getService(AbandonedCartEntityService::CLASS_NAME);

        return $entityService;
    }
}
