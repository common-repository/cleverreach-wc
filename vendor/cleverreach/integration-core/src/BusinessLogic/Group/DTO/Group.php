<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Data\TimestampsAware;

class Group extends TimestampsAware
{
    const CLASS_NAME = __CLASS__;
    /**
     * Group id.
     *
     * @var string
     */
    protected $id;
    /**
     * Group name.
     *
     * @var string
     */
    protected $name;
    /**
     * Group locked state.
     *
     * @var bool
     */
    protected $locked;
    /**
     * Group backup state.
     *
     * @var bool
     */
    protected $backup;
    /**
     * Group receiver info.
     *
     * @var string
     */
    protected $receiverInfo;
    /**
     * Group created at timestamp.
     *
     * @var \DateTime
     */
    protected $createdAt;
    /**
     * Group last mailing timestamp.
     *
     * @var \DateTime
     */
    protected $lastMailingTime;
    /**
     * Group last changed timestamp.
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return void
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return bool
     */
    public function isBackup()
    {
        return $this->backup;
    }

    /**
     * @param bool $backup
     *
     * @return void
     */
    public function setBackup($backup)
    {
        $this->backup = $backup;
    }

    /**
     * @return string
     */
    public function getReceiverInfo()
    {
        return $this->receiverInfo;
    }

    /**
     * @param string $receiverInfo
     *
     * @return void
     */
    public function setReceiverInfo($receiverInfo)
    {
        $this->receiverInfo = $receiverInfo;
    }

    /**
     * @return \DateTime | null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime | null
     */
    public function getLastMailingTime()
    {
        return $this->lastMailingTime;
    }

    /**
     * @param \DateTime $lastMailingTime
     *
     * @return void
     */
    public function setLastMailingTime($lastMailingTime)
    {
        $this->lastMailingTime = $lastMailingTime;
    }

    /**
     * @return \DateTime | null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\DTO\Group Group instance.
     */
    public static function fromArray(array $data)
    {
        $group = new self();
        $group->setId(self::getDataValue($data, 'id'));
        $group->setName(self::getDataValue($data, 'name'));
        $group->setLocked(self::getDataValue($data, 'locked', false));
        $group->setBackup(self::getDataValue($data, 'backup', true));
        $group->setReceiverInfo(self::getDataValue($data, 'receiver_info'));
        $group->setCreatedAt(static::getDate(self::getDataValue($data, 'stamp', null)));
        $group->setLastMailingTime(static::getDate(self::getDataValue($data, 'last_mailing', null)));
        $group->setUpdatedAt(static::getDate(self::getDataValue($data, 'last_changed', null)));

        return $group;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'locked' => $this->isLocked(),
            'backup' => $this->isBackup(),
            'receiver_info' => $this->getReceiverInfo(),
            'stamp' => static::getTimestamp($this->getCreatedAt()),
            'last_mailing' => static::getTimestamp($this->getLastMailingTime()),
            'last_changed' => static::getTimestamp($this->getUpdatedAt()),
        );
    }
}
