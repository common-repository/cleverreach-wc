<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class AbandonedCartTrigger extends DataTransferObject
{
    /**
     * @var string
     */
    protected $poolId;
    /**
     * @var string
     */
    protected $groupId;
    /**
     * @var string
     */
    protected $cartId;
    /**
     * @var string
     */
    protected $customerId;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartData
     */
    protected $abandonedCartData;

    /**
     * @return string
     */
    public function getPoolId()
    {
        return $this->poolId;
    }

    /**
     * @param string $poolId
     *
     * @return void
     */
    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     *
     * @return void
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @param string $cartId
     *
     * @return void
     */
    public function setCartId($cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     *
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartData
     */
    public function getAbandonedCartData()
    {
        return $this->abandonedCartData;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartData $abandonedCartData
     *
     * @return void
     */
    public function setAbandonedCartData($abandonedCartData)
    {
        $this->abandonedCartData = $abandonedCartData;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'poolid' => $this->getPoolId(),
            'groupid' => $this->getGroupId(),
            'cartId' => $this->getCartId(),
            'customerId' => $this->getCustomerId(),
            'abandonedCartData' => $this->getAbandonedCartData()->toArray(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setPoolId(self::getDataValue($data, 'poolid'));
        $entity->setGroupId(self::getDataValue($data, 'groupid'));
        $entity->setCartId(self::getDataValue($data, 'cartId'));
        $entity->setCustomerId(self::getDataValue($data, 'customerId'));
        $entity->setAbandonedCartData(
            AbandonedCartData::fromArray(self::getDataValue($data, 'abandonedCartData', array()))
        );

        return $entity;
    }
}
