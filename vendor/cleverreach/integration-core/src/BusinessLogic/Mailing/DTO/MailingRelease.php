<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

/**
 * Class MailingRelease
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\DTO
 */
class MailingRelease extends DataTransferObject
{
    /**
     * @var \DateTime
     */
    protected $startTime;

    /**
     * MailingRelease constructor.
     *
     * @param \DateTime $startTime
     */
    public function __construct(\DateTime $startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'time' => $this->startTime->getTimestamp(),
        );
    }
}
