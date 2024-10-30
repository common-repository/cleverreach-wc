<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\EntityConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Configuration\IndexMap;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\TimeProvider;

/**
 * Class AutomationRecord
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities
 */
class AutomationRecord extends Entity
{
    const CLASS_NAME = __CLASS__;
    /**
     * Id of the associated cart automation <FK>.
     *
     * @var int
     */
    protected $automationId;
    /**
     * Group that will be used to trigger an automation.
     *
     * @var string
     */
    protected $groupId;
    /**
     * Email of the receiver (poolId can be substituted).
     *
     * @var string
     */
    protected $email;
    /**
     * System cart id.
     *
     * @var string
     */
    protected $cartId;
    /**
     * Id of the associated schedule <FK>
     *
     * @var int
     */
    protected $scheduleId;
    /**
     * @var \DateTime
     */
    protected $scheduledTime;
    /**
     * @var \DateTime
     */
    protected $sentTime;
    /**
     * @var bool
     */
    protected $isRecovered;
    /**
     * @var string
     * @see \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus
     */
    protected $status;
    /**
     * @var float
     */
    protected $amount;
    /**
     * @var string
     */
    protected $errorMessage;
    /**
     * Array of field names.
     *
     * @var string[]
     */
    protected $fields = array(
        'id',
        'automationId',
        'groupId',
        'email',
        'cartId',
        'scheduleId',
        'scheduledTime',
        'sentTime',
        'isRecovered',
        'status',
        'errorMessage',
        'amount',
    );

    /**
     * @return int
     */
    public function getAutomationId()
    {
        return $this->automationId;
    }

    /**
     * @param int $automationId
     *
     * @return void
     */
    public function setAutomationId($automationId)
    {
        $this->automationId = $automationId;
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
     * @return \DateTime
     */
    public function getScheduledTime()
    {
        return $this->scheduledTime;
    }

    /**
     * @param \DateTime $scheduledTime
     *
     * @return void
     */
    public function setScheduledTime($scheduledTime)
    {
        $this->scheduledTime = $scheduledTime;
    }

    /**
     * @return \DateTime
     */
    public function getSentTime()
    {
        return $this->sentTime;
    }

    /**
     * @param \DateTime $sentTime
     *
     * @return void
     */
    public function setSentTime($sentTime)
    {
        $this->sentTime = $sentTime;
    }

    /**
     * @return bool
     */
    public function getIsRecovered()
    {
        return $this->isRecovered;
    }

    /**
     * @param bool $isRecovered
     *
     * @return void
     */
    public function setIsRecovered($isRecovered)
    {
        $this->isRecovered = $isRecovered;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return void
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return void
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['scheduledTime'] = $this->getTimeProvider()->serializeDate($this->scheduledTime);
        $data['sentTime'] = $this->getTimeProvider()->serializeDate($this->sentTime);

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);
        $this->scheduledTime = $this->getTimeProvider()->deserializeDateString($data['scheduledTime']);
        $this->sentTime = $this->getTimeProvider()->deserializeDateString($data['sentTime']);
    }

    /**
     * Returns entity configuration object.
     *
     * @return EntityConfiguration Configuration object.
     */
    public function getConfig()
    {
        $indexMap = new IndexMap();
        $indexMap->addIntegerIndex('automationId');
        $indexMap->addStringIndex('email');
        $indexMap->addStringIndex('cartId');
        $indexMap->addStringIndex('groupId');
        $indexMap->addIntegerIndex('scheduleId');
        $indexMap->addDateTimeIndex('scheduledTime');
        $indexMap->addDateTimeIndex('sentTime');
        $indexMap->addStringIndex('status');
        $indexMap->addBooleanIndex('isRecovered');

        return new EntityConfiguration($indexMap, 'AutomationRecord');
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
