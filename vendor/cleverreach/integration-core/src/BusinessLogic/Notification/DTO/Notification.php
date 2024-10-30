<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class Notification extends DataTransferObject
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
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $url;
    /**
     * @var \DateTime
     */
    private $date;

    /**
     * Notification constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

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
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return void
     */
    public function setDate($date)
    {
        $this->date = $date;
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
            'url' => $this->getUrl(),
            'date' => $this->getDate(),
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
        $entity = new static(self::getDataValue($data, 'id'), self::getDataValue($data, 'name'));

        $entity->setDescription(self::getDataValue($data, 'description'));
        $entity->setUrl(self::getDataValue($data, 'url'));
        $entity->setDate(self::getDataValue($data, 'date', null));

        return $entity;
    }
}
