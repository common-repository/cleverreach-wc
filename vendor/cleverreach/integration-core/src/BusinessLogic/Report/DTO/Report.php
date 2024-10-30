<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class Report
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO
 */
class Report extends DataTransferObject
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $senderName;
    /**
     * @var string
     */
    protected $senderEmail;
    /**
     * @var \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Stats[]
     */
    protected $stats;

    /**
     * Report constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param string $senderName
     *
     * @return void
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * @param string $senderEmail
     *
     * @return void
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    }

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Stats[]
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Stats[] $stats
     *
     * @return void
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    /**
     * @param string $key
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Stats|null
     */
    public function getSpecificStats($key)
    {
        return static::getDataValue($this->stats, $key, null);
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Report\DTO\Report
     */
    public static function fromArray(array $data)
    {
        $report = new static(static::getDataValue($data, 'id'), static::getDataValue($data, 'name'));

        $report->senderName = static::getDataValue($data, 'sender_name');
        $report->senderEmail = static::getDataValue($data, 'sender_email');
        if (!empty($data['stats'])) {
            $report->stats = Stats::fromBatch($data['stats']);
        }

        return $report;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $stats = array();
        foreach ($this->stats as $key => $specificStats) {
            $stats[$key] = $specificStats->toArray();
        }

        return array(
            'id' => $this->id,
            'name' => $this->name,
            'sender_name' => $this->senderName,
            'sender_email' => $this->senderEmail,
            'stats' => $stats,
        );
    }
}
