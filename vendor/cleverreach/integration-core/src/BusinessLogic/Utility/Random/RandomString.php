<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Random;

/**
 * Class RandomString
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Utility\Random
 */
class RandomString
{
    /**
     * @var string
     */
    private static $CHAR_SET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generates a random string
     *
     * @param int $length
     *
     * @return string
     */
    public static function generate($length = 32)
    {
        $result = '';

        $charSetLength = strlen(self::$CHAR_SET);
        for ($i = 0; $i < $length; $i++) {
            /** @noinspection RandomApiMigrationInspection */
            $result .= self::$CHAR_SET[rand(0, $charSetLength - 1)];
        }

        return $result;
    }
}
