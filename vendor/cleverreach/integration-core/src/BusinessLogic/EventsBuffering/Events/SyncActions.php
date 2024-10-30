<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events;

/**
 * Class SyncActions
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events
 */
class SyncActions
{
    const UPSERT_RECEIVER = 'upsert_receiver';
    const DELETE_RECEIVER = 'delete_receiver';
    const SUBSCRIBE_RECEIVER = 'subscribe_receiver';
    const UNSUBSCRIBE_RECEIVER = 'unsubscribe_receiver';
}
