<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\API\Http;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Http\Proxy as BaseProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;

class Proxy extends BaseProxy
{
    const CLASS_NAME = __CLASS__;

    /**
     * Proxy constructor.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        parent::__construct($client, null);
    }

    /**
     * Checks if API is alive.
     *
     * @return bool
     */
    public function isAPIActive()
    {
        try {
            $this->get('debug/ping.json');
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getHeaders()
    {
        return array();
    }
}
