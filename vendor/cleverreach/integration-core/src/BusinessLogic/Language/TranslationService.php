<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService as BaseService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class TranslationService
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language
 */
abstract class TranslationService extends Singleton implements BaseService
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * @inheritDoc
     */
    public function translate($string, array $arguments = array())
    {
        return vsprintf($string, $arguments);
    }
}
