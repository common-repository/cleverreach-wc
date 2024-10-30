<?php

/** @noinspection DuplicatedCode */

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class AutomationSubmit extends DataTransferObject
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $source;

    /**
     * AutomationSubmit constructor.
     *
     * @param string $name
     * @param string $storeId
     */
    public function __construct($name, $storeId)
    {
        $this->name = $name;
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'storeid' => $this->getStoreId(),
            'description' => $this->getDescription(),
            'source' => $this->getSource(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\DTO\AutomationSubmit
     */
    public static function fromArray(array $data)
    {
        $entity = new static(self::getDataValue($data, 'name'), self::getDataValue($data, 'storeid'));
        $entity->setDescription(self::getDataValue($data, 'description'));
        $entity->setSource(self::getDataValue($data, 'source'));

        return $entity;
    }
}
