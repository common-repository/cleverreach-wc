<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class Form extends DataTransferObject
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * Synonymous with group id.
     *
     * @var string
     */
    private $customerTableId;
    /**
     * @var string
     */
    private $content;

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
    public function getCustomerTableId()
    {
        return $this->customerTableId;
    }

    /**
     * @param string $customerTableId
     *
     * @return void
     */
    public function setCustomerTableId($customerTableId)
    {
        $this->customerTableId = $customerTableId;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'customer_tables_id' => $this->getCustomerTableId(),
            'content' => $this->getContent(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\DTO\Form
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setId(static::getDataValue($data, 'id'));
        $entity->setName(static::getDataValue($data, 'name'));
        $entity->setCustomerTableId(static::getDataValue($data, 'customer_tables_id'));
        $entity->setContent(static::getDataValue($data, 'content'));

        return $entity;
    }
}
