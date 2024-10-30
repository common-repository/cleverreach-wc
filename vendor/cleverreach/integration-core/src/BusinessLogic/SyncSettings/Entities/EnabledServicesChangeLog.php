<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

class EnabledServicesChangeLog extends Entity
{
    const CLASS_NAME =  __CLASS__;

    /**
     * @var \DateTime
     */
    public $createdAt;
    /**
     * @var string
     */
    public $context;
    /**
     * @var mixed[]
     */
    public $services;
    /**
     * @var string[]
     */
    protected $fields = array('id', 'createdAt', 'services', 'context');

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addDateTimeIndex('createdAt');
        $indexMap->addStringIndex('context');

        return new EntityConfiguration($indexMap, 'EnabledServicesChangeLog');
    }
}
