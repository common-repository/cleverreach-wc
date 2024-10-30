<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification;

/**
 * Interface NotificationService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts
 */
interface NotificationService
{
    const CLASS_NAME = __CLASS__;

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\DTO\Notification $notification
     *
     * @return void
     */
    public function push(Notification $notification);
}
