<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetriveFormException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

abstract class FormService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getConfigManager()->saveConfigValue('defaultFormId', $id);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getConfigManager()->getConfigValue('defaultFormId', '');
    }

    /**
     * @inheritDoc
     */
    public function getForm($formId, $isContentIncluded = false)
    {
        try {
            $form = $this->getProxy()->getForm($formId, $isContentIncluded);
        } catch (\Exception $e) {
            throw new FailedToRetriveFormException($e->getMessage(), $e->getCode());
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function getForms($groupId, $isContentIncluded = false)
    {
        try {
            $forms = $this->getProxy()->getForms($groupId, $isContentIncluded);
        } catch (\Exception $e) {
            throw new FailedToRetriveFormException($e->getMessage(), $e->getCode());
        }

        return $forms;
    }

    /**
     * @inheritDoc
     */
    public function createForm($groupId, $type, array $typeData)
    {
        try {
            $id = $this->getProxy()->createForm($groupId, $type, $typeData);
        } catch (\Exception $e) {
            throw new FailedToCreateFormException($e->getMessage(), $e->getCode());
        }

        return $id;
    }

    /**
     * Retrieves form proxy.
     *
     * @return Proxy
     */
    private function getProxy()
    {
        /** @var Proxy $proxy */
        $proxy = ServiceRegister::getService(Proxy::CLASS_NAME);

        return $proxy;
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
}
