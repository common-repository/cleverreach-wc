<?php

namespace CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;

/**
 * Class DoubleOptInEmail
 *
 * @package CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO
 */
class DoubleOptInEmail extends DataTransferObject implements Serializable
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var int|string
     */
    protected $formId;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var DoiData
     */
    protected $doiData;

    /**
     * DoubleOptInEmail constructor.
     *
     * @param int|string $formId
     * @param string $type
     * @param string $email
     * @param DoiData $doiData
     */
    public function __construct($formId, $type, $email, DoiData $doiData)
    {
        $this->formId = $formId;
        $this->type = $type;
        $this->email = $email;
        $this->doiData = $doiData;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return Serializer::serialize(array(
            $this->formId,
            $this->type,
            $this->email,
            $this->doiData,
        ));
    }

    /**
     * 2@inheritDoc
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list($this->formId, $this->type, $this->email, $this->doiData) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array(
            'form_id' => $this->formId,
            'type' => $this->type,
            'email' => $this->email,
            'doidata' => $this->doiData->toArray(),
        );
    }

    /**
     * @return int|string
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param int|string $formId
     *
     * @return void
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoiData
     */
    public function getDoiData()
    {
        return $this->doiData;
    }

    /**
     * @param \CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoiData $doiData
     *
     * @return void
     */
    public function setDoiData($doiData)
    {
        $this->doiData = $doiData;
    }

    /**
     * @inheritDoc
     *
     * @return DoubleOptInEmail
     */
    public static function fromArray(array $data)
    {
        return new static(
            static::getDataValue($data, 'form_id'),
            static::getDataValue($data, 'type'),
            static::getDataValue($data, 'email'),
            DoiData::fromArray(static::getDataValue($data, 'doidata', array()))
        );
    }
}
