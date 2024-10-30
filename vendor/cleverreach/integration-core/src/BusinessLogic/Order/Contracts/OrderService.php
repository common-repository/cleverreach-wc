<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts;

/**
 * Class OrderService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts
 */
interface OrderService
{
    /**
     * Class name.
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Checks whether order items can be synced or not.
     *
     * @return bool Flag that indicates whether order items can be synced or not.
     */
    public function canSynchronizeOrderItems();

    /**
     * Retrieves list of order items for a given order id.
     *
     * @param string | int $orderId Order identificator.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem[]
     */
    public function getOrderItems($orderId);

    /**
     * Provides order source that will be attached to receiver during export.
     *
     * @param mixed $orderId
     *
     * @return string
     */
    public function getOrderSource($orderId);
}
