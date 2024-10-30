<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Trigger
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO
 */
class Trigger extends DataTransferObject
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
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\AbandonedCart
     */
    protected $cart;

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
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\AbandonedCart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\AbandonedCart $cart
     *
     * @return void
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'poolid' => $this->getPoolId(),
            'groupid' => $this->getGroupId(),
            'abandonedCartData' => $this->getCart()->toArray(),
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\DTO\Trigger
     */
    public static function fromArray(array $data)
    {
        $entity = new static();
        $entity->setPoolId(self::getDataValue($data, 'poolid'));
        $entity->setGroupId(self::getDataValue($data, 'groupid'));
        $entity->setCart(
            AbandonedCart::fromArray(self::getDataValue($data, 'abandonedCartData', array()))
        );

        return $entity;
    }
}
