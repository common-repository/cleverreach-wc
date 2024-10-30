<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class ConnectionStatus
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\DTO
 */
class ConnectionStatus extends DataTransferObject
{
    /**
     * @var bool
     */
    protected $isConnected;
    /**
     * @var string
     */
    protected $message;

    /**
     * ConnectionStatus constructor.
     *
     * @param bool $isConnected
     * @param string $message
     */
    public function __construct($isConnected, $message = '')
    {
        $this->isConnected = $isConnected;
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->isConnected;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'isConnected' => $this->isConnected,
            'message' => $this->message,
        );
    }
}
