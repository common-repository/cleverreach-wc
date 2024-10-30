<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;

class AbandonedCartRecord extends Entity
{
    const CLASS_NAME = __CLASS__;
    /**
     * @var string $context
     */
    protected $context;
    /**
     * @var string
     */
    protected $groupId;
    /**
     * @var string
     */
    protected $poolId;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $cartId;
    /**
     * @var string
     */
    protected $customerId;
    /**
     * @var int
     */
    protected $scheduleId;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger
     */
    protected $trigger;

    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array(
        'id',
        'context',
        'groupId',
        'poolId',
        'email',
        'cartId',
        'customerId',
        'scheduleId',
        'trigger',
    );

    /**
     * Retrieves context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets context.
     *
     * @param string $context
     *
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     *
     * @return void
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getPoolId()
    {
        return $this->poolId;
    }

    /**
     * @param string $poolId
     *
     * @return void
     */
    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @param string $cartId
     *
     * @return void
     */
    public function setCartId($cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     *
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getScheduleId()
    {
        return $this->scheduleId;
    }

    /**
     * @param int $scheduleId
     *
     * @return void
     */
    public function setScheduleId($scheduleId)
    {
        $this->scheduleId = $scheduleId;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Automation\AbandonedCart\DTO\AbandonedCartTrigger $trigger
     *
     * @return void
     */
    public function setTrigger($trigger)
    {
        $this->trigger = $trigger;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'context' => $this->getContext(),
            'groupId' => $this->getGroupId(),
            'poolId' => $this->getPoolId(),
            'email' => $this->getEmail(),
            'cartId' => $this->getCartId(),
            'customerId' => $this->getCustomerId(),
            'scheduleId' => $this->getScheduleId(),
            'trigger' => $this->getTrigger()->toArray(),
        );
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);
        $this->setTrigger(AbandonedCartTrigger::fromArray($data['trigger']));
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('groupId');
        $indexMap->addStringIndex('poolId');
        $indexMap->addStringIndex('email');
        $indexMap->addStringIndex('cartId');
        $indexMap->addIntegerIndex('scheduleId');
        $indexMap->addStringIndex('context');

        return new EntityConfiguration($indexMap, 'AbandonedCartRecord');
    }
}
