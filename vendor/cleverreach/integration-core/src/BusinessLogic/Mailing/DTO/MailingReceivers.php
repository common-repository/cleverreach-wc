<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class MailingReceivers extends DataTransferObject
{
    /**
     * @var mixed[]
     */
    protected $groups = array();
    /**
     * @var string
     */
    protected $filter = '';

    /**
     * Retrieves groups.
     *
     * @return mixed[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Sets groups.
     *
     * @param mixed[] $groups Groups.
     *
     * @return void
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Retrieves filter.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets filter.
     *
     * @param string $filter Filter.
     *
     * @return void
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $result = array();

        if (!empty($this->groups)) {
            $result['groups'] = $this->groups;
        }

        if (!empty($this->filter)) {
            $result['filter'] = $this->filter;
        }

        return $result;
    }

    /**
     * Creates an instance from the array.
     *
     * @inheritDoc
     *
     * @return static Static instance.
     */
    public static function fromArray(array $data)
    {
        $entity = new static;

        $entity->setGroups(static::getDataValue($data, 'groups', array()));
        $entity->setFilter(static::getDataValue($data, 'filter'));

        return $entity;
    }
}
