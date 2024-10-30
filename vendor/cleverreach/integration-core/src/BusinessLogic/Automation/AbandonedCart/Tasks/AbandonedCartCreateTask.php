<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Tasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartEntityService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts\AbandonedCartService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartSubmit;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

class AbandonedCartCreateTask extends Task
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates abandoned cart chain on clever reach.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Exceptions\FailedToCreateAbandonedCartException
     */
    public function execute()
    {
        $service = $this->getService();
        $storeId = $service->getStoreId();
        $cartData = new AbandonedCartSubmit($service->getAutomationName(), $storeId);
        $cartData->setSource($this->getConfigService()->getIntegrationName());

        $cart = $service->create($cartData);
        $this->reportProgress(70);
        $entityService = $this->getEntityService();
        $entityService->set($cart);
        $entityService->setStoreId($storeId);
        $this->reportProgress(100);
    }

    /**
     * Retrieves abandoned cart service.
     *
     * @return AbandonedCartService
     */
    private function getService()
    {
        /** @var AbandonedCartService $service */
        $service = ServiceRegister::getService(AbandonedCartService::CLASS_NAME);

        return $service;
    }

    /**
     * Retrieves abandoned cart entity service.
     *
     * @return AbandonedCartEntityService
     */
    private function getEntityService()
    {
        /** @var AbandonedCartEntityService $entityService */
        $entityService = ServiceRegister::getService(AbandonedCartEntityService::CLASS_NAME);

        return $entityService;
    }
}
