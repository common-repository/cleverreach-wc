<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class AbandonedCart extends DataTransferObject
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var int
     */
    protected $lastExecuted;
    /**
     * @var bool
     */
    protected $active;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getLastExecuted()
    {
        return $this->lastExecuted;
    }

    /**
     * @param int $lastExecuted
     *
     * @return void
     */
    public function setLastExecuted($lastExecuted)
    {
        $this->lastExecuted = $lastExecuted;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'lastExecuted' => $this->getLastExecuted(),
            'active' => $this->isActive(),
        );
    }

    /**
     * Creates instance from an array.
     *
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCart
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setId(static::getDataValue($data, 'id'));
        $entity->setName(static::getDataValue($data, 'name'));
        $entity->setDescription(static::getDataValue($data, 'description'));
        $entity->setType(static::getDataValue($data, 'type'));
        $entity->setLastExecuted(static::getDataValue($data, 'lastExecuted', 0));
        $entity->setActive(static::getDataValue($data, 'active', false));

        return $entity;
    }
}
