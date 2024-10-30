<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class SnapshotService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats
 */
class SnapshotService implements Contracts\SnapshotService
{
    const DEFAULT_INTERVAL = 30;

    /**
     * @var StatsService
     */
    protected $statsService;

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken
     * @throws \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function createSnapshot()
    {
        $entity = new Stats();
        $entity->setContext(static::getConfigManager()->getContext());
        $entity->setCreatedAt(new \DateTime());
        $entity->setSubscribed($this->getStatsService()->getSubscribed());
        $entity->setUnsubscribed($this->getStatsService()->getUnsubscribed());

        $this->getRepository()->save($entity);
    }

    /**
     * @inheritDoc
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats[]
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getSnapshots()
    {
        $filter = new QueryFilter();
        $filter->where('context', Operators::EQUALS, static::getConfigManager()->getContext())
            ->setLimit($this->getInterval())
            ->orderBy('createdAt');

        /** @var Stats[] $stats */
        $stats = $this->getRepository()->select($filter);

        return $stats;
    }

    /**
     * @inheritDoc
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function remove()
    {
        $days = $this->getInterval();
        $interval = new \DateInterval("P{$days}D");
        $date = new \DateTime();
        $date->sub($interval);

        $filter = new QueryFilter();
        $filter->where('context', Operators::EQUALS, static::getConfigManager()->getContext())
            ->where('createdAt', Operators::LESS_THAN, $date);

        /** @var ConditionallyDeletes $repository */
        $repository = $this->getRepository();
        $repository->deleteWhere($filter);
    }

    /**
     * @inheritDoc
     * @return int|mixed|null
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function getInterval()
    {
        return static::getConfigManager()->getConfigValue('statsInterval', static::DEFAULT_INTERVAL);
    }

    /**
     * @inheritDoc
     * @param int $days
     *
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    public function setInterval($days)
    {
        static::getConfigManager()->saveConfigValue('statsInterval', $days);
    }

    /**
     * @return StatsService
     */
    protected function getStatsService()
    {
        if (!$this->statsService) {
            /** @var StatsService $statsService */
            $statsService = ServiceRegister::getService(StatsService::CLASS_NAME);
            $this->statsService = $statsService;
        }

        return $this->statsService;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function getRepository()
    {
        $repository = RepositoryRegistry::getRepository(Stats::getClassName());
        if (!($repository instanceof ConditionallyDeletes)) {
            throw new RepositoryClassException('Stats repository must be instance of the ConditionallyDeletes interface');
        }

        return $repository;
    }

    /**
     * @return ConfigurationManager
     */
    protected static function getConfigManager()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);

        return $configurationManager;
    }
}
