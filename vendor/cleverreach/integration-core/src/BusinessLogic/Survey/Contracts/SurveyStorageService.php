<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts;

/**
 * Interface SurveyStorageService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\Contracts
 */
interface SurveyStorageService
{
    const CLASS_NAME = __CLASS__;
    /**
     * Returns last poll ID retrieved from CleverReach poll endpoint.
     *
     * @return string|null Poll ID
     */
    public function getLastPollId();

    /**
     * Sets last poll ID retrieved from CleverReach poll endpoint.
     *
     * @param string $pollId Poll ID
     *
     * @return void
     */
    public function setLastPollId($pollId);

    /**
     * Set flag that indicates that specific survey type is opened
     *
     * @param string $type survey type
     *
     * @return void
     */
    public function setSurveyOpened($type);

    /**
     * Check if survey form is opened for the given type
     *
     * @param string $type
     *
     * @return bool
     */
    public function isSurveyOpened($type);

    /**
     * Return url of plugin with CleverReach poll popup
     *
     * @return string
     */
    public function getPopUpUrl();

    /**
     * Return notification message that will be shown user as system notification
     *
     * @return string
     */
    public function getDefaultMessage();
}
