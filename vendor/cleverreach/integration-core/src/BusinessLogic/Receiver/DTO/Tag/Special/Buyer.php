<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special;

/**
 * Class Buyer
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special
 */
class Buyer extends SpecialTag
{
    /**
     * Buyer constructor.
     *
     * @param string $source
     */
    public function __construct($source)
    {
        parent::__construct($source, 'Buyer');
    }
}
