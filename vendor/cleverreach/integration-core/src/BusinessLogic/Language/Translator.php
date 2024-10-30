<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Translator
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language
 */
class Translator
{
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService|null
     */
    protected static $translationService;

    /**
     * Translates provided string.
     *
     * @param string $string String to be translated.
     * @param mixed[] $arguments List of translation arguments.
     *
     * @return string Translated string.
     */
    public static function translate($string, array $arguments = array())
    {
        return self::getTranslationService()->translate($string, $arguments);
    }

    /**
     * Retrieves translation service.
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService
     */
    protected static function getTranslationService()
    {
        if (self::$translationService === null) {
            /** @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService $translationService */
            $translationService = ServiceRegister::getService(TranslationService::CLASS_NAME);
            self::$translationService = $translationService;
        }

        return self::$translationService;
    }

    /**
     * Resets translation service instance.
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$translationService = null;
    }
}
