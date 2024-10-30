<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts;

/**
 * Interface EventsService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Contracts
 */
interface EventsService
{
    /**
     * Provides url that will listen to web hook requests.
     *
     * @return string
     */
    public function getEventUrl();

    /**
     * Provides event type. One of [form | receiver]
     *
     * @return string
     */
    public function getType();

    /**
     * Provides call token.
     *
     * @return string
     */
    public function getCallToken();

    /**
     * Sets call token.
     *
     * @param mixed $token
     *
     * @return void
     */
    public function setCallToken($token);

    /**
     * Provides secret.
     *
     * @return string
     */
    public function getSecret();

    /**
     * Sets secret.
     *
     * @param mixed $secret
     *
     * @return void
     */
    public function setSecret($secret);

    /**
     * Provides event verification used during the process of event registration.
     *
     * @return string
     */
    public function getVerificationToken();

    /**
     * Sets event verification token.
     *
     * @param string $token
     *
     * @return void
     */
    public function setVerificationToken($token);
}
