<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class NPS extends DataTransferObject
{
    /**
     * @var Counts
     */
    protected $counts;
    /**
     * @var int
     */
    protected $nps;

    /**
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\Counts
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Survey\DTO\Counts $counts
     *
     * @return void
     */
    public function setCounts($counts)
    {
        $this->counts = $counts;
    }

    /**
     * @return int
     */
    public function getNps()
    {
        return $this->nps;
    }

    /**
     * @param int $nps
     *
     * @return void
     */
    public function setNps($nps)
    {
        $this->nps = $nps;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'counts' => $this->counts->toArray(),
            'nps' => $this->nps,
        );
    }

    /**
     * @inheritDoc
     *
     * @return NPS
     */
    public static function fromArray(array $data)
    {
        $nps = new static();
        $nps->counts = Counts::fromArray(static::getDataValue($data, 'counts', array()));
        $nps->nps = static::getDataValue($data, 'nps');

        return $nps;
    }
}
