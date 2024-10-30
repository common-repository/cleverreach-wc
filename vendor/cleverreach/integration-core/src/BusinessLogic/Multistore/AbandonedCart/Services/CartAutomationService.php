<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\CartAutomationService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\CreateCartAutomationTask;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;

/**
 * Class CartAutomationService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Services
 */
class CartAutomationService implements BaseService
{
    /**
     * @inheritDoc
     *
     * @throws FailedToCreateCartException
     */
    public function create($storeId, $name, $source, array $settings)
    {
        $cart = new CartAutomation();
        $cart->setStoreId($storeId);
        $cart->setName($name);
        $cart->setSource($source);
        $cart->setSettings($settings);
        $cart->setContext($this->getCurrentContext());
        $cart->setStatus('initialized');
        try {
            $id = $this->getRepository()->save($cart);
            $cart->setId($id);
            $this->enqueue(new CreateCartAutomationTask($id));
        } catch (\Exception $e) {
            throw  new FailedToCreateCartException($e->getMessage(), $e->getCode(), $e);
        }

        return $cart;
    }

    /**
     * Updates cart.
     *
     * @param CartAutomation $cart
     *
     * @return CartAutomation
     *
     * @throws FailedToUpdateCartException
     */
    public function update(CartAutomation $cart)
    {
        try {
            $this->getRepository()->update($cart);
        } catch (\Exception $e) {
            throw new FailedToUpdateCartException($e->getMessage(), $e->getCode(), $e);
        }

        return $cart;
    }

    /**
     * Provides cart automation identified by id.
     *
     * @param int $id
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation | null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function find($id)
    {
        $query = new QueryFilter();
        $query->where('id', Operators::EQUALS, $id);
        $query->where('context', Operators::EQUALS, $this->getCurrentContext());

        /** @var CartAutomation|null $cartAutomation */
        $cartAutomation = $this->getRepository()->selectOne($query);

        return $cartAutomation;
    }

    /**
     * @inheritDoc
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function findBy(array $query)
    {
        $query['context'] = $this->getCurrentContext();
        $queryFilter = new QueryFilter();

        foreach ($query as $column => $value) {
            if ($value === null) {
                $queryFilter->where($column, Operators::NULL);
            } else {
                $queryFilter->where($column, Operators::EQUALS, $value);
            }
        }

        /** @var CartAutomation[] $cartAutomation */
        $cartAutomation = $this->getRepository()->select($queryFilter);

        return $cartAutomation;
    }

    /**
     * Deletes cart identified by id.
     *
     * @param int $id
     *
     * @return void
     *
     * @throws FailedToDeleteCartException
     */
    public function delete($id)
    {
        try {
            if ($cart = $this->find($id)) {
                $this->getRepository()->delete($cart);
            }
        } catch (\Exception $e) {
            throw new FailedToDeleteCartException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes carts identified by query.
     *
     * @inheritDoc
     *
     * @throws FailedToDeleteCartException
     */
    public function deleteBy(array $query)
    {
        try {
            $carts = $this->findBy($query);
            $repository = $this->getRepository();
            foreach ($carts as $cart) {
                $repository->delete($cart);
            }
        } catch (\Exception $e) {
            throw new FailedToDeleteCartException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Provides cart automation repository.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function getRepository()
    {
        return RepositoryRegistry::getRepository(CartAutomation::getClassName());
    }

    /**
     * Provides context.
     *
     * @return string
     */
    protected function getCurrentContext()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager->getContext();
    }

    /**
     * Enqueues cart automation task.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Tasks\Create\CreateCartAutomationTask $task
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    protected function enqueue(CreateCartAutomationTask $task)
    {
        /** @var QueueService $queue */
        $queue = ServiceRegister::getService(QueueService::CLASS_NAME);
        /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Configuration\Configuration $configuration */
        $configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
        $queue->enqueue($configuration->getDefaultQueueName(), $task, $this->getCurrentContext());
    }
}
