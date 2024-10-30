<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormCacheService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\AfterFormCacheCreatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\AfterFormCacheDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\AfterFormCacheUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\BeforeFormCacheCreatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\BeforeFormCacheDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\BeforeFormCacheUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Events\FormEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToCreateFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToDeleteFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToRetrieveFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Exceptions\FailedToUpdateFormCacheException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class FormCacheService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form
 */
class FormCacheService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function getForm($formId)
    {
        try {
            $query = new QueryFilter();
            $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());
            $query->where('id', Operators::EQUALS, $formId);

            /** @var Form|null $form */
            $form = $this->getFormRepository()->selectOne($query);
        } catch (\Exception $e) {
            throw new FailedToRetrieveFormCacheException($e->getMessage(), $e->getCode());
        }

        if ($form === null) {
            throw new FailedToRetrieveFormCacheException("Form with id [$formId] not found in cache.");
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function getFormByApiId($apiId)
    {
        try {
            $query = new QueryFilter();
            $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());
            $query->where('apiId', Operators::EQUALS, $apiId);

            /** @var Form|null $form */
            $form = $this->getFormRepository()->selectOne($query);
        } catch (\Exception $e) {
            throw new FailedToRetrieveFormCacheException($e->getMessage(), $e->getCode());
        }

        if ($form === null) {
            throw new FailedToRetrieveFormCacheException("Form with id [$apiId] not found in cache.");
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function getForms()
    {
        try {
            $query = new QueryFilter();
            $query->where('context', Operators::EQUALS, $this->getConfigManager()->getContext());

            /** @var Form[] $forms */
            $forms = $this->getFormRepository()->select($query);
        } catch (\Exception $e) {
            throw new FailedToRetrieveFormCacheException($e->getMessage(), $e->getCode());
        }

        return $forms;
    }

    /**
     * @inheritDoc
     */
    public function createForm(Form $form)
    {
        FormEventBus::getInstance()->fire(new BeforeFormCacheCreatedEvent($form));

        try {
            $id = $this->getFormRepository()->save($form);
        } catch (\Exception $e) {
            throw new FailedToCreateFormCacheException($e->getMessage(), $e->getCode());
        }

        $form->setId($id);

        FormEventBus::getInstance()->fire(new AfterFormCacheCreatedEvent($form));

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function updateForm(Form $form)
    {
        FormEventBus::getInstance()->fire(new BeforeFormCacheUpdatedEvent($form));

        try {
            $this->getFormRepository()->update($form);
        } catch (\Exception $e) {
            throw new FailedToUpdateFormCacheException($e->getMessage(), $e->getCode());
        }

        FormEventBus::getInstance()->fire(new AfterFormCacheUpdatedEvent($form));

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function deleteForm(Form $form)
    {
        FormEventBus::getInstance()->fire(new BeforeFormCacheDeletedEvent($form));

        try {
            $this->getFormRepository()->delete($form);
        } catch (\Exception $e) {
            throw new FailedToDeleteFormCacheException($e->getMessage(), $e->getCode());
        }

        FormEventBus::getInstance()->fire(new AfterFormCacheDeletedEvent($form));
    }

    /**
     * Retrieves form repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getFormRepository()
    {
        return RepositoryRegistry::getRepository(Form::getClassName());
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
