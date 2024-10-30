<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\ReceiverService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

abstract class ReceiverService extends Singleton implements BaseService
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Performs subscribe specific actions.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return void
     */
    public function subscribe(Receiver $receiver)
    {
    }

    /**
     * Performs unsubscribe specific actions.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $receiver
     *
     * @return void
     */
    public function unsubscribe(Receiver $receiver)
    {
    }
}
