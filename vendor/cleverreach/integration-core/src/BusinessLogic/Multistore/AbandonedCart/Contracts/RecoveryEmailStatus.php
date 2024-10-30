<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts;

/**
 * Interface RecoveryEmailStatus\
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\Contracts
 */
interface RecoveryEmailStatus
{
    const SENT = 'sent';
    const NOT_SENT = 'not_sent';
    const SENDING = 'sending';
    const PENDING = 'pending';
}
