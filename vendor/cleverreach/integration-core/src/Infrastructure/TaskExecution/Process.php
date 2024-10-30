<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution;

use InvalidArgumentException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\Runnable;

/**
 * Class Process
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entities
 */
class Process extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Unique identifier.
     *
     * @var string
     */
    protected $guid;
    /**
     * Runnable instance.
     *
     * @var Runnable
     */
    protected $runner;

    /**
     * @inheritDoc
     *
     * @throws \InvalidArgumentException In case when @see $data does not have all needed keys.
     */
    public function inflate(array $data)
    {
        if (!isset($data['guid'], $data['runner'])) {
            throw new InvalidArgumentException('Data array needs to have "guid" and "runner" keys.');
        }

        parent::inflate($data);
        $this->setGuid($data['guid']);
        $this->setRunner(Serializer::unserialize($data['runner']));
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['guid'] = $this->getGuid();
        $data['runner'] = Serializer::serialize($this->getRunner());

        return $data;
    }

    /**
     * Returns entity configuration object
     *
     * @return EntityConfiguration
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('guid');

        return new EntityConfiguration($indexMap, 'Process');
    }

    /**
     * Gets Guid.
     *
     * @return string Guid.
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Sets Guid.
     *
     * @param string $guid Guid.
     *
     * @return void
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Gets Runner.
     *
     * @return Runnable Runner.
     */
    public function getRunner()
    {
        return $this->runner;
    }

    /**
     * Sets Runner.
     *
     * @param Runnable $runner Runner.
     *
     * @return void
     */
    public function setRunner(Runnable $runner)
    {
        $this->runner = $runner;
    }
}
