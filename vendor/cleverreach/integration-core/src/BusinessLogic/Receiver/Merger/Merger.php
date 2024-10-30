<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Singleton;

/**
 * Class Merger
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger
 */
class Merger extends Singleton
{
    const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Performs merge of base fields.
     *
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $from
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver $to
     *
     * @return void
     */
    public function merge(Receiver $from, Receiver $to)
    {
        $to->setSource($from->getSource());
        $to->setActivated($from->getActivated());
        $to->setRegistered($from->getRegistered());
        $to->addTags($from->getTags());
        $to->addModifiers($from->getModifiers());
        $to->setSalutation($from->getSalutation());
        $to->setFirstName($from->getFirstName());
        $to->setLastName($from->getLastName());
        $to->setStreet($from->getStreet());
        $to->setStreetNumber($from->getStreetNumber());
        $to->setZip($from->getZip());
        $to->setCity($from->getCity());
        $to->setCompany($from->getCompany());
        $to->setState($from->getState());
        $to->setCountry($from->getCountry());
        $to->setBirthday($from->getBirthday());
        $to->setPhone($from->getPhone());
        $to->setLanguage($from->getLanguage());
    }
}
