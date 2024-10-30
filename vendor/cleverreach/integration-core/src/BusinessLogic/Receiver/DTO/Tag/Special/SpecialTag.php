<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;

/**
 * Class SpecialTag
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special
 */
abstract class SpecialTag extends Tag
{
    protected $type = 'Special';
}
