<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteCartException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateCartException;

/**
 * Interface CartAutomationService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces
 */
interface CartAutomationService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Creates cart automation.
     *
     * @param string $storeId
     * @param string $name
     * @param string $source
     * @param mixed[] $settings
     *
     * @return CartAutomation
     *
     * @throws FailedToCreateCartException
     */
    public function create($storeId, $name, $source, array $settings);

    /**
     * Updates cart.
     *
     * @param CartAutomation $cart
     *
     * @return CartAutomation
     *
     * @throws FailedToUpdateCartException
     */
    public function update(CartAutomation $cart);

    /**
     * Provides cart identified by id.
     *
     * @param int $id
     *
     * @return CartAutomation | null
     */
    public function find($id);

    /**
     * Provides carts identified by query.
     *
     * @param array<string, mixed> $query
     *
     * @return CartAutomation[]
     */
    public function findBy(array $query);

    /**
     * Deletes cart identified by id.
     *
     * @param int $id
     *
     * @return void
     *
     * @throws FailedToDeleteCartException
     */
    public function delete($id);

    /**
     * Deletes carts identified by query.
     *
     * @param array<string, mixed> $query
     *
     * @return void
     *
     * @throws FailedToDeleteCartException
     */
    public function deleteBy(array $query);
}
