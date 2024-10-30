<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

class AbandonedCartTriggeredLog extends Entity
{
    const CLASS_NAME = __CLASS__;
    /**
     * Cart id.
     *
     * @var string
     */
    protected $cartId;
    /**
     * DateTime when the cart has been triggered and email has been sent.
     *
     * @var \DateTime
     */
    protected $triggeredAt;
    /**
     * User context.
     *
     * @var string
     */
    protected $context;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array(
        'id',
        'cartId',
        'context',
        'triggeredAt',
    );

    /**
     * Retrieves cart id.
     *
     * @return string
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * Sets cart id.
     *
     * @param string $cartId
     *
     * @return void
     */
    public function setCartId($cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * Retrieves triggered at date time.
     *
     * @return \DateTime
     */
    public function getTriggeredAt()
    {
        return $this->triggeredAt;
    }

    /**
     * Sets triggered at date time.
     *
     * @param \DateTime $triggeredAt
     *
     * @return void
     */
    public function setTriggeredAt($triggeredAt)
    {
        $this->triggeredAt = $triggeredAt;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'cartId' => $this->getCartId(),
            'triggeredAt' => $this->getTimeProvider()->serializeDate($this->getTriggeredAt()),
            'context' => $this->getContext(),
        );
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);

        $this->triggeredAt = $this->getTimeProvider()->deserializeDateString($data['triggeredAt']);
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    public function getConfig()
    {
        $map = new IndexMap();
        $map->addStringIndex('cartId');
        $map->addStringIndex('context');

        return new EntityConfiguration($map, 'AbandonedCartTriggerLog');
    }

    /**
     * Retrieves time provider.
     *
     * @return TimeProvider
     */
    private function getTimeProvider()
    {
        /** @var TimeProvider $timeProvider */
        $timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);

        return $timeProvider;
    }
}
