<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;

class SyncSettings extends DataTransferObject
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var string[]
     */
    public $import;
    /**
     * @var string[]
     */
    public $notImport;

    /**
     * SyncSettings constructor.
     *
     * @param string[] $import
     * @param string[] $notImport
     */
    public function __construct(array $import, array $notImport)
    {
        $this->import = $import;
        $this->notImport = $notImport;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'import' => $this->import,
            'not_import' => $this->notImport,
        );
    }

    /**
     * @inheritDoc
     *
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO\SyncSettings
     */
    public static function fromArray(array $data)
    {
        return new static($data['import'], $data['not_import']);
    }
}
