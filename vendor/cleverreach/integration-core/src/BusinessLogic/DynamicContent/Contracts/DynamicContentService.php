<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\DynamicContent;

interface DynamicContentService
{
    const CLASS_NAME = __CLASS__;

    /**
     * Return list of supported contents
     *
     * @return DynamicContent[]
     */
    public function getSupportedDynamicContent();

    /**
     * Appends created content id to the list
     *
     * @param string $id
     *
     * @return void
     */
    public function addCreatedDynamicContentId($id);

    /**
     * Returns list of created content ids
     *
     * @return string[]
     */
    public function getCreatedDynamicContentIds();

    /**
     * Returns password for the Dynamic Content endpoint
     *
     * @return string
     */
    public function getDynamicContentPassword();

    /**
     * Creates password for the Dynamic Content endpoint
     *
     * @return void
     */
    public function createDynamicContentPassword();
}
