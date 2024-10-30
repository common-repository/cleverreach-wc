<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

/**
 * Class ConfigEntity.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entities
 */
class ConfigEntity extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Configuration property name.
     *
     * @var string
     */
    protected $name;
    /**
     * Configuration property value.
     *
     * @var mixed
     */
    protected $value;
    /**
     * Configuration context identifier.
     *
     * @var string
     */
    protected $context;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array('id', 'name', 'value', 'context');

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    public function getConfig()
    {
        $map = new IndexMap();
        $map->addStringIndex('name')
            ->addStringIndex('context');

        return new EntityConfiguration($map, 'Configuration');
    }

    /**
     * Gets configuration property name.
     *
     * @return string Configuration property name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets configuration property name.
     *
     * @param string $name Configuration property name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets Configuration property value.
     *
     * @return mixed Configuration property value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets Configuration property value.
     *
     * @param mixed $value Configuration property value.
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Sets context on config entity.
     *
     * @param string $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Retrieves config value context.
     *
     * @return string Context value.
     */
    public function getContext()
    {
        return $this->context;
    }
}
