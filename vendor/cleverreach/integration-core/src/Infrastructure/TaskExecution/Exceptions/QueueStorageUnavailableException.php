<?php

namespace CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;

/**
 * Class QueueStorageUnavailableException.
 *
 * @package CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions
 */
class QueueStorageUnavailableException extends BaseException
{
    /**
     * QueueStorageUnavailableException constructor.
     *
     * @param string $message Exception message.
     * @param \Throwable $previous Exception instance that was thrown.
     */
    public function __construct($message = '', $previous = null)
    {
        parent::__construct(trim($message . ' Queue storage failed to save item.'), 0, $previous);
    }
}
