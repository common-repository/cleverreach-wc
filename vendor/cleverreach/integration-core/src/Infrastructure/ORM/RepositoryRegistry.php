<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\ArchivedQueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\ArchivedQueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;

/**
 * Class RepositoryRegistry.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM
 */
class RepositoryRegistry
{
    /**
     * @var RepositoryInterface[]
     */
    protected static $instantiated = array();
    /**
     * @var array<string,string>
     */
    protected static $repositories = array();

    /**
     * Returns an instance of repository that is responsible for handling the entity
     *
     * @param string $entityClass Class name of entity.
     *
     * @return RepositoryInterface
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public static function getRepository($entityClass)
    {
        if (!static::isRegistered($entityClass)) {
            throw new RepositoryNotRegisteredException("Repository for entity $entityClass not found or registered.");
        }

        if (!array_key_exists($entityClass, static::$instantiated)) {
            $repositoryClass = static::$repositories[$entityClass];
            /** @var RepositoryInterface $repository */
            $repository = new $repositoryClass();
            $repository->setEntityClass($entityClass);
            static::$instantiated[$entityClass] = $repository;
        }

        return static::$instantiated[$entityClass];
    }

    /**
     * Registers repository for provided entity class
     *
     * @param string $entityClass Class name of entity.
     * @param string $repositoryClass Class name of repository.
     *
     * @return void
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    public static function registerRepository($entityClass, $repositoryClass)
    {
        if (!is_subclass_of($repositoryClass, RepositoryInterface::CLASS_NAME)) {
            throw new RepositoryClassException("Class $repositoryClass is not implementation of RepositoryInterface.");
        }

        unset(static::$instantiated[$entityClass]);
        static::$repositories[$entityClass] = $repositoryClass;
    }

    /**
     * Checks whether repository has been registered for a particular entity.
     *
     * @param string $entityClass Entity for which check has to be performed.
     *
     * @return boolean Returns TRUE if repository has been registered; FALSE otherwise.
     */
    public static function isRegistered($entityClass)
    {
        return isset(static::$repositories[$entityClass]);
    }

    /**
     * Returns queue item repository.
     *
     * @return QueueItemRepository
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public static function getQueueItemRepository()
    {
        /** @var QueueItemRepository $repository */
        $repository = static::getRepository(QueueItem::getClassName());
        if (!($repository instanceof QueueItemRepository)) {
            throw new RepositoryClassException('Instance class is not implementation of QueueItemRepository');
        }

        return $repository;
    }

    /**
     * Returns archived queue item repository
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\ArchivedQueueItemRepository
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public static function getArchivedQueueItemRepository()
    {
        /** @var ArchivedQueueItemRepository $repository */
        $repository = static::getRepository(ArchivedQueueItem::getClassName());
        if (!($repository instanceof ArchivedQueueItemRepository)) {
            throw new RepositoryClassException('Instance class is not implementation of ArchivedQueueItemRepository');
        }

        return $repository;
    }
}
