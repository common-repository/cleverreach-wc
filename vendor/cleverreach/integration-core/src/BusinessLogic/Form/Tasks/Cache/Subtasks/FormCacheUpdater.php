<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Tasks\Cache\Subtasks;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormCacheService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

abstract class FormCacheUpdater extends Task
{
    /**
     * @var callable $formsProvider
     */
    protected $formsProvider;

    /**
     * Sets a function that will provide current forms that must be cached.
     *
     * @param callable $formsProvider
     *
     * @return void
     */
    public function setFormsProvider(callable $formsProvider)
    {
        $this->formsProvider = $formsProvider;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form[]
     */
    protected function getForms()
    {
        return call_user_func($this->formsProvider);
    }

    /**
     * @return FormCacheService
     */
    protected function getFromCacheService()
    {
        /** @var FormCacheService $formCacheService */
        $formCacheService = ServiceRegister::getService(FormCacheService::CLASS_NAME);

        return $formCacheService;
    }

    /**
     * @return ConfigurationManager
     */
    protected function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }

    /**
     * Translate dto to entity.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form $dto
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form
     */
    protected function translateDtoToEntity(Form $dto)
    {
        $entity = new \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form();
        $entity->setApiId($dto->getId());
        $entity->setContent($dto->getContent());
        $entity->setContext($this->getConfigManager()->getContext());
        $entity->setCustomerTableId($dto->getCustomerTableId());
        $entity->setName($dto->getName());

        return $entity;
    }
}
