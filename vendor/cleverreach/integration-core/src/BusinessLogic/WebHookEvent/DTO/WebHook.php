<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO;

/**
 * Class WebHook
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\DTO
 */
class WebHook
{
    /**
     * @note Synonymous with group id.
     *
     * @var string
     */
    private $condition;
    /**
     * @var string
     */
    private $event;
    /**
     * @var mixed[]
     */
    private $payload;

    /**
     * WebHook constructor.
     *
     * @param string $condition
     * @param string $event
     * @param mixed[] $payload
     */
    public function __construct($condition, $event, array $payload)
    {
        $this->condition = $condition;
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed[]
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
